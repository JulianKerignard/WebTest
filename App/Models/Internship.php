<?php
namespace App\Models;

use App\Core\Database;

class Internship {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

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
     * Trouve une offre de stage par son ID
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
     * Recherche des offres de stage avec des filtres de base
     */
    public function search($filters = []) {
        $conditions = [];
        $params = [];

        $sql = "
            SELECT o.*, c.Name as company_name 
            FROM Offers o
            JOIN Company c ON o.ID_Company = c.ID_Company
        ";

        // Par défaut, afficher seulement les offres actives
        $conditions[] = "o.status = 'active'";

        // Recherche par mot-clé (titre, description, nom de l'entreprise)
        if (!empty($filters['keyword'])) {
            $conditions[] = "(o.Offer_title LIKE ? OR o.Description LIKE ? OR c.Name LIKE ?)";
            $keyword = "%{$filters['keyword']}%";
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        // Filtrer par entreprise
        if (!empty($filters['company_id'])) {
            $conditions[] = "o.ID_Company = ?";
            $params[] = $filters['company_id'];
        }

        // Filtrer par niveau d'études
        if (!empty($filters['level_id'])) {
            $conditions[] = "o.ID_level = ?";
            $params[] = $filters['level_id'];
        }

        // Filtrer par rémunération minimale
        if (!empty($filters['min_remuneration'])) {
            $conditions[] = "o.monthly_remuneration >= ?";
            $params[] = $filters['min_remuneration'];
        }

        // Ajouter les conditions à la requête
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        // Tri par date de publication (le plus récent d'abord)
        $sql .= " ORDER BY o.Date_of_publication DESC";

        // Pagination
        if (isset($filters['limit']) && isset($filters['offset'])) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $filters['limit'];
            $params[] = $filters['offset'];
        }

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Crée une nouvelle offre de stage
     */
    public function create($data) {
        return $this->db->insert('Offers', [
            'ID_Company' => $data['ID_Company'],
            'Nomber_of_remaining_internship_places' => $data['Nomber_of_remaining_internship_places'] ?? 1,
            'Description' => $data['Description'],
            'Date_of_publication' => $data['Date_of_publication'] ?? date('Y-m-d'),
            'Offer_title' => $data['Offer_title'],
            'ID_level' => $data['ID_level'] ?? null,
            'Starting_internship_date' => $data['Starting_internship_date'] ?? null,
            'internship_duration' => $data['internship_duration'] ?? null,
            'monthly_remuneration' => $data['monthly_remuneration'] ?? null,
            'location' => $data['location'] ?? null,
            'remote_possible' => isset($data['remote_possible']) ? 1 : 0,
            'status' => $data['status'] ?? 'active'
        ]);
    }

    /**
     * Met à jour une offre de stage existante
     */
    public function update($id, $data) {
        return $this->db->update('Offers', [
            'ID_Company' => $data['ID_Company'],
            'Nomber_of_remaining_internship_places' => $data['Nomber_of_remaining_internship_places'] ?? 1,
            'Description' => $data['Description'],
            'Offer_title' => $data['Offer_title'],
            'ID_level' => $data['ID_level'] ?? null,
            'Starting_internship_date' => $data['Starting_internship_date'] ?? null,
            'internship_duration' => $data['internship_duration'] ?? null,
            'monthly_remuneration' => $data['monthly_remuneration'] ?? null,
            'location' => $data['location'] ?? null,
            'remote_possible' => isset($data['remote_possible']) ? 1 : 0,
            'status' => $data['status'] ?? 'active',
            'updated_at' => date('Y-m-d H:i:s')
        ], 'ID_Offer = ?', [$id]);
    }

    /**
     * Supprime une offre de stage
     */
    public function delete($id) {
        return $this->db->delete('Offers', 'ID_Offer = ?', [$id]);
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
     * Supprime toutes les compétences associées à une offre
     */
    public function removeAllSkills($offerId) {
        return $this->db->delete('offer_skills', 'offer_id = ?', [$offerId]);
    }

    /**
     * Récupère les offres d'une entreprise
     */
    public function getByCompany($companyId) {
        return $this->db->fetchAll("
            SELECT * FROM Offers WHERE ID_Company = ? ORDER BY Date_of_publication DESC
        ", [$companyId]);
    }
}