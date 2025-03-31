<?php
namespace App\Middleware;

use App\Core\App;
use App\Core\Middleware;

/**
 * Middleware pour contrôler l'accès aux routes protégées
 * Version simplifiée qui vérifie l'authentification et les rôles
 */
class AuthMiddleware extends Middleware {
    protected $roles = [];

    /**
     * Constructeur
     * @param array|string $roles Rôle(s) autorisé(s) pour accéder à la ressource
     */
    public function __construct($roles = []) {
        $this->roles = (array)$roles;
    }

    /**
     * Exécute le middleware
     */
    public function execute($next) {
        $session = App::$app->session;
        $user = $session->get('user');

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            $session->setFlash('error', 'Vous devez être connecté pour accéder à cette page');
            return App::$app->response->redirect('/login');
        }

        // Si aucun rôle n'est spécifié, tout utilisateur connecté est autorisé
        if (empty($this->roles)) {
            return $next();
        }

        // Vérifier si l'utilisateur a le rôle requis
        if (!in_array($user['role'], $this->roles)) {
            $session->setFlash('error', 'Vous n\'avez pas les permissions requises');
            return App::$app->response->redirect('/');
        }

        // Tout est bon, on continue
        return $next();
    }
}