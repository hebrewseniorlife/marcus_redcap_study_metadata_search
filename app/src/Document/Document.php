<?php

namespace Document;

class Document {    
    public function __construct(int $id = 0)
    {
        $this->id = $id;
        $this->key = '';
        $this->context = [];
        $this->date_created = date('Y-m-d H:i:s');
    }
    
    /**
     * id
     *
     * @var int
     */
    public $id;

    /**
     * key
     *
     * @var string
     */
    public $key = '';
        
    /**
     * name
     *
     * @var string
     */
    public $name = '';
    
    /**
     * type
     *
     * @var string
     */
    public $entity = '';
    
    /**
     * label
     *
     * @var string
     */
    public $label = '';
    
    /**
     * field_type
     *
     * @var string
     */
    public $field_type = '';
    
    /**
     * field_order
     *
     * @var int
     */
    public $field_order = 0;

    /**
     * form_name
     *
     * @var string
     */
    public $form_name = '';

    /**
     * form_title
     *
     * @var string
     */
    public $form_title = '';
    
    /**
     * project_id
     *
     * @var int
     */
    public $project_id = 0;
        
    /**
     * project_title
     *
     * @var string
     */
    public $project_title = '';
    
    /**
     *  note
     *
     * @var string
     */
    public $note = '';
    
    /**
     * data
     *
     * @var array
     */
    public $context = [];


    /**
     * date_created
     *
     * @var DateTime
     */
    public $date_created = null;

    /**
     * Converts the Document object to an array representation.
     *
     * @return array The array representation of the Document object.
     */
    public function toArray(): array {
        return get_object_vars($this);
    }
}


