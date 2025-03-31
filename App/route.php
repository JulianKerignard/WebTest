<?php
/**
 * Fichier de définition des routes pour l'application LeBonPlan
 */

namespace App;

use App\Core\App;

/**
 * Configure les routes de l'application
 *
 * @param App $app Instance de l'application
 * @return void
 */
function registerRoutes(App $app) {
    // Récupérer le routeur et les middlewares
    $router = $app->router;

    // Middlewares
    $csrfMiddleware = new \App\Middleware\CsrfMiddleware();
    $authMiddleware = new \App\Middleware\AuthMiddleware(['student', 'pilot', 'admin']);
    $adminMiddleware = new \App\Middleware\AuthMiddleware(['admin']);
    $adminPilotMiddleware = new \App\Middleware\AuthMiddleware(['admin', 'pilot']);
    $studentMiddleware = new \App\Middleware\AuthMiddleware(['student']);

    // --------------------------------
    // Routes publiques
    // --------------------------------

    // Page d'accueil
    $router->get('/', [\App\Controllers\HomeController::class, 'index']);

    // Pages légales
    $router->get('/terms', [\App\Controllers\LegalController::class, 'terms']);
    $router->get('/privacy', [\App\Controllers\LegalController::class, 'privacy']);
    $router->get('/mentions', [\App\Controllers\LegalController::class, 'mentions']);

    // Contact
    $router->get('/contact', [\App\Controllers\ContactController::class, 'index']);
    $router->post('/contact/submit', [\App\Controllers\ContactController::class, 'submit'], [$csrfMiddleware]);

    // --------------------------------
    // Routes d'authentification
    // --------------------------------

    // Connexion
    $router->get('/login', [\App\Controllers\AuthController::class, 'login']);
    $router->post('/login', [\App\Controllers\AuthController::class, 'authenticate'], [$csrfMiddleware]);

    // Inscription
    $router->get('/register', [\App\Controllers\AuthController::class, 'register']);
    $router->post('/register', [\App\Controllers\AuthController::class, 'store'], [$csrfMiddleware]);

    // Déconnexion
    $router->get('/logout', [\App\Controllers\AuthController::class, 'logout'], [$authMiddleware]);

    // --------------------------------
    // Routes de consultation publiques
    // --------------------------------

    // Recherche et affichage des stages
    $router->get('/stages', [\App\Controllers\InternshipController::class, 'index']);
    $router->get('/stages/{id}', [\App\Controllers\InternshipController::class, 'show']);

    // Recherche et affichage des entreprises
    $router->get('/companies', [\App\Controllers\CompanyController::class, 'index']);
    $router->get('/companies/{id}', [\App\Controllers\CompanyController::class, 'show']);

    // --------------------------------
    // Routes étudiant
    // --------------------------------

    // Dashboard étudiant
    $router->get('/student/dashboard', [\App\Controllers\StudentController::class, 'dashboard'], [$studentMiddleware]);

    // Profil étudiant
    $router->get('/student/profile', [\App\Controllers\StudentController::class, 'profile'], [$studentMiddleware]);
    $router->post('/student/profile/update', [\App\Controllers\StudentController::class, 'updateProfile'], [$studentMiddleware, $csrfMiddleware]);

    // Candidatures
    $router->get('/student/applications', [\App\Controllers\StudentController::class, 'applications'], [$studentMiddleware]);
    $router->post('/apply', [\App\Controllers\ApplicationController::class, 'apply'], [$studentMiddleware, $csrfMiddleware]);
    $router->get('/applications/{id}', [\App\Controllers\ApplicationController::class, 'show'], [$authMiddleware]);

    // Wishlist (favoris)
    $router->get('/student/wishlist', [\App\Controllers\WishlistController::class, 'index'], [$studentMiddleware]);
    $router->post('/wishlist/add', [\App\Controllers\WishlistController::class, 'addToWishlist'], [$studentMiddleware, $csrfMiddleware]);
    $router->post('/wishlist/remove', [\App\Controllers\WishlistController::class, 'removeFromWishlist'], [$studentMiddleware, $csrfMiddleware]);

    // --------------------------------
    // Routes pilote de promotion
    // --------------------------------

    // Dashboard pilote
    $router->get('/pilot/dashboard', [\App\Controllers\PilotController::class, 'dashboard'], [$adminPilotMiddleware]);

    // Gestion des étudiants
    $router->get('/pilot/students', [\App\Controllers\PilotController::class, 'students'], [$adminPilotMiddleware]);
    $router->get('/pilot/students/{id}', [\App\Controllers\PilotController::class, 'viewStudent'], [$adminPilotMiddleware]);

    // Gestion des stages
    $router->get('/pilot/internships', [\App\Controllers\PilotController::class, 'internships'], [$adminPilotMiddleware]);

    // --------------------------------
    // Routes administrateur
    // --------------------------------

    // Dashboard admin
    $router->get('/admin/dashboard', [\App\Controllers\AdminController::class, 'dashboard'], [$adminMiddleware]);

    // Gestion des pilotes
    $router->get('/admin/pilots', [\App\Controllers\AdminController::class, 'pilots'], [$adminMiddleware]);
    $router->get('/admin/pilots/create', [\App\Controllers\AdminController::class, 'createPilot'], [$adminMiddleware]);
    $router->post('/admin/pilots/store', [\App\Controllers\AdminController::class, 'storePilot'], [$adminMiddleware, $csrfMiddleware]);
    $router->post('/admin/pilots/delete/{id}', [\App\Controllers\AdminController::class, 'deletePilot'], [$adminMiddleware, $csrfMiddleware]);

    // Gestion des étudiants
    $router->get('/admin/students', [\App\Controllers\AdminController::class, 'students'], [$adminMiddleware]);
    $router->get('/admin/students/create', [\App\Controllers\AdminController::class, 'createStudent'], [$adminMiddleware]);
    $router->post('/admin/students/store', [\App\Controllers\AdminController::class, 'storeStudent'], [$adminMiddleware, $csrfMiddleware]);
    $router->post('/admin/students/delete/{id}', [\App\Controllers\AdminController::class, 'deleteStudent'], [$adminMiddleware, $csrfMiddleware]);

    // Gestion des entreprises
    $router->get('/admin/companies', [\App\Controllers\CompanyController::class, 'adminIndex'], [$adminMiddleware]);
    $router->get('/admin/companies/create', [\App\Controllers\CompanyController::class, 'create'], [$adminMiddleware]);
    $router->post('/admin/companies/store', [\App\Controllers\CompanyController::class, 'store'], [$adminMiddleware, $csrfMiddleware]);
    $router->post('/admin/companies/delete/{id}', [\App\Controllers\CompanyController::class, 'delete'], [$adminMiddleware, $csrfMiddleware]);

    // Gestion des offres de stage
    $router->get('/admin/internships', [\App\Controllers\InternshipController::class, 'adminIndex'], [$adminMiddleware]);
    $router->get('/admin/internships/create', [\App\Controllers\InternshipController::class, 'create'], [$adminMiddleware]);
    $router->post('/admin/internships/store', [\App\Controllers\InternshipController::class, 'store'], [$adminMiddleware, $csrfMiddleware]);
    $router->post('/admin/internships/delete/{id}', [\App\Controllers\InternshipController::class, 'delete'], [$adminMiddleware, $csrfMiddleware]);

    // --------------------------------
    // Routes des erreurs
    // --------------------------------

    $router->get('/404', function() {
        return (new \App\Core\Template())->renderWithLayout('error/404', 'main');
    });
}