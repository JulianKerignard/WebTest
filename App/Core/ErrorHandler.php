<?php
namespace App\Core;

class ErrorHandler {
    private static $instance = null;
    private $logger;

    private function __construct() {
        // Set up error handlers
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);

        // Initialize logger
        $this->logger = new Logger();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function handleError($level, $message, $file, $line) {
        $error = [
            'type' => $level,
            'message' => $message,
            'file' => $file,
            'line' => $line
        ];

        $this->logger->logError($error);

        // Don't execute PHP's internal error handler
        return true;
    }

    public function handleException(\Throwable $exception) {
        $error = [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];

        $this->logger->logError($error);

        // Display error page in production, or detailed error in development
        if (getenv('APP_ENV') === 'production') {
            http_response_code(500);
            include __DIR__ . '/../Views/error/500.php';
        } else {
            http_response_code(500);
            echo '<h1>Error</h1>';
            echo '<p><strong>Message:</strong> ' . $exception->getMessage() . '</p>';
            echo '<p><strong>File:</strong> ' . $exception->getFile() . '</p>';
            echo '<p><strong>Line:</strong> ' . $exception->getLine() . '</p>';
            echo '<h2>Trace</h2>';
            echo '<pre>' . $exception->getTraceAsString() . '</pre>';
        }
    }

    public function handleShutdown() {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }
}