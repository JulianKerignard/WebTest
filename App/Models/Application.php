<?php
namespace App\Models;

use App\Core\Database;
use App\Core\App;

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
            $application['time_ago'] = $this->getTimeAgo($application['created_at']);
        }

        return $applications;
    }

    /**
     * Récupère les candidatures pour une entreprise
     */
    public function findByCompanyId($companyId, $limit = null, $offset = 0) {
        $sql = "
            SELECT a.*, o.Offer_title, o.Description as offer_description, 
                o.ID_Company as company_id, 
                s.ID_account as student_id, acc.Username as student_name
            FROM applications a
            JOIN Offers o ON a.offer_id = o.ID_Offer
            JOIN Student s ON a.student_id = s.ID_account
            JOIN Account acc ON s.ID_account = acc.ID_account
            WHERE o.ID_Company = ?
            ORDER BY a.created_at DESC
        ";

        $params = [$companyId];

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
     * Récupère une candidature par son ID avec toutes les informations associées
     * Version avec débogage pour identifier les problèmes
     */
    public function findById($id) {
        // Première requête pour vérifier si l'application existe
        try {
            $baseApplication = $this->db->fetch("SELECT * FROM applications WHERE id = ?", [$id]);

            if (!$baseApplication) {
                // Journaliser l'échec
                if (isset(App::$app->logger)) {
                    App::$app->logger->logError("Application introuvable avec l'ID {$id}");
                }
                return null;
            }

            // Si l'application existe, récupérer l'offre associée
            $offer = $this->db->fetch("SELECT * FROM Offers WHERE ID_Offer = ?", [$baseApplication['offer_id']]);
            if (!$offer) {
                // Journaliser l'échec
                if (isset(App::$app->logger)) {
                    App::$app->logger->logError("Offre introuvable pour l'application ID {$id}, offer_id {$baseApplication['offer_id']}");
                }
                // Retourner uniquement les données de base sans les relations
                $baseApplication['status_label'] = self::$statusLabels[$baseApplication['status']] ?? 'Inconnu';
                return $baseApplication;
            }

            // Si l'offre existe, récupérer l'entreprise associée
            $company = $this->db->fetch("SELECT * FROM Company WHERE ID_Company = ?", [$offer['ID_Company']]);
            if (!$company) {
                // Journaliser l'échec
                if (isset(App::$app->logger)) {
                    App::$app->logger->logError("Entreprise introuvable pour l'offre ID {$offer['ID_Offer']}, company_id {$offer['ID_Company']}");
                }
            }

            // Récupérer les informations de l'étudiant et du compte
            $student = $this->db->fetch("SELECT * FROM Student WHERE ID_account = ?", [$baseApplication['student_id']]);
            $account = $this->db->fetch("SELECT * FROM Account WHERE ID_account = ?", [$baseApplication['student_id']]);

            // Construire l'objet application complet
            $application = $baseApplication;

            // Ajouter les informations de l'offre
            if ($offer) {
                $application['Offer_title'] = $offer['Offer_title'];
                $application['offer_description'] = $offer['Description'];
                $application['location'] = $offer['location'];
                $application['internship_duration'] = $offer['internship_duration'];
                $application['monthly_remuneration'] = $offer['monthly_remuneration'];
                $application['Starting_internship_date'] = $offer['Starting_internship_date'];
                $application['company_id'] = $offer['ID_Company'];
            }

            // Ajouter les informations de l'entreprise
            if ($company) {
                $application['company_name'] = $company['Name'];
            }

            // Ajouter les informations du compte et de l'étudiant
            if ($account) {
                $application['student_name'] = $account['Username'];
                $application['student_email'] = $account['Email'];
            }

            if ($student) {
                $application['school_name'] = $student['school_name'];
                $application['study_field'] = $student['study_field'];
            }

            // Ajouter le libellé du statut
            $application['status_label'] = self::$statusLabels[$application['status']] ?? 'Inconnu';

            // Récupérer l'historique des statuts
            $application['status_history'] = $this->getApplicationHistory($id);

            // Récupérer les notes internes
            $application['notes'] = $this->getApplicationNotes($id);

            return $application;

        } catch (\Exception $e) {
            // Journaliser l'erreur
            if (isset(App::$app->logger)) {
                App::$app->logger->logError("Erreur dans findById: " . $e->getMessage());
            }
            return null;
        }
    }

    /**
     * Récupère l'historique des statuts d'une candidature
     */
    public function getApplicationHistory($applicationId) {
        try {
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
        } catch (\Exception $e) {
            // En cas d'erreur, retourner un tableau vide
            if (isset(App::$app->logger)) {
                App::$app->logger->logError("Erreur dans getApplicationHistory: " . $e->getMessage());
            }
            return [];
        }
    }

    /**
     * Récupère les notes internes d'une candidature
     */
    public function getApplicationNotes($applicationId) {
        try {
            return $this->db->fetchAll("
                SELECT n.*, a.Username as author_name
                FROM application_notes n
                JOIN Account a ON n.user_id = a.ID_account
                WHERE n.application_id = ?
                ORDER BY n.created_at DESC
            ", [$applicationId]);
        } catch (\Exception $e) {
            // En cas d'erreur, retourner un tableau vide
            if (isset(App::$app->logger)) {
                App::$app->logger->logError("Erreur dans getApplicationNotes: " . $e->getMessage());
            }
            return [];
        }
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

        try {
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
        } catch (\Exception $e) {
            // En cas d'erreur, initialiser les statistiques de base
            $stats['total'] = 0;
            foreach (self::$statusLabels as $status => $label) {
                $stats[$status] = 0;
            }
            $stats['wishlist'] = 0;

            if (isset(App::$app->logger)) {
                App::$app->logger->logError("Erreur dans getApplicationStatistics: " . $e->getMessage());
            }
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