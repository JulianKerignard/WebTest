<?php
namespace App\Models;

use App\Core\Database;

class Company {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findAll($limit = null, $offset = 0) {
        $sql = "SELECT c.*, s.Sector as sector_name,
               (SELECT COUNT(*) FROM Offers WHERE ID_Company = c.ID_Company) as offers_count,
               (SELECT AVG(rating) FROM company_evaluations WHERE company_id = c.ID_Company) as avg_rating
               FROM Company c
               LEFT JOIN Sector_Of_Activity s ON c.ID_Sector = s.ID_Sector
               ORDER BY c.Name";

        if ($limit !== null) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }

        return $this->db->fetchAll($sql);
    }

    public function findById($id) {
        return $this->db->fetch("
            SELECT c.*, s.Sector as sector_name,
            (SELECT COUNT(*) FROM Offers WHERE ID_Company = c.ID_Company) as offers_count,
            (SELECT AVG(rating) FROM company_evaluations WHERE company_id = c.ID_Company) as avg_rating,
            (SELECT COUNT(*) FROM company_evaluations WHERE company_id = c.ID_Company) as reviews_count
            FROM Company c
            LEFT JOIN Sector_Of_Activity s ON c.ID_Sector = s.ID_Sector
            WHERE c.ID_Company = ?
        ", [$id]);
    }

    public function create($data) {
        // Traitement du logo si fourni
        $logoPath = null;
        if (isset($data['logo']) && $data['logo']['error'] === UPLOAD_ERR_OK) {
            $result = \App\Helpers\FileHelper::uploadFile(
                $data['logo'],
                'company_logos',
                ['jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png']]
            );

            if ($result['success']) {
                $logoPath = $result['filename'];
            }
        }

        return $this->db->insert('Company', [
            'Name' => $data['Name'],
            'Description' => $data['Description'],
            'ID_Sector' => $data['ID_Sector'] ?? null,
            'Adresse' => $data['Adresse'] ?? null,
            'Size' => $data['Size'] ?? null,
            'Email' => $data['Email'] ?? null,
            'Phone' => $data['Phone'] ?? null,
            'Website' => $data['Website'] ?? null,
            'Logo' => $logoPath,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function update($id, $data) {
        $updateData = [
            'Name' => $data['Name'],
            'Description' => $data['Description'],
            'ID_Sector' => $data['ID_Sector'] ?? null,
            'Adresse' => $data['Adresse'] ?? null,
            'Size' => $data['Size'] ?? null,
            'Email' => $data['Email'] ?? null,
            'Phone' => $data['Phone'] ?? null,
            'Website' => $data['Website'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Traitement du logo si fourni
        if (isset($data['logo']) && $data['logo']['error'] === UPLOAD_ERR_OK) {
            $result = \App\Helpers\FileHelper::uploadFile(
                $data['logo'],
                'company_logos',
                ['jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png']]
            );

            if ($result['success']) {
                // Supprimer l'ancien logo si existant
                $company = $this->findById($id);
                if ($company && $company['Logo']) {
                    \App\Helpers\FileHelper::deleteFile($company['Logo'], 'company_logos');
                }

                $updateData['Logo'] = $result['filename'];
            }
        }

        return $this->db->update('Company', $updateData, 'ID_Company = ?', [$id]);
    }

    public function delete($id) {
        // Récupérer d'abord les infos pour supprimer le logo
        $company = $this->findById($id);
        if ($company && $company['Logo']) {
            \App\Helpers\FileHelper::deleteFile($company['Logo'], 'company_logos');
        }

        return $this->db->delete('Company', 'ID_Company = ?', [$id]);
    }

    public function search($filters = []) {
        $conditions = [];
        $params = [];

        $sql = "
            SELECT c.*, s.Sector as sector_name,
            (SELECT COUNT(*) FROM Offers WHERE ID_Company = c.ID_Company) as offers_count,
            (SELECT AVG(rating) FROM company_evaluations WHERE company_id = c.ID_Company) as avg_rating
            FROM Company c
            LEFT JOIN Sector_Of_Activity s ON c.ID_Sector = s.ID_Sector
        ";

        // Recherche par nom, description ou adresse
        if (!empty($filters['keyword'])) {
            $conditions[] = "(c.Name LIKE ? OR c.Description LIKE ? OR c.Adresse LIKE ?)";
            $keyword = "%{$filters['keyword']}%";
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        // Filtrage par secteur d'activité
        if (!empty($filters['sector_id'])) {
            $conditions[] = "c.ID_Sector = ?";
            $params[] = $filters['sector_id'];
        }

        // Filtrage par taille d'entreprise
        if (!empty($filters['size'])) {
            $conditions[] = "c.Size = ?";
            $params[] = $filters['size'];
        }

        // Filtrage par nombre minimum d'offres
        if (!empty($filters['min_offers'])) {
            $conditions[] = "(SELECT COUNT(*) FROM Offers WHERE ID_Company = c.ID_Company) >= ?";
            $params[] = $filters['min_offers'];
        }

        // Filtrage par note minimum
        if (!empty($filters['min_rating'])) {
            $conditions[] = "(SELECT AVG(rating) FROM company_evaluations WHERE company_id = c.ID_Company) >= ?";
            $params[] = $filters['min_rating'];
        }

        // Application des conditions de recherche
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        // Tri des résultats
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'name':
                    $sql .= " ORDER BY c.Name";
                    break;
                case 'offers':
                    $sql .= " ORDER BY offers_count DESC";
                    break;
                case 'rating':
                    $sql .= " ORDER BY avg_rating DESC";
                    break;
                default:
                    $sql .= " ORDER BY c.Name";
            }
        } else {
            $sql .= " ORDER BY c.Name";
        }

        // Pagination
        if (isset($filters['limit']) && isset($filters['offset'])) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $filters['limit'];
            $params[] = $filters['offset'];
        }

        return $this->db->fetchAll($sql, $params);
    }

    public function findBySector($sectorId) {
        return $this->db->fetchAll("
            SELECT c.*, s.Sector as sector_name,
            (SELECT COUNT(*) FROM Offers WHERE ID_Company = c.ID_Company) as offers_count,
            (SELECT AVG(rating) FROM company_evaluations WHERE company_id = c.ID_Company) as avg_rating
            FROM Company c
            LEFT JOIN Sector_Of_Activity s ON c.ID_Sector = s.ID_Sector
            WHERE c.ID_Sector = ?
            ORDER BY c.Name
        ", [$sectorId]);
    }

    public function getOffers($companyId) {
        return $this->db->fetchAll("
            SELECT o.*, l.Study_level as study_level,
            (SELECT COUNT(*) FROM applications WHERE offer_id = o.ID_Offer) as applications_count
            FROM Offers o
            LEFT JOIN Level_Of_Study l ON o.ID_level = l.ID_level
            WHERE o.ID_Company = ?
            ORDER BY o.Date_of_publication DESC
        ", [$companyId]);
    }

    public function getTotalCompanies() {
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM Company");
        return $result ? $result['count'] : 0;
    }

    public function rate($companyId, $studentId, $rating, $comment = null) {
        // Valider la note (1-5)
        $rating = max(1, min(5, $rating));

        // Vérifier si l'étudiant a déjà évalué cette entreprise
        $existing = $this->db->fetch("
            SELECT * FROM company_evaluations 
            WHERE student_id = ? AND company_id = ?
        ", [$studentId, $companyId]);

        if ($existing) {
            // Mise à jour de l'évaluation existante
            return $this->db->update('company_evaluations', [
                'rating' => $rating,
                'comment' => $comment,
                'created_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$existing['id']]);
        }

        // Création d'une nouvelle évaluation
        return $this->db->insert('company_evaluations', [
            'student_id' => $studentId,
            'company_id' => $companyId,
            'rating' => $rating,
            'comment' => $comment,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getAverageRating($companyId) {
        $result = $this->db->fetch("
            SELECT AVG(rating) as avg_rating 
            FROM company_evaluations 
            WHERE company_id = ?
        ", [$companyId]);

        return $result ? round($result['avg_rating'], 1) : 0;
    }

    public function getEvaluations($companyId) {
        return $this->db->fetchAll("
            SELECT ce.*, a.Username as student_name
            FROM company_evaluations ce
            JOIN Account a ON ce.student_id = a.ID_account
            WHERE ce.company_id = ?
            ORDER BY ce.created_at DESC
        ", [$companyId]);
    }

    public function getSectors() {
        return $this->db->fetchAll("SELECT * FROM Sector_Of_Activity ORDER BY Sector");
    }

    public function getCompanyStatistics() {
        return [
            'total' => $this->db->fetch("SELECT COUNT(*) as count FROM Company")['count'],
            'with_offers' => $this->db->fetch("
                SELECT COUNT(DISTINCT ID_Company) as count 
                FROM Offers
            ")['count'],
            'top_rated' => $this->db->fetch("
                SELECT c.Name, AVG(ce.rating) as avg_rating
                FROM Company c
                JOIN company_evaluations ce ON c.ID_Company = ce.company_id
                GROUP BY c.ID_Company
                ORDER BY avg_rating DESC
                LIMIT 1
            ")
        ];
    }

    public function getCompaniesBySector() {
        return $this->db->fetchAll("
            SELECT s.Sector, COUNT(c.ID_Company) as count
            FROM Sector_Of_Activity s
            LEFT JOIN Company c ON s.ID_Sector = c.ID_Sector
            GROUP BY s.ID_Sector
            ORDER BY count DESC
        ");
    }

    public function getTopCompanies($limit = 5) {
        return $this->db->fetchAll("
            SELECT c.*, COUNT(o.ID_Offer) as offers_count
            FROM Company c
            JOIN Offers o ON c.ID_Company = o.ID_Company
            GROUP BY c.ID_Company
            ORDER BY offers_count DESC
            LIMIT ?
        ", [$limit]);
    }
}