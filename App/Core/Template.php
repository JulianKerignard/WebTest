<?php
namespace App\Core;

class Template {
    private $layout = 'main';

    /**
     * Render a view with the specified parameters
     */
    public function render($view, $params = []) {
        return $this->renderView($view, $params);
    }

    /**
     * Render a view with a layout
     */
    public function renderWithLayout($view, $layout = null, $params = []) {
        $this->layout = $layout ?: $this->layout;
        $viewContent = $this->renderView($view, $params);

        if ($this->layout) {
            $layoutParams = array_merge($params, ['content' => $viewContent]);
            return $this->renderView("layouts/{$this->layout}", $layoutParams);
        }

        return $viewContent;
    }

    /**
     * Render view content
     */
    private function renderView($view, $params = []) {
        // Extract params to make them available in the view
        foreach ($params as $key => $value) {
            $$key = $value;
        }

        // Start output buffering
        ob_start();

        // Include the view file
        $viewFile = __DIR__ . "/../Views/{$view}.php";
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo "View file not found: {$viewFile}";
        }

        // Get buffered content and clean buffer
        return ob_get_clean();
    }

    /**
     * Get layout content
     */
    public function getLayoutContent($params = []) {
        return $this->renderView("layouts/{$this->layout}", $params);
    }
}