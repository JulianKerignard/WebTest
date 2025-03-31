<?php
namespace App\Controllers;

use App\Core\App;
use App\Core\Template;
use App\Models\Application;
use App\Models\Internship;
use App\Models\Student;
use App\Models\Company;
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

        // Important: Vérification de l'existence des données
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

        // Générer le chemin du CV s'il existe
        if (!empty($application['cv_path'])) {
            $application['cv_url'] = '/uploads/cv/' . $application['cv_path'];
        }

        // Ajouter les statuts pour l'interface
        $status_options = Application::$statusLabels;

        // Générer un token CSRF
        $csrf_token = SecurityHelper::generateCSRFToken();

        // Debug: Vérifier les données (à commenter en production)
        // var_dump($application); exit;

        return $this->template->renderWithLayout($viewTemplate, 'dashboard', [
            'application' => $application,
            'status_options' => $status_options,
            'user' => $user,
            'csrf_token' => $csrf_token
        ]);
    }
}