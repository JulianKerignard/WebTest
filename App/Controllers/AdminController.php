<?php
namespace App\Controllers;

use App\Core\App;
use App\Core\Template;
use App\Models\Admin;
use App\Models\Student;
use App\Models\Pilot;
use App\Models\User;
use App\Helpers\SecurityHelper;

class AdminController {
    private $template;
    private $adminModel;
    private $studentModel;
    private $pilotModel;
    private $userModel;

    public function __construct() {
        $this->template = new Template();
        $this->adminModel = new Admin();
        $this->studentModel = new Student();
        $this->pilotModel = new Pilot();
        $this->userModel = new User();
    }

    /**
     * Display admin dashboard
     */
    public function dashboard() {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'admin') {
            $session->setFlash('error', 'Accès non autorisé');
            return App::$app->response->redirect('/login');
        }

        $adminData = $this->adminModel->findById($user['id']);
        $stats = $this->adminModel->getStatistics();

        return $this->template->renderWithLayout('admin/dashboard', 'dashboard', [
            'admin' => $adminData,
            'stats' => $stats,
            'user' => $user
        ]);
    }

    /**
     * Display all pilots
     */
    public function pilots() {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'admin') {
            $session->setFlash('error', 'Accès non autorisé');
            return App::$app->response->redirect('/login');
        }

        $pilots = $this->adminModel->getAllPilotes();

        return $this->template->renderWithLayout('admin/pilots/index', 'dashboard', [
            'pilots' => $pilots,
            'user' => $user
        ]);
    }

    /**
     * Display pilot creation form
     */
    public function createPilot() {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'admin') {
            $session->setFlash('error', 'Accès non autorisé');
            return App::$app->response->redirect('/login');
        }

        return $this->template->renderWithLayout('admin/pilots/create', 'dashboard', [
            'user' => $user
        ]);
    }

    /**
     * Store new pilot
     */
    public function storePilot() {
        $request = App::$app->request;
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'admin') {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $data = $request->getBody();

        // Validate data
        if (empty($data['Username']) || empty($data['Email']) || empty($data['Password'])) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Tous les champs sont obligatoires'
            ], 400);
        }

        // Check if email already exists
        $existingUser = $this->userModel->findByEmail($data['Email']);
        if ($existingUser) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Cet email est déjà utilisé'
            ], 400);
        }

        // Create user account
        $accountId = $this->userModel->create([
            'Email' => $data['Email'],
            'Username' => $data['Username'],
            'Password' => SecurityHelper::hashPassword($data['Password']),
            'Civility' => $data['Civility'] ?? null,
            '_Rank' => 2 // Rank for pilot
        ]);

        // Create pilot profile
        $this->adminModel->createPilote($accountId);

        return App::$app->response->json([
            'success' => true,
            'message' => 'Pilote créé avec succès',
            'redirect' => '/admin/pilots'
        ]);
    }

    /**
     * Delete pilot
     */
    public function deletePilot($id) {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'admin') {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        // Delete pilot profile
        $this->adminModel->deletePilote($id);

        // Delete user account
        $this->userModel->delete($id);

        return App::$app->response->json([
            'success' => true,
            'message' => 'Pilote supprimé avec succès'
        ]);
    }

    /**
     * Display all students
     */
    public function students() {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'admin') {
            $session->setFlash('error', 'Accès non autorisé');
            return App::$app->response->redirect('/login');
        }

        $students = $this->studentModel->findAll();

        return $this->template->renderWithLayout('admin/students/index', 'dashboard', [
            'students' => $students,
            'user' => $user
        ]);
    }

    /**
     * Display student creation form
     */
    public function createStudent() {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'admin') {
            $session->setFlash('error', 'Accès non autorisé');
            return App::$app->response->redirect('/login');
        }

        return $this->template->renderWithLayout('admin/students/create', 'dashboard', [
            'user' => $user
        ]);
    }

    /**
     * Store new student
     */
    public function storeStudent() {
        $request = App::$app->request;
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'admin') {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $data = $request->getBody();

        // Validate data
        if (empty($data['Username']) || empty($data['Email']) || empty($data['Password'])) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Tous les champs sont obligatoires'
            ], 400);
        }

        // Check if email already exists
        $existingUser = $this->userModel->findByEmail($data['Email']);
        if ($existingUser) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Cet email est déjà utilisé'
            ], 400);
        }

        // Create user account
        $accountId = $this->userModel->create([
            'Email' => $data['Email'],
            'Username' => $data['Username'],
            'Password' => SecurityHelper::hashPassword($data['Password']),
            'Civility' => $data['Civility'] ?? null,
            '_Rank' => 1 // Rank for student
        ]);

        // Create student profile
        $this->studentModel->create($accountId, [
            'Licence' => isset($data['Licence']) ? 1 : 0,
            'Majority' => $data['Majority'] ?? null,
            'promotion' => $data['promotion'] ?? null
        ]);

        return App::$app->response->json([
            'success' => true,
            'message' => 'Étudiant créé avec succès',
            'redirect' => '/admin/students'
        ]);
    }

    /**
     * Delete student
     */
    public function deleteStudent($id) {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'admin') {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        // Delete student profile
        $this->studentModel->delete($id);

        // Delete user account
        $this->userModel->delete($id);

        return App::$app->response->json([
            'success' => true,
            'message' => 'Étudiant supprimé avec succès'
        ]);
    }
}