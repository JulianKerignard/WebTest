<?php
namespace App\Models;

use App\Core\Model;

class Internship extends Model {
    protected $table = 'Offers';
    protected $primaryKey = 'ID_Offer';

    /**
     * Trouve toutes les offres de stage, avec option de limitation
     */
    public function findAll($limit = null, $offset = 0) {
        $sql = "
            SELECT o.*, c.Name as company_name 
            FROM Offers o
            JOIN Company c ON o.ID_Company = c.ID_Company
            WHERE o.status = 'active'
            ORDER BY o.Date_of_publication DESC
        ";

        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            return $this->db->fetchAll($sql, [$limit, $offset]);
        }

        return $this->db->fetchAll($sql);
    }

    /**
     * Trouve une offre de stage par son ID avec les détails de l'entreprise
     */
    public function findById($id) {
        return $this->db->fetch("
            SELECT o.*, c.Name as company_name, c.Description as company_description,
                   l.Study_level as study_level
            FROM Offers o
            JOIN Company c ON o.ID_Company = c.ID_Company
            LEFT JOIN Level_Of_Study l ON o.ID_level = l.ID_level
            WHERE o.ID_Offer = ?
        ", [$id]);
    }

    /**
     * Recherche basique des offres de stage
     */
    public function search($filters = []) {
        $conditions = ['o.status = "active"'];
        $params = [];

        // Recherche par mot-clé
        if (!empty($filters['keyword'])) {
            $conditions[] = "(o.Offer_title LIKE ? OR o.Description LIKE ? OR c.Name LIKE ?)";
            $keyword = "%{$filters['keyword']}%";
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        // Filtrer par localisation
        if (!empty($filters['location'])) {
            $conditions[] = "o.location LIKE ?";
            $params[] = "%{$filters['location']}%";
        }

        // Filtrer par entreprise
        if (!empty($filters['company_id'])) {
            $conditions[] = "o.ID_Company = ?";
            $params[] = $filters['company_id'];
        }

        // Filtrer par domaine ou secteur
        if (!empty($filters['domain'])) {
            $conditions[] = "c.ID_Sector = ?";
            $params[] = $filters['domain'];
        }

        // Filtrer par niveau d'études
        if (!empty($filters['level'])) {
            $conditions[] = "o.ID_level = ?";
            $params[] = $filters['level'];
        }

        // Filtrer par rémunération minimale
        if (!empty($filters['remuneration'])) {
            $conditions[] = "o.monthly_remuneration >= ?";
            $params[] = $filters['remuneration'];
        }

        // Filtrer par durée
        if (!empty($filters['duration'])) {
            $conditions[] = "o.internship_duration LIKE ?";
            $params[] = "%{$filters['duration']}%";
        }

        $sql = "
            SELECT o.*, c.Name as company_name 
            FROM Offers o
            JOIN Company c ON o.ID_Company = c.ID_Company
            WHERE " . implode(' AND ', $conditions) . "
            ORDER BY " . (!empty($filters['sort']) ? $this->getSortOrder($filters['sort']) : "o.Date_of_publication DESC");

        // Pagination
        if (isset($filters['limit']) && isset($filters['offset'])) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $filters['limit'];
            $params[] = $filters['offset'];
        }

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Renvoie l'ordre de tri en fonction du paramètre
     */
    private function getSortOrder($sort) {
        switch ($sort) {
            case 'recent':
                return "o.Date_of_publication DESC";
            case 'salary':
                return "o.monthly_remuneration DESC";
            case 'duration':
                return "o.internship_duration DESC";
            case 'relevant':
            default:
                return "o.Date_of_publication DESC";
        }
    }

    /**
     * Récupère les compétences associées à une offre
     */
    public function getSkillsForOffer($offerId) {
        return $this->db->fetchAll("
            SELECT s.* 
            FROM offer_skills os
            JOIN Skills s ON os.skill_id = s.ID_skill
            WHERE os.offer_id = ?
        ", [$offerId]);
    }

    /**
     * Associe une compétence à une offre
     */
    public function addSkill($offerId, $skillId) {
        try {
            $this->db->insert('offer_skills', [
                'offer_id' => $offerId,
                'skill_id' => $skillId
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Supprime les compétences associées à une offre
     */
    public function removeSkills($offerId) {
        return $this->db->delete('offer_skills', 'offer_id = ?', [$offerId]);
    }

    /**
     * Récupère les niveaux d'études
     */
    public function getLevels() {
        return $this->db->fetchAll("SELECT * FROM Level_Of_Study ORDER BY ID_level");
    }

    /**
     * Récupère les compétences
     */
    public function getSkills() {
        return $this->db->fetchAll("SELECT * FROM Skills ORDER BY Skill_name");
    }
}