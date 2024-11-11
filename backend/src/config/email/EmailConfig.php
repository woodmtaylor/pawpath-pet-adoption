<?php
namespace PawPath\config\email;

class EmailConfig {
    public static function getConfig(): array {
        $env = $_ENV['APP_ENV'] ?? 'development';
        
        if ($env === 'development') {
            // Use Mailtrap in development
            return [
                'host' => 'sandbox.smtp.mailtrap.io',
                'port' => 2525,
                'username' => $_ENV['MAILTRAP_USERNAME'],
                'password' => $_ENV['MAILTRAP_PASSWORD'],
                'encryption' => 'tls',
                'from_address' => 'testing@pawpath.com',
                'from_name' => 'PawPath Testing'
            ];
        } else {
            // Use production settings (Gmail or other SMTP)
            return [
                'host' => $_ENV['MAIL_HOST'],
                'port' => $_ENV['MAIL_PORT'],
                'username' => $_ENV['MAIL_USERNAME'],
                'password' => $_ENV['MAIL_PASSWORD'],
                'encryption' => 'tls',
                'from_address' => $_ENV['MAIL_FROM_ADDRESS'],
                'from_name' => $_ENV['MAIL_FROM_NAME']
            ];
        }
    }
}
