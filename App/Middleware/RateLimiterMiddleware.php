<?php
namespace App\Middleware;

use App\Core\App;
use App\Core\Middleware;

class RateLimiterMiddleware extends Middleware {
    private $maxAttempts;
    private $decayMinutes;
    private $key;

    public function __construct($maxAttempts = 5, $decayMinutes = 10, $key = null) {
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
        $this->key = $key;
    }

    public function execute($next) {
        $request = App::$app->request;
        $session = App::$app->session;

        // Utiliser l'IP ou l'email si fourni
        $ip = $request->getClientIp();
        $email = $request->get('email');

        // Le clé peut être différente selon le contexte (login, register, etc.)
        $key = $this->key ?: 'rate_limit';
        $identifier = $email ?: $ip;
        $storageKey = "{$key}:{$identifier}";

        // Structure des données: [nb_attempts, timestamp_expiration]
        $limiter = $session->get($storageKey);

        if (!$limiter) {
            $limiter = [1, time() + ($this->decayMinutes * 60)];
            $session->set($storageKey, $limiter);
            return $next();
        }

        // Vérifier si la période a expiré
        if (time() > $limiter[1]) {
            // Réinitialiser
            $limiter = [1, time() + ($this->decayMinutes * 60)];
            $session->set($storageKey, $limiter);
            return $next();
        }

        // Vérifier si le nombre maximal de tentatives est atteint
        if ($limiter[0] >= $this->maxAttempts) {
            // Journaliser la tentative excessive
            App::$app->logger->logActivity("Rate limit exceeded for {$key} from {$identifier}");

            // Stocker la tentative dans la base de données si c'est un login
            if ($key === 'login' && $email) {
                App::$app->db->insert('login_attempts', [
                    'ip_address' => $ip,
                    'email' => $email,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'successful' => 0
                ]);
            }

            // Pour les requêtes AJAX
            if ($request->isAjax()) {
                return App::$app->response->json([
                    'success' => false,
                    'message' => 'Trop de tentatives. Veuillez réessayer dans ' . ceil(($limiter[1] - time()) / 60) . ' minutes.'
                ], 429);
            }

            // Pour les requêtes normales
            $session->setFlash('error', 'Trop de tentatives. Veuillez réessayer dans ' . ceil(($limiter[1] - time()) / 60) . ' minutes.');
            return App::$app->response->redirect('back');
        }

        // Incrémenter le compteur
        $limiter[0]++;
        $session->set($storageKey, $limiter);

        return $next();
    }
}