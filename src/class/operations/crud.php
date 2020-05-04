<?php


namespace CRUDNSC;

use DatabaseNSC\MongoDBId;
use DatabaseNSC\MongoDBTemplate;
use Error;
use IdDecorator;
use InterfaceListNSC\_Create;
use InterfaceListNSC\_DatabaseRepository;
use InterfaceListNSC\_Delete;
use InterfaceListNSC\_Pager;
use InterfaceListNSC\_Product;
use InterfaceListNSC\_Read;
use InterfaceListNSC\_Schema;
use InterfaceListNSC\_Template;
use InterfaceListNSC\_Update;
use language\Serverlanguage;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use MongoDB\Exception\Exception;
use ProductsNSC\QuestionProduct;
use ProductsNSC\QuizProduct;
use QuestionSchema;
use QuizSchema;
use Security\PagerValidator;
use Security\UpdaterValidator;


abstract class CRUDBuilder
{
}


abstract class QuestionCRUD extends CRUDBuilder implements _Create, _Read
{
    abstract function _Read(): ?_Product;

    abstract function _Create(_Product $product): void;
}

class QuestionCRUDMongoDb extends QuestionCRUD
{


    use MongoDBTemplate;
    private array $_viewRenderOptions = [];

    public const FOR_QUIZ = 'for_quiz';
    public const QUESTION_PARTIAL = 'question_partial';

    public function __construct()
    {
        $this->_collectionName = 'question';
    }


    function _Create(_Product $product): void
    {

        if ($product instanceof QuestionProduct) {
            if ($product->_GetSchema() instanceof \MultiSchema) {
                throw new Error("Provide only one create per method");
            } else {
                $LObulk = new BulkWrite;

                //Podstawowy system usuwanie Id w celu generowania nowego ID
                $data = $product->_GetSchema()->_GetAllItems();
                unset($data->_id);
                $id = $LObulk->insert(MongoDBId::PrepareID($data, [QuestionSchema::QUIZ_ID]));

                $product->_SetSchema(new \IdDecorator($this->getId($id), $product->_GetSchema()));
                $this->_GetDb()->executeBulkWrite($this->getCollection(), $LObulk);

                $product->_SetInformation(sprintf(Serverlanguage::getInstance()->GetMessage('c.s'), "QUESTION"), true);
            }
        } else
            $product->_SetInformation(sprintf(Serverlanguage::getInstance()->GetMessage('c.f'), "QUESTION"), false);
    }

    function _Read(): ?_Product
    {
        try {
            if ($this->_viewRenderOptions['view'] == self::FOR_QUIZ) {

                $data = $this->_GetDb()->executeQuery($this->getCollection(),
                    new Query([QuestionSchema::QUIZ_ID => new ObjectId($this->_viewRenderOptions['id_question'])]))->toArray();
                $questions = [];
                foreach ($data as $index => $value) {
                    $questions[] = new IdDecorator(MongoDBId::getId($value->{'_id'}), new QuestionSchema($value));
                }
                $product = new QuestionProduct(new \MultiSchema($questions));
                $product->_SetInformation(sprintf(Serverlanguage::getInstance()->GetMessage('r.s'), 'QUESTION'), true);
                return $product;


            }
            if ($this->_viewRenderOptions['view'] == self::QUESTION_PARTIAL) {

                $data = $this->_GetDb()->executeQuery($this->getCollection(), new Query(['_id' => new ObjectId($this->_viewRenderOptions['id'])], ['limit' => 1]))->toArray();
                $product = new QuestionProduct(new IdDecorator(MongoDBId::getId($data[0]->{'_id'}), new QuestionSchema($data[0])));
                $product->_SetInformation(sprintf(Serverlanguage::getInstance()->GetMessage('r.s'), 'QUESTION'), true);
                return $product;

            }

        } catch (\MongoDB\Driver\Exception\Exception $e) {
        }
        return null;
    }

    public function _SetViewRender(array $option): void
    {

        $this->_viewRenderOptions = $option;
    }
}


abstract class QuizCRUD extends CRUDBuilder implements _Create, _Read, _Update, _Delete
{
    abstract function _Read(): ?_Product;

    abstract function _Update(_Product $product): void;

    abstract function _Delete(_Product $product): void;

    abstract function _Create(_Product $product): void;
}

class QuizCRUDMongoDb extends QuizCRUD
{
    use MongoDBTemplate;

    public const FULL = 'full';
    public const QUIZ_PARTIAL = 'quiz_partial';

    private array $_viewRenderOptions = [];
    private array $_updateRenderOptions = [];

    public function __construct(?BulkWrite $bulk = null)
    {
        $this->_collectionName = 'quiz';
    }

    public function _SetViewRender(array $option): void
    {
        $this->_viewRenderOptions = $option;

    }


    function _Read(): ?_Product
    {
        try {
            /*Get all quizes in database
             * */
            if ($this->_viewRenderOptions['view'] === self::FULL) {

                $data = $this->_GetDb()->executeQuery($this->getCollection(), new Query([]))->toArray();
                $quizzes = [];
                foreach ($data as $index => $value) {
                    $quizzes[] = new IdDecorator(MongoDBId::getId($value->{'_id'}), new QuizSchema($value));
                }
                $product = new QuizProduct(new \MultiSchema($quizzes));
                $product->_SetInformation(sprintf(Serverlanguage::getInstance()->GetMessage('r.s'), 'QUIZ'), true);
                return $product;

            }
            if ($this->_viewRenderOptions['view'] == self::QUIZ_PARTIAL) {
                $data = $this->_GetDb()->executeQuery($this->getCollection(), new Query(['_id' => new ObjectId($this->_viewRenderOptions['id'])]))->toArray();
                $product = new QuizProduct(new IdDecorator(MongoDBId::getId($data[0]->{'_id'}), new QuizSchema($data[0])));
                $product->_SetInformation(sprintf(Serverlanguage::getInstance()->GetMessage('r.s'), 'QUIZ'), true);
                return $product;
            }
        } catch (\MongoDB\Driver\Exception\Exception $e) {

        }
        return null;
    }

    function _Update(_Product $product): void
    {

        $product->_SetInformation(sprintf(Serverlanguage::getInstance()->GetMessage('u.s'), 'QUIZ'), true);
    }

    function _Delete(_Product $product): void
    {
        var_dump("Usuwanie");
    }

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

    public function _SetUpdateRender(array $option): void
    {
        if ((new UpdaterValidator)->_IsValid([UpdaterValidator::CLS=>QuizSchema::class,UpdaterValidator::VALUE=>$option]))
            $this->_updateRenderOptions = $option;
    }
}









