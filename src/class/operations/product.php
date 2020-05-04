<?php

namespace ProductsNSC;


use Error;
use InterfaceListNSC\_Informator;
use InterfaceListNSC\_Product;
use InterfaceListNSC\_Schema;
use QuestionSchema;
use QuizSchema;
use Security\ArrayValidator;
use UserSchema;


abstract class ProductBuilder implements _Product{

    private ?string $_message = null;
    private ?bool $_status = null;


    protected ?_Schema $_schema = null;


    public function _GetInformation(): _Informator
    {
        if(!is_null($this->_message) && !is_null($this->_status))
            return new \Informator($this->_message,$this->_status);
        throw new Error("Provide action");
    }
    public function _SetInformation(string $message, bool $type)
    {
      $this->_message = $message;
      $this->_status = $type;
    }

}


//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
/*
 * Authentication*/

class AuthenticationUser extends ProductBuilder
{
    public function __construct(UserSchema $schema)
    {
        $this->_schema = $schema;
    }

    function _GetSchema(): UserSchema
    {
        return $this->_schema;
    }


    function _SetSchema(object $data)
    {
        throw new Error("Authenticate product cannot be set");
    }
}

//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
/*
 * QUIZ schemas*/

class QuestionProduct extends ProductBuilder
{

    function _GetSchema(): _Schema
    {
        return $this->_schema;
    }
    public function __construct($data)
    {
        $this->_SetSchema($data);
    }


    private function assignData($data, string $schemaName)
    {
        if ($data instanceof _Schema)
            return $data;
        elseif ((new ArrayValidator)->_IsValid($data))
            return new $schemaName($data);
        else
            throw new Error("Not provided object");
    }

    function _SetSchema(object $data)
    {
        $this->_schema = $this->assignData($data, QuestionSchema::class);
    }
}


class QuizProduct extends ProductBuilder
{
    public const QUESTION_PREF = 'qst_';

    private array $_questionsNumbers = [];
    private int $_questionPosition = 0;


    public function __construct($data)
    {
        $this->_SetSchema((object)$data);
    }


    function _GetSchema(): _Schema
    {
        if ($this->_schema)
            return $this->_schema;
        throw new Error("Assign quiz to product");
    }

    private function assignData($data, string $schemaName)
    {

        if ($data instanceof _Schema)
            return $data;
        elseif ((new ArrayValidator)->_IsValid($data))
            return new $schemaName($data);
        else
            throw new Error("Not provided array");
    }

    function _SetSchema(object $data)
    {
        $this->_schema = $this->assignData($data, QuizSchema::class);
    }

    function AddQuestion(int $number, $question)
    {
        $this->_questionsNumbers[$this->_questionPosition] = self::QUESTION_PREF . $number;
        $this->_questionsNumbers++;

        $this->_schema->_ConnectSchema(self::QUESTION_PREF . $number, $this->assignData($question, QuestionSchema::class));
    }

    function GetQuestions(): array
    {
        $arr = [];
        foreach ($this->_questionsNumbers as $index => $value) {
            $arr[] = $this->_schema->_GetConnectedSchema($value);
        }
        return $arr;
    }


    function GetQuestion(string $name) : ?QuestionSchema
    {
            $LOschema = $this->_schema->_GetConnectedSchema(self::QUESTION_PREF . $name);
            if($LOschema instanceof QuestionSchema)
                return $LOschema;
        return null;
    }
}