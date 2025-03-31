<?php
namespace App\Models;

use App\Core\Model;

class Wishlist extends Model {
    protected $table = 'wishlist';
    protected $primaryKey = 'id';

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
     * Récupère la wishlist d'un étudiant avec les détails des offres
     */
    public function getWishlist($studentId, $limit = null, $offset = 0) {
        $sql = "
            SELECT w.*, o.Offer_title, o.Description as offer_description, 
                   c.Name as company_name, o.internship_duration, 
                   o.monthly_remuneration, o.location, o.ID_Offer,
                   o.remote_possible
            FROM wishlist w
            JOIN Offers o ON w.offer_id = o.ID_Offer
            JOIN Company c ON o.ID_Company = c.ID_Company
            WHERE w.student_id = ?
            ORDER BY w.created_at DESC
        ";

        $params = [$studentId];

        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }

        $wishlist = $this->db->fetchAll($sql, $params);

        // Enrichir les données avec les skills pour chaque offre
        if ($wishlist) {
            $internshipModel = new Internship();
            foreach ($wishlist as &$item) {
                $item['skills'] = $internshipModel->getSkillsForOffer($item['offer_id']);
                $item['company_initials'] = $this->getInitials($item['company_name']);
                $item['time_ago'] = $this->getTimeAgo($item['created_at']);
            }
        }

        return $wishlist;
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
     * Retourne les initiales d'une chaîne de caractères
     */
    private function getInitials($string) {
        $words = explode(' ', $string);
        $initials = '';

        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
                if (strlen($initials) >= 2) {
                    break;
                }
            }
        }

        return $initials ?: 'N/A';
    }

    /**
     * Calcule le temps écoulé depuis une date donnée
     */
    private function getTimeAgo($datetime) {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;

        if ($diff < 60) {
            return "il y a quelques secondes";
        } else if ($diff < 3600) {
            $mins = floor($diff / 60);
            return "il y a " . $mins . " minute" . ($mins > 1 ? "s" : "");
        } else if ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "il y a " . $hours . " heure" . ($hours > 1 ? "s" : "");
        } else if ($diff < 604800) {
            $days = floor($diff / 86400);
            return "il y a " . $days . " jour" . ($days > 1 ? "s" : "");
        } else if ($diff < 2592000) {
            $weeks = floor($diff / 604800);
            return "il y a " . $weeks . " semaine" . ($weeks > 1 ? "s" : "");
        } else if ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return "il y a " . $months . " mois";
        } else {
            $years = floor($diff / 31536000);
            return "il y a " . $years . " an" . ($years > 1 ? "s" : "");
        }
    }
}