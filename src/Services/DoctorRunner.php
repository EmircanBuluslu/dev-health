<?php

namespace DevHealth\LaravelHealth\Services;

use DevHealth\LaravelHealth\Contracts\CheckInterface;
use DevHealth\LaravelHealth\ValueObjects\Result;

class DoctorRunner
{
    private array $checks = [];

    public function registerCheck(CheckInterface $check): self
    {
        $this->checks[] = $check;
        return $this;
    }

    public function registerChecks(array $checks): self
    {
        foreach ($checks as $check) {
            $this->registerCheck($check);
        }
        return $this;
    }

    public function run(): array
    {
        $allResults = [];

        foreach ($this->checks as $check) {
            $checkName = $check->getName();
            $result = $check->run();

            $results = is_array($result) ? $result : [$result];

            $allResults[$checkName] = [
                'description' => $check->getDescription(),
                'results' => $results,
            ];
        }

        return $allResults;
    }

    public function getChecks(): array
    {
        return $this->checks;
    }
}
