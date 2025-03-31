<?php
namespace App\Models;

use App\Core\Database;

class Evaluation {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function createCompanyEvaluation($studentId, $companyId, $rating) {
        // Vérifier si l'étudiant a déjà évalué cette entreprise
        $existing = $this->db->fetch("
            SELECT * FROM company_evaluations 
            WHERE student_id = ? AND company_id = ?
        ", [$studentId, $companyId]);

        if ($existing) {
            // Mise à jour de l'évaluation existante
            return $this->db->update('company_evaluations', [
                'rating' => $rating,
                'created_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$existing['id']]);
        }

        // Création d'une nouvelle évaluation
        return $this->db->insert('company_evaluations', [
            'student_id' => $studentId,
            'company_id' => $companyId,
            'rating' => $rating,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getCompanyEvaluations($companyId) {
        return $this->db->fetchAll("
            SELECT ce.*, acc.Username as student_name
            FROM company_evaluations ce
            JOIN Student s ON ce.student_id = s.ID_account
            JOIN Account acc ON s.ID_account = acc.ID_account
            WHERE ce.company_id = ?
            ORDER BY ce.created_at DESC
        ", [$companyId]);
    }

    public function getAverageCompanyRating($companyId) {
        $result = $this->db->fetch("
            SELECT AVG(rating) as avg_rating 
            FROM company_evaluations 
            WHERE company_id = ?
        ", [$companyId]);

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
}