<?php
namespace App\Controllers;

use App\Core\App;
use App\Core\Template;
use App\Models\Student;
use App\Models\Internship;
use App\Models\Application;
use App\Models\Wishlist;
use App\Helpers\FileHelper;

class StudentController {
    private $template;
    private $studentModel;
    private $internshipModel;
    private $applicationModel;
    private $wishlistModel;

    public function __construct() {
        $this->template = new Template();
        $this->studentModel = new Student();
        $this->internshipModel = new Internship();
        $this->applicationModel = new Application();
        $this->wishlistModel = new Wishlist();
    }

    /**
     * Display student dashboard
     */
    public function dashboard() {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'student') {
            $session->setFlash('error', 'Accès non autorisé');
            return App::$app->response->redirect('/login');
        }

        $student = $this->studentModel->findById($user['id']);
        $applications = $this->applicationModel->findByStudentId($user['id']);
        $wishlist = $this->wishlistModel->getWishlist($user['id']);

        // Get some recommended internships
        $recommendedInternships = $this->internshipModel->search([
            'limit' => 3
        ]);

        return $this->template->renderWithLayout('student/dashboard', 'dashboard', [
            'student' => $student,
            'applications' => $applications,
            'wishlist' => $wishlist,
            'recommendedInternships' => $recommendedInternships,
            'user' => $user
        ]);
    }

    /**
     * Display student profile
     */
    public function profile() {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'student') {
            $session->setFlash('error', 'Accès non autorisé');
            return App::$app->response->redirect('/login');
        }

        $student = $this->studentModel->findById($user['id']);

        return $this->template->renderWithLayout('student/profile', 'dashboard', [
            'student' => $student,
            'user' => $user
        ]);
    }

    /**
     * Update student profile
     */
    public function updateProfile() {
        $request = App::$app->request;
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'student') {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $data = $request->getBody();

        // Update basic user info
        $result = App::$app->db->update('Account', [
            'Username' => $data['Username'],
            'Email' => $data['Email'],
            'Civility' => $data['Civility'] ?? null
        ], 'ID_account = ?', [$user['id']]);

        // Update student-specific info
        $this->studentModel->update($user['id'], [
            'Licence' => isset($data['Licence']) ? 1 : 0,
            'Majority' => $data['Majority'] ?? null,
            'promotion' => $data['promotion'] ?? null
        ]);

        // Handle CV upload if provided
        $cvFile = $request->getFile('cv');
        if ($cvFile && $cvFile['error'] === UPLOAD_ERR_OK) {
            $result = $this->studentModel->uploadCV($user['id'], $cvFile);
            if (!$result['success']) {
                return App::$app->response->json([
                    'success' => false,
                    'message' => $result['error']
                ], 400);
            }
        }

        // Update session data
        $updatedStudent = $this->studentModel->findById($user['id']);
        $session->set('user', [
            'id' => $updatedStudent['ID_account'],
            'email' => $updatedStudent['Email'],
            'username' => $updatedStudent['Username'],
            'role' => 'student'
        ]);

        return App::$app->response->json([
            'success' => true,
            'message' => 'Profil mis à jour avec succès'
        ]);
    }

    /**
     * Display applications
     */
    public function applications() {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'student') {
            $session->setFlash('error', 'Accès non autorisé');
            return App::$app->response->redirect('/login');
        }

        $applications = $this->applicationModel->findByStudentId($user['id']);

        return $this->template->renderWithLayout('student/applications', 'dashboard', [
            'applications' => $applications,
            'user' => $user
        ]);
    }

    /**
     * Apply for an internship
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

        $offerId = $request->get('offer_id');
        $coverLetter = $request->get('cover_letter');

        if (!$offerId || !$coverLetter) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Tous les champs sont obligatoires'
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

        // Handle CV upload or use existing CV
        $student = $this->studentModel->findById($user['id']);
        $cvPath = $student['CV'];

        $cvFile = $request->getFile('cv');
        if ($cvFile && $cvFile['error'] === UPLOAD_ERR_OK) {
            $uploadResult = FileHelper::uploadFile($cvFile, 'cv');
            if ($uploadResult['success']) {
                $cvPath = $uploadResult['filename'];
                // Update student CV
                $this->studentModel->update($user['id'], ['CV' => $cvPath]);
            } else {
                return App::$app->response->json([
                    'success' => false,
                    'message' => $uploadResult['error']
                ], 400);
            }
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
                'message' => 'Candidature envoyée avec succès'
            ]);
        } else {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de la candidature'
            ], 500);
        }
    }
}