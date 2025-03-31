<?php
namespace App\Models;

use App\Core\Model;

class Application extends Model {
    protected $table = 'applications';
    protected $primaryKey = 'id';

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

            // Calculer le temps écoulé depuis la création
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

        // Gérer le cas de l'entretien
        if ($status === self::STATUS_INTERVIEW && isset($_POST['interview_date'])) {
            $data['interview_date'] = $_POST['interview_date'];
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
     * Récupère une candidature par son ID avec toutes les informations associées
     */
    public function findById($id) {
        $application = $this->db->fetch("
            SELECT a.*, 
                o.Offer_title, o.Description as offer_description, o.location,
                o.internship_duration, o.monthly_remuneration, o.Starting_internship_date,
                c.Name as company_name, c.ID_Company as company_id,
                acc.Username as student_name, acc.Email as student_email,
                s.school_name, s.study_field
            FROM applications a
            JOIN Offers o ON a.offer_id = o.ID_Offer
            JOIN Company c ON o.ID_Company = c.ID_Company
            JOIN Student s ON a.student_id = s.ID_account
            JOIN Account acc ON s.ID_account = acc.ID_account
            WHERE a.id = ?
        ", [$id]);

        if ($application) {
            // Ajouter le label du statut
            $application['status_label'] = self::$statusLabels[$application['status']] ?? 'Inconnu';

            // Récupérer l'historique des statuts
            $application['status_history'] = $this->getApplicationHistory($id);

            // Récupérer les notes internes
            $application['notes'] = $this->getApplicationNotes($id);
        }

        return $application;
    }

    /**
     * Récupère l'historique des statuts d'une candidature
     */
    public function getApplicationHistory($applicationId) {
        $history = $this->db->fetchAll("
            SELECT * FROM application_status_history 
            WHERE application_id = ? 
            ORDER BY created_at DESC
        ", [$applicationId]);

        // Ajouter les libellés des statuts
        foreach ($history as &$entry) {
            $entry['status_label'] = self::$statusLabels[$entry['status']] ?? 'Inconnu';
            $entry['time_ago'] = $this->getTimeAgo($entry['created_at']);
        }

        return $history;
    }

    /**
     * Récupère les notes internes d'une candidature
     */
    public function getApplicationNotes($applicationId) {
        return $this->db->fetchAll("
            SELECT n.*, a.Username as author_name
            FROM application_notes n
            JOIN Account a ON n.user_id = a.ID_account
            WHERE n.application_id = ?
            ORDER BY n.created_at DESC
        ", [$applicationId]);
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
     * Ajoute une note interne à une candidature
     */
    public function addNote($applicationId, $userId, $content) {
        $result = $this->db->insert('application_notes', [
            'application_id' => $applicationId,
            'user_id' => $userId,
            'content' => $content,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if ($result) {
            return [
                'success' => true,
                'message' => 'Note ajoutée avec succès',
                'notes' => $this->getApplicationNotes($applicationId)
            ];
        }

        return [
            'success' => false,
            'message' => 'Erreur lors de l\'ajout de la note'
        ];
    }

    /**
     * Récupère des statistiques de candidatures pour un étudiant ou une entreprise
     */
    public function getApplicationStatistics($id, $type = 'student') {
        $stats = [];
        $field = $type === 'student' ? 'student_id' : 'company_id';

        // Pour le cas d'une entreprise, on a besoin d'une jointure
        $whereClause = $type === 'student'
            ? "a.student_id = ?"
            : "o.ID_Company = ?";

        $fromClause = $type === 'student'
            ? "FROM applications a"
            : "FROM applications a JOIN Offers o ON a.offer_id = o.ID_Offer";

        // Total des candidatures
        $sql = "SELECT COUNT(*) as count {$fromClause} WHERE {$whereClause}";
        $result = $this->db->fetch($sql, [$id]);
        $stats['total'] = $result['count'] ?? 0;

        // Par statut
        foreach (self::$statusLabels as $status => $label) {
            $sql = "SELECT COUNT(*) as count {$fromClause} WHERE {$whereClause} AND a.status = ?";
            $result = $this->db->fetch($sql, [$id, $status]);
            $stats[$status] = $result['count'] ?? 0;
        }

        // Pour les étudiants, ajouter les stages dans la wishlist
        if ($type === 'student') {
            $sql = "SELECT COUNT(*) as count FROM wishlist WHERE student_id = ?";
            $result = $this->db->fetch($sql, [$id]);
            $stats['wishlist'] = $result['count'] ?? 0;
        }

        return $stats;
    }

    /**
     * Calcule le temps écoulé depuis une date donnée
     */
    private function getTimeAgo($datetime) {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;

        if ($diff < 60) {
            return "il y a quelques secondes";
        } else if ($diff < 3600) {
            $mins = floor($diff / 60);
            return "il y a " . $mins . " minute" . ($mins > 1 ? "s" : "");
        } else if ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "il y a " . $hours . " heure" . ($hours > 1 ? "s" : "");
        } else if ($diff < 604800) {
            $days = floor($diff / 86400);
            return "il y a " . $days . " jour" . ($days > 1 ? "s" : "");
        } else if ($diff < 2592000) {
            $weeks = floor($diff / 604800);
            return "il y a " . $weeks . " semaine" . ($weeks > 1 ? "s" : "");
        } else if ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return "il y a " . $months . " mois";
        } else {
            $years = floor($diff / 31536000);
            return "il y a " . $years . " an" . ($years > 1 ? "s" : "");
        }
    }
}