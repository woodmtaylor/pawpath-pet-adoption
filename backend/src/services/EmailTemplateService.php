<?php
namespace PawPath\services;

class EmailTemplateService {
    public static function getVerificationEmail(string $name, string $link): string {
        return self::getTemplate('verification', [
            'name' => $name,
            'link' => $link
        ]);
    }
    
    public static function getPasswordResetEmail(string $name, string $link): string {
        return self::getTemplate('password-reset', [
            'name' => $name,
            'link' => $link
        ]);
    }
    
    public static function getWelcomeEmail(string $name): string {
        return self::getTemplate('welcome', [
            'name' => $name
        ]);
    }
    
    public static function getAdoptionApplicationEmail(array $data): string {
        return self::getTemplate('adoption-application', $data);
    }
    
    private static function getTemplate(string $name, array $data): string {
        $template = file_get_contents(__DIR__ . "/../templates/emails/{$name}.html");
        
        foreach ($data as $key => $value) {
            $template = str_replace("{{" . $key . "}}", $value, $template);
        }
        
        return $template;
    }
}
