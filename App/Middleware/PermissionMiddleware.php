<?php
namespace App\Middleware;

use App\Core\App;
use App\Core\Middleware;

class PermissionMiddleware extends Middleware {
    private $permissions;

    /**
     * @param array $permissions Les permissions requises (ex: ['view_students', 'edit_companies'])
     */
    public function __construct($permissions = []) {
        $this->permissions = (array) $permissions;
    }

    public function execute($next) {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user) {
            $session->setFlash('error', 'Vous devez être connecté pour accéder à cette page');
            return App::$app->response->redirect('/login');
        }

        // Si aucune permission n'est requise
        if (empty($this->permissions)) {
            return $next();
        }

        // Admin a toutes les permissions
        if ($user['role'] === 'admin') {
            return $next();
        }

        // Charger les permissions de l'utilisateur
        $userPermissions = $this->getUserPermissions($user['id'], $user['role']);

        // Vérifier si l'utilisateur a au moins une des permissions requises
        $hasPermission = false;
        foreach ($this->permissions as $permission) {
            if (in_array($permission, $userPermissions)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            $session->setFlash('error', 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page');

            // Rediriger en fonction du rôle
            if ($user['role'] === 'pilot') {
                return App::$app->response->redirect('/pilot/dashboard');
            } else {
                return App::$app->response->redirect('/student/dashboard');
            }
        }

        return $next();
    }

    /**
     * Récupère les permissions d'un utilisateur en fonction de son rôle
     */
    private function getUserPermissions($userId, $role) {
        // Dans un système plus avancé, ces permissions seraient en base de données
        $rolePermissions = [
            'admin' => [
                'view_students', 'create_students', 'edit_students', 'delete_students',
                'view_pilots', 'create_pilots', 'edit_pilots', 'delete_pilots',
                'view_companies', 'create_companies', 'edit_companies', 'delete_companies',
                'view_internships', 'create_internships', 'edit_internships', 'delete_internships',
                'view_applications', 'manage_applications',
                'view_statistics'
            ],
            'pilot' => [
                'view_students', 'create_students', 'edit_students',
                'view_companies', 'create_companies', 'edit_companies',
                'view_internships', 'create_internships', 'edit_internships',
                'view_applications', 'manage_applications',
                'view_statistics'
            ],
            'student' => [
                'view_companies',
                'view_internships',
                'apply_internships',
                'manage_wishlist',
                'view_own_applications',
                'edit_own_profile'
            ]
        ];

        return $rolePermissions[$role] ?? [];
    }
}