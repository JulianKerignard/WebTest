<?php
namespace App\Controllers;

use App\Core\App;
use App\Core\Template;
use App\Models\Internship;
use App\Models\Company;
use App\Models\Wishlist;
use App\Helpers\SecurityHelper;

class InternshipController {
    private $template;
    private $internshipModel;
    private $companyModel;
    private $wishlistModel;

    public function __construct() {
        $this->template = new Template();
        $this->internshipModel = new Internship();
        $this->companyModel = new Company();
        $this->wishlistModel = new Wishlist();
    }

    /**
     * Affiche la liste des offres de stage
     */
    public function index() {
        $request = App::$app->request;
        $page = (int)$request->get('page', 1);
        $limit = 12;
        $offset = ($page - 1) * $limit;

        // Récupérer les filtres simplifiés
        $filters = [
            'keyword' => $request->get('keyword'),
            'company_id' => $request->get('company_id'),
            'level_id' => $request->get('level_id'),
            'min_remuneration' => $request->get('min_remuneration'),
            'limit' => $limit,
            'offset' => $offset
        ];

        // Récupérer les offres de stage
        $internships = $this->internshipModel->search($filters);

        // Déterminer le nombre total d'offres pour la pagination
        $totalInternships = count($this->internshipModel->search(['keyword' => $filters['keyword'], 'company_id' => $filters['company_id'], 'level_id' => $filters['level_id']]));
        $totalPages = ceil($totalInternships / $limit);

        // Récupérer les données pour les filtres
        $companies = $this->companyModel->findAll();
        $levels = $this->internshipModel->getLevels();

        // Vérifier si l'utilisateur est connecté
        $session = App::$app->session;
        $user = $session->get('user');

        // Si l'utilisateur est un étudiant, vérifier les offres dans sa wishlist
        if ($user && $user['role'] === 'student') {
            foreach ($internships as &$internship) {
                $internship['in_wishlist'] = $this->wishlistModel->isInWishlist($user['id'], $internship['ID_Offer']);
            }
        }

        // Générer un token CSRF
        $csrfToken = SecurityHelper::generateCSRFToken();

        return $this->template->renderWithLayout('stages/index', 'main', [
            'internships' => $internships,
            'companies' => $companies,
            'levels' => $levels,
            'filters' => $filters,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalInternships
            ],
            'user' => $user,
            'csrf_token' => $csrfToken
        ]);
    }

    /**
     * Affiche les détails d'une offre de stage
     */
    public function show($id) {
        $internship = $this->internshipModel->findById($id);

        if (!$internship) {
            App::$app->session->setFlash('error', 'Stage non trouvé');
            return App::$app->response->redirect('/stages');
        }

        // Récupérer les compétences du stage
        $skills = $this->internshipModel->getSkillsForOffer($id);

        // Récupérer les informations détaillées de l'entreprise
        $company = $this->companyModel->findById($internship['ID_Company']);

        // Vérifier si l'utilisateur est connecté
        $session = App::$app->session;
        $user = $session->get('user');

        // Vérifier si le stage est dans les favoris
        $inWishlist = false;
        if ($user && $user['role'] === 'student') {
            $inWishlist = $this->wishlistModel->isInWishlist($user['id'], $id);
        }

        // Générer un token CSRF
        $csrfToken = SecurityHelper::generateCSRFToken();

        return $this->template->renderWithLayout('stages/show', 'main', [
            'internship' => $internship,
            'company' => $company,
            'skills' => $skills,
            'inWishlist' => $inWishlist,
            'user' => $user,
            'csrf_token' => $csrfToken
        ]);
    }

    /**
     * Affiche la liste des offres de stage (admin)
     */
    public function adminIndex() {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'pilot')) {
            $session->setFlash('error', 'Accès non autorisé');
            return App::$app->response->redirect('/login');
        }

        $internships = $this->internshipModel->findAll();

        return $this->template->renderWithLayout('admin/internships/index', 'dashboard', [
            'internships' => $internships,
            'user' => $user
        ]);
    }

    /**
     * Affiche le formulaire de création d'offre de stage
     */
    public function create() {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'pilot')) {
            $session->setFlash('error', 'Accès non autorisé');
            return App::$app->response->redirect('/login');
        }

        $companies = $this->companyModel->findAll();
        $levels = $this->internshipModel->getLevels();
        $skills = $this->internshipModel->getSkills();

        return $this->template->renderWithLayout('admin/internships/create', 'dashboard', [
            'companies' => $companies,
            'levels' => $levels,
            'skills' => $skills,
            'user' => $user
        ]);
    }

    /**
     * Crée une nouvelle offre de stage
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
        if (empty($data['Offer_title']) || empty($data['Description']) || empty($data['ID_Company'])) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Veuillez remplir tous les champs obligatoires'
            ], 400);
        }

        // Créer l'offre de stage
        $result = $this->internshipModel->create($data);

        if ($result) {
            // Gérer les compétences si elles sont spécifiées
            if (!empty($data['skills'])) {
                $skills = explode(',', $data['skills']);
                foreach ($skills as $skillId) {
                    $this->internshipModel->addSkill($result, $skillId);
                }
            }

            return App::$app->response->json([
                'success' => true,
                'message' => 'Offre de stage créée avec succès',
                'redirect' => '/admin/internships'
            ]);
        } else {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'offre de stage'
            ], 500);
        }
    }

    /**
     * Affiche le formulaire d'édition d'offre de stage
     */
    public function edit($id) {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'pilot')) {
            $session->setFlash('error', 'Accès non autorisé');
            return App::$app->response->redirect('/login');
        }

        $internship = $this->internshipModel->findById($id);

        if (!$internship) {
            $session->setFlash('error', 'Stage non trouvé');
            return App::$app->response->redirect('/admin/internships');
        }

        $companies = $this->companyModel->findAll();
        $levels = $this->internshipModel->getLevels();
        $skills = $this->internshipModel->getSkills();
        $internshipSkills = $this->internshipModel->getSkillsForOffer($id);

        return $this->template->renderWithLayout('admin/internships/edit', 'dashboard', [
            'internship' => $internship,
            'companies' => $companies,
            'levels' => $levels,
            'skills' => $skills,
            'internshipSkills' => $internshipSkills,
            'user' => $user
        ]);
    }

    /**
     * Met à jour une offre de stage
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

        // Validation simplifiée
        if (empty($data['Offer_title']) || empty($data['Description']) || empty($data['ID_Company'])) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Veuillez remplir tous les champs obligatoires'
            ], 400);
        }

        // Mettre à jour l'offre de stage
        $result = $this->internshipModel->update($id, $data);

        if ($result) {
            // Gérer les compétences si elles sont spécifiées
            if (isset($data['skills'])) {
                // Supprimer les anciennes compétences
                $this->internshipModel->removeAllSkills($id);

                // Ajouter les nouvelles
                $skills = explode(',', $data['skills']);
                foreach ($skills as $skillId) {
                    $this->internshipModel->addSkill($id, $skillId);
                }
            }

            return App::$app->response->json([
                'success' => true,
                'message' => 'Offre de stage mise à jour avec succès',
                'redirect' => '/admin/internships'
            ]);
        } else {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'offre de stage'
            ], 500);
        }
    }

    /**
     * Supprime une offre de stage
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

        $result = $this->internshipModel->delete($id);

        if ($result) {
            return App::$app->response->json([
                'success' => true,
                'message' => 'Offre de stage supprimée avec succès'
            ]);
        } else {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'offre de stage'
            ], 500);
        }
    }
}