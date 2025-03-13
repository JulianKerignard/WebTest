<?php
namespace App\Helpers;

class SecurityHelper {
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public static function generateToken($length = 32) {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes($length));
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes($length));
        }
        return bin2hex(random_bytes($length));
    }

    public static function sanitizeInput($input) {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key] = self::sanitizeInput($value);
            }
            return $input;
        }

        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    public static function validateCSRFToken($token) {
        $session = \App\Core\Session::getInstance();
        $csrfToken = $session->get('csrf_token');

        if (!$csrfToken || $token !== $csrfToken) {
            return false;
        }

        return true;
    }

    public static function generateCSRFToken() {
        $token = self::generateToken();
        $session = \App\Core\Session::getInstance();
        $session->set('csrf_token', $token);
        return $token;
    }
}