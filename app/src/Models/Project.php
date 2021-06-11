<?php

namespace Models;

class Project {
    /**
     * project_id
     *
     * @var int
     */
    public $project_id;
        
    /**
     * title
     *
     * @var string
     */
    public $title;    
    
    /**
     * enable
     *
     * @var boolean
     */
    public $enable;
    
    /**
     * documents
     *
     * @var array
     */
    public $documents = [];
}