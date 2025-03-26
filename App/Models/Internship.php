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
            WHERE o.status = 'active'
            ORDER BY o.Date_of_publication DESC
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
        // Convertir la rémunération en nombre décimal si nécessaire
        if (isset($data['monthly_remuneration']) && is_string($data['monthly_remuneration'])) {
            $data['monthly_remuneration'] = str_replace(',', '.', $data['monthly_remuneration']);
        }

        $offerId = $this->db->insert('Offers', [
            'ID_Company' => $data['ID_Company'],
            'Nomber_of_remaining_internship_places' => $data['Nomber_of_remaining_internship_places'] ?? '1',
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

        // Si des compétences sont spécifiées, les ajouter
        if (!empty($data['skills']) && $offerId) {
            $skills = is_array($data['skills']) ? $data['skills'] : explode(',', $data['skills']);
            foreach ($skills as $skillId) {
                $this->addSkill($offerId, $skillId);
            }
        }

        return $offerId;
    }

    public function update($id, $data) {
        // Convertir la rémunération en nombre décimal si nécessaire
        if (isset($data['monthly_remuneration']) && is_string($data['monthly_remuneration'])) {
            $data['monthly_remuneration'] = str_replace(',', '.', $data['monthly_remuneration']);
        }

        $result = $this->db->update('Offers', [
            'ID_Company' => $data['ID_Company'],
            'Nomber_of_remaining_internship_places' => $data['Nomber_of_remaining_internship_places'] ?? '1',
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

        // Mettre à jour les compétences si spécifiées
        if (isset($data['skills'])) {
            // Supprimer les compétences existantes
            $this->removeAllSkills($id);

            // Ajouter les nouvelles compétences
            $skills = is_array($data['skills']) ? $data['skills'] : explode(',', $data['skills']);
            foreach ($skills as $skillId) {
                $this->addSkill($id, $skillId);
            }
        }

        return $result;
    }

    public function delete($id) {
        // Supprimer les compétences associées d'abord (pas nécessaire avec les contraintes ON DELETE CASCADE)
        $this->removeAllSkills($id);

        // Supprimer l'offre
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

        // Par défaut, n'afficher que les offres actives sauf si spécifié
        if (!isset($filters['status'])) {
            $conditions[] = "o.status = 'active'";
        } else if ($filters['status'] !== 'all') {
            $conditions[] = "o.status = ?";
            $params[] = $filters['status'];
        }

        // Ajouter filter conditions
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

        if (!empty($filters['location'])) {
            $conditions[] = "o.location LIKE ?";
            $params[] = "%{$filters['location']}%";
        }

        if (!empty($filters['remote'])) {
            $conditions[] = "o.remote_possible = 1";
        }

        // Recherche par compétences (avec sous-requête)
        if (!empty($filters['skills']) && is_array($filters['skills'])) {
            $skillPlaceholders = implode(', ', array_fill(0, count($filters['skills']), '?'));
            $conditions[] = "o.ID_Offer IN (
                SELECT os.offer_id 
                FROM offer_skills os 
                WHERE os.skill_id IN ({$skillPlaceholders})
                GROUP BY os.offer_id
                HAVING COUNT(DISTINCT os.skill_id) = ?
            )";

            foreach ($filters['skills'] as $skillId) {
                $params[] = $skillId;
            }

            $params[] = count($filters['skills']); // Nombre de compétences requises
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
                case 'company':
                    $sql .= " ORDER BY c.Name";
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

        // Par défaut, n'afficher que les offres actives sauf si spécifié
        if (!isset($filters['status'])) {
            $conditions[] = "o.status = 'active'";
        } else if ($filters['status'] !== 'all') {
            $conditions[] = "o.status = ?";
            $params[] = $filters['status'];
        }

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

        if (!empty($filters['location'])) {
            $conditions[] = "o.location LIKE ?";
            $params[] = "%{$filters['location']}%";
        }

        if (!empty($filters['remote'])) {
            $conditions[] = "o.remote_possible = 1";
        }

        // Recherche par compétences (avec sous-requête)
        if (!empty($filters['skills']) && is_array($filters['skills'])) {
            $skillPlaceholders = implode(', ', array_fill(0, count($filters['skills']), '?'));
            $conditions[] = "o.ID_Offer IN (
                SELECT os.offer_id 
                FROM offer_skills os 
                WHERE os.skill_id IN ({$skillPlaceholders})
                GROUP BY os.offer_id
                HAVING COUNT(DISTINCT os.skill_id) = ?
            )";

            foreach ($filters['skills'] as $skillId) {
                $params[] = $skillId;
            }

            $params[] = count($filters['skills']); // Nombre de compétences requises
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $result = $this->db->fetch($sql, $params);
        return $result ? $result['count'] : 0;
    }

    public function getLevels() {
        return $this->db->fetchAll("SELECT * FROM Level_Of_Study ORDER BY ID_level");
    }

    public function getSkills() {
        return $this->db->fetchAll("SELECT * FROM Skills ORDER BY Skill_name");
    }

    public function getSkillsForOffer($offerId) {
        return $this->db->fetchAll("
            SELECT s.* 
            FROM offer_skills os
            JOIN Skills s ON os.skill_id = s.ID_skill
            WHERE os.offer_id = ?
        ", [$offerId]);
    }

    public function addSkill($offerId, $skillId) {
        try {
            $this->db->insert('offer_skills', [
                'offer_id' => $offerId,
                'skill_id' => $skillId
            ]);
            return true;
        } catch (\Exception $e) {
            // Gère les erreurs de clé dupliquée ou autre
            return false;
        }
    }

    public function removeSkill($offerId, $skillId) {
        return $this->db->delete('offer_skills', 'offer_id = ? AND skill_id = ?', [$offerId, $skillId]);
    }

    public function removeAllSkills($offerId) {
        return $this->db->delete('offer_skills', 'offer_id = ?', [$offerId]);
    }

    public function getInternshipStatistics() {
        return [
            'total' => $this->db->fetch("SELECT COUNT(*) as count FROM Offers")['count'],
            'active' => $this->db->fetch("SELECT COUNT(*) as count FROM Offers WHERE status = 'active'")['count'],
            'filled' => $this->db->fetch("SELECT COUNT(*) as count FROM Offers WHERE status = 'filled'")['count'],
            'pending' => $this->db->fetch("SELECT COUNT(*) as count FROM Offers WHERE status = 'pending'")['count'],
            'expired' => $this->db->fetch("SELECT COUNT(*) as count FROM Offers WHERE status = 'expired'")['count'],
            'avg_remuneration' => $this->db->fetch("SELECT AVG(monthly_remuneration) as avg FROM Offers WHERE monthly_remuneration > 0")['avg']
        ];
    }

    public function getInternshipsBySkill() {
        return $this->db->fetchAll("
            SELECT s.Skill_name, COUNT(os.offer_id) as count
            FROM Skills s
            JOIN offer_skills os ON s.ID_skill = os.skill_id
            GROUP BY s.ID_skill
            ORDER BY count DESC
        ");
    }

    public function getInternshipsByDuration() {
        return $this->db->fetchAll("
            SELECT internship_duration, COUNT(*) as count
            FROM Offers
            WHERE internship_duration IS NOT NULL
            GROUP BY internship_duration
            ORDER BY count DESC
        ");
    }

    public function updateStatus($id, $status) {
        return $this->db->update('Offers', [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'ID_Offer = ?', [$id]);
    }

    public function getByCompany($companyId) {
        return $this->db->fetchAll("
            SELECT * FROM Offers WHERE ID_Company = ? ORDER BY Date_of_publication DESC
        ", [$companyId]);
    }
}