<?php

class Informator implements \InterfaceListNSC\_Informator{

    private string $_msg;
    private bool $_status;

    public function __construct(string $msg,bool $status)
    {
        $this->_msg = $msg;
        $this->_status = $status;
    }

    public function _GetMSG()
    {
        return $this->_msg;
    }

    function _GetStatus()
    {
       return $this->_status;
    }
}