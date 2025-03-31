<?php
namespace App\Helpers;

class FileHelper {
    private static $allowedExtensions = [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document']
    ];

    private static $maxFileSize = 5242880; // 5 MB

    public static function uploadFile($file, $directory = '') {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'error' => self::getUploadErrorMessage($file['error'])
            ];
        }

        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileType = $file['type'];

        // Valider la taille du fichier
        if ($fileSize > self::$maxFileSize) {
            return [
                'success' => false,
                'error' => 'Le fichier dépasse la taille maximale autorisée.'
            ];
        }

        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Valider le type de fichier
        if (!isset(self::$allowedExtensions[$fileExtension])) {
            return [
                'success' => false,
                'error' => 'Type de fichier non autorisé. Types acceptés: ' . implode(', ', array_keys(self::$allowedExtensions))
            ];
        }

        // Vérifier le type MIME réel du fichier
        $realMimeType = mime_content_type($fileTmpName);
        if (!in_array($realMimeType, self::$allowedExtensions[$fileExtension])) {
            return [
                'success' => false,
                'error' => 'Le contenu du fichier ne correspond pas à son extension.'
            ];
        }

        // Vérifier et créer le répertoire de destination
        $uploadDir = __DIR__ . '/../../storage/uploads/';
        $targetDir = $uploadDir . $directory;

        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                return [
                    'success' => false,
                    'error' => 'Erreur lors de la création du répertoire de destination.'
                ];
            }
        }

        // Générer un nom de fichier unique pour éviter les collisions
        $newFileName = md5(uniqid() . time() . rand(1000, 9999)) . '.' . $fileExtension;
        $targetFile = $targetDir . '/' . $newFileName;

        // Déplacer le fichier
        if (!move_uploaded_file($fileTmpName, $targetFile)) {
            return [
                'success' => false,
                'error' => 'Erreur lors du déplacement du fichier.'
            ];
        }

        return [
            'success' => true,
            'filename' => $newFileName,
            'original_name' => $fileName,
            'path' => $targetFile
        ];
    }

    public static function deleteFile($filename, $directory = '') {
        $uploadDir = __DIR__ . '/../../storage/uploads/';
        $filePath = $uploadDir . $directory . '/' . $filename;

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }

    private static function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'Le fichier dépasse la taille maximale autorisée par PHP.';
            case UPLOAD_ERR_FORM_SIZE:
                return 'Le fichier dépasse la taille maximale autorisée par le formulaire.';
            case UPLOAD_ERR_PARTIAL:
                return 'Le fichier n\'a été que partiellement téléchargé.';
            case UPLOAD_ERR_NO_FILE:
                return 'Aucun fichier n\'a été téléchargé.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Dossier temporaire manquant.';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Échec de l\'écriture du fichier sur le disque.';
            case UPLOAD_ERR_EXTENSION:
                return 'Une extension PHP a arrêté l\'upload du fichier.';
            default:
                return 'Erreur inconnue lors de l\'upload.';
        }
    }
}