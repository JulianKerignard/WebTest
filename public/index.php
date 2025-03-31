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

// Enregistrer les routes de l'application
require_once __DIR__ . '/../App/route.php';
\App\registerRoutes($app);

// Démarrer l'application
$app->run();

// Mesurer le temps d'exécution (pour le débogage)
if ($config->get('app.debug')) {
    $executionTime = round((microtime(true) - APP_START) * 1000, 2);
    header('X-Execution-Time: ' . $executionTime . 'ms');
}