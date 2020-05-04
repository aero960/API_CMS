<?php

namespace Routes;

use Error;
use Exception;
use InterfaceListNSC\_Output;
use InterfaceListNSC\_Parameters;
use InterfaceListNSC\_ParamtereFetch;
use InterfaceListNSC\_Route;
use InterfaceListNSC\_RouteIterator;
use InterfaceListNSC\_RouteMechanic;
use InterfaceListNSC\_RouteValidator;
use InterfaceListNSC\_SingleRoute;
use InterfaceListNSC\_Validator;
use language\Serverlanguage;
use Phroute\Phroute\Dispatcher;
use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;
use Phroute\Phroute\RouteCollector;

class RouteListManagment implements _RouteIterator, \Iterator
{
    private array $routesList = [];
    private int $position = 0;

    public function __construct(array $LOroutesList)
    {

        foreach ($LOroutesList as $index) {
            if (!($index instanceof _Route))
                throw new \Error(Serverlanguage::getInstance()->ImportAndServerMessage("r.r"));
        }
        $this->routesList = $LOroutesList;
    }

    public function current()
    {
        return $this->routesList[$this->position];
    }

    public function next()
    {
        ++$this->position;
    }

    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        return isset($this->routesList[$this->position]);
    }

    public function rewind()
    {
        $this->position = 0;
    }

    function _GetActiveRoute()
    {
        /** @var array $LOactivePage */
        $LOactivePage = array_filter($this->routesList, fn(_Route $index) => $index->_IsActive());

        if (!empty($LOactivePage)) {
            return array_shift($LOactivePage);
        }
        return null;
    }

    function _AppendRoutes(_RouteIterator $routeIterator)
    {
        /**@var  $index _Route */
        foreach ($routeIterator as $index){
            $this->routesList[] = $index;
        }

    }
}



/*
 * Route library
 */

final class PHRouteMechanic implements _RouteMechanic
{

    private RouteCollector $routeManager;
    private $_information;

    public function __construct()
    {
        $this->routeManager = new RouteCollector();
    }


    function _ExecuteSubject(): void
    {
        $requestMETHOD = $_SERVER['REQUEST_METHOD'];
        $requestURI = $_SERVER['REQUEST_URI'];

        try {
            $LOdispatcher = new Dispatcher($this->routeManager->getData());
            $this->_information = $LOdispatcher->dispatch($requestMETHOD, $requestURI);
        } catch (HttpRouteNotFoundException  $e) {
            $this->_information = ["info" => "Route Doesnt exist"];
        } catch (HttpMethodNotAllowedException $e) {
            $this->_information = ["Info" => "Method isn't allowed"];
        }
    }

    function _GetInformation()
    {
        return $this->_information;
    }


    function _GetMechanic(): RouteCollector
    {
        return $this->routeManager;
    }
}

/*
 * Route builder
 */


abstract class RouteBuilder implements _SingleRoute, _Route
{

    public const GET = 'get';
    public const POST = 'post';


    private string $_method;
    private string $_path;

    private bool $_isActive = false;

    private _Output $_element;

    private _ParamtereFetch $_fullParameters;

    public function __construct( string $method, string $path, _Output $element, _ParamtereFetch $fullParameters)
    {
        $this->_method = $method;
        $this->_path = $path;
        $this->_element = $element;
        $this->_fullParameters = $fullParameters;
    }

    private function namedRequestParameters(array $args)
    {
        $matches = [];

        preg_match_all('/\{(\w*)\}/', $this->_path, $matches);

        $matches =   array_map(function (&$index) {
           return "R_" . preg_replace("/\{|\}/", '', $index);
        }, $matches[0]);

        $data = array_flip($matches);

        $counter = 0;
        foreach ($data as $index => $value) {
            $data[$index] = $args[$counter];
            $counter++;
        }
        return $data;
    }


    function _Action()
    {
        $this->_isActive = true;
        $route = new Parameters();
        $route->_SetParameters($this->namedRequestParameters(func_get_args()));

        $request = new Parameters();
        $request->_SetParameters($_REQUEST);



        $this->_fullParameters->_SetParameters($request->_GetParameters());
        $this->_fullParameters->_SetParameters($route->_GetParameters());
    }

    function _GetSubject()
    {
        return $this->_element->_GetContainer();
    }


    function _IsActive()
    {
        return $this->_isActive;
    }

    function _RouteType(): object
    {
        /*
         * All properties will be lowercase
         */
        return (object)[_Route::Method => strtolower($this->_method),
            _Route::Path => strtolower($this->_path)];
    }
}


/*
 * Parameters implementation
 * */

class Parameters implements _Parameters
{

    protected array $_parameters = [];

    function _GetParameters(): array
    {
        return $this->_parameters;
    }

    function _SetParameters(array $_parameters)
    {
        $this->_parameters = $_parameters;
    }
}

class FullParameters implements _ParamtereFetch
{

    private array $_parameters;

    public function _GetParameter(string $parameter)
    {
        if (array_key_exists($parameter, $this->_GetParameters()))
            return $this->_GetParameters()[$parameter];
        else
            throw new Error("Parameter doesnt exist");
    }


    function _GetParameters(): array
    {

        return array_merge(...$this->_parameters);
    }

    function _SetParameters(array $parameters)
    {
        $this->_parameters[] = $parameters;
    }
}


/*
* Implementation for concentrate class
*/

class NormalRoute extends RouteBuilder
{

    public function __construct( string $method, string $path, _Output $element, _ParamtereFetch $fullParameters)
    {
        parent::__construct( $method, $path, $element, $fullParameters);
    }


    function _SetRoute(_RouteMechanic $routeMechanic)
    {
        /** @var PHRouteMechanic $routeMechanic */
        $routeMechanic->_GetMechanic()->{$this->_RouteType()->{_Route::Method}}($this->_RouteType()->{_Route::Path}, [$this, "_Action"]);
    }
}


class PermissionRoute extends RouteBuilder
{
    private _RouteValidator $_validator;


    public function __construct( string $method, string $path, _Output $element, _RouteValidator $validator, _ParamtereFetch $fullParameters)
    {
        parent::__construct( $method, $path, $element, $fullParameters);
        $this->_validator = $validator;
    }

    function _SetRoute(_RouteMechanic $routeMechanic)
    {
        /** @var PHRouteMechanic $routeMechanic */
        $uniqID = uniqid('FILTERERS:', true);
        $routeMechanic->_GetMechanic()->filter($uniqID, [$this->_validator, "_Execute"]);
        $routeMechanic->_GetMechanic()->{$this->_RouteType()->{_Route::Method}}($this->_RouteType()->{_Route::Path},
            [$this, "_Action"], ["before" => $uniqID]);
    }


}


















