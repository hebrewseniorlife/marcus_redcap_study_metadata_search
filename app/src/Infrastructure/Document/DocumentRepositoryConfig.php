<?php

namespace Infrastructure\Document;

class DocumentRepositoryConfig
{
    /**
     * __construct
     *
     * @param  string $dsn
     */
    public function __construct(
        public readonly string $tempFolder = '',
        public readonly string $dsn = ''
    ){}
}