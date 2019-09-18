<?php
namespace Core\Model;

use \Core\Controller\Database\DatabaseController;

class Table
{
    protected $db;

    protected $table;

    public function __construct(DatabaseController $db)
    {
        $this->db = $db;

        if (is_null($this->table)) {
            //App\Model\Table\ClassTable
            $parts = explode('\\', get_class($this));
            $class_name = end($parts);
            // $class_name = str_replace('Table', '', $class_name);
            // $class_name = Text::splitAtUpperCase($class_name);
            $this->table = strtolower(str_replace('Table', '', $class_name));
        }
    }

    public function count()
    {
        return $this->query("SELECT COUNT(id) as nbrow FROM {$this->table}", null, true);
    }

    /**
     * Return the last inserted id in a table
     *
     * @return void
     */
    public function lastId()
    {
        return $this->query("SELECT MAX(id) AS id FROM {$this->table}", null, true);
    }

    /**
     * Find lines in table
     *
     * @param string $column
     * @param mixed $attribute
     * @return void
     */
    public function find($attribute, $column = 'id')
    {
        return $this->query("SELECT * FROM {$this->table} WHERE {$column} = ?", [$attribute], true);
    }
    /**
     * Find lines in a table with a multiple WHERE condition
     *
     * @param array $fields
     * @param boolean $one
     * @return void
     */
    public function select(array $fields, bool $one = true)
    {
        $sql_parts = [];
        $attributes = [];
        foreach($fields as $k => $v){
            $sql_parts[] = "$k = ?";
            $attributes[] = $v;
        }
        $sql_part = implode(' AND ', $sql_parts);
        return $this->query("SELECT * FROM {$this->table} WHERE {$sql_part}", $attributes, $one);
    }

    /**
     * Update SQL query
     * UPDATE {$this->table} SET $sql_part WHERE $column = ?
     *
     * @param integer $id
     * @param string $column
     * @param array $fields
     * @return bool
     */
    public function update(int $id, string $column, array $fields)
    {
        $sql_parts = [];
        $attributes = [];
        foreach($fields as $k => $v){
            $sql_parts[] = "$k = ?";
            $attributes[] = $v;
        }
        $attributes[] = $id;
        $sql_part = implode(', ', $sql_parts);
        return $this->query("UPDATE {$this->table} SET $sql_part WHERE $column = ?", $attributes, true);
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
        foreach($fields as $k => $v){
            $sql_parts[] = "$k = :$k";
            $attributes[":".$k] = "$v";
        }
        foreach($columns as $k => $v){
            $column_parts[] = "$k = :$k";
            $attributes[":".$k] = "$v";
        }
        $sql_part = implode(', ', $sql_parts);
        $column_part = implode(' AND ', $column_parts);
        return $this->query("UPDATE {$this->table} SET $sql_part WHERE $column_part", $attributes, true);
    }

    /**
     * Query :
     * "INSERT INTO {$this->table} SET $sql_part"
     *
     * @param array $fields
     * @param boolean $class
     * @return boolean
     */
    public function create(array $fields, $class=false)
    {
        $sql_parts = [];
        $attributes = [];
        if($class) {
            $methods = get_class_methods($fields);
            $array = [];
            foreach($methods as $value) {
                if(strrpos($value, 'get') === 0) {
                    $column = strtolower(explode('get', $value)[1]);
                    $array[$column] = $fields->$value();
                }
            }
            $fields = $array;
        }
        foreach($fields as $k => $v){
            $sql_parts[] = "$k = ?";
            $attributes[] = $v;
        }
        $sql_part = implode(', ', $sql_parts);
        return $this->query("INSERT INTO {$this->table} SET $sql_part", $attributes, true);
    }

    public function delete($id)
    {
        return $this->query("DELETE FROM {$this->table} WHERE id = ?", [$id], true);
    }

    public function selectEverything(bool $fetch = false )
    {
        return $this->query("SELECT * FROM {$this->table}", null ,$fetch);
    }

    /**
     * Determines if it's a "prepare" query or not
     *
     * @param string $statement eg. "SELECT bar FROM foo".
     * @param array|null $attributes
     * @param boolean $one Determines the result that you want fetch(true) or fetchAll(false) default: fetchAll.
     * @param string|null $class_name If you want to use a different class name of the class where you are.
     * @return void
     */
    public function query(string $statement, ?array $attributes = null, bool $one = false, ?string $class_name = null)
    {
        if (is_null($class_name)) {
            $class_name = str_replace('Table', 'Entity', get_class($this));
        }

        if ($attributes) {
            return $this->db->prepare(
                $statement,
                $attributes,
                $class_name,
                $one
            );
        } else {
            return $this->db->query(
                $statement,
                $class_name,
                $one
            );
        }
    }
}