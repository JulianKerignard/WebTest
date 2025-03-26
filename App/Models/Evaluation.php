<?php
namespace App\Models;

use App\Core\Database;

class Evaluation {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function createCompanyEvaluation($studentId, $companyId, $rating, $comment = null, $isPublic = true) {
        // Vérifier si l'étudiant a déjà évalué cette entreprise
        $existing = $this->db->fetch("
            SELECT * FROM company_evaluations 
            WHERE student_id = ? AND company_id = ?
        ", [$studentId, $companyId]);

        if ($existing) {
            // Mise à jour de l'évaluation existante
            return $this->db->update('company_evaluations', [
                'rating' => $rating,
                'comment' => $comment,
                'is_public' => $isPublic ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$existing['id']]);
        }

        // Création d'une nouvelle évaluation
        return $this->db->insert('company_evaluations', [
            'student_id' => $studentId,
            'company_id' => $companyId,
            'rating' => $rating,
            'comment' => $comment,
            'is_public' => $isPublic ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function createInternshipEvaluation($studentId, $offerId, $rating, $comment = null, $isPublic = true) {
        // Vérifier si l'étudiant a déjà évalué ce stage
        $existing = $this->db->fetch("
            SELECT * FROM internship_evaluations 
            WHERE student_id = ? AND offer_id = ?
        ", [$studentId, $offerId]);

        if ($existing) {
            // Mise à jour de l'évaluation existante
            return $this->db->update('internship_evaluations', [
                'rating' => $rating,
                'comment' => $comment,
                'is_public' => $isPublic ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$existing['id']]);
        }

        // Création d'une nouvelle évaluation
        return $this->db->insert('internship_evaluations', [
            'student_id' => $studentId,
            'offer_id' => $offerId,
            'rating' => $rating,
            'comment' => $comment,
            'is_public' => $isPublic ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getCompanyEvaluations($companyId) {
        return $this->db->fetchAll("
            SELECT ce.*, acc.Username as student_name
            FROM company_evaluations ce
            JOIN Student s ON ce.student_id = s.ID_account
            JOIN Account acc ON s.ID_account = acc.ID_account
            WHERE ce.company_id = ? AND ce.is_public = 1
            ORDER BY ce.created_at DESC
        ", [$companyId]);
    }

    public function getInternshipEvaluations($offerId) {
        return $this->db->fetchAll("
            SELECT ie.*, acc.Username as student_name
            FROM internship_evaluations ie
            JOIN Student s ON ie.student_id = s.ID_account
            JOIN Account acc ON s.ID_account = acc.ID_account
            WHERE ie.offer_id = ? AND ie.is_public = 1
            ORDER BY ie.created_at DESC
        ", [$offerId]);
    }

    public function getAverageCompanyRating($companyId) {
        $result = $this->db->fetch("
            SELECT AVG(rating) as avg_rating 
            FROM company_evaluations 
            WHERE company_id = ?
        ", [$companyId]);

        return $result ? round($result['avg_rating'], 1) : 0;
    }

    public function getAverageInternshipRating($offerId) {
        $result = $this->db->fetch("
            SELECT AVG(rating) as avg_rating 
            FROM internship_evaluations 
            WHERE offer_id = ?
        ", [$offerId]);

        return $result ? round($result['avg_rating'], 1) : 0;
    }

    public function hasStudentEvaluatedCompany($studentId, $companyId) {
        $result = $this->db->fetch("
            SELECT COUNT(*) as count 
            FROM company_evaluations 
            WHERE student_id = ? AND company_id = ?
        ", [$studentId, $companyId]);

        return $result['count'] > 0;
    }

    public function hasStudentEvaluatedInternship($studentId, $offerId) {
        $result = $this->db->fetch("
            SELECT COUNT(*) as count 
            FROM internship_evaluations 
            WHERE student_id = ? AND offer_id = ?
        ", [$studentId, $offerId]);

        return $result['count'] > 0;
    }

    public function getTopRatedCompanies($limit = 5) {
        return $this->db->fetchAll("
            SELECT c.ID_Company, c.Name, c.ID_Sector, AVG(ce.rating) as avg_rating, COUNT(ce.id) as reviews_count
            FROM Company c
            JOIN company_evaluations ce ON c.ID_Company = ce.company_id
            GROUP BY c.ID_Company
            HAVING reviews_count >= 3
            ORDER BY avg_rating DESC
            LIMIT ?
        ", [$limit]);
    }

    public function deleteCompanyEvaluation($id, $studentId) {
        return $this->db->delete('company_evaluations', 'id = ? AND student_id = ?', [$id, $studentId]);
    }

    public function deleteInternshipEvaluation($id, $studentId) {
        return $this->db->delete('internship_evaluations', 'id = ? AND student_id = ?', [$id, $studentId]);
    }
}