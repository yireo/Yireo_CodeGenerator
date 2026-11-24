<?php declare(strict_types=1);

namespace Yireo\CodeGenerator\Test\Unit\Console\Command;

use Magento\Framework\App\State as AppState;
use Magento\Framework\Component\ComponentRegistrar;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Yireo\CodeGenerator\Console\Command\GenerateCommand;
use Yireo\CodeGenerator\Model\NameValidator;
use Yireo\CodeGenerator\Scenario\ScenarioInterface;
use Yireo\CodeGenerator\Scenario\ScenarioProvider;

final class GenerateCommandTest extends TestCase
{
    public function testSuccess(): void
    {
        $commandTester = $this->createCommandTester();
        $result = $commandTester->execute(['moduleName' => 'Yireo_Example', 'scenario' => 'example']);

        $this->assertSame(Command::SUCCESS, $result);
    }

    public function testTypeOption(): void
    {
        $commandTester = $this->createCommandTester();
        $this->assertSame(Command::SUCCESS, $commandTester->execute([
            'moduleName' => 'Yireo_Example',
            'scenario' => 'example',
            '--type' => 'unit',
        ]));
        $this->assertSame(Command::INVALID, $commandTester->execute([
            'moduleName' => 'Yireo_Example',
            'scenario' => 'example',
            '--type' => 'functional',
        ]));
        $this->assertStringContainsString('Unsupported type', $commandTester->getDisplay());
    }

    public function testProductionModeIsRefused(): void
    {
        $commandTester = $this->createCommandTester(AppState::MODE_PRODUCTION);
        $result = $commandTester->execute(['moduleName' => 'Yireo_Example', 'scenario' => 'example']);

        $this->assertSame(Command::FAILURE, $result);
        $this->assertStringContainsString('production mode', $commandTester->getDisplay());
    }

    /**
     * @dataProvider invalidModuleNames
     */
    public function testInvalidModuleName(string $moduleName): void
    {
        $commandTester = $this->createCommandTester();
        $result = $commandTester->execute(['moduleName' => $moduleName, 'scenario' => 'example']);

        $this->assertSame(Command::INVALID, $result);
        $this->assertStringContainsString('Invalid module name', $commandTester->getDisplay());
        $this->assertStringNotContainsString($moduleName, $commandTester->getDisplay());
    }

    public static function invalidModuleNames(): array
    {
        return [['../../etc'], ['Yireo'], ['<info>Yireo_Foo</info>']];
    }

    public function testUnregisteredModule(): void
    {
        $commandTester = $this->createCommandTester(AppState::MODE_DEVELOPER, null);
        $result = $commandTester->execute(['moduleName' => 'Yireo_Unknown', 'scenario' => 'example']);

        $this->assertSame(Command::INVALID, $result);
        $this->assertStringContainsString('not registered', $commandTester->getDisplay());
    }

    public function testUnknownScenario(): void
    {
        $commandTester = $this->createCommandTester();
        $result = $commandTester->execute(['moduleName' => 'Yireo_Example', 'scenario' => 'unknown']);

        $this->assertSame(Command::INVALID, $result);
        $this->assertStringContainsString('Unknown scenario', $commandTester->getDisplay());
    }

    private function createCommandTester(
        string $mode = AppState::MODE_DEVELOPER,
        ?string $modulePath = 'tmp'
    ): CommandTester {
        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $componentRegistrar->method('getPath')->willReturn($modulePath === null ? null : sys_get_temp_dir());

        $appState = $this->createMock(AppState::class);
        $appState->method('getMode')->willReturn($mode);

        $scenarioProvider = new ScenarioProvider([
            'example' => $this->createMock(ScenarioInterface::class),
        ]);

        return new CommandTester(new GenerateCommand(
            $componentRegistrar,
            $appState,
            new NameValidator(),
            $scenarioProvider
        ));
    }
}
