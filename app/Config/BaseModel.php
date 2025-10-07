<?php

namespace Config;

use PDO;
use PDOException;
use PDOStatement;

/**
 * BaseModel
 *
 * All models should extend this class.
 *
 * -------------------- MODELS CONVENTIONS --------------------
 * 1. Namespace and Base Class:
 *    - Models extend BaseModel and set $table & $primaryKey in __construct.
 *
 * 2. Naming:
 *    - Class: PascalCase (e.g., UserModel)
 *    - Method: camelCase (e.g., getUserByEmail)
 *    - Table aliases: snake_case
 *    - Variables: descriptive, snake_case
 *
 * 3. Methods:
 *    - getXXX(): returns array of fetched results
 *    - insertXXX(): returns inserted ID or false
 *    - updateXXX(): returns true or false (true if any row was affected)
 *    - deleteXXX(): returns true or false (true if any row was affected)
 *
 * 4. SQL and Security:
 *    - Always use named placeholders (:placeholder) or positional placeholders (?)
 *    - Never concatenate user input
 *    - Passwords must be hashed
 *    - queries always prepared with methods inherited from this class
 *
 * 5. Comments:
 *    - Public methods: Docblock + parameters + return type
 *    - Inline comments: optional clauses, logic explanations
 *
 * -------------------------------------------------------------------
 * End of Models Conventions
 */

class BaseModel
{
    protected $table;
    protected $primaryKey;


    /** simple PDO connection
     * @return PDO|null
     */
    public function connect(): ?PDO
    {
        $db = null;
        try {
            $dsn = "mysql:host=" . getenv('host'). ";dbname=". getenv('dbname'). ";charset=utf8";
            //Install database
            $db = new PDO($dsn, getenv('username'), getenv('password'));
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
        return $db;
    }

    /**
     * @param $conn
     */
    private function disconnect($conn)
    {
        unset($conn);
    }

    /**
     * @param PDOStatement $stmt
     * @param array $params
     * @return PDOStatement
     */
    private function bindValues(PDOStatement $stmt, array $params = []): PDOStatement
    {
        if (!count($params) == 0 and ! is_null($params)){

            if(array_keys($params) === range(0, count($params) - 1)){
                for($i=1; $i <= count($params); $i++){
                    $stmt->bindValue($i, $params[$i-1]);    
                }
            }else {
                foreach ($params as $key => $value){
                    $stmt->bindValue($key, $value);
                }
            }
        }

        return $stmt;
    }

     /**
     * @param string $sql
     * @param array $params
     * @param false $fetchAssoc
     * @return mixed
     */
    public function select(string $sql, array $params = [], bool $fetchAssoc = true)
    {
        $result = null;
        $db = $this->connect();
        
        //prepare the statement
        $stmt = $db->prepare($sql);
        
        $stmt = $this->bindValues($stmt, $params);
        
        if ($stmt->execute()){
            $result = true;
            if ($fetchAssoc){
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $result = $stmt->fetch();
            }
        }else{
            $result = false;
            $this->disconnect($db);
        }

        return $result;
    }

    /**
     * @param string $sql
     * @param array $params
     * @param false $lastID
     * @return bool|string
     */
    public function insert(string $sql, array $params = [], bool $lastId = true)
    {
        $result = null;
        $db = $this->connect();
        //prepare the statement
        $stmt = $db->prepare($sql);
        
        $stmt = $this->bindValues($stmt, $params);

        if ($stmt->execute()){
            $result = true;
            if ($lastId){
                $result = $db->lastInsertId();
            }
        }else{
            $result = false;
            $this->disconnect($db);
        }
        return $result;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return bool
     */
    public function update(string $sql, array $params): bool
    {
        $result = false;

         $result = null;
        $db = $this->connect();
        //prepare the statement
        $stmt = $db->prepare($sql);
        
        $stmt = $this->bindValues($stmt, $params);

        if ($stmt->execute()){
            $result = true;
        }else{
            $result = false;
            $this->disconnect($db);
        }

        return $result;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return bool
     */
    public function delete(string $sql, array $params): bool
    {
        $result = false;

         $result = null;
        $db = $this->connect();
        //prepare the statement
        $stmt = $db->prepare($sql);
        
        $stmt = $this->bindValues($stmt, $params);

        if ($stmt->execute()){
            $result = true;
        }else{
            $result = false;
            $this->disconnect($db);
        }

        return $result;
    }
}