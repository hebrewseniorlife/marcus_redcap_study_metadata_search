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
     * field_order
     *
     * @var int
     */
    public $field_order;

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
     *  note
     *
     * @var string
     */
    public $note;
    
    /**
     * data
     *
     * @var array
     */
    public $context;
}