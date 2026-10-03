<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;

    private PDO $connection;


    private function __construct()
    {
        try {

            $config = require BASE_PATH . '/app/Config/database.php';


            $dsn = sprintf(
                "%s:host=%s;port=%s;dbname=%s",
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['database']
            );


            $this->connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );


        } catch (PDOException $e) {

            throw new PDOException(
                "Database connection failed: " . $e->getMessage()
            );

        }
    }


    public static function getInstance(): Database
    {
        if (self::$instance === null) {

            self::$instance = new Database();

        }

        return self::$instance;
    }


    public function getConnection(): PDO
    {
        return $this->connection;
    }


    public function query(string $sql, array $params = []): \PDOStatement
    {
        $statement = $this->connection->prepare($sql);

        $statement->execute($params);

        return $statement;
    }


    public function prepare(string $sql): \PDOStatement
    {
        return $this->connection->prepare($sql);
    }


    private function __clone()
    {
    }


    public function __wakeup()
    {
        throw new \Exception(
            "Cannot unserialize Database singleton"
        );
    }
}