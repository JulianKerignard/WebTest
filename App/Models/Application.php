<?php
namespace App\Models;

use App\Core\Database;

class Application {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Cette table n'existe pas encore dans le schéma SQL, on va la créer conceptuellement

    public function create($studentId, $offerId, $coverLetter, $cvPath, $status = 'pending') {
        // Note: Cette méthode est théorique car la table n'existe pas encore
        return $this->db->insert('applications', [
            'student_id' => $studentId,
            'offer_id' => $offerId,
            'cover_letter' => $coverLetter,
            'cv_path' => $cvPath,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function findByStudentId($studentId) {
        // Note: Cette méthode est théorique car la table n'existe pas encore
        return $this->db->fetchAll("
            SELECT a.*, o.Offer_title, o.Description as offer_description, c.Name as company_name
            FROM applications a
            JOIN Offers o ON a.offer_id = o.ID_Offer
            JOIN Company c ON o.ID_Company = c.ID_Company
            WHERE a.student_id = ?
            ORDER BY a.created_at DESC
        ", [$studentId]);
    }

    public function findByOfferId($offerId) {
        // Note: Cette méthode est théorique car la table n'existe pas encore
        return $this->db->fetchAll("
            SELECT a.*, acc.Username as student_name
            FROM applications a
            JOIN Student s ON a.student_id = s.ID_account
            JOIN Account acc ON s.ID_account = acc.ID_account
            WHERE a.offer_id = ?
            ORDER BY a.created_at DESC
        ", [$offerId]);
    }

    public function updateStatus($applicationId, $status) {
        // Note: Cette méthode est théorique car la table n'existe pas encore
        return $this->db->update('applications', [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$applicationId]);
    }

    public function findById($id) {
        // Note: Cette méthode est théorique car la table n'existe pas encore
        return $this->db->fetch("
            SELECT a.*, o.Offer_title, o.Description as offer_description, c.Name as company_name,
                   acc.Username as student_name
            FROM applications a
            JOIN Offers o ON a.offer_id = o.ID_Offer
            JOIN Company c ON o.ID_Company = c.ID_Company
            JOIN Student s ON a.student_id = s.ID_account
            JOIN Account acc ON s.ID_account = acc.ID_account
            WHERE a.id = ?
        ", [$id]);
    }
}