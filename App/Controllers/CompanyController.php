<?php
namespace App\Controllers;

use App\Core\App;
use App\Core\Template;
use App\Models\Company;
use App\Models\Internship;
use App\Models\Evaluation;
use App\Helpers\SecurityHelper;

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
     * Affiche la liste des entreprises
     */
    public function index() {
        $request = App::$app->request;
        $page = (int)$request->get('page', 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Récupérer les paramètres de recherche simplifiés
        $keyword = $request->get('keyword');

        // Récupérer les entreprises
        $companies = $keyword
            ? $this->companyModel->search($keyword)
            : $this->companyModel->findAll($limit, $offset);

        $totalCompanies = $this->companyModel->getTotalCompanies();
        $totalPages = ceil($totalCompanies / $limit);

        // Vérifier si l'utilisateur est connecté
        $session = App::$app->session;
        $user = $session->get('user');

        // Récupérer les secteurs pour le filtre
        $sectors = $this->companyModel->getSectors();

        // Générer un token CSRF
        $csrfToken = SecurityHelper::generateCSRFToken();

        return $this->template->renderWithLayout('company/index', 'main', [
            'companies' => $companies,
            'sectors' => $sectors,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalCompanies
            ],
            'keyword' => $keyword,
            'user' => $user,
            'csrf_token' => $csrfToken
        ]);
    }

    /**
     * Affiche les détails d'une entreprise
     */
    public function show($id) {
        $company = $this->companyModel->findById($id);

        if (!$company) {
            App::$app->session->setFlash('error', 'Entreprise non trouvée');
            return App::$app->response->redirect('/companies');
        }

        // Récupérer les offres de l'entreprise
        $offers = $this->companyModel->getOffers($id);

        // Vérifier si l'utilisateur est connecté
        $session = App::$app->session;
        $user = $session->get('user');

        // Générer un token CSRF
        $csrfToken = SecurityHelper::generateCSRFToken();

        return $this->template->renderWithLayout('company/show', 'main', [
            'company' => $company,
            'offers' => $offers,
            'user' => $user,
            'csrf_token' => $csrfToken
        ]);
    }

    /**
     * Évaluer une entreprise
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

        // Vérifier si l'entreprise existe
        $company = $this->companyModel->findById($companyId);
        if (!$company) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Entreprise non trouvée'
            ], 404);
        }

        // Créer l'évaluation
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

    /**
     * Affiche la interface admin pour les entreprises
     */
    public function adminIndex() {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'pilot')) {
            $session->setFlash('error', 'Accès non autorisé');
            return App::$app->response->redirect('/login');
        }

        $companies = $this->companyModel->findAll();

        return $this->template->renderWithLayout('admin/companies/index', 'dashboard', [
            'companies' => $companies,
            'user' => $user
        ]);
    }

    /**
     * Afficher le formulaire de création d'entreprise
     */
    public function create() {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'pilot')) {
            $session->setFlash('error', 'Accès non autorisé');
            return App::$app->response->redirect('/login');
        }

        $sectors = $this->companyModel->getSectors();

        return $this->template->renderWithLayout('admin/companies/create', 'dashboard', [
            'sectors' => $sectors,
            'user' => $user
        ]);
    }

    /**
     * Créer une nouvelle entreprise
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

        // Validation simplifiée
        if (empty($data['Name']) || empty($data['Description'])) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Le nom et la description sont obligatoires'
            ], 400);
        }

        // Créer l'entreprise
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
     * Supprimer une entreprise
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

        $result = $this->companyModel->delete($id);

        if ($result) {
            return App::$app->response->json([
                'success' => true,
                'message' => 'Entreprise supprimée avec succès'
            ]);
        } else {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'entreprise'
            ], 500);
        }
    }
}