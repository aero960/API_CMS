<?php

namespace InterfaceListNSC;

/*
 * TODO Name should begin with;
 * NOTE:
 * The interface name should begin with
 */

/*
 * Database management interface
 * You can choose between
 * TODO Database types
 * MongoDB
 * MYSQL
 */

use mysql_xdevapi\Schema;

interface _DatabaseRepository
{
    function _GetDB();
    /*
     * Get type of database*/
    function _GetType();
}


interface _Database
{
    function _GetDB();

    function _ChangeDatabase($_Name);

    function _CloseConnections();
}




//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

/*
 * Singletone Specification
 */

interface _Singleton
{
    static function getInstance();
}

interface _SingletonInitializer extends _Singleton
{
    function _Initialize(object $args);
}

//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
/*
 * Interfaces to manage Routes
 * */

interface _RouteMechanic
{

    function _ExecuteSubject();

    function _GetInformation();

    function _GetMechanic(): object;
}


interface _SingleRoute
{
    function _Action();
}


interface _RouteManager extends _RouteIterator, _Singleton
{
    function _GetDefaultRoute();

    function _ExecuteRoutes();
}


interface _RouteIterator
{
    function _GetActiveRoute();

    function _AppendRoutes(_RouteIterator $routeIterator);
}

interface _Route
{
    const Method = 'method';
    const Path = 'path';

    function _GetSubject();

    function _IsActive();

    function _RouteType(): object;

    function _SetRoute(_RouteMechanic $routeMechanic);

}

//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
/*
 * Route parameters
 */

interface _Parameters
{
    function _GetParameters(): array;

    function _SetParameters(array $parameters);
}

interface _ParamtereFetch extends _Parameters
{
    function _GetParameter(string $parameter);
}

interface _ValidateParameters
{
    function checkValidParameters(): bool;
}


//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
/*
 * Data containers
 */


interface _Output
{
    function _GetContainer(): array;

}


//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
/*
 * Authenticate validation for protected things
 * */

interface _Validator
{
    function _IsValid($value): bool;
}

interface _RouteValidator
{
    function _Execute();

}

//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<


interface _Item
{
    function _GetValue();
}


interface _Schema
{
    function _GetAllItems(): object;
    function _ConnectSchema(string $name,_Schema $schema);
    function _GetConnectedSchema(string $name) : _Schema;
}

interface _DecoratorIdSchema extends _Schema {
    function _GetId() : string;
    function _SetIdName(string $name);
    function _GetSchema();

}


interface _Informator{
    function _GetMSG();
    function _GetStatus();
}

interface _Product
{
    function _GetSchema(): _Schema;
    function _SetSchema(object $data);
    function _GetInformation() : _Informator;
    function _SetInformation(string $message,bool $type);
}


//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
/*
 * Operations
*/

interface _Read
{
    function _Read(): ?_Product;
    public function _SetViewRender(array $option): void;
}

interface _Pager extends _Read
{
    const PAGE = 'page';
    const LIMIT_PER_PAGE = 'limit';

   function _SetOptions(array $options) : void;
}

interface _Update
{
    function _Update(_Product $product): void;
    public function _SetUpdateRender(array $option): void;
}

interface _Delete
{
    function _Delete(_Product $product): void;
}

interface _Create
{
    function _Create(_Product $product): void;
}


//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

/*
 * State fundamental interface
 */

interface _StateMachine
{
    function _SetStat(_State $state);

    function _GetState(): _State;
}


interface _State
{
    function _Action(_StateMachine $instance): bool;
}

//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

/*
 * Plugins managment
 */

interface _Plugin
{
    function _GetPlugin();
}

interface _RoutePlugin extends _Plugin
{
    function _GetPlugin(): _RouteIterator;
}


interface _ExtendPlugin
{
    function _SetPlugin(_Plugin $plugin);
}


//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
/*
 * Implementation facade
 */

interface FacadeEasier
{


    function _Build(): void;
}


//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
/*
 * Implementation Authentication
 */

interface _Authentication
{
    function _GetCurrentUser() : _Schema;
    function _SetCurrentUser(_Schema $schema);
    function _IsAuthenticated() : bool;
    /*
        * Create token for website
        * */
    function _CreateToken(_Schema $schema): string;

    function _CheckAuthenticate();
}

interface _Token{
   function _GetKey();
   function _GetData() : object;
   function _GetExper() : int;
}


//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
/*
 * Fabric implementation
 * */
interface _Fabric{
    function _CreateProduct() : _Product;
}

//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
/*
 * Converter implementation
 * */


interface _Converter {
    function _Convert($value);
}















