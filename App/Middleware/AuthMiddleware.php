<?php
namespace App\Middleware;

use App\Core\App;
use App\Core\Middleware;

class AuthMiddleware extends Middleware {
    protected $actions = [];
    protected $roles = [];

    public function __construct($roles = [], $actions = []) {
        $this->roles = $roles;
        $this->actions = $actions;
    }

    public function execute($next) {
        $session = App::$app->session;
        $user = $session->get('user');
        $currentAction = App::$app->router->currentAction;

        // Check if user is logged in
        if (!$user) {
            $session->setFlash('error', 'Vous devez être connecté pour accéder à cette page');
            return App::$app->response->redirect('/login');
        }

        // Check if action is protected
        if (!empty($this->actions) && !in_array($currentAction, $this->actions)) {
            return $next();
        }

        // Check if user has required role
        if (!empty($this->roles) && !in_array($user['role'], $this->roles)) {
            $session->setFlash('error', 'Vous n\'avez pas les permissions requises');
            return App::$app->response->redirect('/');
        }

        return $next();
    }
}