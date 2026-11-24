<?php declare(strict_types=1);

namespace Yireo\CodeGenerator\Model;

use Magento\Framework\ObjectManagerInterface;

class ClassStubFactory
{
    public function __construct(
        private readonly ObjectManagerInterface $objectManager,
        private readonly NameValidator $nameValidator
    ) {
    }

    public function create(string $moduleName, string $fullQualifiedClassName): ClassStub
    {
        return $this->objectManager->create(ClassStub::class, [
            'moduleName' => $moduleName,
            'fullQualifiedClassName' => $fullQualifiedClassName,
        ]);
    }

    public function createTest(ClassStub $original, string $type = 'Integration'): ClassStub
    {
        $parts = [
            $original->getModuleClassPrefix(),
            'Test',
            $this->nameValidator->normalizeTestType($type),
            $original->getRelativeNamespace(),
            $original->getClassName().'Test',
        ];

        return $this->create(
            $original->getModuleName(),
            implode('\\', array_filter($parts, fn (string $part) => $part !== ''))
        );
    }
}
