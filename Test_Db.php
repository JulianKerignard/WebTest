<?php
// db_check.php - Placez ce fichier à la racine de votre projet

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;
use App\Core\Logger;

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Vérification de la base de données</h1>";

// Connexion à la base de données
try {
    $db = Database::getInstance();
    if ($db->isConnected()) {
        echo "<p style='color:green'>✅ Connexion à la base de données réussie!</p>";
    } else {
        echo "<p style='color:red'>❌ Erreur de connexion: " . $db->getLastError() . "</p>";
        exit;
    }
} catch (\Exception $e) {
    echo "<p style='color:red'>❌ Exception lors de la connexion: " . $e->getMessage() . "</p>";
    exit;
}

// Vérification des tables nécessaires
$tables = [
    'applications' => "
        CREATE TABLE IF NOT EXISTS applications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            offer_id INT NOT NULL,
            cover_letter TEXT NOT NULL,
            cv_path VARCHAR(255) NOT NULL,
            status ENUM('pending', 'in-review', 'interview', 'accepted', 'rejected') NOT NULL DEFAULT 'pending',
            feedback TEXT NULL,
            interview_date DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            FOREIGN KEY (student_id) REFERENCES Student(ID_account) ON DELETE CASCADE,
            FOREIGN KEY (offer_id) REFERENCES Offers(ID_Offer) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    'application_status_history' => "
        CREATE TABLE IF NOT EXISTS application_status_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            application_id INT NOT NULL,
            status ENUM('pending', 'in-review', 'interview', 'accepted', 'rejected') NOT NULL,
            comment TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    'application_notes' => "
        CREATE TABLE IF NOT EXISTS application_notes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            application_id INT NOT NULL,
            user_id INT NOT NULL,
            content TEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES Account(ID_account) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    "
];

foreach ($tables as $tableName => $createStatement) {
    try {
        // Vérifier si la table existe
        $result = $db->fetch("SHOW TABLES LIKE '{$tableName}'");

        if ($result) {
            echo "<p>Table <strong>{$tableName}</strong>: ✅ Existe</p>";

            // Afficher le nombre d'enregistrements
            $count = $db->fetch("SELECT COUNT(*) as count FROM {$tableName}");
            echo "<p style='margin-left:20px;'>Nombre d'enregistrements: " . $count['count'] . "</p>";

            // Afficher la structure
            echo "<details style='margin-left:20px;'>";
            echo "<summary>Structure de la table</summary>";
            echo "<pre>";
            $columns = $db->fetchAll("SHOW COLUMNS FROM {$tableName}");
            print_r($columns);
            echo "</pre>";
            echo "</details>";
        } else {
            echo "<p>Table <strong>{$tableName}</strong>: ❌ N'existe pas</p>";

            // Créer la table
            $db->query($createStatement);
            echo "<p style='margin-left:20px;color:green'>✅ Table créée avec succès!</p>";
        }
    } catch (\Exception $e) {
        echo "<p style='color:red'>❌ Erreur lors de la vérification/création de la table {$tableName}: " . $e->getMessage() . "</p>";
    }
}

// Vérifier les données de test
echo "<h2>Vérification des données de test</h2>";

$tablesWithData = [
    'Account' => "SELECT * FROM Account LIMIT 5",
    'Student' => "SELECT * FROM Student LIMIT 5",
    'Offers' => "SELECT * FROM Offers LIMIT 5",
    'Company' => "SELECT * FROM Company LIMIT 5",
    'applications' => "SELECT * FROM applications LIMIT 5"
];

foreach ($tablesWithData as $tableName => $query) {
    try {
        $data = $db->fetchAll($query);

        echo "<details>";
        echo "<summary>Données de <strong>{$tableName}</strong> (" . count($data) . " enregistrements)</summary>";
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        echo "</details>";

    } catch (\Exception $e) {
        echo "<p style='color:red'>❌ Erreur lors de la récupération des données de {$tableName}: " . $e->getMessage() . "</p>";
    }
}

// Créer des données de test si nécessaire
echo "<h2>Création de données de test</h2>";

// Vérifier s'il y a des applications
$appCount = $db->fetch("SELECT COUNT(*) as count FROM applications")['count'];

if ($appCount == 0) {
    echo "<p>Aucune candidature dans la base de données. Création de données de test...</p>";

    try {
        // Vérifier s'il y a des étudiants et des offres
        $studentCount = $db->fetch("SELECT COUNT(*) as count FROM Student")['count'];
        $offerCount = $db->fetch("SELECT COUNT(*) as count FROM Offers")['count'];

        if ($studentCount > 0 && $offerCount > 0) {
            $student = $db->fetch("SELECT ID_account FROM Student LIMIT 1");
            $offer = $db->fetch("SELECT ID_Offer FROM Offers LIMIT 1");

            if ($student && $offer) {
                // Créer une application de test
                $appId = $db->insert('applications', [
                    'student_id' => $student['ID_account'],
                    'offer_id' => $offer['ID_Offer'],
                    'cover_letter' => 'Lettre de motivation de test pour le débogage.',
                    'cv_path' => 'test_cv.pdf',
                    'status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                if ($appId) {
                    // Ajouter un historique de statut
                    $db->insert('application_status_history', [
                        'application_id' => $appId,
                        'status' => 'pending',
                        'comment' => 'Candidature créée pour le test',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);

                    echo "<p style='color:green'>✅ Candidature de test créée avec ID: {$appId}</p>";
                }
            } else {
                echo "<p style='color:orange'>⚠️ Impossible de créer une candidature de test: étudiant ou offre introuvable</p>";
            }
        } else {
            echo "<p style='color:orange'>⚠️ Impossible de créer une candidature de test: pas assez d'étudiants ({$studentCount}) ou d'offres ({$offerCount})</p>";
        }

    } catch (\Exception $e) {
        echo "<p style='color:red'>❌ Erreur lors de la création des données de test: " . $e->getMessage() . "</p>";
    }
}

echo "<p>Vérification terminée.</p>";