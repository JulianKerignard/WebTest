<?php
namespace App\Models;

use App\Core\Database;

class Evaluation {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Cette table n'existe pas encore dans le schéma SQL, on va la créer conceptuellement

    public function createCompanyEvaluation($studentId, $companyId, $rating, $comment = null) {
        // Note: Cette méthode est théorique car la table n'existe pas encore
        return $this->db->insert('company_evaluations', [
            'student_id' => $studentId,
            'company_id' => $companyId,
            'rating' => $rating,
            'comment' => $comment,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function createInternshipEvaluation($studentId, $offerId, $rating, $comment = null) {
        // Note: Cette méthode est théorique car la table n'existe pas encore
        return $this->db->insert('internship_evaluations', [
            'student_id' => $studentId,
            'offer_id' => $offerId,
            'rating' => $rating,
            'comment' => $comment,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getCompanyEvaluations($companyId) {
        // Note: Cette méthode est théorique car la table n'existe pas encore
        return $this->db->fetchAll("
            SELECT ce.*, acc.Username as student_name
            FROM company_evaluations ce
            JOIN Student s ON ce.student_id = s.ID_account
            JOIN Account acc ON s.ID_account = acc.ID_account
            WHERE ce.company_id = ?
            ORDER BY ce.created_at DESC
        ", [$companyId]);
    }

    public function getInternshipEvaluations($offerId) {
        // Note: Cette méthode est théorique car la table n'existe pas encore
        return $this->db->fetchAll("
            SELECT ie.*, acc.Username as student_name
            FROM internship_evaluations ie
            JOIN Student s ON ie.student_id = s.ID_account
            JOIN Account acc ON s.ID_account = acc.ID_account
            WHERE ie.offer_id = ?
            ORDER BY ie.created_at DESC
        ", [$offerId]);
    }

    public function getAverageCompanyRating($companyId) {
        // Note: Cette méthode est théorique car la table n'existe pas encore
        $result = $this->db->fetch("
            SELECT AVG(rating) as avg_rating 
            FROM company_evaluations 
            WHERE company_id = ?
        ", [$companyId]);

        return $result ? $result['avg_rating'] : 0;
    }

    public function getAverageInternshipRating($offerId) {
        // Note: Cette méthode est théorique car la table n'existe pas encore
        $result = $this->db->fetch("
            SELECT AVG(rating) as avg_rating 
            FROM internship_evaluations 
            WHERE offer_id = ?
        ", [$offerId]);

        return $result ? $result['avg_rating'] : 0;
    }
}