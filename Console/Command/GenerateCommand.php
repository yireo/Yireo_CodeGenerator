<?php declare(strict_types=1);

namespace Yireo\CodeGenerator\Console\Command;

use InvalidArgumentException;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Component\ComponentRegistrar;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Yireo\CodeGenerator\Model\NameValidator;
use Yireo\CodeGenerator\Scenario\ScenarioProvider;

class GenerateCommand extends Command
{
    public function __construct(
        private readonly ComponentRegistrar $componentRegistrar,
        private readonly AppState $appState,
        private readonly NameValidator $nameValidator,
        private readonly ScenarioProvider $scenarioProvider,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('yireo:code:generate')
            ->setDescription('Generate code for a given module')
            ->addArgument('moduleName', InputArgument::REQUIRED, 'Module name')
            ->addArgument('scenario', InputArgument::REQUIRED, 'Scenario name')
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'Test type (unit or integration)', 'integration')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->appState->getMode() === AppState::MODE_PRODUCTION) {
            $output->writeln('<error>Code generation is not allowed in production mode</error>');
            return Command::FAILURE;
        }

        $moduleName = (string)$input->getArgument('moduleName');
        try {
            $this->nameValidator->validateModuleName($moduleName);
        } catch (InvalidArgumentException) {
            $output->writeln('<error>Invalid module name given as argument</error>');
            return Command::INVALID;
        }

        $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
        if (empty($path) || false === is_dir($path)) {
            $output->writeln('<error>Module name is not registered</error>');
            return Command::INVALID;
        }

        try {
            $this->nameValidator->normalizeTestType((string)$input->getOption('type'));
        } catch (InvalidArgumentException) {
            $output->writeln('<error>Unsupported type</error>');
            return Command::INVALID;
        }

        $scenario = (string)$input->getArgument('scenario');
        if (false === array_key_exists($scenario, $this->scenarioProvider->getScenarios())) {
            $output->writeln('<error>Unknown scenario</error>');
            return Command::INVALID;
        }

        return Command::SUCCESS;
    }
}
