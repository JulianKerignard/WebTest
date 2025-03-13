<?php
namespace App\Models;

use App\Core\Database;

class Wishlist {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Cette table n'existe pas encore dans le schéma SQL, on va la créer conceptuellement

    public function addToWishlist($studentId, $offerId) {
        // Vérifier si l'offre est déjà dans les favoris
        $existing = $this->db->fetch("
            SELECT * FROM wishlist WHERE student_id = ? AND offer_id = ?
        ", [$studentId, $offerId]);

        if ($existing) {
            return false; // Déjà dans les favoris
        }

        // Note: cette méthode est théorique car la table n'existe pas encore
        return $this->db->insert('wishlist', [
            'student_id' => $studentId,
            'offer_id' => $offerId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function removeFromWishlist($studentId, $offerId) {
        // Note: cette méthode est théorique car la table n'existe pas encore
        return $this->db->delete('wishlist', 'student_id = ? AND offer_id = ?', [$studentId, $offerId]);
    }

    public function getWishlist($studentId) {
        // Note: cette méthode est théorique car la table n'existe pas encore
        return $this->db->fetchAll("
            SELECT w.*, o.Offer_title, o.Description as offer_description, c.Name as company_name,
                   o.internship_duration, o.monthly_remuneration, o.Date_of_publication
            FROM wishlist w
            JOIN Offers o ON w.offer_id = o.ID_Offer
            JOIN Company c ON o.ID_Company = c.ID_Company
            WHERE w.student_id = ?
            ORDER BY w.created_at DESC
        ", [$studentId]);
    }

    public function isInWishlist($studentId, $offerId) {
        // Note: cette méthode est théorique car la table n'existe pas encore
        $result = $this->db->fetch("
            SELECT * FROM wishlist WHERE student_id = ? AND offer_id = ?
        ", [$studentId, $offerId]);

        return $result ? true : false;
    }

    public function countByStudent($studentId) {
        // Note: cette méthode est théorique car la table n'existe pas encore
        $result = $this->db->fetch("
            SELECT COUNT(*) as count FROM wishlist WHERE student_id = ?
        ", [$studentId]);

        return $result ? $result['count'] : 0;
    }

    public function getMostWishlisted($limit = 5) {
        // Note: cette méthode est théorique car la table n'existe pas encore
        return $this->db->fetchAll("
            SELECT o.*, c.Name as company_name, COUNT(w.id) as wishlist_count
            FROM wishlist w
            JOIN Offers o ON w.offer_id = o.ID_Offer
            JOIN Company c ON o.ID_Company = c.ID_Company
            GROUP BY w.offer_id
            ORDER BY wishlist_count DESC
            LIMIT ?
        ", [$limit]);
    }
}