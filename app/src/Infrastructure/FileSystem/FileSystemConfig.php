<?php

namespace Infrastructure\FileSystem;

class FileSystemConfig {
    function __construct(
        public readonly string $tempFolder = ""
    ){}
}