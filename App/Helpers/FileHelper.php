<?php
namespace App\Helpers;

use App\Core\App;

class FileHelper {
    private static $allowedExtensions = [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'gif' => ['image/gif']
    ];

    private static $maxFileSize = 5242880; // 5 MB par défaut

    public static function uploadFile($file, $directory = '', $allowedTypes = null, $maxSize = null) {
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
        $maxSize = $maxSize ?: self::$maxFileSize;
        if ($fileSize > $maxSize) {
            return [
                'success' => false,
                'error' => 'Le fichier dépasse la taille maximale autorisée (' . self::formatFileSize($maxSize) . ').'
            ];
        }

        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Valider le type de fichier
        $allowedTypes = $allowedTypes ?: self::$allowedExtensions;

        if (!isset($allowedTypes[$fileExtension])) {
            return [
                'success' => false,
                'error' => 'Type de fichier non autorisé. Types acceptés: ' . implode(', ', array_keys($allowedTypes))
            ];
        }

        // Vérifier le type MIME réel du fichier
        $realMimeType = mime_content_type($fileTmpName);
        if (!in_array($realMimeType, $allowedTypes[$fileExtension])) {
            return [
                'success' => false,
                'error' => 'Le contenu du fichier ne correspond pas à son extension.'
            ];
        }

        // Vérifier et créer le répertoire de destination
        $uploadDir = App::$app->config->get('uploads.upload_path', __DIR__ . '/../../storage/uploads/');
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
        $newFileName = self::generateUniqueFileName($fileExtension);
        $targetFile = $targetDir . '/' . $newFileName;

        // Déplacer le fichier
        if (!move_uploaded_file($fileTmpName, $targetFile)) {
            return [
                'success' => false,
                'error' => 'Erreur lors du déplacement du fichier.'
            ];
        }

        // Journaliser l'upload
        App::$app->logger->logActivity("File uploaded: {$newFileName} (original: {$fileName})");

        return [
            'success' => true,
            'filename' => $newFileName,
            'original_name' => $fileName,
            'path' => $targetFile,
            'relative_path' => $directory . '/' . $newFileName,
            'size' => $fileSize,
            'type' => $fileType
        ];
    }

    public static function deleteFile($filename, $directory = '') {
        $uploadDir = App::$app->config->get('uploads.upload_path', __DIR__ . '/../../storage/uploads/');
        $filePath = $uploadDir . $directory . '/' . $filename;

        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                App::$app->logger->logActivity("File deleted: {$filename}");
                return true;
            }
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

    private static function generateUniqueFileName($extension) {
        return md5(uniqid() . time() . rand(1000, 9999)) . '.' . $extension;
    }

    private static function formatFileSize($size) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 2) . ' ' . $units[$i];
    }

    public static function getFileTypeIcon($filename) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'pdf':
                return '<i class="fas fa-file-pdf"></i>';
            case 'doc':
            case 'docx':
                return '<i class="fas fa-file-word"></i>';
            case 'xls':
            case 'xlsx':
                return '<i class="fas fa-file-excel"></i>';
            case 'ppt':
            case 'pptx':
                return '<i class="fas fa-file-powerpoint"></i>';
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
                return '<i class="fas fa-file-image"></i>';
            default:
                return '<i class="fas fa-file"></i>';
        }
    }

    public static function isFileExists($filename, $directory = '') {
        $uploadDir = App::$app->config->get('uploads.upload_path', __DIR__ . '/../../storage/uploads/');
        $filePath = $uploadDir . $directory . '/' . $filename;

        return file_exists($filePath);
    }

    public static function getFileUrl($filename, $directory = '') {
        $baseUrl = App::$app->config->get('app.url');
        $uploadsUrl = App::$app->config->get('uploads.url_path', '/uploads');

        return $baseUrl . $uploadsUrl . '/' . $directory . '/' . $filename;
    }
}