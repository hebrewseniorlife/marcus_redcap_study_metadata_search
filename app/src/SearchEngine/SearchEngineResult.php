<?php

namespace SearchEngine;

use Document\Document;

class SearchEngineResult {       
    /**
     * name
     *
     * @var Array(Document)
     */
    public $documents = [];
    
    /**
     * __construct
     *
     * @param  Array $documents
     * @return SearchEngineResult
     */
    function __construct(array $documents = [])
    {
        $this->documents = $documents;
    }
}
