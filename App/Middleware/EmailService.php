<?php
namespace App\Services;

use App\Core\App;

class EmailService {
    private $config;
    private $logger;

    public function __construct() {
        $this->config = App::$app->config;
        $this->logger = App::$app->logger;
    }

    /**
     * Envoie un email
     *
     * @param string $to Adresse email du destinataire
     * @param string $subject Sujet de l'email
     * @param string $body Corps de l'email (HTML)
     * @param array $options Options supplémentaires
     * @return bool Succès ou échec
     */
    public function send($to, $subject, $body, $options = []) {
        // Configuration de base
        $from = $options['from'] ?? $this->config->get('mail.from_address');
        $fromName = $options['from_name'] ?? $this->config->get('mail.from_name');
        $replyTo = $options['reply_to'] ?? $from;
        $cc = $options['cc'] ?? null;
        $bcc = $options['bcc'] ?? null;
        $attachments = $options['attachments'] ?? [];

        // En mode développement, on simule l'envoi
        if ($this->config->get('app.env') === 'development' || $this->config->get('mail.simulate', true)) {
            $this->logger->logActivity("EMAIL SIMULATED TO: {$to}, SUBJECT: {$subject}");
            $this->logger->logActivity("EMAIL CONTENT: " . substr(strip_tags($body), 0, 200) . "...");
            return true;
        }

        // En production, on utiliserait une véritable bibliothèque d'envoi d'email
        // comme PHPMailer, SwiftMailer, etc.
        // Voici un exemple avec la fonction mail() de PHP (non recommandé en production)

        // Headers
        $headers = "From: {$fromName} <{$from}>\r\n";
        $headers .= "Reply-To: {$replyTo}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        if ($cc) {
            $headers .= "Cc: {$cc}\r\n";
        }

        if ($bcc) {
            $headers .= "Bcc: {$bcc}\r\n";
        }

        // Envoi de l'email
        $success = mail($to, $subject, $body, $headers);

        if ($success) {
            $this->logger->logActivity("Email sent to {$to}, subject: {$subject}");
        } else {
            $this->logger->logActivity("Failed to send email to {$to}, subject: {$subject}");
        }

        return $success;
    }

    /**
     * Envoie un email de bienvenue à un nouvel utilisateur
     */
    public function sendWelcomeEmail($user) {
        $subject = "Bienvenue sur " . $this->config->get('app.name');

        // Corps de l'email
        $body = "
            <html>
            <body>
                <h1>Bienvenue sur " . $this->config->get('app.name') . " !</h1>
                <p>Bonjour {$user['Username']},</p>
                <p>Nous sommes ravis de vous accueillir sur notre plateforme de stages.</p>
                <p>Vous pouvez dès maintenant rechercher des offres de stage et constituer votre profil pour attirer l'attention des recruteurs.</p>
                <p>N'hésitez pas à nous contacter si vous avez la moindre question.</p>
                <p>Cordialement,<br>L'équipe " . $this->config->get('app.name') . "</p>
            </body>
            </html>
        ";

        return $this->send($user['Email'], $subject, $body);
    }

    /**
     * Envoie un email de réinitialisation de mot de passe
     */
    public function sendPasswordResetEmail($email, $token) {
        $resetUrl = $this->config->get('app.url') . '/reset-password?token=' . $token;
        $subject = "Réinitialisation de votre mot de passe";

        $body = "
            <html>
            <body>
                <h1>Réinitialisation de votre mot de passe</h1>
                <p>Vous avez demandé à réinitialiser votre mot de passe sur " . $this->config->get('app.name') . ".</p>
                <p>Cliquez sur le lien ci-dessous pour définir un nouveau mot de passe :</p>
                <p><a href='{$resetUrl}'>{$resetUrl}</a></p>
                <p>Ce lien est valable pendant 1 heure.</p>
                <p>Si vous n'avez pas demandé à réinitialiser votre mot de passe, vous pouvez ignorer cet email.</p>
                <p>Cordialement,<br>L'équipe " . $this->config->get('app.name') . "</p>
            </body>
            </html>
        ";

        return $this->send($email, $subject, $body);
    }

    /**
     * Envoie une notification pour une nouvelle candidature
     */
    public function sendApplicationNotification($application) {
        // Pour les étudiants
        $studentSubject = "Confirmation de votre candidature";
        $studentBody = "
            <html>
            <body>
                <h1>Votre candidature a été envoyée</h1>
                <p>Bonjour {$application['student_name']},</p>
                <p>Nous confirmons la réception de votre candidature pour le stage <strong>{$application['offer_title']}</strong> chez <strong>{$application['company_name']}</strong>.</p>
                <p>Vous pouvez suivre l'état de votre candidature depuis votre tableau de bord.</p>
                <p>Cordialement,<br>L'équipe " . $this->config->get('app.name') . "</p>
            </body>
            </html>
        ";

        $this->send($application['student_email'], $studentSubject, $studentBody);

        // Pour l'entreprise (si implémenté)
        // $this->send($application['company_email'], ...);

        return true;
    }
}