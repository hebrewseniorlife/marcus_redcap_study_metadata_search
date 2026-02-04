<?php

namespace Domain\Project\Contract;

use Domain\Project\Project;

interface ProjectRepository
{
    public function getProject(int $pid): array;
    public function getDetails(int $pid): array;
    public function getDataDictionary(int $pid): array;
}