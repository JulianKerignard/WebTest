<?php
namespace App\Models;

use App\Core\Database;

class Company {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Récupère toutes les entreprises avec pagination optionnelle
     */
    public function findAll($limit = null, $offset = 0) {
        $sql = "SELECT c.*, s.Sector as sector_name FROM Company c
               LEFT JOIN Sector_Of_Activity s ON c.ID_Sector = s.ID_Sector
               ORDER BY c.Name";

        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            return $this->db->fetchAll($sql, [$limit, $offset]);
        }

        return $this->db->fetchAll($sql);
    }

    /**
     * Récupère une entreprise par son ID
     */
    public function findById($id) {
        return $this->db->fetch("
            SELECT c.*, s.Sector as sector_name,
            (SELECT COUNT(*) FROM Offers WHERE ID_Company = c.ID_Company) as offers_count,
            (SELECT AVG(rating) FROM company_evaluations WHERE company_id = c.ID_Company) as avg_rating,
            (SELECT COUNT(*) FROM company_evaluations WHERE company_id = c.ID_Company) as reviews_count
            FROM Company c
            LEFT JOIN Sector_Of_Activity s ON c.ID_Sector = s.ID_Sector
            WHERE c.ID_Company = ?
        ", [$id]);
    }

    /**
     * Recherche des entreprises par un mot-clé
     */
    public function search($keyword) {
        $sql = "
            SELECT c.*, s.Sector as sector_name
            FROM Company c
            LEFT JOIN Sector_Of_Activity s ON c.ID_Sector = s.ID_Sector
            WHERE c.Name LIKE ? OR c.Description LIKE ?
            ORDER BY c.Name
        ";

        $keyword = "%" . $keyword . "%";
        return $this->db->fetchAll($sql, [$keyword, $keyword]);
    }

    /**
     * Crée une nouvelle entreprise
     */
    public function create($data) {
        return $this->db->insert('Company', [
            'Name' => $data['Name'],
            'Description' => $data['Description'],
            'ID_Sector' => $data['ID_Sector'] ?? null,
            'Adresse' => $data['Adresse'] ?? null,
            'Size' => $data['Size'] ?? null,
            'Email' => $data['Email'] ?? null,
            'Phone' => $data['Phone'] ?? null,
            'Website' => $data['Website'] ?? null,
            'Logo' => $data['Logo'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Met à jour une entreprise existante
     */
    public function update($id, $data) {
        $updateData = [
            'Name' => $data['Name'],
            'Description' => $data['Description'],
            'ID_Sector' => $data['ID_Sector'] ?? null,
            'Adresse' => $data['Adresse'] ?? null,
            'Size' => $data['Size'] ?? null,
            'Email' => $data['Email'] ?? null,
            'Phone' => $data['Phone'] ?? null,
            'Website' => $data['Website'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (isset($data['Logo'])) {
            $updateData['Logo'] = $data['Logo'];
        }

        return $this->db->update('Company', $updateData, 'ID_Company = ?', [$id]);
    }

    /**
     * Supprime une entreprise
     */
    public function delete($id) {
        return $this->db->delete('Company', 'ID_Company = ?', [$id]);
    }

    /**
     * Récupère les offres d'une entreprise
     */
    public function getOffers($companyId) {
        return $this->db->fetchAll("
            SELECT o.*, l.Study_level as study_level
            FROM Offers o
            LEFT JOIN Level_Of_Study l ON o.ID_level = l.ID_level
            WHERE o.ID_Company = ?
            ORDER BY o.Date_of_publication DESC
        ", [$companyId]);
    }

    /**
     * Récupère le nombre total d'entreprises
     */
    public function getTotalCompanies() {
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM Company");
        return $result ? $result['count'] : 0;
    }

    /**
     * Récupère tous les secteurs d'activité
     */
    public function getSectors() {
        return $this->db->fetchAll("SELECT * FROM Sector_Of_Activity ORDER BY Sector");
    }
}