<?php

namespace Models;

use Models\Document;

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
    public $title = "";    
    
    /**
     * enable
     *
     * @var boolean
     */
    public $enable = true;
    
    /**
     * documents
     *
     * @var array
     */
    public $documents = [];
    
    /**
     * form_denylist
     *
     * @var array
     */
    public $form_denylist = [];
    
    /**
     * getDocument
     *
     * @param  string $id
     * @return Document
     */
    public function getDocument(string $id = null) : Document{
        foreach($this->documents as $document){
            if ($document->id == $id){
                return $document;
            }
        }

        return null;
    }
}