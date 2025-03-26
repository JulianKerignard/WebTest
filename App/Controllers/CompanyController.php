<?php
namespace App\Controllers;

use App\Core\App;
use App\Core\Template;
use App\Models\Company;
use App\Models\Internship;
use App\Models\Evaluation;

class CompanyController {
    private $template;
    private $companyModel;
    private $internshipModel;
    private $evaluationModel;

    public function __construct() {
        $this->template = new Template();
        $this->companyModel = new Company();
        $this->internshipModel = new Internship();
        $this->evaluationModel = new Evaluation();
    }

    /**
     * Display companies list
     */
    public function index() {
        $request = App::$app->request;
        $page = (int)$request->get('page', 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Get search keyword if any
        $keyword = $request->get('keyword');
        $sectorId = $request->get('sector_id');

        // Get companies
        $companies = [];
        if ($keyword) {
            $companies = $this->companyModel->search($keyword);
        } else if ($sectorId) {
            $companies = $this->companyModel->findBySector($sectorId);
        } else {
            $companies = $this->companyModel->findAll($limit, $offset);
        }

        $totalCompanies = $this->companyModel->getTotalCompanies();
        $totalPages = ceil($totalCompanies / $limit);

        // Check if user is logged in
        $session = App::$app->session;
        $user = $session->get('user');

        return $this->template->renderWithLayout('companies/index', 'main', [
            'companies' => $companies,
            'pagination' => [
                'page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalCompanies
            ],
            'keyword' => $keyword,
            'sector_id' => $sectorId,
            'user' => $user
        ]);
    }

    /**
     * Display company details
     */
    public function show($id) {
        $company = $this->companyModel->findById($id);

        if (!$company) {
            App::$app->session->setFlash('error', 'Entreprise non trouvée');
            return App::$app->response->redirect('/companies');
        }

        // Get company offers
        $offers = $this->companyModel->getOffers($id);

        // Get company evaluations
        $evaluations = $this->evaluationModel->getCompanyEvaluations($id);
        $averageRating = $this->evaluationModel->getAverageCompanyRating($id);

        // Check if user is logged in
        $session = App::$app->session;
        $user = $session->get('user');

        return $this->template->renderWithLayout('companies/show', 'main', [
            'company' => $company,
            'offers' => $offers,
            'evaluations' => $evaluations,
            'averageRating' => $averageRating,
            'user' => $user
        ]);
    }

    /**
     * Rate a company
     */
    public function rate() {
        $request = App::$app->request;
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || $user['role'] !== 'student') {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Vous devez être connecté en tant qu\'étudiant pour évaluer une entreprise'
            ], 403);
        }

        $companyId = $request->get('company_id');
        $rating = $request->get('rating');
        $comment = $request->get('comment');

        if (!$companyId || !$rating) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'L\'identifiant de l\'entreprise et la note sont obligatoires'
            ], 400);
        }

        // Check if company exists
        $company = $this->companyModel->findById($companyId);
        if (!$company) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Entreprise non trouvée'
            ], 404);
        }

        // Create evaluation
        $result = $this->evaluationModel->createCompanyEvaluation($user['id'], $companyId, $rating, $comment);

        if ($result) {
            return App::$app->response->json([
                'success' => true,
                'message' => 'Évaluation enregistrée avec succès'
            ]);
        } else {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement de l\'évaluation'
            ], 500);
        }
    }

    /* Admin functions */

    /**
     * Display company creation form
     */
    public function create() {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'pilot')) {
            $session->setFlash('error', 'Accès non autorisé');
            return App::$app->response->redirect('/');
        }

        return $this->template->renderWithLayout('admin/companies/create', 'dashboard', [
            'user' => $user
        ]);
    }

    /**
     * Store new company
     */
    public function store() {
        $request = App::$app->request;
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'pilot')) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $data = $request->getBody();

        // Validate data
        if (empty($data['Name']) || empty($data['Description'])) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Le nom et la description sont obligatoires'
            ], 400);
        }

        // Create company
        $result = $this->companyModel->create($data);

        if ($result) {
            return App::$app->response->json([
                'success' => true,
                'message' => 'Entreprise créée avec succès',
                'redirect' => '/admin/companies'
            ]);
        } else {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'entreprise'
            ], 500);
        }
    }

    /**
     * Display company edit form
     */
    public function edit($id) {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'pilot')) {
            $session->setFlash('error', 'Accès non autorisé');
            return App::$app->response->redirect('/');
        }

        $company = $this->companyModel->findById($id);

        if (!$company) {
            $session->setFlash('error', 'Entreprise non trouvée');
            return App::$app->response->redirect('/admin/companies');
        }

        return $this->template->renderWithLayout('admin/companies/edit', 'dashboard', [
            'company' => $company,
            'user' => $user
        ]);
    }

    /**
     * Update company
     */
    public function update($id) {
        $request = App::$app->request;
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'pilot')) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $data = $request->getBody();

        // Validate data
        if (empty($data['Name']) || empty($data['Description'])) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Le nom et la description sont obligatoires'
            ], 400);
        }

        // Update company
        $this->companyModel->update($id, $data);

        return App::$app->response->json([
            'success' => true,
            'message' => 'Entreprise mise à jour avec succès',
            'redirect' => '/admin/companies'
        ]);
    }

    /**
     * Delete company
     */
    public function delete($id) {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'pilot')) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $this->companyModel->delete($id);

        return App::$app->response->json([
            'success' => true,
            'message' => 'Entreprise supprimée avec succès'
        ]);
    }
}