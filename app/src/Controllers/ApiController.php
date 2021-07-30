<?php

namespace Controllers;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Monolog\Logger;
use Logging\ExternalModuleLogHandler;
use Psr\Log\LoggerInterface;
use Logging\Log;

/**
 * ApiController
 */
class ApiController {    
    protected $module;
    protected $logger; 

    /**
     * __construct
     *
     * @param  mixed $module
     * @return void
     */
    function __construct(object $module, LoggerInterface $logger = null)
    {
        $this->module = $module;

        if ($logger === null)
        {
            $logger = Log::getLogger();
            $logger->pushHandler(new ExternalModuleLogHandler($module, Logger::INFO));
        }

        $this->logger = $logger;
    }
    
    /**
     * handle
     *
     * @param  mixed $request
     * @param  mixed $reponse
     * @return Response
     */
    function handle(Request $request, Response $reponse) : Response
    {
        return $reponse;
    }
    
    /**
     * getNamedKey
     *
     * @param  mixed $key
     * @return array
     */
    public function getNamedKey(string $key = "") : ? array 
    {
        return ApiController::getModuleNamedKey($this->module, $key);
    }
    
    /**
     * getModuleNamedKey
     *
     * @param  mixed $module
     * @param  mixed $key
     * @return array
     */
    public static function getModuleNamedKey(object $module, $key) : ? array
    {
        if (strlen($key) == 0)
        {
            return null;
        }

        $keys   = $module->getSystemSetting("api-key");
        $names  = $module->getSystemSetting("api-name");

        $index  = array_search($key, $keys);
        if ($index === false)
        {
            return null;
        }

        return [
            "name" => $names[$index],
            "key"  => $keys[$index]
        ];
    }
}