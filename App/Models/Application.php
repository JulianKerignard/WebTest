<?php
namespace App\Controllers;

use App\Core\App;
use App\Core\Template;
use App\Models\Application;
use App\Models\Internship;
use App\Models\Student;
use App\Models\Company;
use App\Helpers\FileHelper;
use App\Helpers\SecurityHelper;

class ApplicationController {
    private $template;
    private $applicationModel;
    private $internshipModel;
    private $studentModel;
    private $companyModel;

    public function __construct() {
        $this->template = new Template();
        $this->applicationModel = new Application();
        $this->internshipModel = new Internship();
        $this->studentModel = new Student();
        $this->companyModel = new Company();
    }

    /**
     * Afficher les candidatures de l'étudiant connecté
     */
    public function index() {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user) {
            $session->setFlash('error', 'Vous devez être connecté pour accéder à cette page');
            return App::$app->response->redirect('/login');
        }

        // Récupérer les paramètres de pagination
        $request = App::$app->request;
        $page = (int)$request->get('page', 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Récupérer les candidatures selon le rôle
        $applications = [];
        $totalApplications = 0;

        if ($user['role'] === 'student') {
            $applications = $this->applicationModel->findByStudentId($user['id'], $limit, $offset);
            // Compter le nombre total de candidatures pour la pagination
            $stats = $this->applicationModel->getApplicationStatistics($user['id'], 'student');
            $totalApplications = $stats['total'];

            return $this->template->renderWithLayout('student/applications', 'dashboard', [
                'applications' => $applications,
                'stats' => $stats,
                'pagination' => [
                    'page' => $page,
                    'total_pages' => ceil($totalApplications / $limit),
                    'total_items' => $totalApplications
                ],
                'user' => $user
            ]);
        } elseif ($user['role'] === 'company') {
            // Récupérer l'ID de l'entreprise associée à cet utilisateur
            $company = $this->companyModel->findByAccountId($user['id']);

            if (!$company) {
                $session->setFlash('error', 'Aucune entreprise associée à votre compte');
                return App::$app->response->redirect('/company/dashboard');
            }

            $applications = $this->applicationModel->findByCompanyId($company['ID_Company'], $limit, $offset);

            // Statistiques de candidatures
            $stats = $this->applicationModel->getApplicationStatistics($company['ID_Company'], 'company');
            $totalApplications = $stats['total'];

            return $this->template->renderWithLayout('company/applications', 'dashboard', [
                'applications' => $applications,
                'stats' => $stats,
                'pagination' => [
                    'page' => $page,
                    'total_pages' => ceil($totalApplications / $limit),
                    'total_items' => $totalApplications
                ],
                'user' => $user
            ]);
        } elseif ($user['role'] === 'admin' || $user['role'] === 'pilot') {
            // Pour les administrateurs ou pilotes, afficher toutes les candidatures
            $sql = "SELECT a.*, o.Offer_title, c.Name as company_name
                  FROM applications a 
                  JOIN Offers o ON a.offer_id = o.ID_Offer
                  JOIN Company c ON o.ID_Company = c.ID_Company
                  ORDER BY a.created_at DESC LIMIT ?, ?";
            $applications = App::$app->db->fetchAll($sql, [$offset, $limit]);

            $totalSql = "SELECT COUNT(*) as count FROM applications";
            $totalApplications = App::$app->db->fetch($totalSql)['count'];

            return $this->template->renderWithLayout('admin/applications/index', 'dashboard', [
                'applications' => $applications,
                'pagination' => [
                    'page' => $page,
                    'total_pages' => ceil($totalApplications / $limit),
                    'total_items' => $totalApplications
                ],
                'user' => $user
            ]);
        }

        $session->setFlash('error', 'Accès non autorisé');
        return App::$app->response->redirect('/');
    }

    /**
     * Afficher les détails d'une candidature
     */
    public function show($id) {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user) {
            $session->setFlash('error', 'Vous devez être connecté pour accéder à cette page');
            return App::$app->response->redirect('/login');
        }

        // Récupérer la candidature avec toutes les informations nécessaires
        $application = $this->applicationModel->findById($id);

        // Vérification critique : rediriger si l'application n'existe pas
        if (!$application) {
            $session->setFlash('error', 'Candidature non trouvée');
            return App::$app->response->redirect('/applications');
        }

        // Vérifier les permissions
        $hasAccess = false;

        if ($user['role'] === 'student' && $application['student_id'] == $user['id']) {
            $hasAccess = true;
            $viewTemplate = 'student/application-details';
        } elseif ($user['role'] === 'company') {
            // Vérifier si cette candidature concerne une offre de cette entreprise
            $company = $this->companyModel->findByAccountId($user['id']);
            if ($company && $application['company_id'] == $company['ID_Company']) {
                $hasAccess = true;
                $viewTemplate = 'company/application-details';
            }
        } elseif ($user['role'] === 'admin' || $user['role'] === 'pilot') {
            $hasAccess = true;
            $viewTemplate = 'admin/applications/show';
        }

        if (!$hasAccess) {
            $session->setFlash('error', 'Vous n\'êtes pas autorisé à accéder à cette candidature');
            return App::$app->response->redirect('/applications');
        }

        // Ajouter les données formatées nécessaires à l'affichage
        $this->prepareApplicationData($application);

        return $this->template->renderWithLayout($viewTemplate, 'dashboard', [
            'application' => $application,
            'status_options' => Application::$statusLabels,
            'user' => $user,
            'csrf_token' => SecurityHelper::generateCSRFToken()
        ]);
    }

    /**
     * Prépare les données de candidature pour l'affichage
     */
    private function prepareApplicationData(&$application) {
        // Générer le chemin du CV s'il existe
        if (!empty($application['cv_path'])) {
            $application['cv_url'] = '/uploads/cv/' . $application['cv_path'];
        }

        // Formater la date d'entretien si elle existe
        if (!empty($application['interview_date'])) {
            $application['interview_date_formatted'] = date('d/m/Y H:i', strtotime($application['interview_date']));
        }

        // Récupérer l'historique des statuts s'il n'est pas déjà présent
        if (!isset($application['status_history'])) {
            $application['status_history'] = $this->applicationModel->getApplicationHistory($application['id']);
        }

        // Ajouter les libellés des statuts pour l'historique
        if (isset($application['status_history']) && is_array($application['status_history'])) {
            foreach ($application['status_history'] as &$history) {
                $history['status_label'] = Application::$statusLabels[$history['status']] ?? 'Inconnu';
                $history['time_ago'] = $this->getTimeAgo($history['created_at']);
            }
        }

        // Récupérer les notes sur la candidature si elles ne sont pas déjà présentes
        if (!isset($application['notes'])) {
            $application['notes'] = $this->applicationModel->getApplicationNotes($application['id']);
        }
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

    /**
     * Créer une nouvelle candidature
     */
    public function apply() {
        $request = App::$app->request;
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'student') {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Vous devez être connecté en tant qu\'étudiant pour postuler'
            ], 403);
        }

        $data = $request->getBody();
        $offerId = $data['offer_id'] ?? null;
        $coverLetter = $data['cover_letter'] ?? null;

        if (!$offerId || !$coverLetter) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'L\'identifiant de l\'offre et la lettre de motivation sont obligatoires'
            ], 400);
        }

        // Vérifier si l'offre existe
        $internship = $this->internshipModel->findById($offerId);
        if (!$internship) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Stage non trouvé'
            ], 404);
        }

        // Vérifier si l'étudiant a déjà postulé
        if ($this->applicationModel->hasStudentApplied($user['id'], $offerId)) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Vous avez déjà postulé à cette offre'
            ], 400);
        }

        // Gérer l'upload du CV
        $cvPath = null;
        $cvFile = $request->getFile('cv');

        if ($cvFile && $cvFile['error'] === UPLOAD_ERR_OK) {
            $result = FileHelper::uploadFile($cvFile, 'cv');
            if ($result['success']) {
                $cvPath = $result['filename'];

                // Mettre à jour le CV de l'étudiant
                $this->studentModel->update($user['id'], [
                    'CV' => $cvPath
                ]);
            } else {
                return App::$app->response->json([
                    'success' => false,
                    'message' => $result['error']
                ], 400);
            }
        } else {
            // Utiliser le CV existant de l'étudiant
            $student = $this->studentModel->findById($user['id']);
            $cvPath = $student['CV'] ?? null;
        }

        if (!$cvPath) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Vous devez télécharger un CV pour postuler'
            ], 400);
        }

        // Créer la candidature
        $result = $this->applicationModel->create($user['id'], $offerId, $coverLetter, $cvPath);

        if ($result['success']) {
            return App::$app->response->json([
                'success' => true,
                'message' => 'Votre candidature a été envoyée avec succès',
                'id' => $result['id']
            ]);
        } else {
            return App::$app->response->json([
                'success' => false,
                'message' => $result['message']
            ], 500);
        }
    }

    /**
     * Mettre à jour le statut d'une candidature
     */
    public function updateStatus() {
        $request = App::$app->request;
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'pilot' && $user['role'] !== 'company')) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $applicationId = $request->get('application_id');
        $status = $request->get('status');
        $feedback = $request->get('feedback');

        if (!$applicationId || !$status) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'L\'identifiant de la candidature et le statut sont obligatoires'
            ], 400);
        }

        // Vérifier si la candidature existe
        $application = $this->applicationModel->findById($applicationId);
        if (!$application) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Candidature non trouvée'
            ], 404);
        }

        // Vérifier les permissions pour les entreprises
        if ($user['role'] === 'company') {
            $company = $this->companyModel->findByAccountId($user['id']);
            if (!$company || $application['company_id'] != $company['ID_Company']) {
                return App::$app->response->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à modifier cette candidature'
                ], 403);
            }
        }

        // Mettre à jour le statut
        $result = $this->applicationModel->updateStatus($applicationId, $status, $feedback);

        if ($result['success']) {
            return App::$app->response->json([
                'success' => true,
                'message' => 'Statut de la candidature mis à jour avec succès'
            ]);
        } else {
            return App::$app->response->json([
                'success' => false,
                'message' => $result['message']
            ], 500);
        }
    }

    /**
     * Ajouter une note à une candidature
     */
    public function addNote() {
        $request = App::$app->request;
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'pilot' && $user['role'] !== 'company')) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $applicationId = $request->get('application_id');
        $content = $request->get('note');

        if (!$applicationId || !$content) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'L\'identifiant de la candidature et le contenu sont obligatoires'
            ], 400);
        }

        // Vérifier si la candidature existe
        $application = $this->applicationModel->findById($applicationId);
        if (!$application) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Candidature non trouvée'
            ], 404);
        }

        // Vérifier les permissions pour les entreprises
        if ($user['role'] === 'company') {
            $company = $this->companyModel->findByAccountId($user['id']);
            if (!$company || $application['company_id'] != $company['ID_Company']) {
                return App::$app->response->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à ajouter une note à cette candidature'
                ], 403);
            }
        }

        // Ajouter la note
        $result = $this->applicationModel->addNote($applicationId, $user['id'], $content);

        if ($result['success']) {
            return App::$app->response->json([
                'success' => true,
                'message' => 'Note ajoutée avec succès',
                'notes' => $result['notes']
            ]);
        } else {
            return App::$app->response->json([
                'success' => false,
                'message' => $result['message']
            ], 500);
        }
    }

    /**
     * Télécharger le CV d'une candidature
     */
    public function downloadCV($id) {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user) {
            $session->setFlash('error', 'Vous devez être connecté pour accéder à cette page');
            return App::$app->response->redirect('/login');
        }

        // Récupérer la candidature
        $application = $this->applicationModel->findById($id);

        if (!$application) {
            $session->setFlash('error', 'Candidature non trouvée');
            return App::$app->response->redirect('/applications');
        }

        // Vérifier les permissions
        $hasAccess = false;

        if ($user['role'] === 'student' && $application['student_id'] == $user['id']) {
            $hasAccess = true;
        } elseif ($user['role'] === 'company') {
            $company = $this->companyModel->findByAccountId($user['id']);
            if ($company && $application['company_id'] == $company['ID_Company']) {
                $hasAccess = true;
            }
        } elseif ($user['role'] === 'admin' || $user['role'] === 'pilot') {
            $hasAccess = true;
        }

        if (!$hasAccess) {
            $session->setFlash('error', 'Vous n\'êtes pas autorisé à accéder à ce CV');
            return App::$app->response->redirect('/applications');
        }

        // Vérifier si le CV existe
        if (empty($application['cv_path'])) {
            $session->setFlash('error', 'CV non disponible');
            return App::$app->response->redirect('/applications/' . $id);
        }

        // Chemin du fichier
        $filePath = __DIR__ . '/../../storage/uploads/cv/' . $application['cv_path'];

        if (!file_exists($filePath)) {
            $session->setFlash('error', 'Fichier non trouvé');
            return App::$app->response->redirect('/applications/' . $id);
        }

        // Envoyer le fichier
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="CV_' . $application['student_name'] . '.pdf"');
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
        exit;
    }
}