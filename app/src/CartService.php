<?php

/**
 * CartService
 */
class CartService {  
    /**
     * module
     *
     * @var mixed
     */
    protected $module;
    
    /**
     * sessionKey
     *
     * @var string
     */
    protected $sessionKey = "";
    
    /**
     * __construct
     *
     * @param  mixed $module
     * @return void
     */
    function __construct($module)
    {
        $this->module       = $module;
        $this->sessionKey   = $module->getPrefix();

        if (!isset($_SESSION[$this->sessionKey]))
        {
            $_SESSION[$this->sessionKey] = [];
        }
    }
    
    /**
     * add
     *
     * @param  mixed $documents
     * @return int
     */
    function add(array $ids) : int
    {
        if(count($ids) > 0){
            $temp = array_merge($_SESSION[$this->sessionKey], $ids);
            $_SESSION[$this->sessionKey] = array_values(array_unique($temp));
        }

        return count($_SESSION[$this->sessionKey]);
    }
    
    /**
     * remove
     *
     * @param  mixed $ids
     * @return integer
     */
    function remove(array $ids) : int
    {
        if(count($ids) > 0){
            array_walk($ids, function($value){
                $index = array_search($value, $_SESSION[$this->sessionKey]);
                if ($index >= 0){
                    array_splice($_SESSION[$this->sessionKey], $index, 1);
                }
            });
        }

        return count($_SESSION[$this->sessionKey]);
    }
    
    /**
     * getAll
     *
     * @return array
     */
    function getAll() : array{
        return $_SESSION[$this->sessionKey];
    }
    
    /**
     * clear
     *
     * @return void
     */
    function clear() {
        $_SESSION[$this->sessionKey] = [];
    }
}