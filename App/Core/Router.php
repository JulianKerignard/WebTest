<?php
namespace App\Core;

class Template {
    protected $vars = [];
    protected $viewsPath;

    public function __construct($viewsPath = null) {
        $this->viewsPath = $viewsPath ?? __DIR__ . '/../Views/';
    }

    public function set($key, $value) {
        $this->vars[$key] = $value;
    }

    public function render($template, $data = []) {
        $this->vars = array_merge($this->vars, $data);

        $templatePath = $this->viewsPath . $template . '.php';

        if (!file_exists($templatePath)) {
            throw new \Exception("Template file {$template}.php does not exist.");
        }

        ob_start();
        extract($this->vars);
        include $templatePath;
        return ob_get_clean();
    }

    public function renderWithLayout($template, $layout = 'main', $data = []) {
        $content = $this->render($template, $data);

        $layoutPath = $this->viewsPath . 'Templates/' . $layout . '.php';

        if (!file_exists($layoutPath)) {
            throw new \Exception("Layout file {$layout}.php does not exist.");
        }

        $this->set('content', $content);

        ob_start();
        extract($this->vars);
        include $layoutPath;
        return ob_get_clean();
    }
}