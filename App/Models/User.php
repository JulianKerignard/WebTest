<?php
namespace App\Models;

use App\Core\Database;
use App\Helpers\SecurityHelper;

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
            '_Rank' => $data['_Rank'] ?? 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function update($id, $data) {
        $updateData = [
            'Email' => $data['Email'],
            'Username' => $data['Username'],
            'Civility' => $data['Civility'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->db->update('Account', $updateData, 'ID_account = ?', [$id]);
    }

    public function updatePassword($id, $hashedPassword) {
        return $this->db->update('Account', [
            'Password' => $hashedPassword,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'ID_account = ?', [$id]);
    }

    public function updateLastLogin($id) {
        return $this->db->update('Account', [
            'last_login' => date('Y-m-d H:i:s')
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

    // Méthodes pour la gestion des tokens de réinitialisation
    public function storeResetToken($userId, $token, $expiryDate) {
        // Cette table devrait être créée:
        // CREATE TABLE password_resets (
        //    id INT PRIMARY KEY AUTO_INCREMENT,
        //    user_id INT NOT NULL,
        //    token VARCHAR(100) NOT NULL,
        //    created_at DATETIME NOT NULL,
        //    expires_at DATETIME NOT NULL,
        //    FOREIGN KEY (user_id) REFERENCES Account(ID_account) ON DELETE CASCADE
        // )

        // Supprimer les anciens tokens pour cet utilisateur
        $this->db->delete('password_resets', 'user_id = ?', [$userId]);

        // Créer un nouveau token
        return $this->db->insert('password_resets', [
            'user_id' => $userId,
            'token' => $token,
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => $expiryDate
        ]);
    }

    public function findByResetToken($token) {
        return $this->db->fetch("
            SELECT * FROM password_resets
            WHERE token = ? AND expires_at > NOW()
        ", [$token]);
    }

    public function deleteResetToken($token) {
        return $this->db->delete('password_resets', 'token = ?', [$token]);
    }

    // Méthodes pour la gestion de la sécurité
    public function checkLoginAttempts($email, $ip, $timeframe = 10) {
        $result = $this->db->fetch("
            SELECT COUNT(*) as count
            FROM login_attempts
            WHERE (email = ? OR ip_address = ?)
            AND successful = 0
            AND timestamp > DATE_SUB(NOW(), INTERVAL ? MINUTE)
        ", [$email, $ip, $timeframe]);

        return $result ? $result['count'] : 0;
    }

    public function isAccountLocked($email) {
        // Vérifier si le compte est verrouillé
        // Implémenter selon la politique de sécurité
        $maxAttempts = 5;
        $lockoutTime = 10; // minutes

        $attempts = $this->checkLoginAttempts($email, '', $lockoutTime);
        return $attempts >= $maxAttempts;
    }

    // Gestion des utilisateurs (admin)
    public function getAllUsers($role = null) {
        if ($role === 'admin') {
            return $this->db->fetchAll("
                SELECT a.*, 'admin' as role
                FROM Account a
                JOIN admin adm ON a.ID_account = adm.ID_account
                ORDER BY a.Username
            ");
        } else if ($role === 'pilot') {
            return $this->db->fetchAll("
                SELECT a.*, 'pilot' as role, c.City as center
                FROM Account a
                JOIN pilote p ON a.ID_account = p.ID_account
                LEFT JOIN Center c ON p.Center_ID = c.ID_Center
                ORDER BY a.Username
            ");
        } else if ($role === 'student') {
            return $this->db->fetchAll("
                SELECT a.*, 'student' as role, s.promotion, s.school_name
                FROM Account a
                JOIN Student s ON a.ID_account = s.ID_account
                ORDER BY a.Username
            ");
        } else {
            // Tous les utilisateurs
            return $this->db->fetchAll("
                SELECT a.*, 
                    CASE 
                        WHEN adm.ID_account IS NOT NULL THEN 'admin'
                        WHEN p.ID_account IS NOT NULL THEN 'pilot'
                        WHEN s.ID_account IS NOT NULL THEN 'student'
                        ELSE 'unknown'
                    END as role
                FROM Account a
                LEFT JOIN admin adm ON a.ID_account = adm.ID_account
                LEFT JOIN pilote p ON a.ID_account = p.ID_account
                LEFT JOIN Student s ON a.ID_account = s.ID_account
                ORDER BY a.Username
            ");
        }
    }
}