<?php

namespace Services;

/**
 * CartService
 */
class CartService extends AbstractService {  
   
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
    function __construct($module, $logger = null)
    {
        parent::__construct($module, $logger);

        $this->sessionKey = $module->getPrefix();
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
        $cart = $_SESSION[$this->sessionKey];

        if(count($ids) > 0)
        {
            foreach($ids as $id)
            {
                $index = array_search($id, $cart);
                if ($index >= 0){
                    array_splice($cart, $index, 1);
                }                
            }
        }

        $_SESSION[$this->sessionKey] = $cart;

        return count($cart);
    }
        
    /**
     * reorder
     *
     * @param  mixed $changes
     * @return int
     */
    function reorder(array $changes) : int
    {
        $cart = $_SESSION[$this->sessionKey];

        if (count($changes) > 0)
        {
            foreach($changes as $index => $id)
            {
                // Find the ID (change) in the cart
                $existing = array_search($id, $cart);

                // If it exists then remove it first
                if ($existing >= 0){
                    array_splice($cart, $existing, 1);
                }

                // Insert it at the new position
                array_splice($cart, $index, 0, $id);
            }
        }

        $_SESSION[$this->sessionKey] = $cart;

        return count($cart);
    }

    /**
     * getAll
     *
     * @return array
     */
    function getAll() : array
    {
        return $_SESSION[$this->sessionKey];
    }

    /**
     * setAll
     *
     * @return array
     */
    function setAll(array $ids) : array
    {
        return $_SESSION[$this->sessionKey] = $ids;
    }
    
    /**
     * clear
     *
     * @return void
     */
    function clear() 
    {
        $_SESSION[$this->sessionKey] = [];
    }
    
    /**
     * count
     *
     * @return int
     */
    function count() : int
    {
        return count($_SESSION[$this->sessionKey]);
    }
}