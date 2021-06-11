<?php

namespace Controllers;

use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;
use TemplateEngine as TemplateEngine;

/**
 * AppController
 */
class AppController {   
    protected $module;
    protected $template;

    /**
     * __construct
     *
     * @param  mixed $module
     * @return void
     */
    function __construct(object $module)
    {
        $this->module = $module;
        $this->template = new TemplateEngine($module->getModulePath()."app/resources/templates/");        
    }

    function handle(Request $request, Response $reponse) : Response{
        return $reponse;
    }

    /**
     * createContext
     *
     * @param  mixed $name
     * @param  array $additional
     * @return array
     */
    function createContext(string $name, array $additional = []) : array {
        $context = array(
            "module" => array(
                "name"      => $name,
                "prefix"    => $_GET["prefix"],
                "pid"       => $_GET["pid"]
            ),
            "paths" => array(
                "public"    => $this->module->getUrl('public/'),
                "css"       => $this->module->getUrl('public/css'),
                "scripts"   => $this->module->getUrl('public/scripts'),
                "current"   => $_SERVER['REQUEST_URI'],
                "module"    => APP_PATH_WEBROOT.'ExternalModules/?prefix='.$_GET["prefix"],
                "redcap"    => APP_PATH_WEBROOT
            )       
        );

        return array_merge($context, $additional);
    }
}