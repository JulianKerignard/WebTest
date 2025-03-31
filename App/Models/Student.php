<?php
namespace App\Models;

use App\Core\Model;
use App\Helpers\FileHelper;

class Student extends Model {
    protected $table = 'Student';
    protected $primaryKey = 'ID_account';

    /**
     * Récupère un étudiant avec les informations de son compte
     */
    public function findById($id) {
        return $this->db->fetch("
            SELECT s.*, acc.* 
            FROM Student s
            JOIN Account acc ON s.ID_account = acc.ID_account
            WHERE s.ID_account = ?
        ", [$id]);
    }

    /**
     * Récupère tous les étudiants avec les informations de leur compte
     */
    public function findAll($limit = null, $offset = 0) {
        $sql = "
            SELECT s.*, acc.* 
            FROM Student s
            JOIN Account acc ON s.ID_account = acc.ID_account
            ORDER BY acc.Username
        ";

        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            return $this->db->fetchAll($sql, [$limit, $offset]);
        }

        return $this->db->fetchAll($sql);
    }

    /**
     * Crée un nouveau profil étudiant
     */
    public function create($accountId, $data = []) {
        $now = date('Y-m-d');
        return $this->db->insert('Student', [
            'ID_account' => $accountId,
            'Licence' => $data['Licence'] ?? 0,
            'Majority' => $data['Majority'] ?? $now,
            'promotion' => $data['promotion'] ?? null,
            'CV' => $data['CV'] ?? null,
            'school_name' => $data['school_name'] ?? null,
            'study_field' => $data['study_field'] ?? null
        ]);
    }

    /**
     * Télécharge un CV pour un étudiant
     */
    public function uploadCV($studentId, $file) {
        $result = FileHelper::uploadFile($file, 'cv');

        if ($result['success']) {
            $this->db->update('Student', [
                'CV' => $result['filename']
            ], 'ID_account = ?', [$studentId]);

            return $result;
        }

        return $result;
    }

    /**
     * Récupère les candidatures d'un étudiant
     */
    public function getAppliedInternships($studentId) {
        return $this->db->fetchAll("
            SELECT a.*, o.Offer_title, o.Description as offer_description, c.Name as company_name
            FROM applications a
            JOIN Offers o ON a.offer_id = o.ID_Offer
            JOIN Company c ON o.ID_Company = c.ID_Company
            WHERE a.student_id = ?
            ORDER BY a.created_at DESC
        ", [$studentId]);
    }

    /**
     * Récupère les stages favoris d'un étudiant
     */
    public function getFavorites($studentId) {
        return $this->db->fetchAll("
            SELECT w.*, o.Offer_title, o.Description as offer_description, c.Name as company_name
            FROM wishlist w
            JOIN Offers o ON w.offer_id = o.ID_Offer
            JOIN Company c ON o.ID_Company = c.ID_Company
            WHERE w.student_id = ?
            ORDER BY w.created_at DESC
        ", [$studentId]);
    }

    /**
     * Récupère les étudiants d'une promotion
     */
    public function getStudentsByPromotion($promotionId) {
        return $this->db->fetchAll("
            SELECT s.*, acc.* 
            FROM Student s
            JOIN Account acc ON s.ID_account = acc.ID_account
            WHERE s.promotion = ?
        ", [$promotionId]);
    }
}