<?php

use Models\SearchEngineSettings;
use Psr\Log\LoggerInterface;

interface ISearchEngine
{
    public function initialize(SearchEngineSettings $settings, LoggerInterface $logger);

    public function updateDocument(array $document);
    public function deleteDocument(string $id);
    public function getDocument(string $id);
    
    public function rebuild();
    public function getStats() : array;
    public function search(string $term, array $options) : array;
    public function searchBy(string $field, string $value) : array;
    
    public static function getSchema(SearchEngineSettings $settings);
    public static function getConfig(SearchEngineSettings $settings);
}
