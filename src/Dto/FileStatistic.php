<?php
declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Dto;

final readonly class FileStatistic
{
    public function __construct(
        private string $path,
        private ProcessStatistic $statistic,
    ) {
        $statistic->markFileVisit($path);
    }

    public function getRegistrationCount(): int
    {
        return $this->statistic->getFileRegistrationCount($this->path);
    }

    public function tickRegistration(): void
    {
        $this->statistic->tickRegistration($this->path);
    }
}
