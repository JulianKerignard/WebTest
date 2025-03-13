<?php
namespace App\Helpers;

class FileHelper {
    private static $allowedExtensions = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    private static $uploadDir = __DIR__ . '/../../storage/uploads/';

    public static function uploadFile($file, $directory = '', $allowedTypes = null) {
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

        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validate file type if specified
        if ($allowedTypes !== null) {
            if (!in_array($fileExtension, array_keys($allowedTypes))) {
                return [
                    'success' => false,
                    'error' => 'Type de fichier non autorisé.'
                ];
            }

            if ($allowedTypes[$fileExtension] !== $fileType) {
                return [
                    'success' => false,
                    'error' => 'Type MIME de fichier non autorisé.'
                ];
            }
        } else {
            // Use default allowed types
            if (!isset(self::$allowedExtensions[$fileExtension])) {
                return [
                    'success' => false,
                    'error' => 'Type de fichier non autorisé.'
                ];
            }
        }

        // Generate unique filename
        $newFileName = md5(uniqid() . $fileName) . '.' . $fileExtension;

        // Create target directory if it doesn't exist
        $targetDir = self::$uploadDir . $directory;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $targetFile = $targetDir . '/' . $newFileName;

        // Move the file
        if (move_uploaded_file($fileTmpName, $targetFile)) {
            return [
                'success' => true,
                'filename' => $newFileName,
                'path' => $targetFile
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Erreur lors de l\'upload du fichier.'
            ];
        }
    }

    public static function deleteFile($filename, $directory = '') {
        $filePath = self::$uploadDir . $directory . '/' . $filename;

        if (file_exists($filePath)) {
            unlink($filePath);
            return true;
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