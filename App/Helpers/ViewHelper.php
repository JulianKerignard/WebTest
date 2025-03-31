<?php
namespace App\Helpers;

/**
 * Classe utilitaire pour sécuriser l'affichage des données dans les vues
 */
class ViewHelper {
    /**
     * Récupère une valeur d'un tableau de manière sécurisée
     *
     * @param array|null $array Tableau contenant les données
     * @param string $key Clé à récupérer
     * @param mixed $default Valeur par défaut si la clé n'existe pas
     * @return mixed La valeur ou la valeur par défaut
     */
    public static function safe($array, $key, $default = '') {
        if (!isset($array) || !is_array($array) || !isset($array[$key])) {
            return $default;
        }
        return $array[$key];
    }

    /**
     * Récupère et échappe une valeur d'un tableau
     *
     * @param array|null $array Tableau contenant les données
     * @param string $key Clé à récupérer
     * @param mixed $default Valeur par défaut si la clé n'existe pas
     * @return string La valeur échappée ou la valeur par défaut
     */
    public static function escape($array, $key, $default = '') {
        return htmlspecialchars(self::safe($array, $key, $default), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Vérifie si une clé existe et a une valeur non vide
     *
     * @param array|null $array Tableau à vérifier
     * @param string $key Clé à vérifier
     * @return bool True si la clé existe et a une valeur non vide
     */
    public static function has($array, $key) {
        return isset($array) && is_array($array) && isset($array[$key]) && !empty($array[$key]);
    }

    /**
     * Formate une date à partir d'un tableau
     *
     * @param array|null $array Tableau contenant les données
     * @param string $key Clé à récupérer
     * @param string $format Format de la date
     * @param mixed $default Valeur par défaut si la clé n'existe pas
     * @return string La date formatée ou la valeur par défaut
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
     * Récupère une valeur numérique d'un tableau et la formate
     *
     * @param array|null $array Tableau contenant les données
     * @param string $key Clé à récupérer
     * @param int $decimals Nombre de décimales
     * @param mixed $default Valeur par défaut si la clé n'existe pas
     * @return string La valeur formatée ou la valeur par défaut
     */
    public static function number($array, $key, $decimals = 2, $default = '') {
        $value = self::safe($array, $key);
        if (empty($value) || !is_numeric($value)) {
            return $default;
        }

        return number_format((float)$value, $decimals, ',', ' ');
    }

    /**
     * Tronque un texte à une certaine longueur
     *
     * @param array|null $array Tableau contenant les données
     * @param string $key Clé à récupérer
     * @param int $length Longueur maximale
     * @param string $append Texte à ajouter en cas de troncature
     * @param mixed $default Valeur par défaut si la clé n'existe pas
     * @return string Le texte tronqué ou la valeur par défaut
     */
    public static function truncate($array, $key, $length = 100, $append = '...', $default = '') {
        $text = self::safe($array, $key);
        if (empty($text)) {
            return $default;
        }

        if (strlen($text) <= $length) {
            return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        }

        return htmlspecialchars(substr($text, 0, $length), ENT_QUOTES, 'UTF-8') . $append;
    }
}