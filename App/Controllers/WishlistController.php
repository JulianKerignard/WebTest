<?php

namespace App\Controllers;

use App\Core\App;
use App\Core\Template;
use App\Models\Wishlist;
use App\Models\Internship;

class WishlistController
{
    private $template;
    private $wishlistModel;
    private $internshipModel;

    public function __construct()
    {
        $this->template = new Template();
        $this->wishlistModel = new Wishlist();
        $this->internshipModel = new Internship();
    }

    public function index()
    {
        // Vérifier si l'utilisateur est connecté
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user) {
            $session->setFlash('error', 'Vous devez être connecté pour accéder à vos favoris');
            return App::$app->response->redirect('/login');
        }

        // Récupérer les favoris de l'utilisateur
        $wishlist = $this->wishlistModel->getWishlist($user['id']);

        return $this->template->renderWithLayout('student/wishlist', 'dashboard', [
            'wishlist' => $wishlist,
            'user' => $user
        ]);
    }

    public function addToWishlist()
    {
        $request = App::$app->request;
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour ajouter aux favoris'
            ], 401);
        }

        $offerId = $request->get('offer_id');
        if (!$offerId) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Identifiant de l\'offre manquant'
            ], 400);
        }

        // Vérifier si l'offre existe
        $offer = $this->internshipModel->findById($offerId);
        if (!$offer) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Offre de stage non trouvée'
            ], 404);
        }

        // Ajouter l'offre aux favoris
        $result = $this->wishlistModel->addToWishlist($user['id'], $offerId);

        if ($result) {
            return App::$app->response->json([
                'success' => true,
                'message' => 'Offre ajoutée aux favoris avec succès'
            ]);
        } else {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Cette offre est déjà dans vos favoris'
            ], 400);
        }
    }

    public function removeFromWishlist()
    {
        $request = App::$app->request;
        $session = App::$app->session;
        $user = $session->get('user');

        if (!$user) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour retirer des favoris'
            ], 401);
        }

        $offerId = $request->get('offer_id');
        if (!$offerId) {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Identifiant de l\'offre manquant'
            ], 400);
        }

        // Retirer l'offre des favoris
        $result = $this->wishlistModel->removeFromWishlist($user['id'], $offerId);

        if ($result) {
            return App::$app->response->json([
                'success' => true,
                'message' => 'Offre retirée des favoris avec succès'
            ]);
        } else {
            return App::$app->response->json([
                'success' => false,
                'message' => 'Erreur lors du retrait des favoris'
            ], 500);
        }
    }

    public function checkWishlist()
    {
        $request = App::$app->request;
        $session = App::$app->session;
        $user = $session->get('user');

        $offerId = $request->get('offer_id');
        if (!$user || !$offerId) {
            return App::$app->response->json([
                'success' => false,
                'inWishlist' => false
            ]);
        }

        $inWishlist = $this->wishlistModel->isInWishlist($user['id'], $offerId);

        return App::$app->response->json([
            'success' => true,
            'inWishlist' => $inWishlist
        ]);
    }
}