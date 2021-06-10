<?php

require_once(__DIR__."/app/bootstrap.php");

use Controllers\AppController as AppController;
use REDCap as REDCap;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;

$request  = Request::createFromGlobals();
$response = new Response();

$response->setContent("Study Metadata Search");

/*
  Future Note:  Should use Response->Send method to reply to the browser.  
  
  Section required as is because REDCap header and footer PHP file do not 
  properly buffer content.  As a result, the require_once must be used. 
*/

$chromeless = $request->query->getBoolean("chromeless", false);
$pid        = $request->query->getInt("pid", -1);

if (!$chromeless){
    if ($pid > 0){
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

