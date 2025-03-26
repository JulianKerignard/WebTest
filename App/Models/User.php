<?php
namespace App\Models;

use App\Core\Database;

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById($id) {
        return $this->db->fetch("SELECT * FROM Account WHERE ID_account = ?", [$id]);
    }

    public function findByEmail($email) {
        return $this->db->fetch("SELECT * FROM Account WHERE Email = ?", [$email]);
    }

    public function create($data) {
        return $this->db->insert('Account', [
            'Email' => $data['Email'],
            'Username' => $data['Username'],
            'Password' => $data['Password'],
            'Civility' => $data['Civility'] ?? null,
            '_Rank' => $data['_Rank'] ?? 1
        ]);
    }

    public function update($id, $data) {
        return $this->db->update('Account', [
            'Email' => $data['Email'],
            'Username' => $data['Username'],
            'Civility' => $data['Civility'] ?? null
        ], 'ID_account = ?', [$id]);
    }

    public function updatePassword($id, $hashedPassword) {
        return $this->db->update('Account', [
            'Password' => $hashedPassword
        ], 'ID_account = ?', [$id]);
    }

    public function delete($id) {
        return $this->db->delete('Account', 'ID_account = ?', [$id]);
    }

    public function getRoleById($id) {
        $admin = $this->db->fetch("SELECT * FROM admin WHERE ID_account = ?", [$id]);
        if ($admin) {
            return 'admin';
        }

        $pilot = $this->db->fetch("SELECT * FROM pilote WHERE ID_account = ?", [$id]);
        if ($pilot) {
            return 'pilot';
        }

        $student = $this->db->fetch("SELECT * FROM Student WHERE ID_account = ?", [$id]);
        if ($student) {
            return 'student';
        }

        return null;
    }
}