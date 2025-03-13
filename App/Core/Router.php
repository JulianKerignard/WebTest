<?php
namespace App\Core;

class Router {
    protected $routes = [];
    protected $request;
    protected $response;

    public function __construct(Request $request, Response $response) {
        $this->request = $request;
        $this->response = $response;
    }

    public function get($path, $callback) {
        $this->routes['get'][$path] = $callback;
    }

    public function post($path, $callback) {
        $this->routes['post'][$path] = $callback;
    }

    public function resolve() {
        $path = $this->request->getPath();
        $method = $this->request->method();
        $callback = $this->routes[$method][$path] ?? false;

        // Si la route exacte n'existe pas, vérifier les routes avec paramètres
        if (!$callback) {
            foreach ($this->routes[$method] as $route => $handler) {
                // Convertir les routes avec des paramètres (ex: /user/{id}) en expression régulière
                $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route);
                $pattern = '#^' . $pattern . '$#';

                if (preg_match($pattern, $path, $matches)) {
                    // Extraire les paramètres
                    $params = array_filter($matches, function($key) {
                        return !is_numeric($key);
                    }, ARRAY_FILTER_USE_KEY);

                    $callback = $handler;
                    break;
                }
            }
        }

        if (!$callback) {
            // Route non trouvée
            $this->response->setStatusCode(404);
            return $this->renderView('error/404');
        }

        if (is_string($callback)) {
            return $this->renderView($callback);
        }

        if (is_array($callback)) {
            $controller = new $callback[0]();
            $method = $callback[1];

            if (isset($params)) {
                return call_user_func_array([$controller, $method], $params);
            }

            return $controller->$method();
        }

        return call_user_func($callback);
    }

    public function renderView($view, $params = []) {
        $template = new Template();
        return $template->render($view, $params);
    }
}