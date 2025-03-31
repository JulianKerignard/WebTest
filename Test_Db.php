<?php
// Test de la base de données pour LeBonPlan

// Charge la configuration de base de données
$config = require __DIR__ . '/App/Config/config.php';
$dbConfig = $config['database'];

echo '<h1>Test de connexion à la base de données LeBonPlan</h1>';
echo '<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    tr:nth-child(even) { background-color: #f9f9f9; }
    .success { color: green; }
    .error { color: red; }
    h2 { margin-top: 30px; }
</style>';

// Test de connexion à la base de données
try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo '<p class="success">✅ Connexion à la base de données réussie!</p>';
    echo '<p>Configuration utilisée:</p>';
    echo '<ul>';
    echo '<li>Hôte: ' . $dbConfig['host'] . '</li>';
    echo '<li>Base de données: ' . $dbConfig['database'] . '</li>';
    echo '<li>Utilisateur: ' . $dbConfig['username'] . '</li>';
    echo '</ul>';

    // Test des tables principales
    $tables = [
        'Account' => 'SELECT COUNT(*) as count, _Rank, COUNT(DISTINCT Email) as unique_emails FROM Account GROUP BY _Rank',
        'Student' => 'SELECT COUNT(*) as count FROM Student',
        'Company' => 'SELECT COUNT(*) as count FROM Company',
        'Offers' => 'SELECT COUNT(*) as count, status FROM Offers GROUP BY status',
        'applications' => 'SELECT COUNT(*) as count, status FROM applications GROUP BY status',
        'wishlist' => 'SELECT COUNT(*) as count FROM wishlist',
    ];

    echo '<h2>Vérification des tables principales</h2>';

    foreach ($tables as $table => $query) {
        echo "<h3>Table: {$table}</h3>";
        try {
            $stmt = $pdo->query($query);
            $results = $stmt->fetchAll();

            if (empty($results)) {
                echo "<p>Aucune donnée trouvée dans la table {$table}</p>";
            } else {
                echo '<table>';
                // En-têtes de colonnes
                echo '<tr>';
                foreach (array_keys($results[0]) as $column) {
                    echo "<th>{$column}</th>";
                }
                echo '</tr>';

                // Données des lignes
                foreach ($results as $row) {
                    echo '<tr>';
                    foreach ($row as $value) {
                        echo "<td>{$value}</td>";
                    }
                    echo '</tr>';
                }
                echo '</table>';
            }
        } catch (PDOException $e) {
            echo '<p class="error">❌ Erreur lors de la requête sur la table ' . $table . ': ' . $e->getMessage() . '</p>';
        }
    }

    // Test des relations entre tables
    echo '<h2>Tests des relations entre tables</h2>';

    // 1. Offres de stage avec détails des entreprises
    echo '<h3>Offres de stage avec détails des entreprises</h3>';
    try {
        $stmt = $pdo->query("
            SELECT o.ID_Offer, o.Offer_title, o.Date_of_publication, 
                   c.Name as company_name, c.Size as company_size
            FROM Offers o
            JOIN Company c ON o.ID_Company = c.ID_Company
            ORDER BY o.Date_of_publication DESC
            LIMIT 5
        ");
        $internships = $stmt->fetchAll();

        if (empty($internships)) {
            echo "<p>Aucune offre de stage trouvée</p>";
        } else {
            echo '<table>';
            echo '<tr>';
            foreach (array_keys($internships[0]) as $column) {
                echo "<th>{$column}</th>";
            }
            echo '</tr>';

            foreach ($internships as $internship) {
                echo '<tr>';
                foreach ($internship as $value) {
                    echo "<td>{$value}</td>";
                }
                echo '</tr>';
            }
            echo '</table>';
        }
    } catch (PDOException $e) {
        echo '<p class="error">❌ Erreur lors de la requête sur les offres: ' . $e->getMessage() . '</p>';
    }

    // 2. Candidatures avec détails des étudiants et offres
    echo '<h3>Candidatures avec détails des étudiants et offres</h3>';
    try {
        $stmt = $pdo->query("
            SELECT a.id, a.status, a.created_at,
                   acc.Username as student_name,
                   o.Offer_title,
                   c.Name as company_name
            FROM applications a
            JOIN Student s ON a.student_id = s.ID_account
            JOIN Account acc ON s.ID_account = acc.ID_account
            JOIN Offers o ON a.offer_id = o.ID_Offer
            JOIN Company c ON o.ID_Company = c.ID_Company
            ORDER BY a.created_at DESC
            LIMIT 5
        ");
        $applications = $stmt->fetchAll();

        if (empty($applications)) {
            echo "<p>Aucune candidature trouvée</p>";
        } else {
            echo '<table>';
            echo '<tr>';
            foreach (array_keys($applications[0]) as $column) {
                echo "<th>{$column}</th>";
            }
            echo '</tr>';

            foreach ($applications as $application) {
                echo '<tr>';
                foreach ($application as $value) {
                    echo "<td>{$value}</td>";
                }
                echo '</tr>';
            }
            echo '</table>';
        }
    } catch (PDOException $e) {
        echo '<p class="error">❌ Erreur lors de la requête sur les candidatures: ' . $e->getMessage() . '</p>';
    }

    // 3. Wishlist avec les détails des offres
    echo '<h3>Stages en wishlist par étudiant</h3>';
    try {
        $stmt = $pdo->query("
            SELECT w.student_id, acc.Username as student_name, 
                   COUNT(w.offer_id) as offers_count,
                   GROUP_CONCAT(o.Offer_title SEPARATOR ', ') as offers
            FROM wishlist w
            JOIN Account acc ON w.student_id = acc.ID_account
            JOIN Offers o ON w.offer_id = o.ID_Offer
            GROUP BY w.student_id
        ");
        $wishlists = $stmt->fetchAll();

        if (empty($wishlists)) {
            echo "<p>Aucune wishlist trouvée</p>";
        } else {
            echo '<table>';
            echo '<tr>';
            foreach (array_keys($wishlists[0]) as $column) {
                echo "<th>{$column}</th>";
            }
            echo '</tr>';

            foreach ($wishlists as $wish) {
                echo '<tr>';
                foreach ($wish as $value) {
                    echo "<td>{$value}</td>";
                }
                echo '</tr>';
            }
            echo '</table>';
        }
    } catch (PDOException $e) {
        echo '<p class="error">❌ Erreur lors de la requête sur les wishlists: ' . $e->getMessage() . '</p>';
    }

    // 4. Test de la requête pour les stages populaires (comme dans HomeController)
    echo '<h3>Test de la requête pour les stages populaires</h3>';
    try {
        $stmt = $pdo->query("
            SELECT o.*, c.Name as company_name, c.Logo as company_logo, c.Size as company_size,
                   o.location, o.monthly_remuneration,
                   (SELECT COUNT(*) FROM applications a WHERE a.offer_id = o.ID_Offer) as application_count,
                   (SELECT COUNT(*) FROM wishlist w WHERE w.offer_id = o.ID_Offer) as wishlist_count
            FROM Offers o
            JOIN Company c ON o.ID_Company = c.ID_Company
            WHERE o.status = 'active'
            ORDER BY application_count DESC, wishlist_count DESC, o.Date_of_publication DESC
            LIMIT 6
        ");
        $popularInternships = $stmt->fetchAll();

        if (empty($popularInternships)) {
            echo "<p>Aucun stage populaire trouvé</p>";
        } else {
            echo '<table>';
            echo '<tr>';
            echo "<th>ID</th>";
            echo "<th>Titre</th>";
            echo "<th>Entreprise</th>";
            echo "<th>Lieu</th>";
            echo "<th>Date de publication</th>";
            echo "<th>Candidatures</th>";
            echo "<th>Wishlists</th>";
            echo '</tr>';

            foreach ($popularInternships as $internship) {
                echo '<tr>';
                echo "<td>{$internship['ID_Offer']}</td>";
                echo "<td>{$internship['Offer_title']}</td>";
                echo "<td>{$internship['company_name']}</td>";
                echo "<td>{$internship['location']}</td>";
                echo "<td>{$internship['Date_of_publication']}</td>";
                echo "<td>{$internship['application_count']}</td>";
                echo "<td>{$internship['wishlist_count']}</td>";
                echo '</tr>';
            }
            echo '</table>';
        }
    } catch (PDOException $e) {
        echo '<p class="error">❌ Erreur lors de la requête sur les stages populaires: ' . $e->getMessage() . '</p>';
    }

} catch (PDOException $e) {
    echo '<p class="error">❌ Erreur de connexion à la base de données: ' . $e->getMessage() . '</p>';
}
?>