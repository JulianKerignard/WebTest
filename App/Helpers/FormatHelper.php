<?php
namespace App\Helpers;

class FormatHelper {
    public static function formatDate($date, $format = 'd/m/Y') {
        if (empty($date)) {
            return '';
        }

        $dateTime = new \DateTime($date);
        return $dateTime->format($format);
    }

    public static function formatCurrency($amount, $currency = 'EUR') {
        if (!is_numeric($amount)) {
            return '';
        }

        $formatter = new \NumberFormatter('fr_FR', \NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($amount, $currency);
    }

    public static function formatNumber($number, $decimals = 2) {
        if (!is_numeric($number)) {
            return '';
        }

        return number_format($number, $decimals, ',', ' ');
    }

    public static function truncateText($text, $length = 100, $ending = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length) . $ending;
    }

    public static function timeSince($date) {
        if (empty($date)) {
            return '';
        }

        $datetime = new \DateTime($date);
        $now = new \DateTime();
        $interval = $now->diff($datetime);

        if ($interval->y > 0) {
            return $interval->y . ' ' . ($interval->y > 1 ? 'ans' : 'an');
        }

        if ($interval->m > 0) {
            return $interval->m . ' mois';
        }

        if ($interval->d > 0) {
            return $interval->d . ' ' . ($interval->d > 1 ? 'jours' : 'jour');
        }

        if ($interval->h > 0) {
            return $interval->h . ' ' . ($interval->h > 1 ? 'heures' : 'heure');
        }

        if ($interval->i > 0) {
            return $interval->i . ' ' . ($interval->i > 1 ? 'minutes' : 'minute');
        }

        return 'à l\'instant';
    }

    public static function formatPhoneNumber($phoneNumber) {
        // Format french phone number
        if (preg_match('/^0[1-9][0-9]{8}$/', $phoneNumber)) {
            return preg_replace('/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', '$1 $2 $3 $4 $5', $phoneNumber);
        }

        return $phoneNumber;
    }
}