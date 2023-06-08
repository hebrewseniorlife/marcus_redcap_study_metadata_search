<?php

namespace Controllers;

use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;
use TemplateEngine as TemplateEngine;
use Logging\Log as Log;
use Logging\ExternalModuleLogHandler;

/**
 * AppController
 */
class AppController {   
    protected $module;
    protected $template;
    protected $logger;

    const REDCAP_SCOPE_HEADER = 'REDCap-Scope';
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
            'root'              => $module->getModulePath()."app/resources/templates/",
            'control-center'    => $module->getModulePath()."app/resources/templates/control-center",
            'project'           => $module->getModulePath()."app/resources/templates/project"
        ]);  

        $this->logger = Log::getLogger();  

        $logLevel = $this->module->getSystemSetting('log-level') ?? 0;

        // If logging is not turned off (LEVEL != 0)
        if ($logLevel > 0)
        {
            $this->logger->pushHandler(new ExternalModuleLogHandler($this->module, $logLevel));  
        }
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
                "pid"       => $_GET["pid"],
                "info"      => $this->module->getModuleInfo() 
            ),
            "paths" => array(
                "public"    => array(
                        "root"      => $this->module->getUrl('public/'),
                        "css"       => $this->module->getUrl('public/css'),
                        "scripts"   => $this->module->getUrl('public/scripts')
                ),
                "module"    => APP_PATH_WEBROOT.'ExternalModules/?prefix='.$_GET["prefix"],
                "project"   => $this->module->getUrl('project.php', $noAuth=false, $useApiEndpoint=true),
                "search"    => $this->module->getUrl('project.php', $noAuth=false, $useApiEndpoint=true)."&action=search",
                "search_by" => $this->module->getUrl('project.php', $noAuth=false, $useApiEndpoint=true)."&action=search-by",
                "cart"      => $this->module->getUrl('cart.php', $noAuth=false, $useApiEndpoint=true)
            )     
        );

        return array_merge_recursive($context, $additional);
    }
}