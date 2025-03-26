<?php
// Définir le début du temps d'exécution pour mesurer les performances
define('APP_START', microtime(true));

// Charger l'autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Charger les variables d'environnement (si utilisation de dotenv)
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = new \Dotenv\Dotenv(__DIR__ . '/..');
    $dotenv->load();
}

// Charger la configuration
$config = \App\Core\Config::getInstance();

// Définir le fuseau horaire
date_default_timezone_set($config->get('app.timezone', 'Europe/Paris'));

// Initialiser le gestionnaire d'erreurs
\App\Core\ErrorHandler::getInstance();

// Initialiser l'application
$app = new \App\Core\App();

// Créer les middlewares
$csrfMiddleware = new \App\Middleware\CsrfMiddleware();
$authMiddleware = new \App\Middleware\AuthMiddleware(['student', 'pilot', 'admin']);
$adminMiddleware = new \App\Middleware\AuthMiddleware(['admin']);
$adminPilotMiddleware = new \App\Middleware\AuthMiddleware(['admin', 'pilot']);
$studentMiddleware = new \App\Middleware\AuthMiddleware(['student']);

// Définir les routes
// Routes publiques
$app->router->get('/', function() {
    return (new \App\Core\Template())->renderWithLayout('home', 'main');
});

// Routes d'authentification
$app->router->get('/login', [\App\Controllers\AuthController::class, 'login']);
$app->router->post('/login', [\App\Controllers\AuthController::class, 'authenticate'], [$csrfMiddleware]);
$app->router->get('/register', [\App\Controllers\AuthController::class, 'register']);
$app->router->post('/register', [\App\Controllers\AuthController::class, 'store'], [$csrfMiddleware]);
$app->router->get('/logout', [\App\Controllers\AuthController::class, 'logout'], [$authMiddleware]);
$app->router->get('/forgot-password', [\App\Controllers\AuthController::class, 'forgotPassword']);
$app->router->post('/forgot-password', [\App\Controllers\AuthController::class, 'sendResetLink'], [$csrfMiddleware]);
$app->router->get('/reset-password', [\App\Controllers\AuthController::class, 'resetPassword']);
$app->router->post('/reset-password', [\App\Controllers\AuthController::class, 'updatePassword'], [$csrfMiddleware]);

// Routes utilisateur
$app->router->get('/profile', [\App\Controllers\UserController::class, 'profile'], [$authMiddleware]);
$app->router->post('/profile/update', [\App\Controllers\UserController::class, 'updateProfile'], [$authMiddleware, $csrfMiddleware]);
$app->router->post('/profile/change-password', [\App\Controllers\UserController::class, 'changePassword'], [$authMiddleware, $csrfMiddleware]);

// Routes entreprises
$app->router->get('/companies', [\App\Controllers\CompanyController::class, 'index']);
$app->router->get('/companies/{id}', [\App\Controllers\CompanyController::class, 'show']);
$app->router->post('/companies/rate', [\App\Controllers\CompanyController::class, 'rate'], [$studentMiddleware, $csrfMiddleware]);

// Routes stages
$app->router->get('/stages', [\App\Controllers\InternshipController::class, 'index']);
$app->router->get('/stages/{id}', [\App\Controllers\InternshipController::class, 'show']);

// Routes candidatures
$app->router->post('/apply', [\App\Controllers\ApplicationController::class, 'apply'], [$studentMiddleware, $csrfMiddleware]);
$app->router->get('/applications', [\App\Controllers\ApplicationController::class, 'index'], [$studentMiddleware]);
$app->router->get('/applications/{id}', [\App\Controllers\ApplicationController::class, 'show'], [$authMiddleware]);
$app->router->post('/applications/update-status', [\App\Controllers\ApplicationController::class, 'updateStatus'], [$adminPilotMiddleware, $csrfMiddleware]);

// Routes wishlist (favoris)
$app->router->get('/wishlist', [\App\Controllers\WishlistController::class, 'index'], [$studentMiddleware]);
$app->router->post('/wishlist/add', [\App\Controllers\WishlistController::class, 'addToWishlist'], [$studentMiddleware, $csrfMiddleware]);
$app->router->post('/wishlist/remove', [\App\Controllers\WishlistController::class, 'removeFromWishlist'], [$studentMiddleware, $csrfMiddleware]);
$app->router->get('/wishlist/check', [\App\Controllers\WishlistController::class, 'checkWishlist'], [$studentMiddleware]);

// Routes étudiants
$app->router->get('/student/dashboard', [\App\Controllers\StudentController::class, 'dashboard'], [$studentMiddleware]);
$app->router->get('/student/profile', [\App\Controllers\StudentController::class, 'profile'], [$studentMiddleware]);
$app->router->post('/student/profile/update', [\App\Controllers\StudentController::class, 'updateProfile'], [$studentMiddleware, $csrfMiddleware]);
$app->router->get('/student/applications', [\App\Controllers\StudentController::class, 'applications'], [$studentMiddleware]);

// Routes pilotes
$app->router->get('/pilot/dashboard', [\App\Controllers\PilotController::class, 'dashboard'], [$adminPilotMiddleware]);
$app->router->get('/pilot/students', [\App\Controllers\PilotController::class, 'students'], [$adminPilotMiddleware]);
$app->router->get('/pilot/students/{id}', [\App\Controllers\PilotController::class, 'viewStudent'], [$adminPilotMiddleware]);
$app->router->get('/pilot/companies', [\App\Controllers\PilotController::class, 'companies'], [$adminPilotMiddleware]);
$app->router->get('/pilot/internships', [\App\Controllers\PilotController::class, 'internships'], [$adminPilotMiddleware]);
$app->router->get('/pilot/statistics', [\App\Controllers\PilotController::class, 'statistics'], [$adminPilotMiddleware]);

// Routes administrateur
$app->router->get('/admin/dashboard', [\App\Controllers\AdminController::class, 'dashboard'], [$adminMiddleware]);
$app->router->get('/admin/pilots', [\App\Controllers\AdminController::class, 'pilots'], [$adminMiddleware]);
$app->router->get('/admin/pilots/create', [\App\Controllers\AdminController::class, 'createPilot'], [$adminMiddleware]);
$app->router->post('/admin/pilots/store', [\App\Controllers\AdminController::class, 'storePilot'], [$adminMiddleware, $csrfMiddleware]);
$app->router->post('/admin/pilots/delete/{id}', [\App\Controllers\AdminController::class, 'deletePilot'], [$adminMiddleware, $csrfMiddleware]);
$app->router->get('/admin/students', [\App\Controllers\AdminController::class, 'students'], [$adminMiddleware]);
$app->router->get('/admin/students/create', [\App\Controllers\AdminController::class, 'createStudent'], [$adminMiddleware]);
$app->router->post('/admin/students/store', [\App\Controllers\AdminController::class, 'storeStudent'], [$adminMiddleware, $csrfMiddleware]);
$app->router->post('/admin/students/delete/{id}', [\App\Controllers\AdminController::class, 'deleteStudent'], [$adminMiddleware, $csrfMiddleware]);

// Routes administrateur pour les entreprises
$app->router->get('/admin/companies', [\App\Controllers\CompanyController::class, 'adminIndex'], [$adminPilotMiddleware]);
$app->router->get('/admin/companies/create', [\App\Controllers\CompanyController::class, 'create'], [$adminPilotMiddleware]);
$app->router->post('/admin/companies/store', [\App\Controllers\CompanyController::class, 'store'], [$adminPilotMiddleware, $csrfMiddleware]);
$app->router->get('/admin/companies/edit/{id}', [\App\Controllers\CompanyController::class, 'edit'], [$adminPilotMiddleware]);
$app->router->post('/admin/companies/update/{id}', [\App\Controllers\CompanyController::class, 'update'], [$adminPilotMiddleware, $csrfMiddleware]);
$app->router->post('/admin/companies/delete/{id}', [\App\Controllers\CompanyController::class, 'delete'], [$adminPilotMiddleware, $csrfMiddleware]);

// Routes administrateur pour les stages
$app->router->get('/admin/internships', [\App\Controllers\InternshipController::class, 'adminIndex'], [$adminPilotMiddleware]);
$app->router->get('/admin/internships/create', [\App\Controllers\InternshipController::class, 'create'], [$adminPilotMiddleware]);
$app->router->post('/admin/internships/store', [\App\Controllers\InternshipController::class, 'store'], [$adminPilotMiddleware, $csrfMiddleware]);
$app->router->get('/admin/internships/edit/{id}', [\App\Controllers\InternshipController::class, 'edit'], [$adminPilotMiddleware]);
$app->router->post('/admin/internships/update/{id}', [\App\Controllers\InternshipController::class, 'update'], [$adminPilotMiddleware, $csrfMiddleware]);
$app->router->post('/admin/internships/delete/{id}', [\App\Controllers\InternshipController::class, 'delete'], [$adminPilotMiddleware, $csrfMiddleware]);

// Définir une route pour les erreurs 404
$app->router->get('/404', function() {
    return (new \App\Core\Template())->renderWithLayout('error/404', 'main');
});

// Démarrer l'application
$app->run();

// Mesurer le temps d'exécution (pour le débogage)
if ($config->get('app.debug')) {
    $executionTime = round((microtime(true) - APP_START) * 1000, 2);
    header('X-Execution-Time: ' . $executionTime . 'ms');
}