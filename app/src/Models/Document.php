<?php

namespace Models;

class Document {    
    /**
     * id
     *
     * @var mixed
     */
    public $id;
        
    /**
     * name
     *
     * @var string
     */
    public $name;
    
    /**
     * type
     *
     * @var mixed
     */
    public $entity;
    
    /**
     * label
     *
     * @var string
     */
    public $label;
    
    /**
     * field_type
     *
     * @var string
     */
    public $field_type;
    
    /**
     * form_name
     *
     * @var string
     */
    public $form_name;
    
    /**
     * project_id
     *
     * @var int
     */
    public $project_id;
        
    /**
     * project_title
     *
     * @var string
     */
    public $project_title;
    
    /**
     * data
     *
     * @var array
     */
    public $context;
}