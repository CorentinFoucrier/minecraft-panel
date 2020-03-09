<?php

namespace Core\Controller\Database;

use \PDO;

class DatabaseMysqlController extends DatabaseController
{

    private PDO $pdo;

    public function __construct(
        string $db_name,
        string $db_user = 'root',
        string $db_pass = 'root',
        string $db_host = 'localhost',
        string $db_char = 'UTF8'
    ) {
        $this->db_name = $db_name;
        $this->db_user = $db_user;
        $this->db_pass = $db_pass;
        $this->db_host = $db_host;
        $this->db_char = $db_char;
    }

    public function getPDO(): \PDO
    {
        if (!isset($this->pdo)) {
            $pdo = new PDO(
                "mysql:host={$this->db_host};dbname={$this->db_name}",
                $this->db_user,
                $this->db_pass
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->pdo = $pdo;
        }

        return $this->pdo;
    }

    /**
     * Make PDO query request
     *
     * @param string $statement eg. "SELECT * FROM {table}"
     * @param string|null $class_name
     * @param boolean $fetchAll false = Single fetch by default
     */
    public function query(string $statement, ?string $class_name = null, bool $fetchAll = false)
    {
        $req = $this->getPDO()->query($statement);

        // If the statement is UPDATE, INSERT or DELETE return the result (bool)
        if (
            strpos($statement, 'UPDATE') === 0 ||
            strpos($statement, 'INSERT') === 0 ||
            strpos($statement, 'DELETE') === 0
        ) {
            return $req;
        }
        // If the statement is SELECT continue

        // If there is a class set fetch mode to PDO::FETCH_CLASS else set to OBJ
        if (is_null($class_name)) {
            $req->setFetchMode(PDO::FETCH_OBJ);
        } else {
            $req->setFetchMode(PDO::FETCH_CLASS, $class_name);
        }

        // If fetchAll is set to true do a fetchAll on request
        if ($fetchAll) {
            $datas = $req->fetchAll();
        } else {
            $datas = $req->fetch();
        }

        return $datas;
    }

    /**
     * Make PDO prepare request
     *
     * @param string $statement eg. "SELECT * FROM {table}"
     * @param array $attributes An array of attribures
     * @param string|null $class_name
     * @param boolean $fetchAll false = Single fetch by default
     */
    public function prepare(string $statement, array $attributes, ?string $class_name = null, bool $fetchAll = false)
    {
        $req = $this->getPDO()->prepare($statement);
        $res = $req->execute($attributes);

        // If the statement is UPDATE, INSERT or DELETE return the result (bool)
        if (
            strpos($statement, 'UPDATE') === 0 ||
            strpos($statement, 'INSERT') === 0 ||
            strpos($statement, 'DELETE') === 0
        ) {
            return $res;
        }
        // If the statement is SELECT continue

        // If there is a class set fetch mode to PDO::FETCH_CLASS else set to OBJ
        if (is_null($class_name)) {
            $req->setFetchMode(PDO::FETCH_OBJ);
        } else {
            $req->setFetchMode(PDO::FETCH_CLASS, $class_name);
        }

        // If fetchAll is set to true do a fetchAll on request
        if ($fetchAll) {
            $datas = $req->fetchAll();
        } else {
            $datas = $req->fetch();
        }

        return $datas;
    }
}
