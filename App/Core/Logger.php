<?php
namespace App\Core;

class Logger {
    private $logDir;
    private $errorLog;
    private $accessLog;
    private $activityLog;

    public function __construct() {
        $this->logDir = __DIR__ . '/../../logs/';

        // Créer le répertoire des logs s'il n'existe pas
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }

        $this->errorLog = $this->logDir . 'error.log';
        $this->accessLog = $this->logDir . 'access.log';
        $this->activityLog = $this->logDir . 'activity.log';
    }

    /**
     * Journaliser une erreur
     */
    public function logError($error) {
        $timestamp = date('Y-m-d H:i:s');

        if (is_array($error)) {
            $errorMessage = "[{$timestamp}] ";
            foreach ($error as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                $errorMessage .= "{$key}: {$value}, ";
            }
            $errorMessage = rtrim($errorMessage, ', ') . PHP_EOL;
        } else {
            $errorMessage = "[{$timestamp}] {$error}" . PHP_EOL;
        }

        error_log($errorMessage, 3, $this->errorLog);

        // En mode debug, également afficher l'erreur
        if (Config::getInstance()->get('app.debug', false)) {
            echo "<div style='background:#f8d7da; color:#721c24; padding:10px; margin:10px; border-radius:5px;'>";
            echo "<strong>Error:</strong> ";
            echo is_array($error) ? json_encode($error) : $error;
            echo "</div>";
        }
    }

    /**
     * Journaliser un accès
     */
    public function logAccess($ip, $method, $path, $statusCode) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$ip} {$method} {$path} {$statusCode}" . PHP_EOL;
        error_log($logMessage, 3, $this->accessLog);
    }

    /**
     * Journaliser une activité
     */
    public function logActivity($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
        error_log($logMessage, 3, $this->activityLog);
    }

    /**
     * Vérifier si les tables de la base de données existent
     */
    public function checkDatabaseTables() {
        // Récupérer la connexion à la base de données
        $db = Database::getInstance();
        $tables = [
            'applications',
            'application_status_history',
            'application_notes',
            'Offers',
            'Student',
            'Company',
            'Account'
        ];

        $results = [];
        foreach ($tables as $table) {
            try {
                $count = $db->fetch("SELECT COUNT(*) as count FROM {$table}");
                $results[$table] = [
                    'exists' => true,
                    'count' => $count['count'] ?? 0
                ];
            } catch (\Exception $e) {
                $results[$table] = [
                    'exists' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        $this->logActivity("Database tables check: " . json_encode($results));
        return $results;
    }
}