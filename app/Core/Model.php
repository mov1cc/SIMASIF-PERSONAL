<?php

namespace App\Core;

use PDO;

class Model
{

    protected Database $database;

    protected string $table = '';


    public function __construct()
    {
        $this->database = Database::getInstance();
    }


    /**
     * Menjalankan query manual
     */
    protected function query(
        string $sql,
        array $params = []
    ): \PDOStatement {

        $statement = $this->database->prepare($sql);

        $statement->execute($params);

        return $statement;

    }


    /**
     * Ambil data berdasarkan primary key id
     */
    public function find(
        int $id
    ): ?array {

        $sql = "
            SELECT *
            FROM {$this->table}
            WHERE id = :id
            LIMIT 1
        ";


        $result = $this->query(
            $sql,
            [
                'id' => $id
            ]
        )->fetch(PDO::FETCH_ASSOC);


        return $result ?: null;

    }


    /**
     * Ambil semua data
     */
    public function all(): array
    {

        $sql = "
            SELECT *
            FROM {$this->table}
        ";


        return $this->query($sql)
            ->fetchAll(PDO::FETCH_ASSOC);

    }


    /**
     * Insert data
     */
    public function insert(
        array $data
    ): int {


        $columns = implode(
            ', ',
            array_keys($data)
        );


        $parameters = implode(
            ', ',
            array_map(
                fn($key) => ':' . $key,
                array_keys($data)
            )
        );


        $sql = "
            INSERT INTO {$this->table}
            ($columns)
            VALUES
            ($parameters)
            RETURNING id
        ";


        return (int) $this->query(
            $sql,
            $data
        )->fetchColumn();

    }


    /**
     * Update data
     */
    public function update(
        int $id,
        array $data
    ): bool {


        $fields = implode(
            ', ',
            array_map(
                fn($key) => "{$key} = :{$key}",
                array_keys($data)
            )
        );


        $sql = "
            UPDATE {$this->table}
            SET {$fields}
            WHERE id = :id
        ";


        $data['id'] = $id;


        return $this->query(
            $sql,
            $data
        )->rowCount() > 0;

    }


    /**
     * Delete data
     */
    public function delete(
        int $id
    ): bool {


        $sql = "
            DELETE FROM {$this->table}
            WHERE id = :id
        ";


        return $this->query(
            $sql,
            [
                'id'=>$id
            ]
        )->rowCount() > 0;

    }

}