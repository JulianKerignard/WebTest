<?php
namespace App\Core;

class Session {
    private static $instance = null;

    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public function remove($key) {
        unset($_SESSION[$key]);
    }

    public function exists($key) {
        return isset($_SESSION[$key]);
    }

    public function setFlash($key, $message) {
        $_SESSION['flash_messages'][$key] = $message;
    }

    public function getFlash($key) {
        $message = $_SESSION['flash_messages'][$key] ?? null;
        unset($_SESSION['flash_messages'][$key]);
        return $message;
    }

    public function hasFlash($key) {
        return isset($_SESSION['flash_messages'][$key]);
    }

    public function getAllFlashes() {
        $messages = $_SESSION['flash_messages'] ?? [];
        $_SESSION['flash_messages'] = [];
        return $messages;
    }

    public function destroy() {
        session_destroy();
    }
}