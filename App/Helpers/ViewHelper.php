<?php
namespace App\Helpers;

/**
 * Classe utilitaire pour sécuriser l'affichage des données dans les vues
 */
class ViewHelper {
    public static function safe($array, $key, $default = '') {
        if (!isset($array) || !is_array($array) || !isset($array[$key])) {
            return $default;
        }
        return $array[$key];
    }

    public static function escape($array, $key, $default = '') {
        return htmlspecialchars(self::safe($array, $key, $default));
    }

    public static function has($array, $key) {
        return isset($array) && is_array($array) && isset($array[$key]) && !empty($array[$key]);
    }

    public static function date($array, $key, $format = 'd/m/Y', $default = '') {
        $value = self::safe($array, $key);
        if (empty($value)) {
            return $default;
        }

        try {
            $date = new \DateTime($value);
            return $date->format($format);
        } catch (\Exception $e) {
            return $default;
        }
    }
}