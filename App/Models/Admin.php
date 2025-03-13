<?php
namespace App\Models;

use App\Core\Database;

class Admin {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById($id) {
        return $this->db->fetch("
            SELECT a.*, acc.* 
            FROM admin a
            JOIN Account acc ON a.ID_account = acc.ID_account
            WHERE a.ID_account = ?
        ", [$id]);
    }

    public function create($accountId) {
        return $this->db->insert('admin', [
            'ID_account' => $accountId
        ]);
    }

    public function getAllPilotes() {
        return $this->db->fetchAll("
            SELECT p.*, acc.* 
            FROM pilote p
            JOIN Account acc ON p.ID_account = acc.ID_account
        ");
    }

    public function createPilote($accountId) {
        return $this->db->insert('pilote', [
            'ID_account' => $accountId
        ]);
    }

    public function deletePilote($id) {
        return $this->db->delete('pilote', 'ID_account = ?', [$id]);
    }

    public function getStatistics() {
        $stats = [
            'total_students' => $this->db->fetch("SELECT COUNT(*) as count FROM Student")['count'],
            'total_companies' => $this->db->fetch("SELECT COUNT(*) as count FROM Company")['count'],
            'total_offers' => $this->db->fetch("SELECT COUNT(*) as count FROM Offers")['count'],
            'total_applications' => 0, // Should be implemented when you create the applications table
        ];

        return $stats;
    }
}