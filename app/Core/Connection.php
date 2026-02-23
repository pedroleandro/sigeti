<?php

namespace App\Core;

use PDO;

class Connection
{
    private static ?PDO $instance = null;

    private const OPTIONS = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false,
    ];

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {

            $host     = $_ENV['DB_HOST'];
            $port     = $_ENV['DB_PORT'];
            $database = $_ENV['DB_DATABASE'];
            $charset  = $_ENV['DB_CHARSET'];
            $username = $_ENV['DB_USERNAME'];
            $password = $_ENV['DB_PASSWORD'];

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $host,
                $port,
                $database,
                $charset
            );

            self::$instance = new PDO(
                $dsn,
                $username,
                $password,
                self::OPTIONS
            );
        }

        return self::$instance;
    }
}