<?php
namespace App\Controllers;

use App\Core\App;
use App\Core\Template;
use App\Models\Internship;
use App\Models\Company;
use App\Models\Wishlist;

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

    public function index() {
        $request = App::$app->request;
        $page = (int)$request->get('page', 1);
        $limit = 12;
        $offset = ($page - 1) * $limit;

        // Récupérer les filtres
        $filters = [
            'keyword' => $request->get('keyword'),
            'company_id' => $request->get('company_id'),
            'level_id' => $request->get('level_id'),
            'min_remuneration' => $request->get('min_remuneration'),
            'duration' => $request->get('duration'),
            'sort' => $request->get('sort', 'date'),
            'limit' => $limit,
            'offset' => $offset
        ];

        // Récupérer les stages filtrés et paginés
        $internships = $this->internshipModel->search($filters);
        $totalInternships = $this->internshipModel->getTotalInternships($filters);
        $totalPages = ceil($totalInternships / $limit);

        // Récupérer les données pour les filtres
        $companies = $this->companyModel->findAll();
        $levels = $this->internshipModel->getLevels();

        // Vérifier si l'utilisateur est connecté
        $session = App::$app->session;
        $user = $session->get('user');

        return $this->template->renderWithLayout('stages/index', 'main', [
            'internships' => $internships,
            'company' => $companies,
            'levels' => $levels,
            'filters' => $filters,
            'pagination' => [
                'page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalInternships
            ],
            'user' => $user
        ]);
    }

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
        if ($user) {
            $inWishlist = $this->wishlistModel->isInWishlist($user['id'], $id);
        }

        return $this->template->renderWithLayout('stages/show', 'main', [
            'internship' => $internship,
            'company' => $company,
            'skills' => $skills,
            'inWishlist' => $inWishlist,
            'user' => $user
        ]);
    }

    // Actions administratives
    public function create() {
        // Vérifier si l'utilisateur est admin
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'pilot')) {
            $session->setFlash('error', 'Accès refusé');
            return App::$app->response->redirect('/');
        }

        $companies = $this->companyModel->findAll();
        $levels = $this->internshipModel->getLevels();
        $skills = $this->internshipModel->getSkills();

        return $this->template->renderWithLayout('admin/internships/create', 'dashboard', [
            'company' => $companies,
            'levels' => $levels,
            'skills' => $skills,
            'user' => $user
        ]);
    }

    public function store() {
        $request = App::$app->request;
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'pilot')) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Accès refusé'
            ], 403);
        }

        $data = $request->getBody();

        // Validation de base
        if (empty($data['Offer_title']) || empty($data['Description']) || empty($data['ID_Company'])) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Veuillez remplir tous les champs obligatoires'
            ], 400);
        }

        // Créer l'offre de stage
        $result = $this->internshipModel->create($data);

        if ($result) {
            // Si des compétences ont été sélectionnées, les associer à l'offre
            if (!empty($data['skills'])) {
                $skills = explode(',', $data['skills']);
                foreach ($skills as $skillId) {
                    // Cette fonction est théorique car la table de jonction n'existe pas encore
                    // $this->internshipModel->addSkill($result, $skillId);
                }
            }

            $session->setFlash('success', 'Offre de stage créée avec succès');
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

    public function edit($id) {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'pilot')) {
            $session->setFlash('error', 'Accès refusé');
            return App::$app->response->redirect('/');
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
            'company' => $companies,
            'levels' => $levels,
            'skills' => $skills,
            'internshipSkills' => $internshipSkills,
            'user' => $user
        ]);
    }

    public function update($id) {
        $request = App::$app->request;
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'pilot')) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Accès refusé'
            ], 403);
        }

        $data = $request->getBody();

        // Validation de base
        if (empty($data['Offer_title']) || empty($data['Description']) || empty($data['ID_Company'])) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Veuillez remplir tous les champs obligatoires'
            ], 400);
        }

        // Mettre à jour l'offre de stage
        $this->internshipModel->update($id, $data);

        // Mettre à jour les compétences
        // Cette partie est théorique car la table de jonction n'existe pas encore
        if (!empty($data['skills'])) {
            // Supprimer les compétences existantes
            // $this->internshipModel->removeAllSkills($id);

            // Ajouter les nouvelles compétences
            $skills = explode(',', $data['skills']);
            foreach ($skills as $skillId) {
                // $this->internshipModel->addSkill($id, $skillId);
            }
        }

        $session->setFlash('success', 'Offre de stage mise à jour avec succès');
        return App::$app->response->json([
            'success' => true,
            'message' => 'Offre de stage mise à jour avec succès',
            'redirect' => '/admin/internships'
        ]);
    }

    public function delete($id) {
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'pilot')) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Accès refusé'
            ], 403);
        }

        $this->internshipModel->delete($id);

        $session->setFlash('success', 'Offre de stage supprimée avec succès');
        return App::$app->response->json([
            'success' => true,
            'message' => 'Offre de stage supprimée avec succès'
        ]);
    }
}