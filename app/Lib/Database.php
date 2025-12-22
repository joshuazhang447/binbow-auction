<?php

namespace App\Lib;

use PDO;
use PDOException;
use ReflectionClass;
use ReflectionException;

class Database
{
    private static $databaseObj;
    private $connection;
    public static function getConnection():Database
    {
        if (!self::$databaseObj)
            self::$databaseObj = new self();
        return self::$databaseObj;
    }

    private function __construct()
    {
        try {
            $this->connection = new PDO("mysql:host=" . DB_HOST . ";dbname=" .
                DB_NAME, DB_USER, DB_PASSWORD);
        }catch (PDOException $e){
            Logger::getLogger()->critical("could not create DB connection: ", ['exception' => $e]);
            die();
        }

    }

    public function __destruct()
    {
        $this->connection = null;
    }

    public function sqlQuery($sql, $bindVal = null, bool $retStmt = false)
    {
        try {
            $statement = $this->connection->prepare($sql);
            if(is_array($bindVal)){
                $result = $statement->execute($bindVal);
            } else {
                $result = $statement->execute();
            }

            if($retStmt){
                return $statement;
            } else {
                return $result;
            }

        } catch (PDOException $e) {
            Logger::getLogger()->critical("could not create DB connection: ", ['exception' => $e]);
            die();
        }

    }

    public function fetch(string $sql,string $class, $bindVal = null):array
    {
        $statement = $this->sqlQuery($sql, $bindVal, true);
        if ($statement->rowCount() == 0) {
            return [];
        }
        try {
            $reflect = new ReflectionClass($class);
            if($reflect->getConstructor()==null){
                $ctor_args = [];
            } else {
                $num = count($reflect->getConstructor()->getParameters());
                $ctor_args = array_fill(0, $num, null);
            }
            return $statement->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE,
                $class, $ctor_args);

        } catch (ReflectionException $e) {
            Logger::getLogger()->critical("could not create DB connection: ", ['exception' => $e]);
            die();
        }

    }

    public function lastInsertId():string
    {
        return $this->connection->lastInsertId();
    }

    public function rowCount($sql, $bindVal = null):int
    {
        $statement = $this->sqlQuery($sql, $bindVal, true);
        return $statement->rowCount();
    }


}
