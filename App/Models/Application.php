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
                c.Name as company_name, c.Logo as company_logo,
                SUBSTRING(c.Name, 1, 2) as company_initials,
                o.location, o.Starting_internship_date, o.monthly_remuneration,
                o.internship_duration
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
            $application['time_ago'] = $this->getTimeAgo($application['created_at']);
        }

        return $applications;
    }

    /**
     * Récupère les candidatures pour une offre donnée
     */
    public function findByOfferId($offerId, $limit = null, $offset = 0) {
        $sql = "
            SELECT a.*, 
                acc.Username as student_name, acc.Email as student_email,
                s.CV, s.promotion, s.school_name, s.study_field
            FROM applications a
            JOIN Student s ON a.student_id = s.ID_account
            JOIN Account acc ON s.ID_account = acc.ID_account
            WHERE a.offer_id = ?
            ORDER BY a.created_at DESC
        ";

        $params = [$offerId];

        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }

        $applications = $this->db->fetchAll($sql, $params);

        // Ajouter les labels pour les statuts
        foreach ($applications as &$application) {
            $application['status_label'] = self::$statusLabels[$application['status']] ?? 'Inconnu';
            $application['time_ago'] = $this->getTimeAgo($application['created_at']);
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
                o.Offer_title, o.Description as offer_description, 
                c.Name as company_name, c.ID_Company as company_id, c.Logo as company_logo,
                acc.Username as student_name, acc.Email as student_email, 
                s.CV, s.promotion, s.school_name, s.study_field
            FROM applications a
            JOIN Offers o ON a.offer_id = o.ID_Offer
            JOIN Company c ON o.ID_Company = c.ID_Company
            JOIN Student s ON a.student_id = s.ID_account
            JOIN Account acc ON s.ID_account = acc.ID_account
            WHERE a.id = ?
        ", [$id]);

        if ($application) {
            $application['status_label'] = self::$statusLabels[$application['status']] ?? 'Inconnu';
            $application['time_ago'] = $this->getTimeAgo($application['created_at']);
        }

        return $application;
    }

    /**
     * Récupère les statistiques des candidatures
     */
    public function getApplicationStatistics($userId = null, $role = null) {
        $stats = [];
        $params = [];
        $whereClause = '';

        // Filtrer par utilisateur et rôle si spécifié
        if ($userId && $role) {
            if ($role === 'student') {
                $whereClause = ' WHERE a.student_id = ?';
                $params[] = $userId;
            } elseif ($role === 'company') {
                $whereClause = ' WHERE o.ID_Company = ?';
                $params[] = $userId;
            }
        }

        // Total des candidatures
        $totalQuery = "SELECT COUNT(*) as count FROM applications a JOIN Offers o ON a.offer_id = o.ID_Offer" . $whereClause;
        $stats['total'] = $this->db->fetch($totalQuery, $params)['count'];

        // Candidatures par statut
        foreach (self::$statusLabels as $status => $label) {
            $statusQuery = "SELECT COUNT(*) as count FROM applications a JOIN Offers o ON a.offer_id = o.ID_Offer WHERE a.status = ?" . ($whereClause ? ' AND ' . substr($whereClause, 7) : '');
            $statusParams = array_merge([$status], $params);
            $stats[strtolower($status)] = $this->db->fetch($statusQuery, $statusParams)['count'];
        }

        return $stats;
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
     * Supprime une candidature
     */
    public function delete($id) {
        return $this->db->delete('applications', 'id = ?', [$id]);
    }

    /**
     * Formatte le temps écoulé depuis une date
     */
    private function getTimeAgo($datetime) {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;

        if ($diff < 60) {
            return "Il y a quelques secondes";
        } else if ($diff < 3600) {
            $mins = floor($diff / 60);
            return "Il y a " . $mins . " minute" . ($mins > 1 ? "s" : "");
        } else if ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "Il y a " . $hours . " heure" . ($hours > 1 ? "s" : "");
        } else if ($diff < 604800) {
            $days = floor($diff / 86400);
            return "Il y a " . $days . " jour" . ($days > 1 ? "s" : "");
        } else if ($diff < 2592000) {
            $weeks = floor($diff / 604800);
            return "Il y a " . $weeks . " semaine" . ($weeks > 1 ? "s" : "");
        } else if ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return "Il y a " . $months . " mois";
        } else {
            $years = floor($diff / 31536000);
            return "Il y a " . $years . " an" . ($years > 1 ? "s" : "");
        }
    }
}