<?php
namespace App\Core;

/**
 * Classe de base pour tous les modèles
 * Fournit les méthodes CRUD communes et la connexion à la base de données
 */
class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Récupère tous les enregistrements de la table avec pagination optionnelle
     */
    public function findAll($limit = null, $offset = 0) {
        $sql = "SELECT * FROM {$this->table}";

        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            return $this->db->fetchAll($sql, [$limit, $offset]);
        }

        return $this->db->fetchAll($sql);
    }

    /**
     * Récupère un enregistrement par sa clé primaire
     */
    public function findById($id) {
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?", [$id]);
    }

    /**
     * Crée un nouvel enregistrement
     */
    public function create($data) {
        return $this->db->insert($this->table, $data);
    }

    /**
     * Met à jour un enregistrement existant
     */
    public function update($id, $data) {
        return $this->db->update($this->table, $data, "{$this->primaryKey} = ?", [$id]);
    }

    /**
     * Supprime un enregistrement
     */
    public function delete($id) {
        return $this->db->delete($this->table, "{$this->primaryKey} = ?", [$id]);
    }

    /**
     * Compte le nombre total d'enregistrements
     */
    public function count() {
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM {$this->table}");
        return $result ? $result['count'] : 0;
    }

    /**
     * Récupère des enregistrements avec des conditions personnalisées
     */
    public function where($conditions, $params = [], $orderBy = null, $limit = null, $offset = 0) {
        $sql = "SELECT * FROM {$this->table} WHERE {$conditions}";

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }

        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Récupère un seul enregistrement avec des conditions personnalisées
     */
    public function findOne($conditions, $params = []) {
        $sql = "SELECT * FROM {$this->table} WHERE {$conditions} LIMIT 1";
        return $this->db->fetch($sql, $params);
    }
}