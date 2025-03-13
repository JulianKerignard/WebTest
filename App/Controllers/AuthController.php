<?php
namespace App\Controllers;

use App\Core\App;
use App\Core\Template;
use App\Models\Student;
use App\Models\Admin;
use App\Models\Pilot;
use App\Helpers\SecurityHelper;

class AuthController {
    private $template;

    public function __construct() {
        $this->template = new Template();
    }

    public function login() {
        // Affiche la page de connexion
        return $this->template->renderWithLayout('auth/login', 'main');
    }

    public function authenticate() {
        $request = App::$app->request;
        $email = $request->get('email');
        $password = $request->get('password');

        // Validation de base
        if (empty($email) || empty($password)) {
            App::$app->session->setFlash('error', 'Veuillez remplir tous les champs');
            return App::$app->response->redirect('/login');
        }

        // Vérifier si l'utilisateur existe
        $db = App::$app->db;
        $user = $db->fetch("SELECT * FROM Account WHERE Email = ?", [$email]);

        if (!$user || !SecurityHelper::verifyPassword($password, $user['Password'])) {
            App::$app->session->setFlash('error', 'Email ou mot de passe incorrect');
            return App::$app->response->redirect('/login');
        }

        // Déterminer le type d'utilisateur (étudiant, pilote, admin)
        $role = $this->determineUserRole($user['ID_account']);

        // Créer la session de l'utilisateur
        App::$app->session->set('user', [
            'id' => $user['ID_account'],
            'email' => $user['Email'],
            'username' => $user['Username'],
            'role' => $role
        ]);

        // Rediriger vers le tableau de bord approprié
        if ($role === 'admin') {
            return App::$app->response->redirect('/admin/dashboard');
        } else if ($role === 'pilot') {
            return App::$app->response->redirect('/pilot/dashboard');
        } else {
            return App::$app->response->redirect('/student/dashboard');
        }
    }

    public function register() {
        // Affiche la page d'inscription
        return $this->template->renderWithLayout('auth/register', 'main');
    }

    public function store() {
        $request = App::$app->request;
        $data = $request->getBody();

        // Validation
        // TODO: Ajouter une validation plus robuste
        if (empty($data['email']) || empty($data['password']) || empty($data['username'])) {
            App::$app->session->setFlash('error', 'Veuillez remplir tous les champs obligatoires');
            return App::$app->response->redirect('/register');
        }

        // Vérifier si l'email existe déjà
        $existingUser = App::$app->db->fetch("SELECT * FROM Account WHERE Email = ?", [$data['email']]);
        if ($existingUser) {
            App::$app->session->setFlash('error', 'Cet email est déjà utilisé');
            return App::$app->response->redirect('/register');
        }

        // Créer un compte utilisateur
        $accountId = App::$app->db->insert('Account', [
            'Email' => $data['email'],
            'Username' => $data['username'],
            'Password' => SecurityHelper::hashPassword($data['password']),
            'Civility' => $data['civility'] ?? null,
            '_Rank' => 1 // 1 pour étudiant
        ]);

        // Créer un profil étudiant
        $studentModel = new Student();
        $studentModel->create($accountId);

        App::$app->session->setFlash('success', 'Votre compte a été créé avec succès !');
        return App::$app->response->redirect('/login');
    }

    public function logout() {
        App::$app->session->remove('user');
        App::$app->session->destroy();
        return App::$app->response->redirect('/');
    }

    private function determineUserRole($accountId) {
        // Vérifier si c'est un admin
        $admin = App::$app->db->fetch("SELECT * FROM admin WHERE ID_account = ?", [$accountId]);
        if ($admin) {
            return 'admin';
        }

        // Vérifier si c'est un pilote
        $pilot = App::$app->db->fetch("SELECT * FROM pilote WHERE ID_account = ?", [$accountId]);
        if ($pilot) {
            return 'pilot';
        }

        // Par défaut, c'est un étudiant
        return 'student';
    }
}