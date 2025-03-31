<?php
namespace App\Controllers;

use App\Core\App;
use App\Core\Template;

/**
 * Contrôleur pour les pages légales et mentions obligatoires
 */
class LegalController {
    private $template;

    public function __construct() {
        $this->template = new Template();
    }

    /**
     * Affiche les conditions générales d'utilisation
     */
    public function terms() {
        // Vérifier si l'utilisateur est connecté
        $session = App::$app->session;
        $user = $session->get('user');

        return $this->template->renderWithLayout('legal/terms', 'main', [
            'user' => $user
        ]);
    }

    /**
     * Affiche la politique de confidentialité
     */
    public function privacy() {
        // Vérifier si l'utilisateur est connecté
        $session = App::$app->session;
        $user = $session->get('user');

        return $this->template->renderWithLayout('legal/privacy', 'main', [
            'user' => $user
        ]);
    }

    /**
     * Affiche les mentions légales
     */
    public function mentions() {
        // Vérifier si l'utilisateur est connecté
        $session = App::$app->session;
        $user = $session->get('user');

        return $this->template->renderWithLayout('legal/mentions', 'main', [
            'user' => $user
        ]);
    }
}