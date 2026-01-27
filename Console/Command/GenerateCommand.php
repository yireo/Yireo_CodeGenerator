<?php declare(strict_types=1);

namespace Yireo\CodeGenerator\Console\Command;

use Magento\Framework\App\State as AppState;
use Magento\Framework\Component\ComponentRegistrar;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends Command
{
    public function __construct(
        private ComponentRegistrar $componentRegistrar,
        private AppState $appState,
        $name = null
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
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $moduleName = (string)$input->getArgument('moduleName');
        if (empty($moduleName)) {
            $output->writeln('<error>No module name given as argument</error>');
            return Command::INVALID;
        }

        $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
        if (false === is_dir($path)) {
            $output->writeln('<error>Module name is not registered</error>');
            return Command::INVALID;
        }

        $type = (string)$input->getOption('type');
        if (false === in_array($type, ['unit', 'integration'])) {
            $output->writeln('<error>Unsupported type</error>');
            return Command::INVALID;
        }


        return Command::SUCCESS;
    }
}
