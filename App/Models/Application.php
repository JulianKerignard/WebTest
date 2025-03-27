<?php
namespace App\Models;

use App\Core\Database;

class Application {
    private $db;

    // Statuts possibles d'une candidature
    const STATUS_PENDING = 'pending';
    const STATUS_IN_REVIEW = 'in-review';
    const STATUS_INTERVIEW = 'interview';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    // Tableau des statuts disponibles pour l'affichage
    public static $statusLabels = [
        self::STATUS_PENDING => 'En attente',
        self::STATUS_IN_REVIEW => 'En cours d\'examen',
        self::STATUS_INTERVIEW => 'Entretien',
        self::STATUS_ACCEPTED => 'Acceptée',
        self::STATUS_REJECTED => 'Refusée'
    ];

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Crée une nouvelle candidature
     */
    public function create($studentId, $offerId, $coverLetter, $cvPath, $status = self::STATUS_PENDING) {
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
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if ($applicationId) {
            // Enregistrer l'historique de statut
            $this->addStatusHistory($applicationId, $status, 'Candidature créée');

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

            // Formatter la date d'entretien si elle existe
            if (!empty($application['interview_date'])) {
                $application['interview_date_formatted'] = date('d/m/Y à H:i', strtotime($application['interview_date']));
            }
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

            // Formatter la date d'entretien si elle existe
            if (!empty($application['interview_date'])) {
                $application['interview_date_formatted'] = date('d/m/Y à H:i', strtotime($application['interview_date']));
            }
        }

        return $applications;
    }

    /**
     * Récupère les candidatures pour une entreprise donnée
     */
    public function findByCompanyId($companyId, $limit = null, $offset = 0, $filters = []) {
        $sql = "
            SELECT a.*, 
                acc.Username as student_name, acc.Email as student_email,
                s.CV, s.promotion, s.school_name, s.study_field,
                o.Offer_title, o.Description as offer_description, 
                o.location, o.Starting_internship_date, o.monthly_remuneration,
                o.internship_duration
            FROM applications a
            JOIN Offers o ON a.offer_id = o.ID_Offer
            JOIN Student s ON a.student_id = s.ID_account
            JOIN Account acc ON s.ID_account = acc.ID_account
            WHERE o.ID_Company = ?
        ";

        $params = [$companyId];

        // Ajouter les filtres
        if (!empty($filters['status'])) {
            $sql .= " AND a.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['offer_id'])) {
            $sql .= " AND a.offer_id = ?";
            $params[] = $filters['offer_id'];
        }

        if (!empty($filters['search'])) {
            $search = "%{$filters['search']}%";
            $sql .= " AND (acc.Username LIKE ? OR o.Offer_title LIKE ? OR acc.Email LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY a.created_at DESC";

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

            // Formatter la date d'entretien si elle existe
            if (!empty($application['interview_date'])) {
                $application['interview_date_formatted'] = date('d/m/Y à H:i', strtotime($application['interview_date']));
            }
        }

        return $applications;
    }

    /**
     * Met à jour le statut d'une candidature
     */
    public function updateStatus($applicationId, $status, $feedback = null, $interviewDate = null) {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($feedback) {
            $data['feedback'] = $feedback;
        }

        if ($interviewDate && $status === self::STATUS_INTERVIEW) {
            $data['interview_date'] = $interviewDate;
        }

        $result = $this->db->update('applications', $data, 'id = ?', [$applicationId]);

        if ($result) {
            // Enregistrer l'historique de statut
            $this->addStatusHistory($applicationId, $status, $feedback);

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
     * Ajoute une entrée dans l'historique des statuts
     */
    private function addStatusHistory($applicationId, $status, $comment = null) {
        return $this->db->insert('application_status_history', [
            'application_id' => $applicationId,
            'status' => $status,
            'comment' => $comment,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Récupère l'historique des statuts d'une candidature
     */
    public function getStatusHistory($applicationId) {
        $history = $this->db->fetchAll("
            SELECT ash.*
            FROM application_status_history ash
            WHERE ash.application_id = ?
            ORDER BY ash.created_at DESC
        ", [$applicationId]);

        // Ajouter les labels pour les statuts
        foreach ($history as &$item) {
            $item['status_label'] = self::$statusLabels[$item['status']] ?? 'Inconnu';
            $item['time_ago'] = $this->getTimeAgo($item['created_at']);
        }

        return $history;
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

            // Formatter la date d'entretien si elle existe
            if (!empty($application['interview_date'])) {
                $application['interview_date_formatted'] = date('d/m/Y à H:i', strtotime($application['interview_date']));
            }

            // Récupérer l'historique des statuts
            $application['status_history'] = $this->getStatusHistory($id);
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

        // Candidatures récentes
        if ($userId && $role === 'student') {
            $stats['recent_activity'] = $this->findByStudentId($userId, 5);
        } elseif ($userId && $role === 'company') {
            $stats['recent_activity'] = $this->findByCompanyId($userId, 5);
        } else {
            $stats['recent_activity'] = $this->getRecentApplications(5);
        }

        // Taux de succès (pour les étudiants)
        if ($userId && $role === 'student' && $stats['total'] > 0) {
            $stats['success_rate'] = round(($stats['accepted'] / $stats['total']) * 100, 1);
        }

        // Temps moyen de réponse (pour les entreprises)
        if ($userId && $role === 'company') {
            $averageTimeQuery = "
                SELECT AVG(TIMESTAMPDIFF(DAY, a.created_at, a.updated_at)) as avg_time
                FROM applications a
                JOIN Offers o ON a.offer_id = o.ID_Offer
                WHERE o.ID_Company = ? AND a.status != 'pending' AND a.updated_at IS NOT NULL
            ";
            $avgTime = $this->db->fetch($averageTimeQuery, [$userId]);
            $stats['average_response_time'] = $avgTime['avg_time'] ? round($avgTime['avg_time'], 1) : 0;
        }

        return $stats;
    }

    /**
     * Récupère les candidatures récentes
     */
    public function getRecentApplications($limit = 10) {
        return $this->db->fetchAll("
            SELECT a.*, 
                o.Offer_title, c.Name as company_name, 
                acc.Username as student_name
            FROM applications a
            JOIN Offers o ON a.offer_id = o.ID_Offer
            JOIN Company c ON o.ID_Company = c.ID_Company
            JOIN Account acc ON a.student_id = acc.ID_account
            ORDER BY a.created_at DESC
            LIMIT ?
        ", [$limit]);
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
        // Supprimer l'historique des statuts d'abord
        $this->db->delete('application_status_history', 'application_id = ?', [$id]);

        // Supprimer la candidature
        return $this->db->delete('applications', 'id = ?', [$id]);
    }

    /**
     * Ajoute une note à une candidature
     */
    public function addNote($applicationId, $userId, $content) {
        return $this->db->insert('application_notes', [
            'application_id' => $applicationId,
            'user_id' => $userId,
            'content' => $content,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Récupère les notes d'une candidature
     */
    public function getNotes($applicationId) {
        return $this->db->fetchAll("
            SELECT an.*, acc.Username as author_name
            FROM application_notes an
            JOIN Account acc ON an.user_id = acc.ID_account
            WHERE an.application_id = ?
            ORDER BY an.created_at DESC
        ", [$applicationId]);
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