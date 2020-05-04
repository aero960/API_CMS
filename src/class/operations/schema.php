<?php

use converterNSC\QuestionConverter;
use converterNSC\StringArrayRefactorConverter;
use InterfaceListNSC\_Converter;
use InterfaceListNSC\_Item;
use InterfaceListNSC\_Schema;
use InterfaceListNSC\_Validator;
use Security\CorrectAnswerValidator;
use Security\DifficultValidator;
use Security\QuestionArrayValidator;

abstract class Item implements _Item
{
    protected string $type;
    private ?_Validator $_validators;
    private $_value;

    public function __construct($value, ?_Validator $validators = null, $defaultValue = null, ?_Converter $converter = null)
    {
        $this->_validators = $validators;
        $this->_value = $value ?? $defaultValue;

        if (!$this->checkValidateValues()) {
            $typeUpper = strtoupper($this->type);
            throw new Error("necessary validate, {$typeUpper} ");
        }
    }

    function _GetValue()
    {
        return $this->_value;
    }

    private function checkValidateValues(): bool
    {
        if (!is_null($this->_value))
            return ($this->_validators != null) ? $this->_validators->_IsValid($this->_value) : true;

        $typeUpper = strtoupper($this->type);
        throw new Error("Value cannot be null, Assign value to properties without default value {$typeUpper}");
    }
}

class DateTimeItem extends Item
{
    public function __construct(?string $value, ?_Validator $validators = null, $defaultValue = null)
    {
        $this->type = "DateTimeItem";
        if (!DateTime::createFromFormat(SchemaBuilder::DATEFORMAT, parent::_GetValue()))
            throw new Error("This isn`t valid date format");
        parent::__construct($value, $validators, $defaultValue);

    }

    public function _GetValue(): DateTime
    {
        return DateTime::createFromFormat(SchemaBuilder::DATEFORMAT, parent::_GetValue());
    }
}


class ArrayItem extends Item
{
    public function __construct(array $value, ?_Validator $validators = null, $defaultValue = null)
    {
        $this->type = "ArrayItem";
        parent::__construct((array)$value, $validators, $defaultValue);
    }


    public function _GetValue(): array
    {
        return parent::_GetValue();
    }

}

class StringItem extends Item
{

    public function __construct(?string $value, ?_Validator $validators = null, $defaultValue = null)
    {
        $this->type = "StringItem";

        parent::__construct($value, $validators, $defaultValue);
    }

    public function _GetValue(): string
    {
        return parent::_GetValue();
    }
}

class NumberItem extends Item
{
    public function __construct(?int $value, ?_Validator $validators = null, $defaultValue = null)
    {
        $this->type = "NumberItem";
        parent::__construct((int)$value, $validators, $defaultValue);
    }

    public function _GetValue(): int
    {
        return parent::_GetValue();
    }
}

/*
 * Note: Not mxinin multiple tables*/

abstract class SchemaBuilder implements _Schema
{
    public const DATEFORMAT = 'Y-m-d H:i:s';

    private $_data;

    private array $_schemas;


    public function searchValue($valueName)
    {
        return is_array($this->_data) ? $this->_data[$valueName] ?? null : $this->_data->{$valueName} ?? null;
    }


    protected function cleanData()
    {
        $this->_data = null;
        unset($this->_data);
    }

    public function __construct($data)
    {
        if (is_object($data) || is_array($data)) {
            $this->_data = $data;
        } else
            throw new Error("$data must be an object or an array");
        $this->initialize();
        $this->cleanData();
    }

    /*
     * Connecting schemas with other schema */

    function _ConnectSchema(string $name, _Schema $schema)
    {
        $this->_schemas[$name] = $schema;
    }

    /*
     * get connected schema*/
    function _GetConnectedSchema(string $name): _Schema
    {
        if (isset($this->_schemas[$name]))
            return $this->_schemas[$name];
        throw new Error("Sub schema must exist in current schema");
    }

    public function convert($value, array $converters)
    {
        /**@var _Converter $value */
        foreach ($converters as $index => $vl) {
            $value = $vl->_Convert($value);
        }
        return $value;
    }

    abstract protected function initialize();
}

abstract class SchemaEasier extends SchemaBuilder
{

    protected const S = 'string';
    protected const I = 'int';
    protected const D = 'dateTime';
    protected const A = 'array';


    protected function createValue(string $type, string $name, $vd = null, $df = null, array $converters = [])
    {
        $value = $this->searchValue($name);
        $value = $this->convert($value, $converters);
        if ($type == self::S)
            return new StringItem($value, $vd, $df);
        if ($type == self::I)
            return new NumberItem($value, $vd, $df);
        if ($type == self::D)
            return new DateTimeItem($value, $vd, $df);
        if ($type == self::A)
            return new ArrayItem($value, $vd, $df);
        throw new Error("Sorry another type isnt provide");
    }


    protected function getAllItems(array $arr): object
    {
        return (object)$arr;
    }
}

class IdDecorator extends SchemaEasier implements \InterfaceListNSC\_DecoratorIdSchema
{

    private _Schema $_schema;
    public string $_idName = '_id';

    private StringItem $_id;

    public function __construct($id, _Schema $schema)
    {
        parent::__construct([$this->_idName => $id]);
        $this->_schema = $schema;
    }

    function _GetAllItems(): object
    {
        return $this->getAllItems(
            array_merge([$this->_idName => $this->_GetId()], (array)$this->_schema->_GetAllItems()));
    }

    function _SetIdName(string $name)
    {
        $this->_idName = $name;
    }

    function _GetId(): string
    {
        return $this->_id->_GetValue();
    }

    function _GetSchema()
    {
        return $this->_schema;
    }

    public function __call($name, $arguments)
    {
        return $this->_schema->{$name}();
    }
    protected function initialize()
    {
        $this->_id = $this->createValue(self::S, $this->_idName,null,'If you can read this, change id name');
    }
}

class MultiSchema implements _Schema{

    private array $_schemas;

    public function __construct(array $schemas)
    {
        foreach($schemas as $index => $value){
            if(!($value instanceof _Schema))
                throw new Error("All value of array must implement schema");
        }
        $this->_schemas = $schemas;
    }

    function _GetAllItems(): object
    {
       return (object)$this->_schemas;
    }

    function _ConnectSchema(string $name, _Schema $schema)
    {
       throw new Error("multischema not provided Connection") ;
    }

    function _GetConnectedSchema(string $name): _Schema
    {
        throw new Error("multischema not provided Connection") ;
    }
}

//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
/*
 * Concentrate  schemas going below*/

//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
/*
 * Authentication*/

class UserSchema extends SchemaEasier
{

    public const USERNAME = 'u_username';
    public const PASSWORD = 'u_password';
    public const EMAIL = 'u_email';
    public const REGISTEREDAT = 'u_registeredAt';
    public const LASTLOGIN = 'u_lastLogin';

    private StringItem $_id;
    private StringItem $_username;
    private StringItem $_password;
    private StringItem $_email;
    private DateTimeItem $_registeredAt;
    private DateTimeItem $_lastLogin;

    public function getUsername()
    {
        return $this->_username->_GetValue();
    }


    public function getPassword()
    {
        return $this->_password->_GetValue();
    }


    public function getEmail()
    {
        return $this->_email->_GetValue();
    }

    public function getRegisteredAt(): DateTime
    {
        return $this->_registeredAt->_GetValue();
    }


    public function getLastLogin(): DateTime
    {
        return $this->_lastLogin->_GetValue();
    }


    function _GetAllItems(): object
    {
        return $this->getAllItems([

            self::USERNAME => $this->searchValue(self::USERNAME),
            self::PASSWORD => $this->searchValue(self::PASSWORD),
            self::EMAIL => $this->searchValue(self::EMAIL),
            self::REGISTEREDAT => $this->searchValue(self::REGISTEREDAT),
            self::LASTLOGIN => $this->searchValue(self::LASTLOGIN),
        ]);
    }

    protected function initialize()
    {
        $this->_username = $this->createValue(self::S, self::USERNAME);
        $this->_password = $this->createValue(self::S, self::PASSWORD);
        $this->_email = $this->createValue(self::S, self::EMAIL);
        $this->_registeredAt = $this->createValue(self::D, self::REGISTEREDAT);
        $this->_lastLogin = $this->createValue(self::D, self::LASTLOGIN);
    }
}


//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
/*
 * QUIZ schemas*/

/*
 *
 *
 * Schema values
 * name => string
 * content => string
 * difficult => int
 * */


class QuizSchema extends SchemaEasier
{
    public const NAME = "q_name";
    public const CONTENT = "q_content";
    public const DIFFICULT = "q_difficult";

    private StringItem $_name;
    private StringItem $_content;
    private NumberItem $_difficult;

    public function getName(): string
    {
        return $this->_name->_GetValue();
    }

    public function getContent(): string
    {
        return $this->_content->_GetValue();
    }

    public function getDifficult(): int
    {
        return $this->_difficult->_GetValue();
    }


    function _GetAllItems(): object
    {
        return (object)[
            self::NAME => $this->getName(),
            self::CONTENT => $this->getContent(),
            self::DIFFICULT => $this->getDifficult()
        ];
    }

    protected function initialize()
    {
        $this->_name = $this->createValue(self::S, self::NAME);
        $this->_content = $this->createValue(self::S, self::CONTENT);
        $this->_difficult = $this->createValue(self::I, self::DIFFICULT, new DifficultValidator);
    }
}

class QuestionSchema extends SchemaEasier
{
    public const NAME = 'qst_name';
    public const CONTENT = 'qst_content';
    public const DIFFICULT = 'qst_difficult';
    public const ANSWERS = 'qst_answer';
    public const CORRECT_ANSWER = 'qst_correctAnswer';
    public const QUIZ_ID = 'q_id';

    private StringItem $_name;
    private StringItem $_content;
    private NumberItem $_difficult;
    private ArrayItem  $_answers;
    private StringItem  $_correctAnswer;
    private StringItem $_quizId;

    public function getQuizId()
    {
        return $this->_quizId->_GetValue();
    }

    public function getName()
    {
        return $this->_name->_GetValue();
    }

    public function getContent()
    {
        return $this->_content->_GetValue();
    }

    public function getDifficult()
    {
        return $this->_difficult->_GetValue();
    }

    public function getAnswers(): array
    {
        return $this->_answers->_GetValue();
    }

    public function getCorrectAnswer()
    {
        return $this->_correctAnswer->_GetValue();
    }

    function _GetAllItems(): object
    {

        return $this->getAllItems([
            self::QUIZ_ID => $this->getQuizId(),
            self::CONTENT => $this->getContent(),
            self::NAME => $this->getName(),
            self::DIFFICULT => $this->getDifficult(),
            self::ANSWERS => $this->getAnswers(),
            self::CORRECT_ANSWER => $this->getCorrectAnswer()
        ]);

    }

    protected function initialize()
    {
        $this->_quizId = $this->createValue(self::S,self::QUIZ_ID);
        $this->_name = $this->createValue(self::S, self::NAME);
        $this->_content = $this->createValue(self::S, self::CONTENT);
        $this->_difficult = $this->createValue(self::I, self::DIFFICULT, new DifficultValidator);
        $this->_answers = $this->createValue(self::A, self::ANSWERS, new QuestionArrayValidator, null, [new StringArrayRefactorConverter, new QuestionConverter()]);
        $this->_correctAnswer = $this->createValue(self::S, self::CORRECT_ANSWER, new CorrectAnswerValidator($this->_answers->_GetValue()));
    }
}








