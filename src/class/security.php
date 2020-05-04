<?php

namespace Security;

use converterNSC\QuestionConverter;
use Error;
use InterfaceListNSC\_Route;
use InterfaceListNSC\_RouteValidator;
use InterfaceListNSC\_SingleRoute;
use InterfaceListNSC\_Validator;

class Validators implements _Validator
{

    private array $_validators;

    public function __construct(array $validators = [])
    {
        foreach ($validators as $index) {
            if (!($index instanceof _Validator))
                throw new \Error("Sorry item must be instance of _Validator");
        }
        $this->_validators = $validators;
    }

    function _IsValid($value): bool
    {
        $validate = true;
        /** @var _Validator $item
         * @var  int $index
         */
        foreach ($this->_validators as $index => $item) {
            $validate = $item->_IsValid($value);
        }
        return $validate;
    }
}


class CorrectAnswerValidator implements _Validator
{

    private array $_answers;

    public function __construct($answers)
    {
        if ((new QuestionArrayValidator)->_IsValid($answers))
            $this->_answers = $answers;
        else
        throw new Error("provided not correct array");
    }

    function _IsValid($value): bool
    {
        return in_array($value, array_column($this->_answers, "ans_sign"));
    }
}

class QuestionArrayValidator implements _Validator
{


    private const PRIMARYKEY = 'ans_sign';

    private function checkValueExist(string $value, array &$arr)
    {

        $index = array_search($value, $arr);
        if (is_numeric($index)) {
            $arr[$index] = null;
            return true;
        }
        return false;
    }

    private function getSubGroupAnswers($value)
    {
        foreach ([['t', 'f'], ['a', 'b', 'c', 'd']] as $index => $vl) {
            if (in_array($value, $vl))
                return $vl;
        }
        return false;
    }


    private function checkKeys(array $value)
    {


        if (isset($value[0][self::PRIMARYKEY])) {
            $LOcheckingArray = $this->getSubGroupAnswers($value[0][self::PRIMARYKEY]);
            foreach ($value as $index => $vl)
                if (!$this->checkValueExist($vl[self::PRIMARYKEY], $LOcheckingArray))
                    return false;
            return true;
        }
        throw new Error('Need index of name `ans_sign` and `ans_content` use converter or provide correct array');

    }

    function _IsValid($value): bool
    {
        if ($this->checkKeys($value) && (count($value) == 2 || count($value) == 4))
            return true;
        return false;
    }
}

class ArrayValidator implements _Validator
{

    function _IsValid($value): bool
    {
        /**@var array $value */
        if (!is_null($value) && is_array($value) && !empty($value)) {
            return true;
        }
        return false;
    }
}


class RouteValidator implements _RouteValidator
{
    private ?_Validator $_validators;

    public function __construct(_Validator $validator = null)
    {
        $this->_validators = $validator ?? null;
    }

    function _Execute()
    {
        echo " strona została zwalidowana";
        return false;
    }
}

class DifficultValidator implements _Validator
{
    function _IsValid($value): bool
    {
        return ($value > 0 && $value < 10);

    }
}

class PagerValidator implements _Validator{

    function _IsValid($value): bool
    {
        $valid = true;
        if(isset($value['page']))
            $valid = (is_numeric($value['page']));
        if(isset($value['limitPerPage']))
            $valid = (is_numeric($value['limitPerPage']));
        return $valid;
    }
}

class UpdaterValidator implements _Validator{

    public const CLS = 'class';
    public const VALUE = 'value';

    function _IsValid($value): bool
    {
        try{
            var_dump((new \ReflectionClass($value[self::CLS]))->getConstants());
        }catch (\ReflectionException $exception){}

        return false;
    }
}


