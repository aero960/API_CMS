<?php


namespace Controller;

use InterfaceListNSC\_ExtendPlugin;
use InterfaceListNSC\_Plugin;
use InterfaceListNSC\_Route;
use InterfaceListNSC\_RouteIterator;
use InterfaceListNSC\_RouteManager;
use InterfaceListNSC\_RouteMechanic;
use InterfaceListNSC\_RoutePlugin;
use InterfaceListNSC\_SingletonInitializer;
use Routes\RouteListManagment;

class ControllerRoute implements _RouteManager, _SingletonInitializer, _ExtendPlugin
{
    private _RouteIterator $_routeIterator;
    private _RouteMechanic $_routeMechanic;
    private _Route $_defaultPage;

    private static ?ControllerRoute $_instance = null;


    public static function getInstance(): ControllerRoute
    {
        if (!self::$_instance) {
            self::$_instance = new ControllerRoute();
        }
        return self::$_instance;
    }


    private function _Action()
    {
        /** @var  $index _Route */
        foreach ($this->_routeIterator as $index) {
            $index->_SetRoute($this->_routeMechanic);
        }
    }

    function _GetDefaultRoute(): _Route
    {
        return $this->_defaultPage;
    }

    function _GetActiveRoute(): _Route
    {
        return $this->_routeIterator->_GetActiveRoute() ?? $this->_GetDefaultRoute();
    }

    function _ExecuteRoutes()
    {
        $this->_Action();
        $this->_routeMechanic->_ExecuteSubject();
        return $this->_routeMechanic->_GetInformation();

    }

    function _Initialize(object $args)
    {

        /** @var  $args ->Mechanic */
        $this->_routeIterator = new RouteListManagment($args->List);
        $this->_routeMechanic = $args->Mechanic;
        $this->_defaultPage = $args->DefaultPage;
    }


    function _SetPlugin(_Plugin $plugin)
    {
        if ($plugin instanceof _RoutePlugin) {
            $this->_AppendRoutes($plugin->_GetPlugin());
        }
    }

    function _AppendRoutes(_RouteIterator $routeIterator)
    {
        $this->_routeIterator->_AppendRoutes($routeIterator);
    }
}




