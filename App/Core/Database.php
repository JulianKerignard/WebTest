<?php
namespace App\Core;

class Database {
    private static $instance = null;
    private $connection = null;
    private $isConnected = false;
    private $lastError = null;

    private function __construct() {
        $host = getenv('DB_HOST') ?: 'localhost';
        $db = getenv('DB_NAME') ?: 'aqghqfnk_test';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->connection = new \PDO($dsn, $user, $pass, $options);
            $this->isConnected = true;
        } catch (\PDOException $e) {
            $this->lastError = $e->getMessage();
            $this->isConnected = false;
            if (isset(App::$app->logger)) {
                App::$app->logger->logError([
                    'type' => 'Database Connection Error',
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
            }
            throw new \PDOException("Erreur de connexion à la base de données: " . $e->getMessage(), (int)$e->getCode());
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            try {
                self::$instance = new Database();
            } catch (\PDOException $e) {
                // Log l'erreur et retourne quand même une instance, même si elle n'est pas connectée
                self::$instance = new Database();
                self::$instance->isConnected = false;
                self::$instance->lastError = $e->getMessage();
            }
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function isConnected() {
        return $this->isConnected;
    }

    public function getLastError() {
        return $this->lastError;
    }

    public function testConnection() {
        if (!$this->isConnected || !$this->connection) {
            return false;
        }

        try {
            $this->connection->query('SELECT 1');
            return true;
        } catch (\PDOException $e) {
            $this->lastError = $e->getMessage();
            $this->isConnected = false;
            return false;
        }
    }

    public function query($sql, $params = []) {
        if (!$this->isConnected || !$this->connection) {
            throw new \Exception("La connexion à la base de données n'est pas établie");
        }

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            $this->lastError = $e->getMessage();
            if (isset(App::$app->logger)) {
                App::$app->logger->logError([
                    'type' => 'Database Query Error',
                    'message' => $e->getMessage(),
                    'sql' => $sql,
                    'params' => print_r($params, true)
                ]);
            }
            throw $e;
        }
    }

    public function fetch($sql, $params = []) {
        if (!$this->isConnected || !$this->connection) {
            return null;
        }

        try {
            $stmt = $this->query($sql, $params);
            return $stmt->fetch();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function fetchAll($sql, $params = []) {
        if (!$this->isConnected || !$this->connection) {
            return [];
        }

        try {
            $stmt = $this->query($sql, $params);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function insert($table, $data) {
        if (!$this->isConnected || !$this->connection) {
            throw new \Exception("La connexion à la base de données n'est pas établie");
        }

        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = implode(', ', array_fill(0, count($keys), '?'));

        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";

        try {
            $this->query($sql, array_values($data));
            return $this->connection->lastInsertId();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function update($table, $data, $where, $whereParams = []) {
        if (!$this->isConnected || !$this->connection) {
            throw new \Exception("La connexion à la base de données n'est pas établie");
        }

        $sets = [];
        foreach (array_keys($data) as $key) {
            $sets[] = "{$key} = ?";
        }

        $sql = "UPDATE {$table} SET " . implode(', ', $sets) . " WHERE {$where}";

        $params = array_merge(array_values($data), $whereParams);
        $this->query($sql, $params);
        return true;
    }

    public function delete($table, $where, $params = []) {
        if (!$this->isConnected || !$this->connection) {
            throw new \Exception("La connexion à la base de données n'est pas établie");
        }

        $sql = "DELETE FROM {$table} WHERE {$where}";
        $this->query($sql, $params);
        return true;
    }
}