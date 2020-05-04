<?php

namespace DatabaseNSC;

use  InterfaceListNSC\_Database;
use  InterfaceListNSC\_DatabaseRepository;
use InterfaceListNSC\_DecoratorIdSchema;
use InterfaceListNSC\_Schema;
use  InterfaceListNSC\_Singleton;

//MongoDB
use InterfaceListNSC\_Template;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use PDO;

/*
 * Class for managing database connections
 */

class Database implements _Database, _Singleton
{
    public const MY_SQL = "mySQL";
    public const MONGO_DB = "mongoDb";

    private ?_DatabaseRepository  $_database = null;
    private static ?Database $_instance = null;
    private ?string $_currentDatabaseName = null;

    private function __construct()
    {
        $this->_ChangeDatabase(self::MY_SQL);
    }

    public function _GetDB(): Manager
    {
        return $this->_database->_GetDB();
    }

    public function _ChangeDatabase($_Name)
    {
        if ($_Name != $this->_currentDatabaseName) {
            switch ($_Name) {
                case self::MONGO_DB:
                    $this->_database = new MongoDB;
                    break;
                case self::MY_SQL:
                    $this->_database = new MysqlDB;
                    break;
                default:
                    throw new \Error("Choose database by class constant");
            }
            $this->_currentDatabaseName = $_Name;
        }
    }

    public static function getInstance(): Database
    {
        if (!self::$_instance) {
            self::$_instance = new Database();
        }
        return self::$_instance;
    }


    public function _CloseConnections()
    {
        array_map(fn(&$index) => $index = null, $this->_databaseList);
    }

}


/*
 * Databases Instances
 */

class MongoDB implements _DatabaseRepository
{

    private static ?Manager $_instance = null;

    public function _GetDB(): Manager
    {
        if (!self::$_instance) self::$_instance = new Manager("mongodb://localhost:27017");
        return self::$_instance;
    }

    public function _GetType()
    {
        return Database::MONGO_DB ?? "mongoDb";
    }
}

class MysqlDB implements _DatabaseRepository
{
    private static ?PDO $_instance = null;

    public function _GetDB(): PDO
    {
        throw new \Error("Sorry mysql not implemented");
    }


    public function _GetType()
    {
        return Database::MY_SQL ?? "mySQL";
    }
}

class MongoDBId
{
    static function getId(ObjectId $id)
    {
        return $id->jsonSerialize()['$oid'];
    }

    /**@param array|string $propertiesNames */
    /**@param array|object $data */
    static function PrepareID($data, $propertiesNames): array
    {
        $data = (array)$data;
        if (is_array($propertiesNames)) {

            foreach ($propertiesNames as $index => $value) {
                if (isset($data[$value])) {
                    $data[$value] = new ObjectId($data[$value]);
                }
            }
        } else {
            $data[$propertiesNames] = new ObjectId($data[$propertiesNames]);
        }

        return $data;
    }

    static function ChangeID($data, $propertiesNames)
    {
        $data = (array)$data;
        try {
            if (is_array($propertiesNames)) {
                foreach ($propertiesNames as $index => $value) {
                    if (isset($data[$value])) {
                        $data[$value] = self::getId($data[$value]);
                    }
                }
            } else {
                $data[$propertiesNames] = self::getId(new ObjectId($data[$propertiesNames]));
            }
        } catch (\Error $error) {
            var_dump("Value must be type of ObjectId");
        }

        return $data;
    }

}


//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
/*
 * Database template for CRUD*/

trait MongoDBTemplate
{



    private string $_collectionName;

    //Do zmiany
    private function _GetDatabaseName(): string
    {
        return 'QuizApplication.';
    }

    private function _GetDb(): Manager
    {
        Database::getInstance()->_ChangeDatabase(Database::MONGO_DB);
        return Database::getInstance()->_GetDB();
    }

    function _GetType()
    {
        return Database::MONGO_DB;
    }

    private function getId(ObjectId $id)
    {
        return MongoDBId::getId($id);
    }

    private function getCollection(): string
    {
        return $this->_GetDatabaseName() . $this->_collectionName;
    }

}





trait MysqlTemplate
{
    public function _GetDb(): Manager
    {
        Database::getInstance()->_ChangeDatabase(Database::MY_SQL);
        return Database::getInstance()->_GetDB();
    }

    function _GetType()
    {
        return Database::MY_SQL;
    }
}


