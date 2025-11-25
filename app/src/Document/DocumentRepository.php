<?php

namespace Document;

use Document\Document as Document;
use Project\Project as Project;
use Psr\Log\LoggerInterface;
use Settings\SettingsHelper as SettingsHelper;
use RedBeanPHP\R as R;
use Services\AbstractService as AbstractService;

/**
 * DocumentRepository
 */
class DocumentRepository extends AbstractService {
    const DEFAULT_DATABASE_NAME = 'documents.sqlite';
    const TYPE = 'document';

    /** @var bool */
    private bool $initialized = false;

    /** @var string */
    private string $dsn = "";

    /**
     * __construct
     *
     * @param  mixed $module
     * @return void
     */
    function __construct($module, LoggerInterface $logger, string $folderPath = null)
    {
        parent::__construct($module, $logger);

        if ($folderPath === null) {
            throw new Exception("Folder path for DocumentRepository cannot be null.");
        }   

        $this->initialize($folderPath);
    }

    /**
     * Creates and returns a new instance of DocumentRepository.
     *
     * @param string $folderPath The path to the folder where the SQLite database will be stored.
     * @param bool $freeze Optional. Whether to freeze the database schema. Default is false.
     * @return DocumentRepository A new instance of DocumentRepository.
     */
    function initialize(string $folderPath, bool $freeze = false) : self {
        // Ensure the storage directory exists
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }   
        
        $this->dsn = "sqlite:".$folderPath.DIRECTORY_SEPARATOR.self::DEFAULT_DATABASE_NAME;        
        $this->logger->info("Document Repository: Initializing with DSN: $dsn");

        // Bootstrap RedBean connection
        R::setup($this->dsn);
        R::freeze($freeze);

        // Ensure connection works
        if (!R::testConnection()) {
            throw new \RuntimeException("Database connection failed.");
        }

        $this->ensureTable();

        return $this;
    }

    /**
     * Gets the Data Source Name (DSN) for the database connection.
     *
     * @return string The DSN string.
     */
    function getDSN(): string {
        return $this->dsn;
    }

    /**
     * Ensures that the required database table exists.
     *
     * This method checks for the existence of the necessary table in the database,
     * and creates it if it does not already exist. It is intended to be called
     * before performing any operations that depend on the table's presence.
     *
     * @return void
     */
    private function ensureTable(): void
    {
        if ($this->initialized) {
            return;
        }

        $bean = R::dispense(self::TYPE);
        $this->fromDomain(new Document(), $bean);

        $id = R::store($bean);
        R::trash($bean);

        $this->initialized = true;
        $this->logger->info("Document Reposiory: Ensured table '".self::TYPE."' exists with ID field.");
    }

    /**
     * Saves the provided Document instance to the repository.
     *
     * @param Document $document The document to be saved.
     * @return int The ID of the saved document.
     */
    public function upsert(Document $document): int
    {
        // Get existing bean if it exists
        $bean = $this->getBean($document) ?? R::dispense(self::TYPE);
        // Map domain object to bean (if any changes)
        $this->fromDomain($document, $bean);
        // Store the bean and update the document ID
        $document->id = (int) R::store($bean);

        return $document->id;
    }

    /**
     * Finds and returns a Document by its unique identifier.
     *
     * @param int $id The unique identifier of the Document to find.
     * @return Document|null The Document instance if found, or null if not found.
     */
    public function find(int $id): ?Document
    {
        $bean = R::load(self::TYPE, $id);
        if (!$bean->id) {
            return null;
        }
        return $this->toDomain($bean);
    }

    /**
     * Counts the total number of documents in the repository.
     *
     * @return int The total count of documents.
     */
    public function count(): int
    {
        $this->ensureTable();
        return (int) R::count(self::TYPE);
    }

    /**
     * Retrieves all documents from the repository.
     *
     * @return Document[] An array of Document instances.
     */
    public function getAll(): array
    {
        $beans = R::findAll(self::TYPE);
        $documents = [];
        foreach ($beans as $bean) {
            $documents[] = $this->toDomain($bean);
        }
        return $documents;
    }

    /**
     * Finds and returns a Document by its unique key.
     *
     * @param string $key The unique key of the Document to find.
     * @return Document|null The Document instance if found, or null if not found.
     */
    public function findByKey(string $key): ?Document
    {
        $beans = R::find(self::TYPE, ' key = ? ', [$key]);
        if (count($beans) === 0) {
            return null;
        }
        // Return the first match
        return $this->toDomain(array_values($beans)[0]);
    }

    /**
     * Finds and returns Documents by their associated project ID.
     *
     * @param int $id The project ID associated with the Documents to find.
     * @return Document[] An array of Document instances associated with the project ID.
     */
    public function findByProject(int $id): array
    {
        $beans = R::findAll(self::TYPE, ' project_id = ? ', [$id]);
        $documents = [];
        foreach ($beans as $bean) {
            $documents[] = $this->toDomain($bean);
        }
        return $documents;
    }

    /**
     * Finds and returns Documents by a specified field and value.
     *
     * @param string $field The field name to search by (e.g., 'name', 'field_type', 'form_name', 'project_id').
     * @param string $value The value to match for the specified field.
     * @return Document[] An array of Document instances that match the specified field and value.
     * @throws InvalidArgumentException If the specified field is not supported for searching.
     */
    public function findAllBy(string $field, string $value): array
    {
        $fields = ['name', 'field_type', 'form_name', 'project_id'];
        if (!in_array($field, $fields)) {
            throw new InvalidArgumentException("Field '$field' is not supported for searching.");
        }

        $beans = R::findAll(self::TYPE, " {$field} = ? ", [$value]);
        $documents = [];
        foreach ($beans as $bean) {
            $documents[] = $this->toDomain($bean);
        }
        return $documents;
    }

    /**
     * Retrieves an existing bean based on the provided Document's ID or key.
     *
     * This method checks if a Document with the same ID or key already exists in the repository.
     * If found, it returns the corresponding RedBeanPHP OODBBean instance; otherwise, it returns null.
     *
     * @param Document $document The Document instance to check for existence.
     * @return \RedBeanPHP\OODBBean|null The existing bean if found, or null if not found.
     */
    public function getBean(Document $document): ?\RedBeanPHP\OODBBean
    {
        if ($document->id > 0) {
            $bean = R::load(self::TYPE, $document->id);
            if ($bean->id) {
                return $bean;
            }
        }

        if (!empty($document->key)) {
            $beans = R::find(self::TYPE, ' key = ? ', [$document->key]);
            if (count($beans) > 0) {
                return array_values($beans)[0];
            }
        }

        return null;
    }


    /**
     * Deletes a document from the repository by its unique identifier.
     *
     * @param int $id The unique identifier of the document to delete.
     * @return bool Returns true if the document was successfully deleted, false otherwise.
     */
    public function delete(int $id): bool
    {
        $bean = R::load(self::TYPE, $id);
        if (!$bean->id) {
            return false;
        }
        R::trash($bean);
        return true;
    }

    /**
     * Deletes all documents from the repository.
     *
     * @return int[] An array of IDs of the deleted documents.
     */
    public function deleteAll(): array
    {
        $this->ensureTable();

        // Retrieve all IDs before wiping the table
        $ids = R::getCol('SELECT id FROM ' . self::TYPE);
        
        // Efficient table wipe
        R::wipe(self::TYPE);

        return $ids;
    }

    /**
     * Converts a RedBeanPHP OODBBean instance to a Document domain object.
     *
     * @param \RedBeanPHP\OODBBean $bean The bean instance to convert.
     * @return Document The corresponding Document domain object.
     */
    private function toDomain(\RedBeanPHP\OODBBean $bean): Document
    {
        $document = new Document();
        $document->id = (int) $bean->id;
        $document->key = $bean->key;
        $document->name = $bean->name;
        $document->entity = $bean->entity;
        $document->label = $bean->label;
        $document->field_type = $bean->field_type;
        $document->field_order = (int) $bean->field_order;
        $document->form_name = $bean->form_name;
        $document->form_title = $bean->form_title;
        $document->project_id = (int) $bean->project_id;
        $document->project_title = $bean->project_title;
        $document->note = $bean->note;
        $document->context = json_decode($bean->context, true) ?? [];
        $document->date_created = $bean->date_created;

        return $document;
    }

    private function fromDomain(Document $document, \RedBeanPHP\OODBBean $bean): void
    {
        $bean->key = $document->key;
        $bean->name = $document->name;
        $bean->entity = $document->entity;
        $bean->label = $document->label;
        $bean->field_type = $document->field_type;
        $bean->field_order = $document->field_order;
        $bean->form_name = $document->form_name;
        $bean->form_title = $document->form_title;
        $bean->project_id = $document->project_id;
        $bean->project_title = $document->project_title;
        $bean->note = $document->note;
        $bean->context = json_encode($document->context);
        $bean->date_created = $document->date_created;
    }
}