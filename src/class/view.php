<?php


use CRUDNSC\QuestionCRUDMongoDb;
use ProductsNSC\QuestionProduct;
use ProductsNSC\QuizProduct;
use CRUDNSC\QuizCRUDMongoDb;

class view extends \ElementNSC\WebPageWP
{

    function _CreateContainer(): void
    {
        /*        $schema = new QuizSchema([
                    QuizSchema::ID => '12324',
                    QuizSchema::NAME => $this->getParameters('name'),
                    QuizSchema::CONTENT => $this->getParameters('content'),
                    QuizSchema::DIFFICULT => $this->getParameters('difficult'),
                ]);


        (new QuestionConverter($value))->_Convert()
*/
        /*
                $quiz->AddQuestion(0, [
                    QuestionSchema::CORRECT_ANSWER => 'a',
                    QuestionSchema::NAME => "zip",
                    QuestionSchema::CONTENT => "Foo is bar ?",
                    QuestionSchema::DIFFICULT => 3,
                    QuestionSchema::ANSWERS => [['ans_sign' => 'a', 'ans_content' => "Lorem"],
                        ['ans_sign' => 'b', 'ans_content' => "Lorem"],
                        ['ans_sign' => 'c', 'ans_content' => "Lorem"],
                        ['ans_sign' => 'd', 'ans_content' => "Lorem"]]
                ]);
                //    $qSchema1 = new QuestionSchema($qSchema->_GetAllItems());*/

        //\DatabaseNSC\Database::getInstance()->_GetDB()->executeBulkWrite('QuizApplication.test',)


        $crud = new QuizCRUDMongoDb();

        /*    $quiz = new QuizProduct(new QuizSchema([
                QuizSchema::NAME => $this->getParameters('name'),
                QuizSchema::CONTENT => $this->getParameters('content'),
                QuizSchema::DIFFICULT => $this->getParameters('difficult')
            ]));
            $crud->_Create($quiz);

            foreach ($this->getParameters('question') as $index => $value) {
                (new \CRUDNSC\QuestionCRUDMongoDb)->_Create(new QuestionProduct(new QuestionSchema(
                    [QuestionSchema::QUIZ_ID => $quiz->_GetSchema()->_GetId(),
                        QuestionSchema::NAME => 'fooTest',
                        QuestionSchema::CORRECT_ANSWER => 'a',
                        QuestionSchema::CONTENT => "Foo is Bar",
                        QuestionSchema::DIFFICULT => 3,
                        QuestionSchema::ANSWERS => $value])));
            }
            */

        $crud->_SetViewRender(['view' => QuizCRUDMongoDb::QUIZ_PARTIAL,'id'=>'5eadc02006390000c5002634','id_question'=>'5eadc02006390000c5002634']);
        $prod = $crud->_Read();
            var_dump($prod->_GetSchema()->_GetAllItems());
        $crud->_SetUpdateRender([]);
        $crud->_Update($prod);

            var_dump( $prod->_GetInformation()->_GetMSG());
        $this->_outputController->setDataSuccess(true);
        $this->_outputController->setInfo('output');

    }
}