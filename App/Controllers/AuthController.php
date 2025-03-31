<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Student;
use App\Models\User;
use App\Helpers\SecurityHelper;

class AuthController extends Controller {
    private $userModel;
    private $studentModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->studentModel = new Student();
    }

    /**
     * Affiche la page de connexion
     */
    public function login() {
        // Si l'utilisateur est déjà connecté, le rediriger
        if ($this->isAuthenticated()) {
            $user = $this->getCurrentUser();
            return $this->redirectUserByRole($user['role']);
        }

        // Générer un token CSRF
        $csrf = SecurityHelper::generateCSRFToken();

        return $this->renderWithLayout('auth/login', 'main', [
            'csrf_token' => $csrf
        ]);
    }

    /**
     * Authentifie un utilisateur
     */
    public function authenticate() {
        $email = $this->request->get('email');
        $password = $this->request->get('password');

        // Validation de base
        if (empty($email) || empty($password)) {
            $this->session->setFlash('error', 'Veuillez remplir tous les champs');
            return $this->response->redirect('/login');
        }

        // Vérifier si l'utilisateur existe
        $user = $this->userModel->findByEmail($email);

        if (!$user || !SecurityHelper::verifyPassword($password, $user['Password'])) {
            $this->session->setFlash('error', 'Email ou mot de passe incorrect');
            return $this->response->redirect('/login');
        }

        // Déterminer le type d'utilisateur
        $role = $this->userModel->getRoleById($user['ID_account']);

        // Mettre à jour la date de dernière connexion
        $this->userModel->updateLastLogin($user['ID_account']);

        // Créer la session de l'utilisateur
        $this->session->set('user', [
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
        if ($this->isAuthenticated()) {
            $user = $this->getCurrentUser();
            return $this->redirectUserByRole($user['role']);
        }

        // Générer un token CSRF
        $csrf = SecurityHelper::generateCSRFToken();

        return $this->renderWithLayout('auth/register', 'main', [
            'csrf_token' => $csrf
        ]);
    }

    /**
     * Crée un nouveau compte utilisateur
     */
    public function store() {
        $data = $this->request->getBody();

        // Validation basique
        if (empty($data['email']) || empty($data['username']) || empty($data['password'])) {
            $this->session->setFlash('error', 'Veuillez remplir tous les champs obligatoires');
            return $this->response->redirect('/register');
        }

        // Vérifier que les mots de passe correspondent
        if ($data['password'] !== $data['password_confirmation']) {
            $this->session->setFlash('error', 'Les mots de passe ne correspondent pas');
            return $this->response->redirect('/register');
        }

        // Vérifier si l'email existe déjà
        if ($this->userModel->findByEmail($data['email'])) {
            $this->session->setFlash('error', 'Cette adresse email est déjà utilisée');
            return $this->response->redirect('/register');
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
        $this->studentModel->create($accountId, [
            'Licence' => isset($data['licence']) ? 1 : 0,
            'Majority' => $data['majority'] ?? date('Y-m-d'),
            'promotion' => $data['promotion'] ?? null,
            'school_name' => $data['school_name'] ?? null,
            'study_field' => $data['study_field'] ?? null
        ]);

        $this->session->setFlash('success', 'Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.');
        return $this->response->redirect('/login');
    }

    /**
     * Déconnecte l'utilisateur
     */
    public function logout() {
        $this->session->remove('user');
        $this->session->destroy();

        return $this->response->redirect('/');
    }

    /**
     * Redirige l'utilisateur en fonction de son rôle
     */
    private function redirectUserByRole($role) {
        switch ($role) {
            case 'admin':
                return $this->response->redirect('/admin/dashboard');
            case 'pilot':
                return $this->response->redirect('/pilot/dashboard');
            case 'student':
            default:
                return $this->response->redirect('/student/dashboard');
        }
    }
}