<?php
namespace App\Controllers;

use App\Core\App;
use App\Core\Template;
use App\Models\Pilot;
use App\Models\Student;
use App\Models\Internship;
use App\Models\Company;

class PilotController {
    private $template;
    private $pilotModel;
    private $studentModel;
    private $internshipModel;
    private $companyModel;

    public function __construct() {
        $this->template = new Template();
        $this->pilotModel = new Pilot();
        $this->studentModel = new Student();
        $this->internshipModel = new Internship();
        $this->companyModel = new Company();
    }

    /**
     * Display pilot dashboard
     */
    public function dashboard() {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'pilot') {
            $session->setFlash('error', 'Accès non autorisé');
            return App::$app->response->redirect('/login');
        }

        $pilotData = $this->pilotModel->findById($user['id']);
        $stats = $this->pilotModel->getStudentStatistics();

        // Get recent activity
        $recentInternships = $this->internshipModel->findAll(5);

        return $this->template->renderWithLayout('pilot/dashboard', 'dashboard', [
            'pilot' => $pilotData,
            'stats' => $stats,
            'recentInternships' => $recentInternships,
            'user' => $user
        ]);
    }

    /**
     * Display students list
     */
    public function students() {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'pilot') {
            $session->setFlash('error', 'Accès non autorisé');
            return App::$app->response->redirect('/login');
        }

        $students = $this->pilotModel->getStudents();

        return $this->template->renderWithLayout('pilot/students', 'dashboard', [
            'students' => $students,
            'user' => $user
        ]);
    }

    /**
     * Display student details
     */
    public function viewStudent($id) {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'pilot') {
            $session->setFlash('error', 'Accès non autorisé');
            return App::$app->response->redirect('/login');
        }

        $student = $this->studentModel->findById($id);

        if (!$student) {
            $session->setFlash('error', 'Étudiant non trouvé');
            return App::$app->response->redirect('/pilot/students');
        }

        // Get student applications
        $applications = [];  // Implement this when applications table exists

        return $this->template->renderWithLayout('pilot/student-view', 'dashboard', [
            'student' => $student,
            'applications' => $applications,
            'user' => $user
        ]);
    }

    /**
     * Display company list
     */
    public function companies() {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'pilot') {
            $session->setFlash('error', 'Accès non autorisé');
            return App::$app->response->redirect('/login');
        }

        $companies = $this->companyModel->findAll();

        return $this->template->renderWithLayout('pilot/company', 'dashboard', [
            'company' => $companies,
            'user' => $user
        ]);
    }

    /**
     * Display internships list
     */
    public function internships() {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'pilot') {
            $session->setFlash('error', 'Accès non autorisé');
            return App::$app->response->redirect('/login');
        }

        $internships = $this->internshipModel->findAll();

        return $this->template->renderWithLayout('pilot/internships', 'dashboard', [
            'internships' => $internships,
            'user' => $user
        ]);
    }

    /**
     * Display statistics
     */
    public function statistics() {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'pilot') {
            $session->setFlash('error', 'Accès non autorisé');
            return App::$app->response->redirect('/login');
        }

        $stats = $this->pilotModel->getStudentStatistics();

        return $this->template->renderWithLayout('pilot/statistics', 'dashboard', [
            'stats' => $stats,
            'user' => $user
        ]);
    }
}