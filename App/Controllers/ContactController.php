<?php
namespace App\Controllers;

use App\Core\App;
use App\Core\Template;
use App\Core\Validator;
use App\Helpers\SecurityHelper;

/**
 * Contrôleur pour la page de contact
 */
class ContactController {
    private $template;

    public function __construct() {
        $this->template = new Template();
    }

    /**
     * Affiche la page de contact
     */
    public function index() {
        // Vérifier si l'utilisateur est connecté
        $session = App::$app->session;
        $user = $session->get('user');

        // Générer un token CSRF
        $csrfToken = SecurityHelper::generateCSRFToken();

        return $this->template->renderWithLayout('contact/index', 'main', [
            'user' => $user,
            'csrf_token' => $csrfToken
        ]);
    }

    /**
     * Traite le formulaire de contact
     */
    public function submit() {
        $request = App::$app->request;
        $session = App::$app->session;

        // Récupérer les données du formulaire
        $data = $request->getBody();

        // Valider les données
        $validator = new Validator();
        if (!$validator->validate($data, [
            'name' => 'required|min:3',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required|min:10',
            'privacy' => 'required'
        ])) {
            // En cas d'erreur, renvoyer vers le formulaire avec les erreurs
            $session->setFlash('errors', $validator->getErrors());
            $session->setFlash('old', $data);
            $session->setFlash('error', 'Veuillez corriger les erreurs dans le formulaire');
            return App::$app->response->redirect('/contact');
        }

        // Enregistrer le message dans un fichier de log (simplification)
        $logMessage = "Date: " . date('Y-m-d H:i:s') . "\n";
        $logMessage .= "Nom: " . $data['name'] . "\n";
        $logMessage .= "Email: " . $data['email'] . "\n";
        $logMessage .= "Sujet: " . $data['subject'] . "\n";
        $logMessage .= "Message: " . $data['message'] . "\n";
        $logMessage .= "------------------------------------------------\n";

        $logDir = __DIR__ . '/../../logs/contacts/';

        // Créer le répertoire s'il n'existe pas
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . 'contact_' . date('Y-m-d') . '.log';
        file_put_contents($logFile, $logMessage, FILE_APPEND);

        // Journaliser l'activité
        App::$app->logger->logActivity("Nouveau message de contact reçu de {$data['email']}");

        // Envoyer un message de succès
        $session->setFlash('success', 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.');

        // Rediriger vers la page de contact
        return App::$app->response->redirect('/contact');
    }
}