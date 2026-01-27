<?php declare(strict_types=1);

namespace Yireo\CodeGenerator\Scenario;

class ScenarioProvider
{
    public function __construct(
        private array $scenarios = []
    ) {
    }

    public function getScenarios(): array
    {
        return $this->scenarios;
    }
}
