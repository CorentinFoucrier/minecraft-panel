<?php

namespace Core\Model;

use Core\Controller\Database\DatabaseController;
use Core\Controller\URLController;

abstract class Table
{

    protected DatabaseController $db;

    protected string $table;

    /**
     * This constructor is called by App\App\getTable() method
     *
     * @param DatabaseController $db
     */
    public function __construct(DatabaseController $db, string $tableName)
    {
        $this->db = $db;
        if (!isset($this->table)) {
            // Build a string with user defined prefix eg. mcp_server
            $this->table = PREFIX . strtolower($tableName);
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
     * @return void
     */
    public function count(bool $fetchAll = false)
    {
        return $this->query("SELECT COUNT(id) as nbrow FROM {$this->table}", null, $fetchAll);
    }

    /**
     * @see Core\Controller\PaginatedQueryController::getNbPages
     * @param integer $id
     * @return void
     */
    public function countById(int $id)
    {
        return $this->query("SELECT COUNT(id) as nbrow FROM {$this->table} WHERE id = ?", [$id], true, null);
    }

    /**
     * Return the last inserted id in a table
     *
     * @return void
     */
    public function lastId(bool $fetchAll = false)
    {
        return $this->query("SELECT MAX(id) AS id FROM {$this->table}", null, $fetchAll);
    }

    /**
     * Find by id
     *
     * @param int $id
     * @return void
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
     * @return void
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
     * @return void
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
     * @return void
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
     * @return bool
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
     * @return bool
     */
    public function updateBy(array $where, array $fields)
    {
        $sql_parts = [];
        $attributes = [];
        foreach ($fields as $k => $v) {
            $sql_parts[] = "$k = ?";
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
     * @return bool
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
     * @param array $fields
     * @param boolean $entityClass
     * @return boolean
     */
    public function create(array $fields, $entityClass = false)
    {
        $sql_parts = [];
        $attributes = [];
        if ($entityClass) {
            $methods = get_class_methods($fields);
            $array = [];
            foreach ($methods as $value) {
                if (strrpos($value, 'get') === 0) {
                    $column = strtolower(explode('get', $value)[1]);
                    $array[$column] = $fields->$value();
                }
            }
            $fields = $array;
        }
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
     * @return void
     */
    public function deleteById(int $id)
    {
        return $this->query("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }

    /**
     * SELECT * FROM Table
     *
     * @param boolean $fetchAll Set to true if you need fetchAll
     * @return void
     */
    public function selectEverything(bool $fetchAll = false)
    {
        return $this->query("SELECT * FROM {$this->table}", null, $fetchAll);
    }

    /**
     * Determines if it's a "prepare" query or not
     *
     * @param string $statement eg. "SELECT bar FROM foo".
     * @param array|null $attributes
     * @param boolean $fetchAll Determines the result that you want fetch(false) or fetchAll(true) default: fetch.
     * @param string|null $class_name If you want to use a different class name of the class where you are.
     * @return void
     */
    public function query(string $statement, ?array $attributes = null, bool $fetchAll = false, ?string $class_name = null)
    {
        if (is_null($class_name)) {
            // Transform the actual table class to an Entity eg. ServerTable to ServerEntity
            $class_name = str_replace('Table', 'Entity', get_class($this));
        }

        // If attributes are set it's a prepare query
        if ($attributes) {
            return $this->db->prepare(
                $statement,
                $attributes,
                $class_name,
                $fetchAll
            );
        } else {
            return $this->db->query(
                $statement,
                $class_name,
                $fetchAll
            );
        }
    }
}
