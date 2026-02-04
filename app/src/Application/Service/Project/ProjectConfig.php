<?php

namespace Application\Service\Project;

class ProjectConfig
{
    public function __construct(
        public readonly int $pid, 
        public readonly bool $indexEnabled = false,
        public readonly string $formDenyList = ""
    ){}

    /**
     * Get deny list as array
     */
    public function getFormDenyListAsArray(): array {
        $wildcardList = [];

        $denylist = $this->formDenyList;
        if (strlen($denylist) > 0){
            $wildcardList = preg_split('/\s*,\s*/', trim($denylist)); 
        }

        return $wildcardList;
    }
}