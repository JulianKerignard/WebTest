<?php
namespace App\Controllers;

use App\Core\App;
use App\Core\Template;
use App\Models\Internship;
use App\Models\Company;
use App\Models\Wishlist;

/**
 * Contrôleur pour la page d'accueil et les pages statiques générales
 */
class HomeController {
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
     * Affiche la page d'accueil
     */
    public function index() {
        // Récupérer les stages populaires/récents (limités à 6)
        $popularInternships = $this->internshipModel->findAll(6);

        // Enrichir les données des stages
        foreach ($popularInternships as &$internship) {
            // Ajouter les initiales de l'entreprise pour l'affichage
            $internship['company_initials'] = substr($internship['company_name'] ?? 'NA', 0, 2);

            // Ajouter les compétences associées
            $internship['skills'] = [];
            $skills = $this->internshipModel->getSkillsForOffer($internship['ID_Offer']);
            foreach ($skills as $skill) {
                $internship['skills'][] = $skill['Skill_name'];
            }

            // Calculer le temps écoulé depuis la publication
            $internship['posted_time'] = $this->getTimeAgo($internship['Date_of_publication']);

            // Ajouter une catégorie pour le filtrage frontend
            $internship['category'] = $this->getCategoryFromSkills($internship['skills']);

            // Niveau d'études requis
            $internship['study_level'] = $internship['study_level'] ?? 'Non spécifié';
        }

        // Vérifier si l'utilisateur est connecté
        $session = App::$app->session;
        $user = $session->get('user');

        // Si l'utilisateur est connecté, vérifier les stages dans sa wishlist
        if ($user && $user['role'] === 'student') {
            foreach ($popularInternships as &$internship) {
                $internship['in_wishlist'] = $this->wishlistModel->isInWishlist($user['id'], $internship['ID_Offer']);
            }
        }

        // Générer un token CSRF pour les formulaires
        $csrfToken = '';
        if (method_exists('\App\Helpers\SecurityHelper', 'generateCSRFToken')) {
            $csrfToken = \App\Helpers\SecurityHelper::generateCSRFToken();
        }

        // Rendre la vue
        return $this->template->renderWithLayout('home/index', 'main', [
            'popularInternships' => $popularInternships,
            'user' => $user,
            'csrf_token' => $csrfToken
        ]);
    }

    /**
     * Détermine une catégorie pour l'offre en fonction des compétences
     */
    private function getCategoryFromSkills($skills) {
        // Catégories simplifiées
        $techSkills = ['JavaScript', 'Python', 'Java', 'PHP', 'C#', 'SQL', 'React', 'Node.js'];
        $marketingSkills = ['Marketing Digital', 'SEO', 'Communication'];
        $financeSkills = ['Excel', 'Finance'];
        $designSkills = ['Design UX/UI', 'Photoshop'];

        // Détermine la catégorie dominante
        $techCount = $marketingCount = $financeCount = $designCount = 0;

        foreach ($skills as $skill) {
            if (in_array($skill, $techSkills)) {
                $techCount++;
            } elseif (in_array($skill, $marketingSkills)) {
                $marketingCount++;
            } elseif (in_array($skill, $financeSkills)) {
                $financeCount++;
            } elseif (in_array($skill, $designSkills)) {
                $designCount++;
            }
        }

        // Retourne la catégorie avec le plus de compétences
        $max = max($techCount, $marketingCount, $financeCount, $designCount);

        if ($max == 0) {
            return 'all'; // Aucune catégorie spécifique trouvée
        } elseif ($max == $techCount) {
            return 'tech';
        } elseif ($max == $marketingCount) {
            return 'marketing';
        } elseif ($max == $financeCount) {
            return 'finance';
        } else {
            return 'design';
        }
    }

    /**
     * Calcule le temps écoulé depuis une date donnée
     */
    private function getTimeAgo($datetime) {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;

        if ($diff < 60) {
            return "il y a quelques secondes";
        } else if ($diff < 3600) {
            $mins = floor($diff / 60);
            return "il y a " . $mins . " minute" . ($mins > 1 ? "s" : "");
        } else if ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "il y a " . $hours . " heure" . ($hours > 1 ? "s" : "");
        } else if ($diff < 604800) {
            $days = floor($diff / 86400);
            return "il y a " . $days . " jour" . ($days > 1 ? "s" : "");
        } else if ($diff < 2592000) {
            $weeks = floor($diff / 604800);
            return "il y a " . $weeks . " semaine" . ($weeks > 1 ? "s" : "");
        } else if ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return "il y a " . $months . " mois";
        } else {
            $years = floor($diff / 31536000);
            return "il y a " . $years . " an" . ($years > 1 ? "s" : "");
        }
    }
}