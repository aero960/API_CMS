# API_CMS
Repository to easy create CRUD APIs.

> How to use ?

## 1. Configure Database classfor yourpurpose or add other database type 
example :
    PATH: ```src/class/database.php```
        1. implement interface _DatabaseRepository
            ```
            class MongoDB implements _DatabaseRepository
            {

            private static ?Manager $_instance = null;
            //configure access to database 
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
            ```

## 2. Configure Fabric method to create instane for db 

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
            //Example
            case 'PostgreSQL'
                $this->_database = new PostgreSQLDB;
            //Example
                    default:
                        throw new \Error("Choose database by class constant");
                }
                $this->_currentDatabaseName = $_Name;
            }
        }


PATH: ```src/test/test.php```
## 3. Configure routes

        ```ControllerRoute::getInstance()->_Initialize((object)[
            
        //Example
            "List" => [
        new NormalRoute('GET','/info', new View('Implement new class of _ParameterFetch') , 'Implement new class of _ParameterFetch')
        ],
        //NOTE: View implement interface Element which has one method to get output from view class 

        //Example
                "Mechanic" => new PHRouteMechanic(),
                "DefaultPage" => new NormalRoute(RouteBuilder::GET, "fas", new \view($LOfullParameters), $LOfullParameters)]);
        ```


    U can also configure Plugin which return RouteListManagment


        PATH: ```src/test/plugin.php```
    ```
        class QuizPlugin implements _RoutePlugin
        {
            private _ParamtereFetch  $_fullParameters;
            private const PATH = 'quiz/';

            public function __construct(_ParamtereFetch $fullParameters)
            {
                $this->_fullParameters = $fullParameters;
            }
            function _GetPlugin(): _RouteIterator
            {
                return new \Routes\RouteListManagment([
                    new NormalRoute(RouteBuilder::POST, self::PATH . "createquiz", new view($this->_fullParameters), $this->_fullParameters),
                    new PermissionRoute(RouteBuilder::GET, self::PATH . "baz", new view($this->_fullParameters),new RouteValidator(), $this->_fullParameters)
                ]);
            }
        }
        ```
    NOTE: Validator has only one method IsValid you can create own Validators to webpage

## 4. Last part of configure is creating new Views and Cruds 

    CRUD example, only specife methods,
    you can make it for multiple databases

    ```
    abstract class QuizCRUD extends CRUDBuilder implements _Create, _Read, _Update, _Delete
    {
        abstract function _Read(): ?_Product;

        abstract function _Update(_Product $product): void;

        abstract function _Delete(_Product $product): void;

        abstract function _Create(_Product $product): void;
    }
    ```

    Implementation for MongoDB

        ```
        class QuizCRUDMongoDb extends QuizCRUD
        ...

        function _Create(_Product $product): void
            {
                if ($product instanceof QuizProduct) {
                    if ($product->_GetSchema() instanceof \MultiSchema) {
                        throw new Error("Provide only one create per method");
                    } else {
                        /** @var QuizProduct $product */
                        $LObulk = new BulkWrite;

                        $data = $product->_GetSchema()->_GetAllItems();
                        unset($data->_id);
                        $id = $LObulk->insert($data);
                        /** @var ObjectId $id */

                        $product->_SetSchema(new \IdDecorator($this->getId($id), $product->_GetSchema()));

                        $this->_GetDb()->executeBulkWrite($this->getCollection(), $LObulk);

                        $product->_SetInformation(sprintf(Serverlanguage::getInstance()->GetMessage('c.s'), 'QUIZ'), true);
                    }
                } else
                    $product->_SetInformation(sprintf(Serverlanguage::getInstance()->GetMessage('c.f'), 'QUIZ'), false);
            }

        ```


NOTE every CRUD use schemas and product 

SEE ```src/class/operations/schema.php``` to get detailed for building schemas and product concentrate ```src/class/operations/product.php```
