<?php
namespace App\Controllers;

use App\Core\App;
use App\Core\Template;
use App\Models\Student;
use App\Models\Internship;
use App\Models\Application;
use App\Models\Wishlist;
use App\Helpers\SecurityHelper;
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
     * Vérification de l'authentification d'un étudiant
     */
    private function requireStudentAuth() {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user) {
            $session->setFlash('error', 'Vous devez être connecté pour accéder à cette page');
            return App::$app->response->redirect('/login');
        }

        if ($user['role'] !== 'student') {
            $session->setFlash('error', 'Accès réservé aux étudiants');
            return App::$app->response->redirect('/');
        }

        return $user;
    }

    /**
     * Affiche le tableau de bord étudiant
     */
    public function dashboard() {
        $user = $this->requireStudentAuth();
        if (!is_array($user)) return $user; // C'est une redirection

        $student = $this->studentModel->findById($user['id']);

        // Récupérer les candidatures récentes (limité à 5)
        $recentApplications = $this->applicationModel->findByStudentId($user['id'], 5);

        // Récupérer la wishlist
        $wishlist = $this->wishlistModel->getWishlist($user['id'], 5);

        // Récupérer des stages recommandés
        $recommendedInternships = $this->internshipModel->findAll(3);

        // Statistiques pour les widgets
        $stats = $this->applicationModel->getApplicationStatistics($user['id']);

        // Calculer le pourcentage de complétion du profil
        $profileTasks = $this->getProfileCompletionTasks($student);
        $profileCompletion = $this->calculateProfileCompletion($profileTasks);

        // Générer un token CSRF
        $csrf_token = SecurityHelper::generateCSRFToken();

        return $this->template->renderWithLayout('student/dashboard', 'dashboard', [
            'student' => $student,
            'recentApplications' => $recentApplications,
            'wishlist' => $wishlist,
            'recommendedInternships' => $recommendedInternships,
            'stats' => $stats,
            'profileTasks' => $profileTasks,
            'profileCompletion' => $profileCompletion,
            'user' => $user,
            'csrf_token' => $csrf_token
        ]);
    }

    /**
     * Affiche le profil de l'étudiant
     */
    public function profile() {
        $user = $this->requireStudentAuth();
        if (!is_array($user)) return $user; // C'est une redirection

        $student = $this->studentModel->findById($user['id']);

        // Générer un token CSRF
        $csrf_token = SecurityHelper::generateCSRFToken();

        return $this->template->renderWithLayout('student/profile', 'dashboard', [
            'student' => $student,
            'user' => $user,
            'csrf_token' => $csrf_token
        ]);
    }

    /**
     * Affiche les candidatures de l'étudiant
     */
    public function applications() {
        $user = $this->requireStudentAuth();
        if (!is_array($user)) return $user; // C'est une redirection

        $request = App::$app->request;

        // Paramètres de pagination
        $page = (int)$request->get('page', 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Récupérer les candidatures avec pagination
        $applications = $this->applicationModel->findByStudentId($user['id'], $limit, $offset);

        // Récupérer les statistiques
        $stats = $this->applicationModel->getApplicationStatistics($user['id']);
        $totalApplications = $stats['total'];

        // Générer un token CSRF
        $csrf_token = SecurityHelper::generateCSRFToken();

        return $this->template->renderWithLayout('student/applications', 'dashboard', [
            'applications' => $applications,
            'stats' => $stats,
            'pagination' => [
                'page' => $page,
                'total_pages' => ceil($totalApplications / $limit),
                'total_items' => $totalApplications
            ],
            'user' => $user,
            'csrf_token' => $csrf_token
        ]);
    }

    /**
     * Affiche les détails d'une candidature
     */
    public function viewApplication($id) {
        $user = $this->requireStudentAuth();
        if (!is_array($user)) return $user; // C'est une redirection

        $session = App::$app->session;

        // Récupérer la candidature
        $application = $this->applicationModel->findById($id);

        // Vérifier si la candidature existe
        if (!$application) {
            $session->setFlash('error', 'Candidature non trouvée');
            return App::$app->response->redirect('/student/applications');
        }

        // Vérifier si l'étudiant est autorisé à voir cette candidature
        if ($application['student_id'] != $user['id']) {
            $session->setFlash('error', 'Vous n\'êtes pas autorisé à voir cette candidature');
            return App::$app->response->redirect('/student/applications');
        }

        // Générer le chemin du CV
        if (!empty($application['cv_path'])) {
            $application['cv_url'] = '/uploads/cv/' . $application['cv_path'];
        }

        // Générer un token CSRF
        $csrf_token = SecurityHelper::generateCSRFToken();

        return $this->template->renderWithLayout('student/application-details', 'dashboard', [
            'application' => $application,
            'user' => $user,
            'csrf_token' => $csrf_token
        ]);
    }

    /**
     * Affiche la wishlist de l'étudiant
     */
    public function wishlist() {
        $user = $this->requireStudentAuth();
        if (!is_array($user)) return $user; // C'est une redirection

        $request = App::$app->request;

        // Paramètres de pagination
        $page = (int)$request->get('page', 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Récupérer la wishlist avec pagination
        $wishlist = $this->wishlistModel->getWishlist($user['id'], $limit, $offset);

        // Compter le nombre total d'éléments
        $totalItems = $this->wishlistModel->countByStudent($user['id']);

        // Générer un token CSRF
        $csrf_token = SecurityHelper::generateCSRFToken();

        return $this->template->renderWithLayout('student/wishlist', 'dashboard', [
            'wishlist' => $wishlist,
            'pagination' => [
                'page' => $page,
                'total_pages' => ceil($totalItems / $limit),
                'total_items' => $totalItems
            ],
            'user' => $user,
            'csrf_token' => $csrf_token
        ]);
    }

    /**
     * Met à jour le profil de l'étudiant
     */
    public function updateProfile() {
        $user = $this->requireStudentAuth();
        if (!is_array($user)) return App::$app->response->json(['success' => false, 'message' => 'Authentification requise'], 401);

        $request = App::$app->request;
        $data = $request->getBody();

        // Validation minimale des données
        if (empty($data['Username'])) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Le nom d\'utilisateur est obligatoire'
            ], 400);
        }

        try {
            // Mettre à jour le compte utilisateur
            $userUpdateResult = App::$app->db->update('Account', [
                'Username' => $data['Username'],
                'Email' => $data['Email'] ?? $user['email'],
                'Civility' => $data['Civility'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'ID_account = ?', [$user['id']]);

            // Mettre à jour le profil étudiant
            $studentUpdateResult = $this->studentModel->update($user['id'], [
                'promotion' => $data['promotion'] ?? null,
                'school_name' => $data['school_name'] ?? null,
                'study_field' => $data['study_field'] ?? null
            ]);

            // Gestion du CV
            $cvFile = $request->getFile('cv');
            if ($cvFile && $cvFile['error'] === UPLOAD_ERR_OK) {
                $uploadResult = FileHelper::uploadFile($cvFile, 'cv');
                if ($uploadResult['success']) {
                    $this->studentModel->update($user['id'], [
                        'CV' => $uploadResult['filename']
                    ]);
                } else {
                    return App::$app->response->json([
                        'success' => false,
                        'message' => $uploadResult['error']
                    ], 400);
                }
            }

            // Mettre à jour la session
            App::$app->session->set('user', [
                'id' => $user['id'],
                'username' => $data['Username'],
                'email' => $data['Email'] ?? $user['email'],
                'role' => 'student'
            ]);

            return App::$app->response->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            // Log l'erreur
            if (isset(App::$app->logger)) {
                App::$app->logger->logError([
                    'type' => 'Profile Update Error',
                    'message' => $e->getMessage(),
                    'user_id' => $user['id']
                ]);
            }

            return App::$app->response->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la mise à jour du profil'
            ], 500);
        }
    }

    /**
     * Calcule les tâches de complétion du profil
     */
    private function getProfileCompletionTasks($student) {
        $tasks = [
            [
                'name' => 'Informations personnelles',
                'completed' => !empty($student['Username']) && !empty($student['Email']) && !empty($student['Civility'])
            ],
            [
                'name' => 'Formation académique',
                'completed' => !empty($student['school_name']) && !empty($student['study_field'])
            ],
            [
                'name' => 'CV',
                'completed' => !empty($student['CV'])
            ],
            [
                'name' => 'Photo de profil',
                'completed' => false // À implémenter avec un champ pour la photo
            ]
        ];

        return $tasks;
    }

    /**
     * Calcule le pourcentage de complétion du profil
     */
    private function calculateProfileCompletion($tasks) {
        if (empty($tasks)) {
            return 0;
        }

        $completedCount = 0;
        foreach ($tasks as $task) {
            if ($task['completed']) {
                $completedCount++;
            }
        }

        return round(($completedCount / count($tasks)) * 100);
    }
}