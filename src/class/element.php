<?php


namespace ElementNSC;


use InterfaceListNSC\_Output;
use InterfaceListNSC\_OutputContent;
use InterfaceListNSC\_ParamtereFetch;
use InterfaceListNSC\_State;
use InterfaceListNSC\_StateMachine;
use InterfaceListNSC\stdClass;
use Iterator;


class OutputController implements _Output, _StateMachine
{


    public const  DATASUCCESS = "dataSuccess";
    public const  OUTPUT = "output";
    public const  INFO = 'info';
    public const MULTICONTENT = 'multiContent';
    public const CONTENT = 'content';
    public const ADDITIONAL = 'additional';


    public function __construct()
    {
        $this->_state = new OutputController_NeedInitialize();
    }

    private bool $_dataSuccess = false;
    private string $_info = '';
    private ?_Output $_content = null;
    private array $_multiContent = [];
    private array $_output = [];
    private array $_additional = [];


    public function setInfo($_info): void
    {
        $this->_info = $_info;

        $this->_state->_Action($this);
    }


    public function setDataSuccess($dataSuccess): void
    {
        $this->_dataSuccess = $dataSuccess;

        $this->_state->_Action($this);

    }


    public function setContent(_Output $_content): void
    {
        $this->_content = $_content;

        $this->_state->_Action($this);
    }


    public function setMultiContent(Iterator $_multiContent): void
    {
        foreach ($_multiContent as $key => $index) {
            $this->_multiContent[] = $index;
        }

        $this->_state->_Action($this);
    }

    public function setAdditionalData(array $_additional)
    {
        $this->_additional = $_additional;
        $this->_state->_Action($this);
    }

    private function createContainer(): void
    {
        $LOdata = [];
        if ($this->_dataSuccess) {
            $data[self::DATASUCCESS] = true;
            $data[self::INFO] = $this->_info;
            $data[self::CONTENT] = ($this->_content) ? $this->_content->_GetContainer() : null;
            $data[self::MULTICONTENT] = $this->_multiContent;
            $data[self::ADDITIONAL] = $this->_additional;
            http_response_code(202);
        } else {
            $data[self::DATASUCCESS] = false;
            $data[self::INFO] = $this->_info;
            http_response_code(404);
        }
        $this->_output = $data;

    }


    private function filterEmptyValues(): array
    {


        $data = [];
        foreach ($this->_output as $index => $value) {
            if (!empty($index))
                $data[$index] = $value;
        }
        return $data;

    }


    function _GetContainer(): array
    {
        if ($this->_state->_Action($this)) {

            $this->createContainer();
            $this->filterEmptyValues();
            return $this->_output;

        }
        throw new \Error("Output must be initialized");
        return [];
    }


    private _State $_state;

    function _SetStat(_State $state)
    {
        $this->_state = $state;
    }


    function _GetState(): _State
    {
        return $this->_state;
    }


}


class OutputController_Initialized implements _State
{

    function _Action(_StateMachine $instance): bool
    {
        return true;
    }
}

class OutputController_NeedInitialize implements _State
{
    function _Action(_StateMachine $instance): bool
    {
        /* @var OutputController $instance */
        $instance->_SetStat(new OutputController_Initialized());
        return false;
    }
}


abstract class Element implements _Output
{
    protected OutputController $_outputController;

    public function __construct()
    {
        $this->_outputController = new OutputController();

    }

    function _GetContainer(): array
    {
        $this->_CreateContainer();
        return $this->_outputController->_GetContainer();
    }

    abstract function _CreateContainer(): void;
}

abstract class Webpage extends Element
{
    function _CreateContainer(): void
    {
        $this->_outputController->setDataSuccess(true);
        $this->_outputController->setInfo("foo");
        $this->_outputController->setAdditionalData(["hejka"]);
    }
}

abstract class WebPageWP extends Element
{
    private _ParamtereFetch $_parameterFetch;

    public function __construct(_ParamtereFetch $paramterFetch)
    {
        parent::__construct();
        $this->_parameterFetch = $paramterFetch;
    }
    protected function getParameters(string $para)
    {
        return $this->_parameterFetch->_GetParameter($para);
    }
   abstract function _CreateContainer(): void;

}


























