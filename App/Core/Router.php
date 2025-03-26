<?php
namespace App\Core;

class Router {
    protected $routes = [];
    protected $middlewares = [];
    protected $request;
    protected $response;
    public $currentAction = null;

    public function __construct(Request $request, Response $response) {
        $this->request = $request;
        $this->response = $response;
    }

    public function get($path, $callback, $middlewares = []) {
        $this->routes['get'][$path] = ['callback' => $callback, 'middlewares' => $middlewares];
    }

    public function post($path, $callback, $middlewares = []) {
        $this->routes['post'][$path] = ['callback' => $callback, 'middlewares' => $middlewares];
    }

    public function resolve() {
        $path = $this->request->getPath();
        $method = $this->request->method();
        $routeData = $this->routes[$method][$path] ?? null;
        $params = [];

        // Si la route exacte n'existe pas, vérifier les routes avec paramètres
        if (!$routeData) {
            foreach ($this->routes[$method] as $route => $data) {
                // Convertir les routes avec des paramètres (ex: /user/{id}) en expression régulière
                $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route);
                $pattern = '#^' . $pattern . '$#';

                if (preg_match($pattern, $path, $matches)) {
                    // Extraire les paramètres
                    $params = array_filter($matches, function($key) {
                        return !is_numeric($key);
                    }, ARRAY_FILTER_USE_KEY);

                    $routeData = $data;
                    break;
                }
            }
        }

        if (!$routeData) {
            // Route non trouvée
            $this->response->setStatusCode(404);
            return $this->renderView('error/404');
        }

        $callback = $routeData['callback'];
        $middlewares = $routeData['middlewares'];

        if (is_string($callback)) {
            return $this->executeMiddlewares($middlewares, function() use ($callback) {
                return $this->renderView($callback);
            });
        }

        if (is_array($callback)) {
            $controller = new $callback[0]();
            $method = $callback[1];
            $this->currentAction = $method;

            return $this->executeMiddlewares($middlewares, function() use ($controller, $method, $params) {
                if (count($params) > 0) {
                    return call_user_func_array([$controller, $method], $params);
                }
                return $controller->$method();
            });
        }

        return $this->executeMiddlewares($middlewares, function() use ($callback) {
            return call_user_func($callback);
        });
    }

    protected function executeMiddlewares(array $middlewares, $callback) {
        $next = $callback;

        foreach (array_reverse($middlewares) as $middleware) {
            $next = function() use ($middleware, $next) {
                return $middleware->execute($next);
            };
        }

        return $next();
    }

    public function renderView($view, $params = []) {
        $template = new Template();
        return $template->render($view, $params);
    }
}