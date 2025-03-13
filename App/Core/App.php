<?php
namespace App\Core;

class App {
    public static $app;
    public $router;
    public $request;
    public $response;
    public $session;
    public $db;

    public function __construct() {
        self::$app = $this;
        $this->request = new Request();
        $this->response = new Response();
        $this->session = Session::getInstance();
        $this->router = new Router($this->request, $this->response);
        $this->db = Database::getInstance();
    }

    public function run() {
        echo $this->router->resolve();
    }
}