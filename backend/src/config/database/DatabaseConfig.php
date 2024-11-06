<?php
// backend/src/config/database/DatabaseConfig.php

namespace PawPath\config\database;

use PDO;
use PDOException;

class DatabaseConfig {
    private static ?PDO $connection = null;
    
    public static function getConnection(): PDO {
        if (self::$connection === null) {
            try {
                // Get environment variables
                $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../');
                $dotenv->load();

                // Debug environment variables
                error_log("Environment variables:");
                error_log("DB_HOST: " . ($_ENV['DB_HOST'] ?? 'not set'));
                error_log("DB_PORT: " . ($_ENV['DB_PORT'] ?? 'not set'));
                error_log("DB_DATABASE: " . ($_ENV['DB_DATABASE'] ?? 'not set'));
                error_log("DB_USERNAME: " . ($_ENV['DB_USERNAME'] ?? 'not set'));
                error_log("DB_PASSWORD is set: " . (isset($_ENV['DB_PASSWORD']) ? 'Yes' : 'No'));
                
                // Use exact matches to your .env file
                $host = $_ENV['DB_HOST'] ?? 'localhost';
                $port = $_ENV['DB_PORT'] ?? '3306';
                $dbName = $_ENV['DB_DATABASE'] ?? 'pawpath';
                $username = $_ENV['DB_USERNAME'] ?? 'root';
                $password = $_ENV['DB_PASSWORD'] ?? '';
                
                $dsn = "mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4";
                
                error_log("Attempting to connect with DSN: $dsn");
                error_log("Username: $username");
                
                self::$connection = new PDO(
                    $dsn,
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
                
                error_log("Database connection successful");
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                throw new PDOException("Connection failed: " . $e->getMessage());
            }
        }
        
        return self::$connection;
    }
}
