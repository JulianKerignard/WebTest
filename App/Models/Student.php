<?php
namespace App\Models;

use App\Core\Database;

class Student {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById($id) {
        return $this->db->fetch("
            SELECT s.*, acc.* 
            FROM Student s
            JOIN Account acc ON s.ID_account = acc.ID_account
            WHERE s.ID_account = ?
        ", [$id]);
    }

    public function findAll() {
        return $this->db->fetchAll("
            SELECT s.*, acc.* 
            FROM Student s
            JOIN Account acc ON s.ID_account = acc.ID_account
        ");
    }

    public function create($accountId, $data = []) {
        $now = date('Y-m-d');
        return $this->db->insert('Student', [
            'ID_account' => $accountId,
            'Licence' => $data['Licence'] ?? 0,
            'Majority' => $data['Majority'] ?? $now,
            'promotion' => $data['promotion'] ?? null,
            'CV' => $data['CV'] ?? null
        ]);
    }

    public function update($id, $data) {
        return $this->db->update('Student', [
            'Licence' => $data['Licence'] ?? 0,
            'Majority' => $data['Majority'] ?? null,
            'promotion' => $data['promotion'] ?? null,
            'CV' => $data['CV'] ?? null
        ], 'ID_account = ?', [$id]);
    }

    public function delete($id) {
        return $this->db->delete('Student', 'ID_account = ?', [$id]);
    }

    public function uploadCV($studentId, $file) {
        // Cette méthode devrait utiliser le FileHelper pour gérer l'upload
        $result = \App\Helpers\FileHelper::uploadFile($file, 'cv');

        if ($result['success']) {
            $this->db->update('Student', [
                'CV' => $result['filename']
            ], 'ID_account = ?', [$studentId]);

            return $result;
        }

        return $result;
    }

    public function getAppliedInternships($studentId) {
        // Cette méthode est théorique car la table des candidatures n'existe pas encore
        return $this->db->fetchAll("
            SELECT a.*, o.Offer_title, o.Description as offer_description, c.Name as company_name
            FROM applications a
            JOIN Offers o ON a.offer_id = o.ID_Offer
            JOIN Company c ON o.ID_Company = c.ID_Company
            WHERE a.student_id = ?
            ORDER BY a.created_at DESC
        ", [$studentId]);
    }

    public function getFavorites($studentId) {
        // Cette méthode est théorique car la table des favoris n'existe pas encore
        return $this->db->fetchAll("
            SELECT w.*, o.Offer_title, o.Description as offer_description, c.Name as company_name
            FROM wishlist w
            JOIN Offers o ON w.offer_id = o.ID_Offer
            JOIN Company c ON o.ID_Company = c.ID_Company
            WHERE w.student_id = ?
            ORDER BY w.created_at DESC
        ", [$studentId]);
    }

    public function getStudentsByPromotion($promotionId) {
        return $this->db->fetchAll("
            SELECT s.*, acc.* 
            FROM Student s
            JOIN Account acc ON s.ID_account = acc.ID_account
            WHERE s.promotion = ?
        ", [$promotionId]);
    }
}