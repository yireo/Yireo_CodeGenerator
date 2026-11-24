<?php declare(strict_types=1);

namespace Yireo\CodeGenerator\Generator;

use Nette\PhpGenerator\ClassType;

interface PhpGeneratorInterface
{
    public function getClassType(): ClassType;
    /**
     * The method body is written into the generated PHP file as-is, so it must be trusted code.
     * Pass external values (user input, AI output) as $arguments to the ? placeholders in the body,
     * so that they are exported as escaped PHP literals.
     *
     * @param string $methodName
     * @param string $methodBody
     * @param array<int|string, mixed>|null $arguments
     * @return void
     */
    public function addClassMethod(string $methodName, string $methodBody, ?array $arguments = null): void;
    public function addTrait(string $traitName): void;
    public function addUse(string $namespace, ?string $alias = null): void;
    public function addConstant(string $name, string $value): void;
    public function output(): string;
}
