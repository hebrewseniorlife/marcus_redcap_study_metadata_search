<?php

use Models\SearchEngineSettings;

interface ISearchEngine
{
    // public function initialize(mixed $config);
    // public function validate(mixed $config);

    // public function insert(array $document);
    public function update(array $document);
    // public function delete(string $id);
    // public function clear();

    public function search(string $phrase, mixed $options);

    public static function getSchema(SearchEngineSettings $settings);
    public static function getConfig(SearchEngineSettings $settings);
}
