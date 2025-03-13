<?php
namespace App\Models;

use App\Core\Database;

class Internship {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findAll($limit = null, $offset = 0) {
        $sql = "
            SELECT o.*, c.Name as company_name 
            FROM Offers o
            JOIN Company c ON o.ID_Company = c.ID_Company
        ";

        if ($limit !== null) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }

        return $this->db->fetchAll($sql);
    }

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

    public function create($data) {
        return $this->db->insert('Offers', [
            'ID_Company' => $data['ID_Company'],
            'Nomber_of_remaining_internship_places' => $data['Nomber_of_remaining_internship_places'] ?? '1',
            'Description' => $data['Description'],
            'Date_of_publication' => $data['Date_of_publication'] ?? date('Y-m-d'),
            'Offer_title' => $data['Offer_title'],
            'ID_level' => $data['ID_level'] ?? null,
            'Starting_internship_date' => $data['Starting_internship_date'] ?? null,
            'internship_duration' => $data['internship_duration'] ?? null,
            'monthly_remuneration' => $data['monthly_remuneration'] ?? null
        ]);
    }

    public function update($id, $data) {
        return $this->db->update('Offers', [
            'ID_Company' => $data['ID_Company'],
            'Nomber_of_remaining_internship_places' => $data['Nomber_of_remaining_internship_places'] ?? '1',
            'Description' => $data['Description'],
            'Offer_title' => $data['Offer_title'],
            'ID_level' => $data['ID_level'] ?? null,
            'Starting_internship_date' => $data['Starting_internship_date'] ?? null,
            'internship_duration' => $data['internship_duration'] ?? null,
            'monthly_remuneration' => $data['monthly_remuneration'] ?? null
        ], 'ID_Offer = ?', [$id]);
    }

    public function delete($id) {
        return $this->db->delete('Offers', 'ID_Offer = ?', [$id]);
    }

    public function search($filters = []) {
        $conditions = [];
        $params = [];

        $sql = "
            SELECT o.*, c.Name as company_name 
            FROM Offers o
            JOIN Company c ON o.ID_Company = c.ID_Company
        ";

        // Add filter conditions
        if (!empty($filters['keyword'])) {
            $conditions[] = "(o.Offer_title LIKE ? OR o.Description LIKE ? OR c.Name LIKE ?)";
            $keyword = "%{$filters['keyword']}%";
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        if (!empty($filters['company_id'])) {
            $conditions[] = "o.ID_Company = ?";
            $params[] = $filters['company_id'];
        }

        if (!empty($filters['level_id'])) {
            $conditions[] = "o.ID_level = ?";
            $params[] = $filters['level_id'];
        }

        if (!empty($filters['min_remuneration'])) {
            $conditions[] = "o.monthly_remuneration >= ?";
            $params[] = $filters['min_remuneration'];
        }

        if (!empty($filters['duration'])) {
            $conditions[] = "o.internship_duration = ?";
            $params[] = $filters['duration'];
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        // Add sorting
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'date':
                    $sql .= " ORDER BY o.Date_of_publication DESC";
                    break;
                case 'remuneration':
                    $sql .= " ORDER BY o.monthly_remuneration DESC";
                    break;
                case 'duration':
                    $sql .= " ORDER BY o.internship_duration";
                    break;
                default:
                    $sql .= " ORDER BY o.Date_of_publication DESC";
            }
        } else {
            $sql .= " ORDER BY o.Date_of_publication DESC";
        }

        // Add pagination
        if (isset($filters['limit']) && isset($filters['offset'])) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $filters['limit'];
            $params[] = $filters['offset'];
        }

        return $this->db->fetchAll($sql, $params);
    }

    public function getTotalInternships($filters = []) {
        $conditions = [];
        $params = [];

        $sql = "
            SELECT COUNT(*) as count
            FROM Offers o
            JOIN Company c ON o.ID_Company = c.ID_Company
        ";

        // Add filter conditions (same as in search method)
        if (!empty($filters['keyword'])) {
            $conditions[] = "(o.Offer_title LIKE ? OR o.Description LIKE ? OR c.Name LIKE ?)";
            $keyword = "%{$filters['keyword']}%";
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        if (!empty($filters['company_id'])) {
            $conditions[] = "o.ID_Company = ?";
            $params[] = $filters['company_id'];
        }

        if (!empty($filters['level_id'])) {
            $conditions[] = "o.ID_level = ?";
            $params[] = $filters['level_id'];
        }

        if (!empty($filters['min_remuneration'])) {
            $conditions[] = "o.monthly_remuneration >= ?";
            $params[] = $filters['min_remuneration'];
        }

        if (!empty($filters['duration'])) {
            $conditions[] = "o.internship_duration = ?";
            $params[] = $filters['duration'];
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $result = $this->db->fetch($sql, $params);
        return $result ? $result['count'] : 0;
    }

    public function getLevels() {
        return $this->db->fetchAll("SELECT * FROM Level_Of_Study");
    }

    public function getSkills() {
        return $this->db->fetchAll("SELECT * FROM Skills");
    }

    // This would require a junction table between Offers and Skills
    public function getSkillsForOffer($offerId) {
        // Note: This is theoretical as the junction table doesn't exist yet
        return $this->db->fetchAll("
            SELECT s.* 
            FROM offer_skills os
            JOIN Skills s ON os.skill_id = s.ID_skill
            WHERE os.offer_id = ?
        ", [$offerId]);
    }
}