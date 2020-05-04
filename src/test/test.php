<?php

namespace Initialize;

use AuthenticationNSC\Authentication;
use Controller\ControllerRoute;
use  DatabaseNSC\Database;


use DatabaseNSC\MongoDB;
use ElementNSC\Webpage;
use ElementNSC\WebPageWP;
use Helper\helper;
use language\Serverlanguage;
use MongoClient;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use Routes\FullParameters;
use Routes\NormalRoute;
use Routes\Parameters;
use Routes\PermissionRoute;
use Routes\PHRouteMechanic;
use Routes\RouteBuilder;
use Security\RouteValidator;
use function Helper\cors;

/*
 *
 * Use this function to run application
 *
 * */

function codeGoesHere()
{

    //ControllerRoute::getInstance();



    $LOfullParameters = new FullParameters();



    ControllerRoute::getInstance()->_Initialize((object)[
        "List" => [],
        "Mechanic" => new PHRouteMechanic(),
        "DefaultPage" => new NormalRoute(RouteBuilder::GET, "fas", new \view($LOfullParameters), $LOfullParameters)]);


    ControllerRoute::getInstance()->_SetPlugin(new \QuizPlugin($LOfullParameters));


    ControllerRoute::getInstance()->_ExecuteRoutes();

    echo json_encode(ControllerRoute::getInstance()->_GetActiveRoute()->_GetSubject());
}


