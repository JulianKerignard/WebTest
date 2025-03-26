<?php
namespace App\Controllers;

use App\Core\App;
use App\Core\Template;
use App\Models\Application;
use App\Models\Internship;
use App\Models\Student;
use App\Helpers\FileHelper;

class ApplicationController {
    private $template;
    private $applicationModel;
    private $internshipModel;
    private $studentModel;

    public function __construct() {
        $this->template = new Template();
        $this->applicationModel = new Application();
        $this->internshipModel = new Internship();
        $this->studentModel = new Student();
    }

    /**
     * Create a new application
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

        // Check if internship exists
        $internship = $this->internshipModel->findById($offerId);
        if (!$internship) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Stage non trouvé'
            ], 404);
        }

        // Handle CV upload
        $cvPath = null;
        $cvFile = $request->getFile('cv');
        if ($cvFile && $cvFile['error'] === UPLOAD_ERR_OK) {
            $result = FileHelper::uploadFile($cvFile, 'cv');
            if ($result['success']) {
                $cvPath = $result['filename'];
            } else {
                return App::$app->response->json([
                    'success' => false,
                    'message' => $result['error']
                ], 400);
            }
        } else {
            // Use student's existing CV
            $student = $this->studentModel->findById($user['id']);
            $cvPath = $student['CV'] ?? null;
        }

        if (!$cvPath) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Vous devez télécharger un CV pour postuler'
            ], 400);
        }

        // Create application
        $result = $this->applicationModel->create($user['id'], $offerId, $coverLetter, $cvPath);

        if ($result) {
            return App::$app->response->json([
                'success' => true,
                'message' => 'Votre candidature a été envoyée avec succès'
            ]);
        } else {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'envoi de votre candidature'
            ], 500);
        }
    }

    /**
     * Display student applications
     */
    public function index() {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'student') {
            $session->setFlash('error', 'Accès non autorisé');
            return App::$app->response->redirect('/login');
        }

        // Get student applications
        $applications = $this->applicationModel->findByStudentId($user['id']);

        return $this->template->renderWithLayout('student/applications', 'dashboard', [
            'applications' => $applications,
            'user' => $user
        ]);
    }

    /**
     * Display application details
     */
    public function show($id) {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user) {
            $session->setFlash('error', 'Vous devez être connecté pour accéder à cette page');
            return App::$app->response->redirect('/login');
        }

        // Get application
        $application = $this->applicationModel->findById($id);

        if (!$application) {
            $session->setFlash('error', 'Candidature non trouvée');
            return App::$app->response->redirect($user['role'] === 'student' ? '/student/applications' : '/');
        }

        // Check permissions
        if ($user['role'] === 'student' && $application['student_id'] !== $user['id']) {
            $session->setFlash('error', 'Vous n\'êtes pas autorisé à accéder à cette candidature');
            return App::$app->response->redirect('/student/applications');
        }

        // For company view - should be implemented with company authentication

        // For admin/pilot view
        if (($user['role'] === 'admin' || $user['role'] === 'pilot')) {
            return $this->template->renderWithLayout('admin/applications/show', 'dashboard', [
                'application' => $application,
                'user' => $user
            ]);
        }

        // Student view
        return $this->template->renderWithLayout('student/application-details', 'dashboard', [
            'application' => $application,
            'user' => $user
        ]);
    }

    /**
     * Update application status (admin/pilot only)
     */
    public function updateStatus() {
        $request = App::$app->request;
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'pilot')) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $applicationId = $request->get('application_id');
        $status = $request->get('status');

        if (!$applicationId || !$status) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'L\'identifiant de la candidature et le statut sont obligatoires'
            ], 400);
        }

        // Update application status
        $result = $this->applicationModel->updateStatus($applicationId, $status);

        if ($result) {
            return App::$app->response->json([
                'success' => true,
                'message' => 'Statut de la candidature mis à jour avec succès'
            ]);
        } else {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la mise à jour du statut'
            ], 500);
        }
    }
}