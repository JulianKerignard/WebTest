<?php
namespace App\Helpers;

/**
 * Helper pour les vues
 * Fournit des fonctions utilitaires pour échapper et formater les données
 */
class ViewHelper {
    /**
     * Échapement de base pour une valeur
     */
    public static function escape($value) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Échappe en toute sécurité une valeur d'un tableau
     */
    public static function escape_value($array, $key, $default = '') {
        if (!isset($array) || !is_array($array) || !isset($array[$key])) {
            return self::escape($default);
        }
        return self::escape($array[$key]);
    }

    /**
     * Version abrégée de escape_value
     */
    public static function v($array, $key, $default = '') {
        return self::escape_value($array, $key, $default);
    }

    /**
     * Vérifie si une clé existe dans un tableau et a une valeur non vide
     */
    public static function has($array, $key) {
        return isset($array) && is_array($array) && isset($array[$key]) && !empty($array[$key]);
    }

    /**
     * Récupère une valeur en toute sécurité sans échappement
     */
    public static function safe($array, $key, $default = '') {
        if (!isset($array) || !is_array($array) || !isset($array[$key])) {
            return $default;
        }
        return $array[$key];
    }

    /**
     * Formate une date
     */
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

    /**
     * Renvoie un extrait d'un texte
     */
    public static function excerpt($text, $length = 100, $suffix = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . $suffix;
    }

    /**
     * Génère les initiales d'un nom
     */
    public static function initials($name, $limit = 2) {
        $words = explode(' ', $name);
        $initials = '';

        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
                if (strlen($initials) >= $limit) {
                    break;
                }
            }
        }

        return $initials ?: 'N/A';
    }

    /**
     * Formatte un nombre avec séparateur de milliers
     */
    public static function number($number, $decimals = 0, $dec_point = ',', $thousands_sep = ' ') {
        return number_format($number, $decimals, $dec_point, $thousands_sep);
    }

    /**
     * Formatte un prix
     */
    public static function price($price, $decimals = 2, $currency = '€') {
        return self::number($price, $decimals) . ' ' . $currency;
    }
}