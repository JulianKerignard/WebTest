<?php
namespace App\Controllers;

use App\Core\App;
use App\Core\Template;
use App\Models\Application;
use App\Models\Internship;
use App\Models\Student;
use App\Models\Company;
use App\Helpers\FileHelper;
use App\Services\EmailService;

class ApplicationController {
    private $template;
    private $applicationModel;
    private $internshipModel;
    private $studentModel;
    private $companyModel;
    private $emailService;

    public function __construct() {
        $this->template = new Template();
        $this->applicationModel = new Application();
        $this->internshipModel = new Internship();
        $this->studentModel = new Student();
        $this->companyModel = new Company();
        $this->emailService = new EmailService();
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

            // Récupérer les filtres
            $filters = [
                'status' => $request->get('status'),
                'offer_id' => $request->get('offer_id'),
                'search' => $request->get('search')
            ];

            $applications = $this->applicationModel->findByCompanyId($company['ID_Company'], $limit, $offset, $filters);

            // Récupérer les offres de l'entreprise pour le filtre
            $offers = $this->internshipModel->getByCompany($company['ID_Company']);

            // Statistiques de candidatures
            $stats = $this->applicationModel->getApplicationStatistics($company['ID_Company'], 'company');
            $totalApplications = $stats['total'];

            return $this->template->renderWithLayout('company/applications', 'dashboard', [
                'applications' => $applications,
                'offers' => $offers,
                'filters' => $filters,
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
            $applications = $this->applicationModel->getRecentApplications($limit);
            // Statistiques globales
            $stats = $this->applicationModel->getApplicationStatistics();
            $totalApplications = $stats['total'];

            return $this->template->renderWithLayout('admin/applications/index', 'dashboard', [
                'applications' => $applications,
                'stats' => $stats,
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

        // Récupérer la candidature
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

                // Charger les notes associées à cette candidature
                $application['notes'] = $this->applicationModel->getNotes($id);
            }
        } elseif ($user['role'] === 'admin' || $user['role'] === 'pilot') {
            $hasAccess = true;
            $viewTemplate = 'admin/applications/show';
        }

        if (!$hasAccess) {
            $session->setFlash('error', 'Vous n\'êtes pas autorisé à accéder à cette candidature');
            return App::$app->response->redirect('/applications');
        }

        // Générer le chemin du CV s'il existe
        if (!empty($application['cv_path'])) {
            $application['cv_url'] = '/uploads/cv/' . $application['cv_path'];
        }

        return $this->template->renderWithLayout($viewTemplate, 'dashboard', [
            'application' => $application,
            'status_options' => Application::$statusLabels,
            'user' => $user,
            'csrf_token' => SecurityHelper::generateCSRFToken()
        ]);
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
            // Envoyer notification à l'entreprise
            $this->sendApplicationNotification($result['id']);

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
        $interviewDate = $request->get('interview_date');

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
        $result = $this->applicationModel->updateStatus($applicationId, $status, $feedback, $interviewDate);

        if ($result['success']) {
            // Envoyer notification à l'étudiant
            $this->sendStatusNotification($applicationId, $status, $feedback, $interviewDate);

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
        $note = $request->get('note');

        if (!$applicationId || !$note) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'L\'identifiant de la candidature et la note sont obligatoires'
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

        // Ajouter la note
        $noteId = $this->applicationModel->addNote($applicationId, $user['id'], $note);

        if ($noteId) {
            // Récupérer toutes les notes
            $notes = $this->applicationModel->getNotes($applicationId);

            return App::$app->response->json([
                'success' => true,
                'message' => 'Note ajoutée avec succès',
                'notes' => $notes
            ]);
        } else {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout de la note'
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

    /**
     * Envoyer une notification pour une nouvelle candidature
     */
    private function sendApplicationNotification($applicationId) {
        $application = $this->applicationModel->findById($applicationId);

        if (!$application) {
            return false;
        }

        // Envoyer un email à l'entreprise
        $this->emailService->sendApplicationNotification($application);

        // Créer une notification dans l'application
        // Cette fonctionnalité serait implémentée dans un service de notification

        return true;
    }

    /**
     * Envoyer une notification pour un changement de statut
     */
    private function sendStatusNotification($applicationId, $status, $feedback, $interviewDate) {
        $application = $this->applicationModel->findById($applicationId);

        if (!$application) {
            return false;
        }

        // Envoyer un email à l'étudiant
        $this->emailService->sendStatusUpdateNotification($application, $status, $feedback, $interviewDate);

        // Créer une notification dans l'application
        // Cette fonctionnalité serait implémentée dans un service de notification

        return true;
    }
}