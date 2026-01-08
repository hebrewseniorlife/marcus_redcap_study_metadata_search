<?php

namespace Infrastructure\Document;

use Psr\Log\LoggerInterface;
use Domain\Document\Contract\DocumentRepository;
use Infrastructure\Document\Sql\SqlDocumentRepository;
use Infrastructure\Document\DocumentRepositoryCongfig;

class DocumentRepositoryFactory
{
    protected LoggerInterface $logger;

    /**
     * __construct
     *
     * @param  LoggerInterface $logger
     * @return void
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * createDocumentRepository
     *
     * @param  DocumentRepositoryConfig $config
     * @return DocumentRepository
     */
    public function createDocumentRepository(DocumentRepositoryConfig $config): DocumentRepository
    {
        // For now, we only have SQL implementation. This can be extended in the future.
        return new SqlDocumentRepository($this->logger, $config);
    }
}