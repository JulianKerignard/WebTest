<?php
/**
 * Fichier de définition des routes pour l'application LeBonPlan
 * Version simplifiée avec regroupement par fonctionnalité
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
    $authMiddleware = new \App\Middleware\AuthMiddleware(['student', 'pilot', 'admin', 'company']);
    $adminMiddleware = new \App\Middleware\AuthMiddleware(['admin']);
    $adminPilotMiddleware = new \App\Middleware\AuthMiddleware(['admin', 'pilot']);
    $studentMiddleware = new \App\Middleware\AuthMiddleware(['student']);
    $companyMiddleware = new \App\Middleware\AuthMiddleware(['company']);

    // Regrouper les routes par fonctionnalité
    registerPublicRoutes($router, $csrfMiddleware);
    registerAuthRoutes($router, $csrfMiddleware);
    registerStudentRoutes($router, $studentMiddleware, $csrfMiddleware);
    registerAdminRoutes($router, $adminMiddleware, $csrfMiddleware);
    registerPilotRoutes($router, $adminPilotMiddleware, $csrfMiddleware);
    registerCompanyRoutes($router, $companyMiddleware, $csrfMiddleware);
    registerApplicationRoutes($router, $authMiddleware, $csrfMiddleware, $studentMiddleware);
}

/**
 * Routes publiques
 */
function registerPublicRoutes($router, $csrfMiddleware) {
    // Page d'accueil
    $router->get('/', [\App\Controllers\HomeController::class, 'index']);

    // Pages légales
    $router->get('/terms', [\App\Controllers\LegalController::class, 'terms']);
    $router->get('/privacy', [\App\Controllers\LegalController::class, 'privacy']);
    $router->get('/mentions', [\App\Controllers\LegalController::class, 'mentions']);

    // Contact
    $router->get('/contact', [\App\Controllers\ContactController::class, 'index']);
    $router->post('/contact/submit', [\App\Controllers\ContactController::class, 'submit'], [$csrfMiddleware]);

    // Recherche et affichage des stages
    $router->get('/stages', [\App\Controllers\InternshipController::class, 'index']);
    $router->get('/stages/{id}', [\App\Controllers\InternshipController::class, 'show']);

    // Recherche et affichage des entreprises
    $router->get('/companies', [\App\Controllers\CompanyController::class, 'index']);
    $router->get('/companies/{id}', [\App\Controllers\CompanyController::class, 'show']);
}

/**
 * Routes d'authentification
 */
function registerAuthRoutes($router, $csrfMiddleware) {
    // Connexion
    $router->get('/login', [\App\Controllers\AuthController::class, 'login']);
    $router->post('/login', [\App\Controllers\AuthController::class, 'authenticate'], [$csrfMiddleware]);

    // Inscription
    $router->get('/register', [\App\Controllers\AuthController::class, 'register']);
    $router->post('/register', [\App\Controllers\AuthController::class, 'store'], [$csrfMiddleware]);

    // Déconnexion (protégée par un middleware d'authentification)
    $router->get('/logout', [\App\Controllers\AuthController::class, 'logout'], [new \App\Middleware\AuthMiddleware()]);

    // Récupération de mot de passe
    $router->get('/forgot-password', [\App\Controllers\AuthController::class, 'forgotPassword']);
    $router->post('/forgot-password', [\App\Controllers\AuthController::class, 'sendResetLink'], [$csrfMiddleware]);
    $router->get('/reset-password/{token}', [\App\Controllers\AuthController::class, 'resetPassword']);
    $router->post('/reset-password', [\App\Controllers\AuthController::class, 'updatePassword'], [$csrfMiddleware]);
}

/**
 * Routes pour les étudiants
 */
function registerStudentRoutes($router, $studentMiddleware, $csrfMiddleware) {
    // Dashboard étudiant
    $router->get('/student/dashboard', [\App\Controllers\StudentController::class, 'dashboard'], [$studentMiddleware]);

    // Profil étudiant
    $router->get('/student/profile', [\App\Controllers\StudentController::class, 'profile'], [$studentMiddleware]);
    $router->post('/student/profile/update', [\App\Controllers\StudentController::class, 'updateProfile'], [$studentMiddleware, $csrfMiddleware]);

    // Candidatures
    $router->get('/student/applications', [\App\Controllers\StudentController::class, 'applications'], [$studentMiddleware]);

    // Wishlist (favoris)
    $router->get('/student/wishlist', [\App\Controllers\WishlistController::class, 'index'], [$studentMiddleware]);
    $router->post('/wishlist/add', [\App\Controllers\WishlistController::class, 'addToWishlist'], [$studentMiddleware, $csrfMiddleware]);
    $router->post('/wishlist/remove', [\App\Controllers\WishlistController::class, 'removeFromWishlist'], [$studentMiddleware, $csrfMiddleware]);
    $router->post('/wishlist/check', [\App\Controllers\WishlistController::class, 'checkWishlist'], [$studentMiddleware, $csrfMiddleware]);
}

/**
 * Routes pour les candidatures
 */
function registerApplicationRoutes($router, $authMiddleware, $csrfMiddleware, $studentMiddleware) {
    // Affichage et gestion des candidatures
    $router->get('/applications', [\App\Controllers\ApplicationController::class, 'index'], [$authMiddleware]);
    $router->get('/applications/{id}', [\App\Controllers\ApplicationController::class, 'show'], [$authMiddleware]);
    $router->post('/applications/update-status', [\App\Controllers\ApplicationController::class, 'updateStatus'], [$authMiddleware, $csrfMiddleware]);
    $router->post('/applications/add-note', [\App\Controllers\ApplicationController::class, 'addNote'], [$authMiddleware, $csrfMiddleware]);
    $router->get('/applications/download-cv/{id}', [\App\Controllers\ApplicationController::class, 'downloadCV'], [$authMiddleware]);

    // Postuler à une offre
    $router->post('/apply', [\App\Controllers\ApplicationController::class, 'apply'], [$studentMiddleware, $csrfMiddleware]);
}

/**
 * Routes pour les administrateurs
 */
function registerAdminRoutes($router, $adminMiddleware, $csrfMiddleware) {
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
}

/**
 * Routes pour les pilotes de promotion
 */
function registerPilotRoutes($router, $adminPilotMiddleware, $csrfMiddleware) {
    // Dashboard pilote
    $router->get('/pilot/dashboard', [\App\Controllers\PilotController::class, 'dashboard'], [$adminPilotMiddleware]);

    // Gestion des étudiants
    $router->get('/pilot/students', [\App\Controllers\PilotController::class, 'students'], [$adminPilotMiddleware]);
    $router->get('/pilot/students/{id}', [\App\Controllers\PilotController::class, 'viewStudent'], [$adminPilotMiddleware]);

    // Gestion des stages
    $router->get('/pilot/internships', [\App\Controllers\PilotController::class, 'internships'], [$adminPilotMiddleware]);

    // Statistiques
    $router->get('/pilot/statistics', [\App\Controllers\PilotController::class, 'statistics'], [$adminPilotMiddleware]);
}

/**
 * Routes pour les entreprises
 */
function registerCompanyRoutes($router, $companyMiddleware, $csrfMiddleware) {
    // Dashboard entreprise
    $router->get('/company/dashboard', [\App\Controllers\CompanyController::class, 'dashboard'], [$companyMiddleware]);

    // Gestion des offres
    $router->get('/company/offers', [\App\Controllers\CompanyController::class, 'offers'], [$companyMiddleware]);
    $router->get('/company/offers/create', [\App\Controllers\CompanyController::class, 'createOffer'], [$companyMiddleware]);
    $router->post('/company/offers/store', [\App\Controllers\CompanyController::class, 'storeOffer'], [$companyMiddleware, $csrfMiddleware]);
    $router->get('/company/offers/{id}', [\App\Controllers\CompanyController::class, 'showOffer'], [$companyMiddleware]);
    $router->post('/company/offers/update/{id}', [\App\Controllers\CompanyController::class, 'updateOffer'], [$companyMiddleware, $csrfMiddleware]);
    $router->post('/company/offers/delete/{id}', [\App\Controllers\CompanyController::class, 'deleteOffer'], [$companyMiddleware, $csrfMiddleware]);

    // Gestion des candidatures
    $router->get('/company/applications', [\App\Controllers\ApplicationController::class, 'companyApplications'], [$companyMiddleware]);
}