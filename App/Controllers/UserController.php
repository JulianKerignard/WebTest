<?php
namespace App\Controllers;

use App\Core\App;
use App\Core\Template;
use App\Models\User;
use App\Helpers\SecurityHelper;

class UserController {
    private $template;
    private $userModel;

    public function __construct() {
        $this->template = new Template();
        $this->userModel = new User();
    }

    /**
     * Display profile page
     */
    public function profile() {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user) {
            $session->setFlash('error', 'Vous devez être connecté pour accéder à votre profil');
            return App::$app->response->redirect('/login');
        }

        $userData = $this->userModel->findById($user['id']);

        return $this->template->renderWithLayout('user/profile', 'dashboard', [
            'user' => $userData
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile() {
        $request = App::$app->request;
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour mettre à jour votre profil'
            ], 401);
        }

        $data = $request->getBody();

        // Validate data
        if (empty($data['Username'])) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Le nom d\'utilisateur est obligatoire'
            ], 400);
        }

        // Update user data
        $result = $this->userModel->update($user['id'], [
            'Username' => $data['Username'],
            'Email' => $data['Email'] ?? $user['email'],
            'Civility' => $data['Civility'] ?? null
        ]);

        if ($result) {
            // Update session data
            $userData = $this->userModel->findById($user['id']);
            $session->set('user', [
                'id' => $userData['ID_account'],
                'email' => $userData['Email'],
                'username' => $userData['Username'],
                'role' => $user['role']
            ]);

            return App::$app->response->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès'
            ]);
        } else {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du profil'
            ], 500);
        }
    }

    /**
     * Change password
     */
    public function changePassword() {
        $request = App::$app->request;
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour modifier votre mot de passe'
            ], 401);
        }

        $data = $request->getBody();

        // Validate data
        if (empty($data['current_password']) || empty($data['new_password']) || empty($data['confirm_password'])) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Tous les champs sont obligatoires'
            ], 400);
        }

        if ($data['new_password'] !== $data['confirm_password']) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Les mots de passe ne correspondent pas'
            ], 400);
        }

        // Check current password
        $userData = $this->userModel->findById($user['id']);
        if (!SecurityHelper::verifyPassword($data['current_password'], $userData['Password'])) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Mot de passe actuel incorrect'
            ], 400);
        }

        // Update password
        $result = $this->userModel->updatePassword($user['id'], SecurityHelper::hashPassword($data['new_password']));

        if ($result) {
            return App::$app->response->json([
                'success' => true,
                'message' => 'Mot de passe mis à jour avec succès'
            ]);
        } else {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du mot de passe'
            ], 500);
        }
    }
}