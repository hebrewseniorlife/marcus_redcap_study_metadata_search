<?php

namespace SearchEngine\Providers;

use Psr\Log\LoggerInterface;
use SearchEngine\SearchEngineProvider as SearchEngineProvider;
use Document\Document as Document;
use SearchEngine\Providers\ISearchEngine as ISearchEngine;
use TeamTNT\TNTSearch\TNTSearch;

class TNTSearchEngine implements ISearchEngine
{
    const DEFAULT_INDEX_NAME = 'document.index';
    const DEFAULT_PRIMARY_KEY = 'id';
    const DEFAULT_SQL = 'select * from document';
    const DEFAULT_SEARCH_LIMIT = 500;

    /**
     * tntSearch
     *
     * @var TNTSearch
     */
    protected $tntSearch;

    /**
     * logger
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * tntConfig
     *
     * @var mixed
     */
    protected $tntConfig;

    /** initialize
     *
     * @param  SearchEngineProvider $provider
     * @param  LoggerInterface $logger
     * @return void
     */
    public function initialize(SearchEngineProvider $provider, LoggerInterface $logger)
    {
        $this->tntConfig = $this->getConfig($provider);

        $this->tntSearch = new TNTSearch();
        $this->tntSearch->loadConfig($this->tntConfig);
        
        $this->logger = (isset($logger)) ? $logger : \Logging\Log::getLogger();
    }

    /**
     * getIndex
     *
     * @return object|null
     */
    public function getIndex(): ?object
    {
        $path =  $this->tntConfig["storage"].DIRECTORY_SEPARATOR.self::DEFAULT_INDEX_NAME;

        if (file_exists($path)) {
            $this->tntSearch->selectIndex(self::DEFAULT_INDEX_NAME);
            return $this->tntSearch->getIndex();
        }  

        return null;
    }

    /**
    * createIndex
    *
    * @return object
    */
    public function createIndex(): object
    {
        $this->logger->debug("TNT Search Engine: Creating new index at: {$this->tntConfig['storage']}");

        $index = $this->tntSearch->createIndex(self::DEFAULT_INDEX_NAME);
        $index->setPrimaryKey(self::DEFAULT_PRIMARY_KEY);
        $index->query(self::DEFAULT_SQL);
        $index->includePrimaryKey();
        $index->disableOutput = true;
        $index->run();

        return $index;
    }


    // /**
    //  * deleteIndex
    //  *
    //  * @return bool
    //  */
    // public function deleteIndex(): bool
    // {
    //     $path =  $this->tntConfig["storage"].DIRECTORY_SEPARATOR.self::DEFAULT_INDEX_NAME;
    //     $this->logger->debug("TNT Search Engine: Deleting index at: {$path}");
        
    //     // Open the file with 'c+' mode, which opens for read and write.
    //     // The file is created if it does not exist.
    //     if (!$fp = fopen($path, 'c+')) {
    //         $this->logger->debug("TNT Search Engine: Cannot open file ({$path})");
    //     }
    //     else{
    //         // Attempt to get an exclusive lock on the file
    //         if (flock($fp, LOCK_EX | LOCK_NB)) { // LOCK_NB prevents blocking
    //             // Lock acquired, now perform the deletion
    //             unlink($path);
    //             $this->logger->debug("TNT Search Engine: File deleted successfully.");

    //             // Release the lock
    //             flock($fp, LOCK_UN);
    //         } else {
    //             // Lock not acquired, likely due to another process holding the lock
    //             $this->logger->debug("TNT Search Engine: Could not get lock. Another process is using the file.");
    //         }
    //     }

    //     return true;
    // }

    /**

     * insertDocument
     *
     * @param  Document $document
     * @return bool
     */
    public function insertDocument(Document $document): bool
    {
        $index = $this->getIndex();
        if ($index == null) {
           throw new \Exception("TNT Search Engine: Index does not exist.");
        }

        $searchable = $document->toSearchableArray();
        $index->delete($searchable['id']); // Ensure no duplicates
        $index->insert($searchable);
        
        return true;
    }

    /**
     * updateDocument
     *
     * @param  Document $document
     * @return bool
     */
    public function updateDocument(Document $document): bool
    {
        $index = $this->getIndex();
        if ($index == null) {
           throw new \Exception("TNT Search Engine: Index does not exist.");
        }
        $searchable = $document->toSearchableArray();
        $index->update($searchable);
        
        return true;
    }

    /**
     * deleteDocument
     *
     * @param  string $id
     * @return bool
     */
    public function deleteDocument(string $id): bool
    {
        $index = $this->getIndex();
        if ($index == null) {
            throw new \Exception("TNT Search Engine: Index does not exist.");
        }
        $index->delete($id);
        
        return true;
    }

    /** getStats
     *
     * @return array
     */
    public function getStats(): array
    {
        $stats = [
            "status" => "Index does not exist.",
            "count" => 0
        ];

        $index = $this->getIndex();
        if ($index != null) {
           $count = $index->totalDocumentsInCollection();
           $stats = [
                "status" => "Index exists.",
                "count" => $count
            ];
        }

        return $stats;
    }

    /**
     * search
     *
     * @param  string $query
     * @param  array $options
     * @return array
     */
    public function search(string $query, array $options = []): array
    {
        $index = $this->getIndex();
        if ($index == null) {
           return [];
        }
        $this->tntSearch->fuzziness(true);
        
        $limit = isset($options['limit']) ? $options['limit'] : self::DEFAULT_SEARCH_LIMIT; 

        return $this->tntSearch->search($query, $limit);
    }
    
    /**
     * searchBy
     *
     * @param  string $field
     * @param  string $value
     * @return array
     */
    public function searchBy(string $field, string $value): array
    {
        $index = $this->getIndex();
        if ($index == null) {
           return [];
        }

        $query = "{$field}__{$value}"; // Double underscore as separator allows for fielded search

        return $this->tntSearch->searchBoolean($query, self::DEFAULT_SEARCH_LIMIT);
    }

    /**
     * getSchema
     *
     * @param  SearchEngineProvider $settings
     * @return array
     */
    public static function getSchema(SearchEngineProvider $settings)
    {
        // Implement schema retrieval logic here
        return $this->tntSearch->info();
    }

    /**
     * getConfig
     *
     * @param  SearchEngineProvider $provider
     * @return array
     */
    public static function getConfig(SearchEngineProvider $provider)
    {
        $tempFolderPath = $provider->settings['temp_folder_path'] ?? sys_get_temp_dir();
        $config = $provider->settings['config'] ?? [];

        if (!isset($config['driver'])) {
            $config['driver'] = 'sqlite';
        }

        if (!isset($config['storage'])) {
            $config['storage'] = $tempFolderPath.DIRECTORY_SEPARATOR.'tntsearch';
        }

        // Ensure the storage directory exists
        if (!file_exists($config['storage'])) {
            mkdir($config['storage'], 0777, true);
        }   

        $config['stemmer'] = \TeamTNT\TNTSearch\Stemmer\PorterStemmer::class;
        $config['fuzziness'] = true;

        return $config;
    }
}