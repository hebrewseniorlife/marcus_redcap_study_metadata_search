<?php

use Twig\Environment as Environment;
use Twig\Loader\FilesystemLoader as FilesystemLoader;
use Twig\Extra\String\StringExtension as StringExtension;
use Twig\Extension\DebugExtension as DebugExtension;
use Twig\Loader\ArrayLoader as ArrayLoader;

/**
 * TemplateEngine - Wrapper for interfacing with Twig template engine 
 */
class TemplateEngine{
    protected $environment;
    
    /**
     * __construct
     *
     * @param  mixed $paths
     * @param  mixed $debug
     * @return void
     */
    function __construct($paths = [], bool $debug = false) {
        
        $loader = new FilesystemLoader($paths);
        if ($this->isAssoc($paths)){
            foreach($paths as $key => $path){
                $loader->addPath($path, $key);
            }    
        }

        $this->environment = new Environment($loader,['debug' => $debug]);
        $this->environment->addExtension(new StringExtension());

        if ($debug){
            $this->environment->addExtension(new DebugExtension());
        }
    }
    
    /**
     * render - Creates a formatted string based on the named template and context object provided.
     *
     * @param  mixed $template
     * @param  mixed $context
     * @return string
     */
    public function render(string $template, array $context) : string {
        return $this->environment->render($template, $context);
    } 
    
    /**
     * renderTemplate - Creates a formatted string based on the template (string) and context provided
     *
     * @param  mixed $stringTemplate
     * @param  mixed $context
     * @return string
     */
    public static function renderTemplate(? string $stringTemplate = "", array $context) : string {
        if (empty($stringTemplate)){
            return "";
        }

        $environment = new Environment(new ArrayLoader([]));
        $environment->addExtension(new StringExtension());
        
        $template = $environment->createTemplate($stringTemplate);
        return $template->render($context); 
    }

    public function isAssoc(array $arr = [])
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}