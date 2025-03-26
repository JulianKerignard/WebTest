<?php
namespace App\Middleware;

use App\Core\App;
use App\Core\Middleware;
use App\Helpers\SecurityHelper;

class CsrfMiddleware extends Middleware {
    public function execute($next) {
        $request = App::$app->request;
        $session = App::$app->session;

        // Only check non-GET requests
        if (!$request->isGet()) {
            $token = $request->get('csrf_token');

            if (!SecurityHelper::validateCSRFToken($token)) {
                $session->setFlash('error', 'Invalid CSRF token');

                // AJAX request
                if ($request->isAjax()) {
                    return App::$app->response->json([
                        'success' => false,
                        'message' => 'Invalid CSRF token'
                    ], 403);
                }

                // Regular request
                return App::$app->response->redirect('back');
            }
        }

        return $next();
    }
}