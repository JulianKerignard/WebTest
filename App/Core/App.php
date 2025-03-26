<?php
namespace App\Core;

class App {
    public static $app;
    public $router;
    public $request;
    public $response;
    public $session;
    public $db;
    public $config;
    public $logger;

    public function __construct() {
        self::$app = $this;
        $this->request = new Request();
        $this->response = new Response();
        $this->session = Session::getInstance();
        $this->router = new Router($this->request, $this->response);
        $this->db = Database::getInstance();
        $this->config = Config::getInstance();
        $this->logger = new Logger();

        // Log access
        $this->logAccess();
    }

    private function logAccess() {
        $method = $this->request->method();
        $path = $this->request->getPath();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        // Log access will be called at the end to include status code
        register_shutdown_function(function() use ($method, $path, $ip) {
            $statusCode = http_response_code();
            $this->logger->logAccess($ip, $method, $path, $statusCode);
        });
    }

    public function run() {
        try {
            echo $this->router->resolve();
        } catch (\Exception $e) {
            $this->logger->logError([
                'type' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($this->request->isAjax()) {
                $this->response->json([
                    'success' => false,
                    'message' => $this->config->get('app.debug') ? $e->getMessage() : 'Une erreur est survenue'
                ], 500);
                return;
            }

            $this->response->setStatusCode(500);
            echo $this->router->renderView('error/500');
        }
    }
}