<?php
namespace App\Helpers;

use App\Core\App;

class SecurityHelper {
    /**
     * Hache un mot de passe de manière sécurisée
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Vérifie un mot de passe par rapport à son hash
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Génère un token aléatoire sécurisé
     */
    public static function generateToken($length = 32) {
        try {
            return bin2hex(random_bytes($length / 2));
        } catch (\Exception $e) {
            // Fallback si random_bytes n'est pas disponible
            if (function_exists('openssl_random_pseudo_bytes')) {
                return bin2hex(openssl_random_pseudo_bytes($length / 2));
            }

            // Dernière solution (moins sécurisée)
            $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $token = '';
            for ($i = 0; $i < $length; $i++) {
                $token .= $chars[rand(0, strlen($chars) - 1)];
            }
            return $token;
        }
    }

    /**
     * Nettoie les entrées utilisateur
     */
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key] = self::sanitizeInput($value);
            }
            return $input;
        }

        // Convertit les caractères spéciaux en entités HTML
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Valide un token CSRF
     */
    public static function validateCSRFToken($token) {
        $session = App::$app->session ?? null;
        if (!$session) {
            return false;
        }

        $csrfToken = $session->get('csrf_token');

        if (!$csrfToken || $token !== $csrfToken) {
            // Journaliser la tentative CSRF
            if (isset(App::$app->logger)) {
                App::$app->logger->logActivity("CSRF token validation failed: provided={$token}, expected={$csrfToken}");
            }
            return false;
        }

        return true;
    }

    /**
     * Génère un token CSRF et le stocke en session
     */
    public static function generateCSRFToken() {
        $token = self::generateToken();
        $session = App::$app->session ?? null;

        if ($session) {
            $session->set('csrf_token', $token);
        }

        return $token;
    }

    /**
     * Vérifie si l'adresse IP est potentiellement dangereuse
     */
    public static function isBlockedIP($ip) {
        // Liste d'IPs bloquées en base de données ou fichier
        $blockedIPs = [];

        // Check direct IP match
        if (in_array($ip, $blockedIPs)) {
            return true;
        }

        // Check for IP range matches if needed
        // ...

        return false;
    }

    /**
     * Valide le format et la sécurité d'un mot de passe
     */
    public static function validatePasswordStrength($password) {
        $errors = [];

        // Longueur minimum
        if (strlen($password) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
        }

        // Doit contenir au moins une lettre majuscule
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une lettre majuscule.";
        }

        // Doit contenir au moins une lettre minuscule
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une lettre minuscule.";
        }

        // Doit contenir au moins un chiffre
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un chiffre.";
        }

        // Optionnel: Doit contenir au moins un caractère spécial
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un caractère spécial.";
        }

        // Vérifier qu'il ne contient pas de séquences évidentes
        $commonSequences = ['123456', 'abcdef', 'qwerty', 'azerty'];
        foreach ($commonSequences as $sequence) {
            if (stripos($password, $sequence) !== false) {
                $errors[] = "Le mot de passe ne doit pas contenir de séquences évidentes.";
                break;
            }
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Vérifie si le fichier téléchargé pourrait être malveillant
     */
    public static function validateFileUpload($file) {
        // Vérifier l'extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $dangerousExtensions = ['php', 'exe', 'js', 'phtml', 'sh', 'bat', 'cmd'];

        if (in_array($extension, $dangerousExtensions)) {
            return false;
        }

        // Vérifier le type MIME réel
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        $allowedMimeTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/png',
            'image/gif'
        ];

        if (!in_array($mimeType, $allowedMimeTypes)) {
            return false;
        }

        // Vérifier la taille maximale
        $maxSize = App::$app->config->get('uploads.max_file_size', 5 * 1024 * 1024); // 5 MB par défaut
        if ($file['size'] > $maxSize) {
            return false;
        }

        return true;
    }
}