<?php

namespace App\Core;

use PDO;


abstract class Model
{

    protected PDO $db;


    protected string $table = '';



    public function __construct()
    {

        $this->db =
            Database::getInstance()
            ->getConnection();

    }



    /**
     * Menjalankan query SELECT banyak data
     */
    protected function query(
        string $sql,
        array $params = []
    ): array {

        $statement =
            $this->db->prepare($sql);


        $statement->execute($params);


        return $statement->fetchAll(
            PDO::FETCH_ASSOC
        );

    }



    /**
     * Mengambil satu data
     */
    protected function first(
        string $sql,
        array $params = []
    ): ?array {

        $statement =
            $this->db->prepare($sql);


        $statement->execute($params);


        $result =
            $statement->fetch(
                PDO::FETCH_ASSOC
            );


        return $result ?: null;

    }



    /**
     * Menjalankan INSERT UPDATE DELETE
     */
    protected function execute(
        string $sql,
        array $params = []
    ): bool {

        $statement =
            $this->db->prepare($sql);


        return $statement->execute($params);

    }



    /**
     * Cari berdasarkan ID
     */
    protected function find(
        int $id
    ): ?array {


        $sql = "
            SELECT *
            FROM {$this->table}
            WHERE id = ?
        ";


        return $this->first(
            $sql,
            [$id]
        );

    }



    /**
     * ID terakhir setelah insert
     */
    protected function lastInsertId(): string
    {

        return $this->db->lastInsertId();

    }

}