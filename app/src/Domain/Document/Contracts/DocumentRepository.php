<?php

namespace Domain\Document\Contracts;

use Domain\Document\Document;

/**
 * DocumentRepository Interface
 */
interface DocumentRepository {
    /**
     * Saves the provided Document instance to the repository.
     *
     * @param Document $document The document to be saved.
     * @return int The ID of the saved document.
     */
    public function upsert(Document $document): int;

    /**
     * Finds and returns a Document by its unique identifier.
     *
     * @param int $id The unique identifier of the Document to find.
     * @return Document|null The Document instance if found, or null if not found.
     */
    public function find(int $id): ?Document;

    /**
     * Counts the total number of documents in the repository.
     *
     * @return int The total count of documents.
     */
    public function count(): int;

    /**
     * Retrieves all documents from the repository.
     *
     * @return Document[] An array of Document instances.
     */
    public function getAll(): array;

    /**
     * Finds and returns a Document by its unique key.
     *
     * @param string $key The unique key of the Document to find.
     * @return Document|null The Document instance if found, or null if not found.
     */
    public function findByKey(string $key): ?Document;

    /**
     * Finds and returns Documents by their associated project ID.
     *
     * @param int $id The project ID associated with the Documents to find.
     * @return Document[] An array of Document instances associated with the project ID.
     */
    public function findByProject(int $id): array;

    /**
     * Finds and returns Documents by a specified field and value.
     *
     * @param string $field The field name to search by (e.g., 'name', 'field_type', 'form_name', 'project_id').
     * @param string $value The value to match for the specified field.
     * @return Document[] An array of Document instances that match the specified field and value.
     * @throws InvalidArgumentException If the specified field is not supported for searching.
     */
    public function findAllBy(string $field, string $value): array;

    /**
     * Deletes a document from the repository by its unique identifier.
     *
     * @param int $id The unique identifier of the document to delete.
     * @return bool Returns true if the document was successfully deleted, false otherwise.
     */
    public function delete(int $id): bool;

    /**
     * Deletes all documents from the repository.
     *
     * @return int[] An array of IDs of the deleted documents.
     */
    public function deleteAll(): array;
}