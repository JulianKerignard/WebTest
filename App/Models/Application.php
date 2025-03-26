<?php
namespace App\Models;

use App\Core\Database;

class Application {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($studentId, $offerId, $coverLetter, $cvPath, $status = 'pending') {
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
        return $this->db->fetchAll("
            SELECT a.*, acc.Username as student_name, s.CV
            FROM applications a
            JOIN Student s ON a.student_id = s.ID_account
            JOIN Account acc ON s.ID_account = acc.ID_account
            WHERE a.offer_id = ?
            ORDER BY a.created_at DESC
        ", [$offerId]);
    }

    public function updateStatus($applicationId, $status, $feedback = null, $interviewDate = null) {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($feedback) {
            $data['feedback'] = $feedback;
        }

        if ($interviewDate && $status === 'interview') {
            $data['interview_date'] = $interviewDate;
        }

        return $this->db->update('applications', $data, 'id = ?', [$applicationId]);
    }

    public function findById($id) {
        return $this->db->fetch("
            SELECT a.*, o.Offer_title, o.Description as offer_description, c.Name as company_name,
                   acc.Username as student_name, s.CV
            FROM applications a
            JOIN Offers o ON a.offer_id = o.ID_Offer
            JOIN Company c ON o.ID_Company = c.ID_Company
            JOIN Student s ON a.student_id = s.ID_account
            JOIN Account acc ON s.ID_account = acc.ID_account
            WHERE a.id = ?
        ", [$id]);
    }

    public function getApplicationStatistics() {
        return [
            'total' => $this->db->fetch("SELECT COUNT(*) as count FROM applications")['count'],
            'pending' => $this->db->fetch("SELECT COUNT(*) as count FROM applications WHERE status = 'pending'")['count'],
            'in_review' => $this->db->fetch("SELECT COUNT(*) as count FROM applications WHERE status = 'in-review'")['count'],
            'interview' => $this->db->fetch("SELECT COUNT(*) as count FROM applications WHERE status = 'interview'")['count'],
            'accepted' => $this->db->fetch("SELECT COUNT(*) as count FROM applications WHERE status = 'accepted'")['count'],
            'rejected' => $this->db->fetch("SELECT COUNT(*) as count FROM applications WHERE status = 'rejected'")['count']
        ];
    }

    public function getApplicationsCountByOffer() {
        return $this->db->fetchAll("
            SELECT o.ID_Offer, o.Offer_title, c.Name as company_name, COUNT(a.id) as application_count
            FROM Offers o
            LEFT JOIN applications a ON o.ID_Offer = a.offer_id
            JOIN Company c ON o.ID_Company = c.ID_Company
            GROUP BY o.ID_Offer
            ORDER BY application_count DESC
        ");
    }

    public function getRecentApplications($limit = 10) {
        return $this->db->fetchAll("
            SELECT a.*, o.Offer_title, c.Name as company_name, acc.Username as student_name
            FROM applications a
            JOIN Offers o ON a.offer_id = o.ID_Offer
            JOIN Company c ON o.ID_Company = c.ID_Company
            JOIN Account acc ON a.student_id = acc.ID_account
            ORDER BY a.created_at DESC
            LIMIT ?
        ", [$limit]);
    }

    public function hasStudentApplied($studentId, $offerId) {
        $result = $this->db->fetch("
            SELECT COUNT(*) as count
            FROM applications
            WHERE student_id = ? AND offer_id = ?
        ", [$studentId, $offerId]);

        return $result['count'] > 0;
    }
}