<?php
namespace App\Controllers;

use App\Core\App;
use App\Core\Template;
use App\Models\Pilot;
use App\Models\Student;
use App\Models\Internship;

class PilotController {
    private $template;
    private $pilotModel;
    private $studentModel;
    private $internshipModel;

    public function __construct() {
        $this->template = new Template();
        $this->pilotModel = new Pilot();
        $this->studentModel = new Student();
        $this->internshipModel = new Internship();
    }

    /**
     * Affiche le tableau de bord du pilote
     */
    public function dashboard() {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'pilot') {
            $session->setFlash('error', 'Accès non autorisé');
            return App::$app->response->redirect('/login');
        }

        $pilotData = $this->pilotModel->findById($user['id']);

        // Statistiques simplifiées
        $stats = [
            'total_students' => $this->db->fetch("SELECT COUNT(*) as count FROM Student")['count'] ?? 0,
            'total_offers' => $this->db->fetch("SELECT COUNT(*) as count FROM Offers")['count'] ?? 0
        ];

        // Récupérer les stages récents
        $recentInternships = $this->internshipModel->findAll(5);

        return $this->template->renderWithLayout('pilot/dashboard', 'dashboard', [
            'pilot' => $pilotData,
            'stats' => $stats,
            'recentInternships' => $recentInternships,
            'user' => $user
        ]);
    }

    /**
     * Affiche la liste des étudiants
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
     * Affiche les détails d'un étudiant
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

        return $this->template->renderWithLayout('pilot/student-view', 'dashboard', [
            'student' => $student,
            'user' => $user
        ]);
    }

    /**
     * Affiche la liste des stages
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
     * Affiche les statistiques de base
     */
    public function statistics() {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'pilot') {
            $session->setFlash('error', 'Accès non autorisé');
            return App::$app->response->redirect('/login');
        }

        // Statistiques simplifiées
        $stats = [
            'total_students' => $this->db->fetch("SELECT COUNT(*) as count FROM Student")['count'] ?? 0,
            'total_companies' => $this->db->fetch("SELECT COUNT(*) as count FROM Company")['count'] ?? 0,
            'total_offers' => $this->db->fetch("SELECT COUNT(*) as count FROM Offers")['count'] ?? 0,
            'total_applications' => $this->db->fetch("SELECT COUNT(*) as count FROM applications")['count'] ?? 0
        ];

        return $this->template->renderWithLayout('pilot/statistics', 'dashboard', [
            'stats' => $stats,
            'user' => $user
        ]);
    }
}