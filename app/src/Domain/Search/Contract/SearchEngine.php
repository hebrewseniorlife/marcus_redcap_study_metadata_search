<?php

namespace Domain\Search\Contract;

use Infrastructure\Search\SearchEngineConfig;
use Domain\Document\Document;
use Psr\Log\LoggerInterface;

interface SearchEngine
{
    public function initialize(LoggerInterface $logger, SearchEngineConfig $config);
    
    public function insertDocument(Document $document);
    public function updateDocument(Document $document);
    public function deleteDocument(string $id);
    
    public function getStats() : array;
    public function search(string $term, array $options) : array;
    public function searchBy(string $field, string $value) : array;
    
    public function getSchema() : array;
    public function getConfig() : SearchEngineConfig;
}
