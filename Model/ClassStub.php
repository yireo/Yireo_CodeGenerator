<?php declare(strict_types=1);

namespace Yireo\CodeGenerator\Model;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

class ClassStub
{
    private readonly string $fullQualifiedClassName;

    public function __construct(
        private readonly ComponentRegistrar $componentRegistrar,
        private readonly Filesystem $filesystem,
        NameValidator $nameValidator,
        private readonly string $moduleName,
        string $fullQualifiedClassName
    ) {
        $fullQualifiedClassName = trim($fullQualifiedClassName, '\\ ');
        $nameValidator->validateClassName($moduleName, $fullQualifiedClassName);
        $this->fullQualifiedClassName = $fullQualifiedClassName;
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function getFullQualifiedClassName(): string
    {
        return $this->fullQualifiedClassName;
    }

    public function getModuleClassPrefix(): string
    {
        return str_replace('_', '\\', $this->moduleName);
    }

    public function getRelativeNamespace(): string
    {
        $namespace = $this->getNamespace();
        $modulePrefix = $this->getModuleClassPrefix();
        if ($namespace === $modulePrefix) {
            return '';
        }

        return substr($namespace, strlen($modulePrefix) + 1);
    }

    public function getClassName(): string
    {
        return substr((string)strrchr($this->getFullQualifiedClassName(), '\\'), 1);
    }

    public function getNamespace(): string
    {
        $length = strlen($this->getFullQualifiedClassName()) - (strlen($this->getClassName()) + 1);

        return substr($this->getFullQualifiedClassName(), 0, $length);
    }

    public function getRelativePath(): string
    {
        $path = ltrim($this->getRelativeNamespace().'/'.$this->getClassName().'.php', '/');

        return str_replace('\\', '/', $path);
    }

    public function getAbsolutePath(): string
    {
        return $this->getModulePath().'/'.$this->getRelativePath();
    }

    /**
     * @return ReflectionMethod[]
     * @throws \ReflectionException
     */
    public function getClassMethods(): array
    {
        return $this->getReflection()->getMethods();
    }

    public function getReflection(): ReflectionClass
    {
        return new ReflectionClass($this->getFullQualifiedClassName());
    }

    public function getContents(): string
    {
        $modulePath = realpath($this->getModulePath());
        $filePath = realpath($this->getAbsolutePath());
        if (false === $modulePath || false === $filePath || false === str_starts_with($filePath, $modulePath.'/')) {
            throw new RuntimeException('File for class "'.$this->getFullQualifiedClassName().'" not found in module');
        }

        $reader = $this->filesystem->getDirectoryReadByPath($modulePath);

        return (string)$reader->readFile(substr($filePath, strlen($modulePath) + 1));
    }

    private function getModulePath(): string
    {
        $modulePath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $this->getModuleName());
        if (empty($modulePath)) {
            throw new RuntimeException('No path found for module "'.$this->getModuleName().'"');
        }

        return $modulePath;
    }
}
