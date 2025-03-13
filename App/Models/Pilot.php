<?php
namespace App\Models;

use App\Core\Database;

class Pilot {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById($id) {
        return $this->db->fetch("
            SELECT p.*, acc.* 
            FROM pilote p
            JOIN Account acc ON p.ID_account = acc.ID_account
            WHERE p.ID_account = ?
        ", [$id]);
    }

    public function findAll() {
        return $this->db->fetchAll("
            SELECT p.*, acc.* 
            FROM pilote p
            JOIN Account acc ON p.ID_account = acc.ID_account
        ");
    }

    public function create($accountId) {
        return $this->db->insert('pilote', [
            'ID_account' => $accountId
        ]);
    }

    public function delete($id) {
        return $this->db->delete('pilote', 'ID_account = ?', [$id]);
    }

    public function getStudents() {
        // In a real application, pilots would be linked to specific promotions/centers
        // For now, we'll return all students
        return $this->db->fetchAll("
            SELECT s.*, acc.* 
            FROM Student s
            JOIN Account acc ON s.ID_account = acc.ID_account
        ");
    }

    public function getStudentStatistics() {
        // In a real application, this would be filtered by promotion/center
        $stats = [
            'total_students' => $this->db->fetch("SELECT COUNT(*) as count FROM Student")['count'],
            'students_with_internship' => 0, // This would require an applications table with status
            'students_searching' => 0, // This would also require status tracking
        ];

        return $stats;
    }
}