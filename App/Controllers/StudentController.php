<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Student;
use App\Models\Internship;
use App\Models\Application;
use App\Models\Wishlist;
use App\Helpers\FileHelper;

class StudentController extends Controller {
    private $studentModel;
    private $internshipModel;
    private $applicationModel;
    private $wishlistModel;

    public function __construct() {
        parent::__construct();
        $this->studentModel = new Student();
        $this->internshipModel = new Internship();
        $this->applicationModel = new Application();
        $this->wishlistModel = new Wishlist();
    }

    /**
     * Affiche le tableau de bord étudiant
     */
    public function dashboard() {
        // Vérifier les autorisations
        if ($this->requireRole('student') !== true) {
            return;
        }

        $user = $this->getCurrentUser();
        $student = $this->studentModel->findById($user['id']);

        // Données pour le tableau de bord
        $applications = $this->applicationModel->findByStudentId($user['id'], 5);
        $wishlist = $this->wishlistModel->getWishlist($user['id']);
        $recommendedInternships = $this->internshipModel->search(['limit' => 3]);

        // Statistiques pour les widgets
        $stats = $this->applicationModel->getApplicationStatistics($user['id']);

        // Calculer le pourcentage de complétion du profil
        $profileTasks = $this->getProfileCompletionTasks($student);
        $profileCompletion = $this->calculateProfileCompletion($profileTasks);

        return $this->renderWithLayout('student/dashboard', 'dashboard', [
            'student' => $student,
            'applications' => $applications,
            'wishlist' => $wishlist,
            'recommendedInternships' => $recommendedInternships,
            'stats' => $stats,
            'profileTasks' => $profileTasks,
            'profileCompletion' => $profileCompletion
        ]);
    }

    /**
     * Affiche le profil de l'étudiant
     */
    public function profile() {
        if ($this->requireRole('student') !== true) {
            return;
        }

        $user = $this->getCurrentUser();
        $student = $this->studentModel->findById($user['id']);

        return $this->renderWithLayout('student/profile', 'dashboard', [
            'student' => $student
        ]);
    }

    /**
     * Met à jour le profil de l'étudiant
     */
    public function updateProfile() {
        if ($this->requireRole('student') !== true) {
            return $this->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $data = $this->request->getBody();
        $user = $this->getCurrentUser();

        // Mettre à jour les informations de base
        $result = $this->session->db->update('Account', [
            'Username' => $data['Username'],
            'Email' => $data['Email'],
            'Civility' => $data['Civility'] ?? null
        ], 'ID_account = ?', [$user['id']]);

        // Mettre à jour les informations spécifiques à l'étudiant
        $this->studentModel->update($user['id'], [
            'Licence' => isset($data['Licence']) ? 1 : 0,
            'Majority' => $data['Majority'] ?? null,
            'promotion' => $data['promotion'] ?? null,
            'school_name' => $data['school_name'] ?? null,
            'study_field' => $data['study_field'] ?? null
        ]);

        // Gérer l'upload du CV si fourni
        $cvFile = $this->request->getFile('cv');
        if ($cvFile && $cvFile['error'] === UPLOAD_ERR_OK) {
            $result = $this->studentModel->uploadCV($user['id'], $cvFile);
            if (!$result['success']) {
                return $this->json([
                    'success' => false,
                    'message' => $result['error']
                ], 400);
            }
        }

        // Mettre à jour les données de session
        $updatedStudent = $this->studentModel->findById($user['id']);
        $this->session->set('user', [
            'id' => $updatedStudent['ID_account'],
            'email' => $updatedStudent['Email'],
            'username' => $updatedStudent['Username'],
            'role' => 'student'
        ]);

        return $this->json([
            'success' => true,
            'message' => 'Profil mis à jour avec succès'
        ]);
    }

    /**
     * Affiche les candidatures de l'étudiant
     */
    public function applications() {
        if ($this->requireRole('student') !== true) {
            return;
        }

        $user = $this->getCurrentUser();

        // Paramètres de pagination
        $page = (int)$this->request->get('page', 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Récupérer les candidatures
        $applications = $this->applicationModel->findByStudentId($user['id'], $limit, $offset);
        $stats = $this->applicationModel->getApplicationStatistics($user['id']);
        $totalApplications = $stats['total'];

        return $this->renderWithLayout('student/applications', 'dashboard', [
            'applications' => $applications,
            'stats' => $stats,
            'pagination' => [
                'page' => $page,
                'total_pages' => ceil($totalApplications / $limit),
                'total_items' => $totalApplications
            ]
        ]);
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
                'name' => 'Compétences',
                'completed' => false // À implémenter avec une table de compétences étudiantes
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