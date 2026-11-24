<?php declare(strict_types=1);

namespace Yireo\CodeGenerator\Generator;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use RuntimeException;
use Yireo\CodeGenerator\Model\NameValidator;

class ModuleContext
{
    private readonly string $testType;

    public function __construct(
        private readonly ComponentRegistrar $componentRegistrar,
        private readonly WriteFactory $writeFactory,
        private readonly NameValidator $nameValidator,
        private readonly string $moduleName,
        string $testType = 'integration',
    ) {
        $this->nameValidator->validateModuleName($moduleName);
        $this->testType = $this->nameValidator->normalizeTestType($testType);
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function getPath(): string
    {
        $moduleName = $this->getModuleName();
        $modulePath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
        if (empty($modulePath)) {
            throw new RuntimeException('No path found for module "'.$moduleName.'"');
        }

        return $modulePath;
    }

    public function getTestPath(): string
    {
        $relativeTestPath = 'Test/'.$this->testType;
        $moduleDirectory = $this->writeFactory->create($this->getPath());
        if (false === $moduleDirectory->isExist($relativeTestPath)) {
            $moduleDirectory->create($relativeTestPath);
        }

        return $this->getPath().'/'.$relativeTestPath.'/';
    }

    public function getNamespace(): string
    {
        return str_replace('_', '\\', $this->getModuleName());
    }

    public function getTestNamespace(): string
    {
        return $this->getNamespace().'\\Test\\'.$this->testType;
    }

    public function writeTestFile(string $relativeFile, string $contents): void
    {
        $this->nameValidator->validateRelativeFile($relativeFile);
        $this->getTestDirectory()->writeFile($relativeFile, $contents);
    }

    private function getTestDirectory(): WriteInterface
    {
        return $this->writeFactory->create($this->getTestPath());
    }
}
