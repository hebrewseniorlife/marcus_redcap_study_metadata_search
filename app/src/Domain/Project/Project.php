<?php

namespace Domain\Project;

use Document\Document as Document;

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
     * enabled
     *
     * @var boolean
     */
    public $enabled = true;
    
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
     * forms
     *
     * @var array
     */
    public $forms = [];
    
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