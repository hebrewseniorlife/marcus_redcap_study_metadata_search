<?php

namespace Infrastructure\Scheduler;

class SchedulerConfig {
    function __construct(        
        public readonly bool $enabled = false,
        public readonly string $pattern = '',
        public readonly array $crons = []
    ){}
}