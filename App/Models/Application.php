<?php
namespace App\Models;

use App\Core\Database;

class Application {
    private $db;

    // Statuts possibles d'une candidature (simplifiés)
    const STATUS_PENDING = 'pending';
    const STATUS_IN_REVIEW = 'in-review';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    // Tableau des statuts disponibles pour l'affichage
    public static $statusLabels = [
        self::STATUS_PENDING => 'En attente',
        self::STATUS_IN_REVIEW => 'En cours d\'examen',
        self::STATUS_ACCEPTED => 'Acceptée',
        self::STATUS_REJECTED => 'Refusée'
    ];

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Crée une nouvelle candidature
     */
    public function create($studentId, $offerId, $coverLetter, $cvPath) {
        // Vérifier si l'étudiant a déjà postulé à cette offre
        if ($this->hasStudentApplied($studentId, $offerId)) {
            return [
                'success' => false,
                'message' => 'Vous avez déjà postulé à cette offre'
            ];
        }

        $applicationId = $this->db->insert('applications', [
            'student_id' => $studentId,
            'offer_id' => $offerId,
            'cover_letter' => $coverLetter,
            'cv_path' => $cvPath,
            'status' => self::STATUS_PENDING,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if ($applicationId) {
            $this->db->insert('application_status_history', [
                'application_id' => $applicationId,
                'status' => self::STATUS_PENDING,
                'comment' => 'Candidature créée',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'id' => $applicationId,
                'message' => 'Candidature envoyée avec succès'
            ];
        }

        return [
            'success' => false,
            'message' => 'Erreur lors de l\'envoi de la candidature'
        ];
    }

    /**
     * Récupère les candidatures d'un étudiant
     */
    public function findByStudentId($studentId, $limit = null, $offset = 0) {
        $sql = "
            SELECT a.*, o.Offer_title, o.Description as offer_description, 
                c.Name as company_name, o.location
            FROM applications a
            JOIN Offers o ON a.offer_id = o.ID_Offer
            JOIN Company c ON o.ID_Company = c.ID_Company
            WHERE a.student_id = ?
            ORDER BY a.created_at DESC
        ";

        $params = [$studentId];

        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }

        $applications = $this->db->fetchAll($sql, $params);

        // Ajouter les labels pour les statuts
        foreach ($applications as &$application) {
            $application['status_label'] = self::$statusLabels[$application['status']] ?? 'Inconnu';
        }

        return $applications;
    }

    /**
     * Met à jour le statut d'une candidature
     */
    public function updateStatus($applicationId, $status, $feedback = null) {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($feedback) {
            $data['feedback'] = $feedback;
        }

        $result = $this->db->update('applications', $data, 'id = ?', [$applicationId]);

        if ($result) {
            // Ajouter un historique de statut
            $this->db->insert('application_status_history', [
                'application_id' => $applicationId,
                'status' => $status,
                'comment' => $feedback,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => 'Statut de la candidature mis à jour avec succès'
            ];
        }

        return [
            'success' => false,
            'message' => 'Erreur lors de la mise à jour du statut'
        ];
    }

    /**
     * Récupère une candidature par son ID
     */
    public function findById($id) {
        $application = $this->db->fetch("
            SELECT a.*, 
                o.Offer_title, o.Description as offer_description, o.location,
                o.internship_duration, o.monthly_remuneration, o.Starting_internship_date,
                c.Name as company_name, c.ID_Company as company_id
            FROM applications a
            JOIN Offers o ON a.offer_id = o.ID_Offer
            JOIN Company c ON o.ID_Company = c.ID_Company
            WHERE a.id = ?
        ", [$id]);

        if ($application) {
            $application['status_label'] = self::$statusLabels[$application['status']] ?? 'Inconnu';

            // Récupérer l'historique des statuts
            $application['status_history'] = $this->db->fetchAll("
                SELECT * FROM application_status_history 
                WHERE application_id = ? 
                ORDER BY created_at DESC
            ", [$id]);
        }

        return $application;
    }

    /**
     * Vérifie si un étudiant a déjà postulé à une offre
     */
    public function hasStudentApplied($studentId, $offerId) {
        $result = $this->db->fetch("
            SELECT COUNT(*) as count
            FROM applications
            WHERE student_id = ? AND offer_id = ?
        ", [$studentId, $offerId]);

        return $result['count'] > 0;
    }

    /**
     * Récupère des statistiques basiques de candidatures pour un étudiant
     */
    public function getStudentStats($studentId) {
        $stats = [];

        // Total des candidatures
        $result = $this->db->fetch("
            SELECT COUNT(*) as count FROM applications WHERE student_id = ?
        ", [$studentId]);
        $stats['total'] = $result['count'] ?? 0;

        // Par statut
        foreach (self::$statusLabels as $status => $label) {
            $result = $this->db->fetch("
                SELECT COUNT(*) as count FROM applications 
                WHERE student_id = ? AND status = ?
            ", [$studentId, $status]);
            $stats[$status] = $result['count'] ?? 0;
        }

        return $stats;
    }
}