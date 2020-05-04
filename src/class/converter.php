<?php


namespace converterNSC;


use Error;
use InterfaceListNSC\_Converter;


/*
 *
 * Convert to index allowed for questions*/

class QuestionConverter implements _Converter
{
    private array $_value;
    private string $_type;

    function _Convert($value)
    {
        $this->_value = (is_array($value)) ? $value : (array)$value;
        $this->_value = (new RecursiveChangeToArray)->_Convert($this->_value);


        $this->checkType();

        if ($this->_type === 'array_pure')
            return $this->pureConvert();
        if ($this->_type === 'array_string')
            return $this->stringConvert();
    }

    private function pureConvert(): array
    {
        return $this->_value;
    }

    private function stringConvert(): array
    {
        $LOconvArr = [];
        foreach ($this->_value as $index => $value) {
            $LOconvArr[$index]['ans_sign'] = $value[0];
            $LOconvArr[$index]['ans_content'] = substr($value, 2);
        }
        return $LOconvArr;
    }

    private function checkType()
    {

        if (isset($this->_value[0]['ans_sign']))
            $this->_type = 'array_pure';
        else
            if ($this->_value[0][1] == ':')
                $this->_type = 'array_string';
            else
                throw new Error("Not provided correct array");
    }
}

class RecursiveChangeToArray implements _Converter
{

    private function recursiveConvert($value)
    {
        if (is_object($value) || is_array($value)) {
            $value = (array)$value;
            foreach ($value as $index => &$vl) {
                $vl = $this->recursiveConvert($vl);
            }
        }
        return $value;
    }
    function _Convert($value)
    {
        return $this->recursiveConvert($value);
    }
}

class StringArrayRefactorConverter implements _Converter
{

    private string $_type;

    /*
     * Convert to array from string*/
    function _Convert($value)
    {
        $value = (is_object($value)) ? (array)$value : $value;
        return ((new \Security\ArrayValidator)->_IsValid($value)) ? $value : preg_split("/[\,|\/]/", $value);
    }


}
