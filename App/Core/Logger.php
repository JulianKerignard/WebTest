<?php
namespace App\Core;

class Logger {
    private $logPath;

    public function __construct() {
        $this->logPath = __DIR__ . '/../../logs/';

        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    public function logError($error) {
        $logFile = $this->logPath . 'error_' . date('Y-m-d') . '.log';
        $date = date('Y-m-d H:i:s');

        $logMessage = "[{$date}] {$error['type']}: {$error['message']} in {$error['file']} on line {$error['line']}\n";

        if (isset($error['trace'])) {
            $logMessage .= "Trace:\n{$error['trace']}\n";
        }

        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    public function logActivity($activity, $userId = null) {
        $logFile = $this->logPath . 'activity_' . date('Y-m-d') . '.log';
        $date = date('Y-m-d H:i:s');
        $userInfo = $userId ? " (User ID: {$userId})" : '';

        $logMessage = "[{$date}]{$userInfo} {$activity}\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    public function logAccess($ip, $method, $path, $statusCode) {
        $logFile = $this->logPath . 'access_' . date('Y-m-d') . '.log';
        $date = date('Y-m-d H:i:s');

        $logMessage = "[{$date}] {$ip} {$method} {$path} {$statusCode}\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}