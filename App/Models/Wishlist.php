<?php
namespace App\Models;

use App\Core\Database;

class Wishlist {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function addToWishlist($studentId, $offerId) {
        // Vérifier si l'offre est déjà dans les favoris
        $existing = $this->db->fetch("
            SELECT * FROM wishlist WHERE student_id = ? AND offer_id = ?
        ", [$studentId, $offerId]);

        if ($existing) {
            return false; // Déjà dans les favoris
        }

        return $this->db->insert('wishlist', [
            'student_id' => $studentId,
            'offer_id' => $offerId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function removeFromWishlist($studentId, $offerId) {
        return $this->db->delete('wishlist', 'student_id = ? AND offer_id = ?', [$studentId, $offerId]);
    }

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

    public function isInWishlist($studentId, $offerId) {
        $result = $this->db->fetch("
            SELECT * FROM wishlist WHERE student_id = ? AND offer_id = ?
        ", [$studentId, $offerId]);

        return $result ? true : false;
    }

    public function countByStudent($studentId) {
        $result = $this->db->fetch("
            SELECT COUNT(*) as count FROM wishlist WHERE student_id = ?
        ", [$studentId]);

        return $result ? $result['count'] : 0;
    }

    public function getMostWishlisted($limit = 5) {
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

    public function getRecentlyAddedToWishlists($studentId, $limit = 5) {
        return $this->db->fetchAll("
            SELECT w.created_at, o.Offer_title, c.Name as company_name
            FROM wishlist w
            JOIN Offers o ON w.offer_id = o.ID_Offer
            JOIN Company c ON o.ID_Company = c.ID_Company
            WHERE w.student_id = ?
            ORDER BY w.created_at DESC
            LIMIT ?
        ", [$studentId, $limit]);
    }

    public function getWishlistStatistics() {
        return [
            'total_wishlisted_offers' => $this->db->fetch("SELECT COUNT(DISTINCT offer_id) as count FROM wishlist")['count'],
            'top_wishlisted_offer' => $this->db->fetch("
                SELECT o.Offer_title, c.Name as company_name, COUNT(w.id) as count
                FROM wishlist w
                JOIN Offers o ON w.offer_id = o.ID_Offer
                JOIN Company c ON o.ID_Company = c.ID_Company
                GROUP BY w.offer_id
                ORDER BY count DESC
                LIMIT 1
            ")
        ];
    }

    public function clearStudentWishlist($studentId) {
        return $this->db->delete('wishlist', 'student_id = ?', [$studentId]);
    }
}