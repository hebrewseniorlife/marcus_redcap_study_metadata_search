<?php

use Models\SearchEngineSettings;

interface ISearchEngine
{
    // public function initialize(mixed $config);
    // public function validate(SearchEngineSettings $config);

    public function updateDocument(array $document);
    public function deleteDocument(string $id);
    public function getDocument(string $id);
    
    public function rebuild();
    public function getStats() : array;
    public function search(string $phrase, array $options);

    public static function getSchema(SearchEngineSettings $settings);
    public static function getConfig(SearchEngineSettings $settings);

}
