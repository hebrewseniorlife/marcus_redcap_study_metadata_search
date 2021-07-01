<?php

require_once(__DIR__."/app/bootstrap.php");

use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;

$request  = Request::createFromGlobals();
$response = new Response();

// $view = $request->get("view", "project");
$pid        = $request->query->getInt("pid", -1);
$isProject  = (isset($pid) && $pid > 0);

if ($isProject){
    $controller = new Controllers\ProjectController($module);
    $response = $controller->handle($request, $response);
}
else{
    $controller = new Controllers\SystemController($module);
    $response = $controller->handle($request, $response);
}

$chromeless = $request->query->getBoolean("chromeless", false);

if (!$chromeless){
    if ($isProject){
        require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';
        echo $response->getContent();
        require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';
    }
    else{
        require_once APP_PATH_DOCROOT . 'ControlCenter/header.php';
        echo $response->getContent();
        require_once APP_PATH_DOCROOT . 'ControlCenter/footer.php';
    }
}
else{
    $response->prepare($request);
    $response->send();
}

