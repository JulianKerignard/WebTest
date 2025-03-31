<?php
namespace App\Models;

use App\Core\Database;

class Wishlist {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Ajoute une offre à la wishlist d'un étudiant
     */
    public function addToWishlist($studentId, $offerId) {
        // Vérifier si l'offre est déjà dans les favoris
        if ($this->isInWishlist($studentId, $offerId)) {
            return false;
        }

        // Ajouter à la wishlist
        return $this->db->insert('wishlist', [
            'student_id' => $studentId,
            'offer_id' => $offerId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Retire une offre de la wishlist d'un étudiant
     */
    public function removeFromWishlist($studentId, $offerId) {
        return $this->db->delete('wishlist', 'student_id = ? AND offer_id = ?', [$studentId, $offerId]);
    }

    /**
     * Récupère la wishlist d'un étudiant
     */
    public function getWishlist($studentId) {
        return $this->db->fetchAll("
            SELECT w.*, o.Offer_title, o.Description as offer_description, c.Name as company_name,
                   o.internship_duration, o.monthly_remuneration, o.Date_of_publication,
                   o.location, o.Starting_internship_date
            FROM wishlist w
            JOIN Offers o ON w.offer_id = o.ID_Offer
            JOIN Company c ON o.ID_Company = c.ID_Company
            WHERE w.student_id = ?
            ORDER BY w.created_at DESC
        ", [$studentId]);
    }

    /**
     * Vérifie si une offre est dans la wishlist d'un étudiant
     */
    public function isInWishlist($studentId, $offerId) {
        $result = $this->db->fetch("
            SELECT COUNT(*) as count FROM wishlist WHERE student_id = ? AND offer_id = ?
        ", [$studentId, $offerId]);

        return $result && $result['count'] > 0;
    }

    /**
     * Compte le nombre d'offres dans la wishlist d'un étudiant
     */
    public function countByStudent($studentId) {
        $result = $this->db->fetch("
            SELECT COUNT(*) as count FROM wishlist WHERE student_id = ?
        ", [$studentId]);

        return $result ? $result['count'] : 0;
    }

    /**
     * Vide la wishlist d'un étudiant
     */
    public function clearStudentWishlist($studentId) {
        return $this->db->delete('wishlist', 'student_id = ?', [$studentId]);
    }
}