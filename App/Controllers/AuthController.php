<?php
namespace App\Controllers;

use App\Core\App;
use App\Core\Template;
use App\Core\Validator;
use App\Models\Student;
use App\Models\Admin;
use App\Models\Pilot;
use App\Models\User;
use App\Helpers\SecurityHelper;
use App\Services\EmailService;

class AuthController {
    private $template;
    private $userModel;
    private $emailService;

    public function __construct() {
        $this->template = new Template();
        $this->userModel = new User();
        $this->emailService = new EmailService();
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
        $ip = $request->getClientIp();
        $session = App::$app->session;

        // Validation de base
        $validator = new Validator();
        if (!$validator->validate([
            'email' => $email,
            'password' => $password
        ], [
            'email' => 'required|email',
            'password' => 'required'
        ])) {
            $session->setFlash('error', 'Veuillez remplir tous les champs correctement');
            return App::$app->response->redirect('/login');
        }

        // Vérifier si le compte est verrouillé pour excès de tentatives
        if ($this->userModel->isAccountLocked($email)) {
            $session->setFlash('error', 'Compte temporairement verrouillé suite à trop de tentatives de connexion. Veuillez réessayer plus tard.');
            App::$app->logger->logActivity("Login attempt on locked account: {$email} from IP: {$ip}");
            return App::$app->response->redirect('/login');
        }

        // Vérifier si l'utilisateur existe
        $user = $this->userModel->findByEmail($email);

        // Journaliser la tentative de connexion
        App::$app->db->insert('login_attempts', [
            'ip_address' => $ip,
            'email' => $email,
            'timestamp' => date('Y-m-d H:i:s'),
            'successful' => $user && SecurityHelper::verifyPassword($password, $user['Password']) ? 1 : 0
        ]);

        if (!$user || !SecurityHelper::verifyPassword($password, $user['Password'])) {
            $session->setFlash('error', 'Email ou mot de passe incorrect');
            App::$app->logger->logActivity("Failed login attempt for email: {$email} from IP: {$ip}");
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

        // Journaliser la connexion réussie
        App::$app->logger->logActivity("User logged in: {$user['Email']} (ID: {$user['ID_account']}, Role: {$role})");

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

        // Validation complète
        $validator = new Validator();
        if (!$validator->validate($data, [
            'email' => 'required|email|unique:Account,Email',
            'username' => 'required|min:3|max:32',
            'password' => 'required|min:8|strongPassword:8',
            'password_confirmation' => 'required|confirmed:password'
        ])) {
            $session->setFlash('errors', $validator->getErrors());
            $session->setFlash('old', $data);
            $session->setFlash('error', 'Veuillez corriger les erreurs dans le formulaire');
            return App::$app->response->redirect('/register');
        }

        // Créer un compte utilisateur sécurisé
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
            'promotion' => $data['promotion'] ?? null,
            'school_name' => $data['school_name'] ?? null,
            'study_field' => $data['study_field'] ?? null
        ]);

        // Journaliser la création du compte
        App::$app->logger->logActivity("New student account created: {$data['email']} (ID: {$accountId})");

        // Envoyer un email de bienvenue
        $userData = [
            'Email' => $data['email'],
            'Username' => $data['username']
        ];
        $this->emailService->sendWelcomeEmail($userData);

        $session->setFlash('success', 'Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.');
        return App::$app->response->redirect('/login');
    }

    /**
     * Déconnecte l'utilisateur
     */
    public function logout() {
        $session = App::$app->session;
        $user = $session->get('user');

        if ($user) {
            App::$app->logger->logActivity("User logged out: {$user['email']} (ID: {$user['id']})");
        }

        $session->remove('user');
        $session->destroy();

        return App::$app->response->redirect('/');
    }

    /**
     * Affiche la page de récupération de mot de passe
     */
    public function forgotPassword() {
        $csrf = SecurityHelper::generateCSRFToken();
        return $this->template->renderWithLayout('auth/forgot-password', 'main', [
            'csrf_token' => $csrf
        ]);
    }

    /**
     * Envoie un lien de réinitialisation de mot de passe
     */
    public function sendResetLink() {
        $request = App::$app->request;
        $email = $request->get('email');
        $session = App::$app->session;

        // Validation
        $validator = new Validator();
        if (!$validator->validate(['email' => $email], ['email' => 'required|email'])) {
            $session->setFlash('error', 'Veuillez entrer une adresse email valide');
            return App::$app->response->redirect('/forgot-password');
        }

        // Vérifier si l'email existe
        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            // Ne pas révéler si l'email existe ou non pour des raisons de sécurité
            $session->setFlash('success', 'Si votre adresse email est associée à un compte, vous recevrez un lien de réinitialisation du mot de passe.');
            return App::$app->response->redirect('/forgot-password');
        }

        // Générer un token de réinitialisation
        $token = SecurityHelper::generateToken(32);
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Stocker le token dans la base de données
        $this->userModel->storeResetToken($user['ID_account'], $token, $expiry);

        // Envoyer l'email avec le lien de réinitialisation
        $this->emailService->sendPasswordResetEmail($email, $token);

        // Journaliser la demande
        App::$app->logger->logActivity("Password reset requested for: {$email}");

        $session->setFlash('success', 'Si votre adresse email est associée à un compte, vous recevrez un lien de réinitialisation du mot de passe.');
        return App::$app->response->redirect('/forgot-password');
    }

    /**
     * Affiche la page de réinitialisation de mot de passe
     */
    public function resetPassword() {
        $request = App::$app->request;
        $token = $request->get('token');

        if (!$token) {
            return App::$app->response->redirect('/forgot-password');
        }

        // Vérifier si le token est valide
        $passwordReset = $this->userModel->findByResetToken($token);
        if (!$passwordReset || strtotime($passwordReset['expires_at']) < time()) {
            $session = App::$app->session;
            $session->setFlash('error', 'Ce lien de réinitialisation est invalide ou a expiré.');
            return App::$app->response->redirect('/forgot-password');
        }

        $csrf = SecurityHelper::generateCSRFToken();
        return $this->template->renderWithLayout('auth/reset-password', 'main', [
            'token' => $token,
            'csrf_token' => $csrf
        ]);
    }

    /**
     * Met à jour le mot de passe
     */
    public function updatePassword() {
        $request = App::$app->request;
        $session = App::$app->session;
        $token = $request->get('token');
        $password = $request->get('password');
        $passwordConfirmation = $request->get('password_confirmation');

        // Validation
        $validator = new Validator();
        if (!$validator->validate([
            'password' => $password,
            'password_confirmation' => $passwordConfirmation
        ], [
            'password' => 'required|min:8|strongPassword:8',
            'password_confirmation' => 'required|confirmed:password'
        ])) {
            $session->setFlash('errors', $validator->getErrors());
            return App::$app->response->redirect('/reset-password?token=' . $token);
        }

        // Vérifier si le token est valide
        $passwordReset = $this->userModel->findByResetToken($token);
        if (!$passwordReset || strtotime($passwordReset['expires_at']) < time()) {
            $session->setFlash('error', 'Ce lien de réinitialisation est invalide ou a expiré.');
            return App::$app->response->redirect('/forgot-password');
        }

        // Mettre à jour le mot de passe
        $this->userModel->updatePassword($passwordReset['user_id'], SecurityHelper::hashPassword($password));

        // Supprimer le token utilisé
        $this->userModel->deleteResetToken($token);

        // Journaliser la réinitialisation
        App::$app->logger->logActivity("Password reset successfully for user ID: {$passwordReset['user_id']}");

        $session->setFlash('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
        return App::$app->response->redirect('/login');
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