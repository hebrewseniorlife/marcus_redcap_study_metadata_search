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
        $this->template = new TemplateEngine([
            'root'      => $module->getModulePath()."app/resources/templates/",
            'system'    => $module->getModulePath()."app/resources/templates/system",
            'project'   => $module->getModulePath()."app/resources/templates/project"
        ]);        
    }
    
    /**
     * handle
     *
     * @param  mixed $request
     * @param  mixed $reponse
     * @return Response
     */
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
                "public"    => array(
                        "root"      => $this->module->getUrl('public/'),
                        "css"       => $this->module->getUrl('public/css'),
                        "scripts"   => $this->module->getUrl('public/scripts')
                ),
                "module"    => APP_PATH_WEBROOT.'ExternalModules/?prefix='.$_GET["prefix"],
                "search"    => $this->module->getUrl('index.php', $noAuth=false, $useApiEndpoint=true)."&action=search",
                "search_by" => $this->module->getUrl('index.php', $noAuth=false, $useApiEndpoint=true)."&action=search-by",
                "api"       => $this->module->getUrl('public.php', $noAuth=false, $useApiEndpoint=true)
            )     
        );

        return array_merge($context, $additional);
    }
}