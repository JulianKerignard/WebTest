<?php
namespace App\Core;

use App\Helpers\SecurityHelper;

/**
 * Classe de base pour tous les contrôleurs
 * Fournit des méthodes utilitaires et l'accès aux composants de l'application
 */
class Controller {
    protected $session;
    protected $request;
    protected $response;
    protected $template;

    public function __construct() {
        $this->session = App::$app->session;
        $this->request = App::$app->request;
        $this->response = App::$app->response;
        $this->template = new Template();
    }

    /**
     * Vérifie si l'utilisateur est connecté
     */
    protected function isAuthenticated() {
        return $this->session->get('user') !== null;
    }

    /**
     * Récupère l'utilisateur actuellement connecté
     */
    protected function getCurrentUser() {
        return $this->session->get('user');
    }

    /**
     * Redirige vers la page de connexion avec un message d'erreur
     */
    protected function redirectToLogin($message = 'Vous devez être connecté pour accéder à cette page') {
        $this->session->setFlash('error', $message);
        return $this->response->redirect('/login');
    }

    /**
     * Vérifie si l'utilisateur a le rôle requis
     */
    protected function requireRole($roles) {
        $user = $this->getCurrentUser();

        if (!$user) {
            return $this->redirectToLogin();
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        if (!in_array($user['role'], $roles)) {
            $this->session->setFlash('error', 'Vous n\'avez pas les permissions requises');
            return $this->response->redirect('/');
        }

        return true;
    }

    /**
     * Rendu d'une vue
     */
    protected function render($view, $params = []) {
        return $this->template->render($view, $params);
    }

    /**
     * Rendu d'une vue avec un layout, inclut automatiquement l'utilisateur connecté
     */
    protected function renderWithLayout($view, $layout = 'main', $params = []) {
        $user = $this->getCurrentUser();
        $params['user'] = $user;
        $params['csrf_token'] = SecurityHelper::generateCSRFToken();

        return $this->template->renderWithLayout($view, $layout, $params);
    }

    /**
     * Renvoie une réponse JSON
     */
    protected function json($data, $statusCode = 200) {
        return $this->response->json($data, $statusCode);
    }

    /**
     * Récupère les messages flash et les supprime de la session
     */
    protected function getFlashMessages() {
        $flash = [];
        if ($this->session->hasFlash('success')) {
            $flash['success'] = $this->session->getFlash('success');
        }
        if ($this->session->hasFlash('error')) {
            $flash['error'] = $this->session->getFlash('error');
        }
        if ($this->session->hasFlash('info')) {
            $flash['info'] = $this->session->getFlash('info');
        }
        if ($this->session->hasFlash('warning')) {
            $flash['warning'] = $this->session->getFlash('warning');
        }
        return $flash;
    }
}