<?php
namespace App\Models;

use App\Core\Database;

class Company {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findAll($limit = null, $offset = 0) {
        $sql = "SELECT * FROM Company";

        if ($limit !== null) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }

        return $this->db->fetchAll($sql);
    }

    public function findById($id) {
        return $this->db->fetch("SELECT * FROM Company WHERE ID_Company = ?", [$id]);
    }

    public function create($data) {
        return $this->db->insert('Company', [
            'Name' => $data['Name'],
            'Description' => $data['Description'],
            'ID_Sector' => $data['ID_Sector'] ?? null,
            'Adresse' => $data['Adresse'] ?? null,
            'Size' => $data['Size'] ?? null
        ]);
    }

    public function update($id, $data) {
        return $this->db->update('Company', [
            'Name' => $data['Name'],
            'Description' => $data['Description'],
            'ID_Sector' => $data['ID_Sector'] ?? null,
            'Adresse' => $data['Adresse'] ?? null,
            'Size' => $data['Size'] ?? null
        ], 'ID_Company = ?', [$id]);
    }

    public function delete($id) {
        return $this->db->delete('Company', 'ID_Company = ?', [$id]);
    }

    public function search($keyword) {
        return $this->db->fetchAll("
            SELECT * FROM Company 
            WHERE Name LIKE ? OR Description LIKE ?
        ", ["%{$keyword}%", "%{$keyword}%"]);
    }

    public function findBySector($sectorId) {
        return $this->db->fetchAll("
            SELECT * FROM Company 
            WHERE ID_Sector = ?
        ", [$sectorId]);
    }

    public function getOffers($companyId) {
        return $this->db->fetchAll("
            SELECT * FROM Offers 
            WHERE ID_Company = ?
        ", [$companyId]);
    }

    public function getTotalCompanies() {
        return $this->db->fetch("SELECT COUNT(*) as count FROM Company")['count'];
    }

    public function rate($companyId, $rating, $studentId) {
        // Note: Cette méthode est théorique car la table des évaluations n'existe pas encore
        return $this->db->insert('company_ratings', [
            'company_id' => $companyId,
            'student_id' => $studentId,
            'rating' => $rating,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getAverageRating($companyId) {
        // Note: Cette méthode est théorique car la table des évaluations n'existe pas encore
        $result = $this->db->fetch("
            SELECT AVG(rating) as avg_rating 
            FROM company_ratings 
            WHERE company_id = ?
        ", [$companyId]);

        return $result ? $result['avg_rating'] : 0;
    }
}