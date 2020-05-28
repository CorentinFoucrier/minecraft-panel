<?php

namespace Core\Model;

use Core\Controller\Database\DatabaseController;
use Core\Controller\URLController;

abstract class Table
{

    public DatabaseController $db;

    public string $table;

    /**
     * This constructor is called by App\App\getTable() method
     *
     * @param DatabaseController $db
     */
    public function __construct(DatabaseController $db, string $tableName)
    {
        $this->db = $db;
        if (!isset($this->table)) {
            // Add underscores before capital letters
            $table_name = preg_replace('/\B([A-Z])/', '_$1', $tableName);
            // add user prefix + lowercase
            $this->table = PREFIX . strtolower($table_name);
        } else if (getenv("ENV_DEV") === "true") {
            throw new \Exception("\$this->table must not be initialized '" . $this->table . "' given.");
        } else {
            header("Location: " . URLController::getUri('error', ["code" => 500]));
            exit();
        }
    }

    /**
     * @see Core\Controller\PaginatedQueryController::getNbPages
     * @param boolean $fetchAll
     */
    public function count(bool $fetchAll = false)
    {
        return $this->query("SELECT COUNT(id) as nbrow FROM {$this->table}", null, $fetchAll);
    }

    /**
     * @see Core\Controller\PaginatedQueryController::getNbPages
     * @param integer $id
     */
    public function countById(int $id)
    {
        return $this->query("SELECT COUNT(id) as nbrow FROM {$this->table} WHERE id = ?", [$id], true, null);
    }

    /**
     * Return the last inserted id in a table
     *
     * @return int
     */
    public function lastId(): int
    {
        return $this->query("SELECT MAX(id) AS id FROM {$this->table}")->getId();
    }

    /**
     * Return the last inserted $column in a table
     *
     */
    public function last(string $column)
    {
        $getColumn = 'get' . ucfirst($column);
        return $this->query("SELECT MAX($column) AS $column FROM {$this->table}")->$getColumn();
    }

    /**
     * Find by id
     *
     * @param int $id
     */
    public function findById(int $id, bool $fetchAll = false)
    {
        return $this->query("SELECT * FROM {$this->table} WHERE id = ?", [$id], $fetchAll);
    }

    /**
     * SELECT * FROM Table WHERE $column
     *
     * @param array $column SQL column name
     * @param boolean $fetchAll Set to true if you need fetchAll
     */
    public function findBy(array $column, bool $fetchAll = false)
    {
        return $this->query("SELECT * FROM {$this->table} WHERE $column", null, $fetchAll);
    }

    /**
     * Find lines in a table with a multiple WHERE condition
     *
     * @param array $fields
     * @param boolean $fetchAll Set to true if you need fetchAll
     */
    public function select(array $fields, bool $fetchAll = false)
    {
        $sql_parts = [];
        $attributes = [];
        foreach ($fields as $k => $v) {
            $sql_parts[] = "$k = ?";
            $attributes[] = $v;
        }
        $sql_part = join(' AND ', $sql_parts);
        return $this->query("SELECT * FROM {$this->table} WHERE {$sql_part}", $attributes, $fetchAll);
    }

    /**
     * Select specific columns from table with multiple where conditions
     * SELECT $columns FROM table WHERE $where
     *
     * @param string $columns
     * @param array $where
     * @param boolean $fetchAll Set to true if you need fetchAll
     */
    public function selectBy(array $columns, array $where, bool $fetchAll = false)
    {
        $where_parts = [];
        foreach ($where as $k => $v) {
            $where_parts[] = "$k = '$v'";
        }
        $columns = join(', ', $columns);
        $where_parts = join(' AND ', $where_parts);
        // Possible statement: "SELECT username, email FROM users WHERE country = France AND city = Paris"
        return $this->query("SELECT {$columns} FROM {$this->table} WHERE {$where_parts}", null, $fetchAll);
    }

    /**
     * Update SQL query
     * UPDATE Table SET $key = ? WHERE id = ? ----> [2, $id]
     *
     * @param integer $id
     * @param array $fields
     */
    public function update(int $id, array $fields)
    {
        $sql_parts = [];
        $attributes = [];
        foreach ($fields as $k => $v) {
            $sql_parts[] = "$k = ?";
            $attributes[] = $v;
        }
        $attributes[] = $id;
        $sql_part = join(', ', $sql_parts);
        return $this->query("UPDATE {$this->table} SET $sql_part WHERE id = ?", $attributes);
    }

    /**
     * Update SQL query
     * UPDATE Table SET $key = ? WHERE id = ? ----> [2, $id]
     *
     * @param integer $id
     * @param array $fields
     */
    public function updateBy(array $where, array $fields)
    {
        $sql_parts = [];
        $attributes = [];
        foreach ($fields as $k => $v) {
            $sql_parts[] = "`$k` = ?";
            $attributes[] = $v;
        }
        foreach ($where as $k => $v) {
            $where_parts[] = "$k = ?";
            $attributes[] = $v;
        }
        $where_part = join(', ', $where_parts);
        $sql_part = join(', ', $sql_parts);
        return $this->query("UPDATE {$this->table} SET $sql_part WHERE $where_part", $attributes);
    }

    /**
     * Query :
     * "UPDATE {$this->table} SET $sql_part WHERE $column_part"
     *
     * @param array $fields The SET field
     * @param array $columns The WHERE field
     */
    public function updateAnd(array $fields, array $columns)
    {
        $sql_parts = [];
        $attributes = [];
        $column_parts = [];
        foreach ($fields as $k => $v) {
            $sql_parts[] = "$k = :$k";
            $attributes[":$k"] = "$v";
        }
        foreach ($columns as $k => $v) {
            $column_parts[] = "$k = :$k";
            $attributes[":$k"] = "$v";
        }
        $sql_part = join(', ', $sql_parts);
        $column_part = join(' AND ', $column_parts);
        return $this->query("UPDATE {$this->table} SET $sql_part WHERE $column_part", $attributes);
    }

    /**
     * Query :
     * "INSERT INTO {$this->table} SET $sql_part"
     *
     */
    public function create(array $fields)
    {
        $sql_parts = [];
        $attributes = [];

        foreach ($fields as $k => $v) {
            $sql_parts[] = "$k = ?";
            $attributes[] = $v;
        }
        $sql_part = join(', ', $sql_parts);
        return $this->query("INSERT INTO {$this->table} SET $sql_part", $attributes);
    }

    /**
     * DELETE FROM Table WHERE $id
     *
     * @param integer $id SQL id
     */
    public function deleteById(int $id)
    {
        return $this->query("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }

    /**
     * DELETE FROM Table WHERE $id
     *
     * @param integer $id SQL id
     */
    public function deleteAnd(array $fields)
    {
        $where_parts = [];
        $attributes = [];
        foreach ($fields as $k => $v) {
            $where_parts[] = "$k = ?";
            $attributes[] = $v;
        }
        $where_part = join(" AND ", $where_parts);
        return $this->query("DELETE FROM {$this->table} WHERE $where_part", $attributes);
    }

    /**
     * SELECT * FROM Table
     *
     * @param boolean $fetchAll Set to true if you need fetchAll
     */
    public function selectEverything(bool $fetchAll = false)
    {
        return $this->query("SELECT * FROM {$this->table}", null, $fetchAll);
    }

    /**
     * SQL INNER JOIN
     *
     * @param array $tables
     * @param array $on
     * @param array|null $where
     * @param boolean $fetchAll
     */
    public function join(array $tables, array $on, ?array $where = null, bool $fetchAll = false)
    {
        $prefix = PREFIX;
        if (count($tables) === count($on)) {
            $on_keys = array_keys($on);
            $on_values = array_values($on);
            for ($i = 0; $i < count($tables); $i++) {
                $currentTable = $prefix . $tables[$i];
                $previousTable = $prefix . $tables[$i - 1];
                if ($i === 0) {
                    $sql_parts[] = "$currentTable ON {$this->table}.$on_keys[$i] = $currentTable.$on_values[$i]";
                } else {
                    $sql_parts[] = "$currentTable ON $previousTable.$on_keys[$i] = $currentTable.$on_values[$i]";
                }
            }
            $sql_part = join(" JOIN ", $sql_parts);
            if (!empty($where)) {
                foreach ($where as $k => $v) {
                    $where_parts[] = "$k = ?";
                    $attributes[] = $v;
                }
                $where_part = "WHERE " . join(" AND ", $where_parts);
            }
            return $this->query("SELECT * FROM {$this->table} JOIN $sql_part $where_part", $attributes, $fetchAll);
        } else {
            throw new \Exception(
                "Number of table(s) given doesn't match with the number of 'on' condition(s)." .
                    "You have " . count($tables) . " table(s) and " . count($on) . " 'on' condition(s)"
            );
        }
    }

    /**
     * Determines if it's a "prepare" query or not
     *
     * @param string $statement eg. "SELECT bar FROM foo".
     * @param array|null $attributes
     * @param boolean $fetchAll Determines the result that you want fetch(false) or fetchAll(true) default: fetch.
     * @param string|null $class_name If you want to use a different class name of the class where you are.
     * @return \PDOStatement|Core\Model\Entity|bool|array
     */
    public function query(string $statement, ?array $attributes = null, bool $fetchAll = false, ?string $class_name = null)
    {
        if (is_null($class_name)) {
            // Transform the actual table class to an Entity eg. ServerTable to ServerEntity
            $class_name = str_replace('Table', 'Entity', get_class($this));
        }

        // If attributes are set it's a prepare query
        if ($attributes) {
            return $this->db->prepare($statement, $attributes, $class_name, $fetchAll);
        } else {
            return $this->db->query($statement, $class_name, $fetchAll);
        }
    }
}
