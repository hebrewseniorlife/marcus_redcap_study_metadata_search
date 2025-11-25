<?php

namespace SearchEngine\Providers;

use SearchEngine\SearchEngineProvider;
use Document\Document;
use Psr\Log\LoggerInterface;

interface ISearchEngine
{
    public function initialize(SearchEngineProvider $provider, LoggerInterface $logger);
    
    public function insertDocument(Document $document);
    public function updateDocument(Document $document);
    public function deleteDocument(string $id);
    
    public function getStats() : array;
    public function search(string $term, array $options) : array;
    public function searchBy(string $field, string $value) : array;
    
    public static function getSchema(SearchEngineProvider $provider);
    public static function getConfig(SearchEngineProvider $provider);
}
