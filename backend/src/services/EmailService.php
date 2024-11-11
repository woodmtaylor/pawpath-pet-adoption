<?php
namespace PawPath\services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use RuntimeException;

class EmailService {
    private ?PHPMailer $mailer = null;
    
    public function __construct() {
        // Defer mailer initialization until needed
    }
    
    private function initializeMailer(): void {
        if ($this->mailer !== null) {
            return;
        }

        try {
            $this->mailer = new PHPMailer(true);
            
            if (!empty($_ENV['MAIL_HOST'])) {
                $this->mailer->isSMTP();
                $this->mailer->Host = $_ENV['MAIL_HOST'];
                $this->mailer->SMTPAuth = true;
                $this->mailer->Username = $_ENV['MAIL_USERNAME'] ?? '';
                $this->mailer->Password = $_ENV['MAIL_PASSWORD'] ?? '';
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $this->mailer->Port = $_ENV['MAIL_PORT'] ?? 587;
            } else {
                $this->mailer->isMail();
            }
            
            $this->mailer->setFrom(
                $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@pawpath.com',
                $_ENV['MAIL_FROM_NAME'] ?? 'PawPath'
            );
            
            if ($_ENV['APP_ENV'] === 'development') {
                $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            }
        } catch (Exception $e) {
            error_log("Failed to initialize mailer: " . $e->getMessage());
            throw new RuntimeException("Email service configuration error");
        }
    }
    
    public function sendVerificationEmail(string $email, string $name, string $token): bool {
        try {
            $this->initializeMailer();
            
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $name);
            $this->mailer->isHTML(true);
            
            $verificationLink = $_ENV['APP_URL'] . "/verify-email?token=" . $token;
            
            $this->mailer->Subject = 'Verify your PawPath account';
            $this->mailer->Body = $this->getVerificationEmailTemplate($name, $verificationLink);
            $this->mailer->AltBody = strip_tags(str_replace('<br>', "\n", $this->mailer->Body));
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Failed to send verification email: " . $e->getMessage());
            return false;
        }
    }

    public function sendPasswordResetEmail(string $email, string $name, string $token): bool {
        try {
            $this->initializeMailer();
            
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $name);
            $this->mailer->isHTML(true);
            
            $resetLink = $_ENV['APP_URL'] . "/reset-password?token=" . $token;
            
            $this->mailer->Subject = 'Reset Your PawPath Password';
            $this->mailer->Body = $this->getPasswordResetEmailTemplate($name, $resetLink);
            $this->mailer->AltBody = strip_tags(str_replace('<br>', "\n", $this->mailer->Body));
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Failed to send password reset email: " . $e->getMessage());
            return false;
        }
    }

    public function sendWelcomeEmail(string $email, string $name): bool {
        try {
            $this->initializeMailer();
            
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $name);
            $this->mailer->isHTML(true);
            
            $this->mailer->Subject = 'Welcome to PawPath!';
            $this->mailer->Body = $this->getWelcomeEmailTemplate($name);
            $this->mailer->AltBody = strip_tags(str_replace('<br>', "\n", $this->mailer->Body));
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Failed to send welcome email: " . $e->getMessage());
            return false;
        }
    }
    
    private function getVerificationEmailTemplate(string $name, string $link): string {
        return "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2>Welcome to PawPath!</h2>
                <p>Hi {$name},</p>
                <p>Thanks for signing up. Please verify your email address to complete your registration.</p>
                <p style='margin: 25px 0;'>
                    <a href='{$link}' 
                       style='background-color: #4F46E5; color: white; padding: 12px 24px; 
                              text-decoration: none; border-radius: 4px;'>
                        Verify Email Address
                    </a>
                </p>
                <p>If you did not create an account, no further action is required.</p>
                <p>Best regards,<br>The PawPath Team</p>
            </div>
        ";
    }
    
    private function getPasswordResetEmailTemplate(string $name, string $link): string {
        return "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2>Reset Your Password</h2>
                <p>Hi {$name},</p>
                <p>You recently requested to reset your password. Click the button below to proceed:</p>
                <p style='margin: 25px 0;'>
                    <a href='{$link}' 
                       style='background-color: #4F46E5; color: white; padding: 12px 24px; 
                              text-decoration: none; border-radius: 4px;'>
                        Reset Password
                    </a>
                </p>
                <p>If you did not request a password reset, please ignore this email.</p>
                <p>Best regards,<br>The PawPath Team</p>
            </div>
        ";
    }
    
    private function getWelcomeEmailTemplate(string $name): string {
        return "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2>Welcome to PawPath!</h2>
                <p>Hi {$name},</p>
                <p>We're excited to have you join our community of pet lovers!</p>
                <p>With PawPath, you can:</p>
                <ul>
                    <li>Find your perfect pet companion</li>
                    <li>Connect with local shelters</li>
                    <li>Access resources about pet care</li>
                    <li>Join our community of pet lovers</li>
                </ul>
                <p>Ready to get started?</p>
                <p style='margin: 25px 0;'>
                    <a href='{$_ENV['APP_URL']}/quiz' 
                       style='background-color: #4F46E5; color: white; padding: 12px 24px; 
                              text-decoration: none; border-radius: 4px;'>
                        Take Our Pet Match Quiz
                    </a>
                </p>
                <p>Best regards,<br>The PawPath Team</p>
            </div>
        ";
    }
}
