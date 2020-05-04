<?php

use ElementNSC\Webpage;
use ElementNSC\WebPageWP;
use InterfaceListNSC\_ParamtereFetch;
use InterfaceListNSC\_RouteIterator;
use InterfaceListNSC\_RoutePlugin;
use Routes\NormalRoute;
use Routes\PermissionRoute;
use Routes\RouteBuilder;
use Security\RouteValidator;




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


