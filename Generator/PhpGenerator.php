<?php declare(strict_types=1);

namespace Yireo\CodeGenerator\Generator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;

class PhpGenerator implements PhpGeneratorInterface
{
    public function __construct(
        private readonly ClassType $classType,
        private readonly PhpNamespace $namespace,
        private readonly PhpFile $file
    ) {
    }

    public function getClassType(): ClassType
    {
        return $this->classType;
    }

    public function getNamespace(): PhpNamespace
    {
        return $this->namespace;
    }

    public function addClassMethod(
        string $methodName,
        string $methodBody,
        ?array $arguments = null,
    ): void {
        $this->getClassType()->addMethod($methodName)
            ->setFinal()
            ->setReturnType('void')
            ->setPublic()
            ->setBody($methodBody, $arguments);
    }

    public function addTrait(string $traitName): void
    {
        $this->getClassType()->addTrait($traitName);
        $this->addUse($traitName);
    }

    public function addUse(string $namespace, ?string $alias = null): void
    {
        $this->getNamespace()->addUse($namespace, $alias);
    }

    public function addConstant(string $name, string $value): void
    {
        $this->getClassType()->addConstant($name, $value);
    }

    public function output(): string
    {
        $this->getNamespace()->add($this->getClassType());
        $this->file->addNamespace($this->getNamespace());
        return (new PsrPrinter)->printFile($this->file);
    }
}
