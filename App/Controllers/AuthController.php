<?php
namespace App\Controllers;

use App\Core\App;
use App\Core\Template;
use App\Models\Student;
use App\Models\Admin;
use App\Models\Pilot;
use App\Models\User;
use App\Helpers\SecurityHelper;

class AuthController {
    private $template;
    private $userModel;

    public function __construct() {
        $this->template = new Template();
        $this->userModel = new User();
    }

    /**
     * Affiche la page de connexion
     */
    public function login() {
        // Si l'utilisateur est déjà connecté, le rediriger
        $session = App::$app->session;
        $user = $session->get('user');
        if ($user) {
            return $this->redirectUserByRole($user['role']);
        }

        // Affiche la page de connexion
        $csrf = SecurityHelper::generateCSRFToken();
        return $this->template->renderWithLayout('auth/login', 'main', [
            'csrf_token' => $csrf
        ]);
    }

    /**
     * Authentifie un utilisateur
     */
    public function authenticate() {
        $request = App::$app->request;
        $email = $request->get('email');
        $password = $request->get('password');
        $session = App::$app->session;

        // Validation de base
        if (empty($email) || empty($password)) {
            $session->setFlash('error', 'Veuillez remplir tous les champs');
            return App::$app->response->redirect('/login');
        }

        // Vérifier si l'utilisateur existe
        $user = $this->userModel->findByEmail($email);

        if (!$user || !SecurityHelper::verifyPassword($password, $user['Password'])) {
            $session->setFlash('error', 'Email ou mot de passe incorrect');
            return App::$app->response->redirect('/login');
        }

        // Déterminer le type d'utilisateur (étudiant, pilote, admin)
        $role = $this->userModel->getRoleById($user['ID_account']);

        // Mettre à jour la date de dernière connexion
        $this->userModel->updateLastLogin($user['ID_account']);

        // Créer la session de l'utilisateur
        $session->set('user', [
            'id' => $user['ID_account'],
            'email' => $user['Email'],
            'username' => $user['Username'],
            'role' => $role
        ]);

        // Rediriger vers le tableau de bord approprié
        return $this->redirectUserByRole($role);
    }

    /**
     * Affiche la page d'inscription
     */
    public function register() {
        // Si l'utilisateur est déjà connecté, le rediriger
        $session = App::$app->session;
        $user = $session->get('user');
        if ($user) {
            return $this->redirectUserByRole($user['role']);
        }

        // Affiche la page d'inscription
        $csrf = SecurityHelper::generateCSRFToken();
        return $this->template->renderWithLayout('auth/register', 'main', [
            'csrf_token' => $csrf
        ]);
    }

    /**
     * Crée un nouveau compte utilisateur
     */
    public function store() {
        $request = App::$app->request;
        $data = $request->getBody();
        $session = App::$app->session;

        // Validation basique
        if (empty($data['email']) || empty($data['username']) || empty($data['password'])) {
            $session->setFlash('error', 'Veuillez remplir tous les champs obligatoires');
            return App::$app->response->redirect('/register');
        }

        // Vérifier que les mots de passe correspondent
        if ($data['password'] !== $data['password_confirmation']) {
            $session->setFlash('error', 'Les mots de passe ne correspondent pas');
            return App::$app->response->redirect('/register');
        }

        // Vérifier si l'email existe déjà
        if ($this->userModel->findByEmail($data['email'])) {
            $session->setFlash('error', 'Cette adresse email est déjà utilisée');
            return App::$app->response->redirect('/register');
        }

        // Créer un compte utilisateur
        $accountId = $this->userModel->create([
            'Email' => $data['email'],
            'Username' => $data['username'],
            'Password' => SecurityHelper::hashPassword($data['password']),
            'Civility' => $data['civility'] ?? null,
            '_Rank' => 1 // 1 pour étudiant
        ]);

        // Créer un profil étudiant
        $studentModel = new Student();
        $studentModel->create($accountId, [
            'Licence' => isset($data['licence']) ? 1 : 0,
            'Majority' => $data['majority'] ?? date('Y-m-d'),
            'promotion' => $data['promotion'] ?? null
        ]);

        $session->setFlash('success', 'Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.');
        return App::$app->response->redirect('/login');
    }

    /**
     * Déconnecte l'utilisateur
     */
    public function logout() {
        $session = App::$app->session;
        $session->remove('user');
        $session->destroy();

        return App::$app->response->redirect('/');
    }

    /**
     * Redirige l'utilisateur en fonction de son rôle
     */
    private function redirectUserByRole($role) {
        switch ($role) {
            case 'admin':
                return App::$app->response->redirect('/admin/dashboard');
            case 'pilot':
                return App::$app->response->redirect('/pilot/dashboard');
            case 'student':
            default:
                return App::$app->response->redirect('/student/dashboard');
        }
    }
}