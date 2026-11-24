<?php declare(strict_types=1);

namespace Yireo\CodeGenerator\Generator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use PHPUnit\Framework\TestCase;
use Yireo\CodeGenerator\Model\ClassStub;

class PhpGeneratorFactory
{
    public function create(ClassStub $classStub): PhpGeneratorInterface
    {
        $className = $classStub->getClassName();
        $classNamespace = $classStub->getNamespace();

        $classType = new ClassType($className);
        $classType->setFinal();
        $classType->setExtends(TestCase::class);

        $namespaceType = new PhpNamespace($classNamespace);
        $namespaceType->add($classType);
        $namespaceType->addUse(TestCase::class);

        $fileType = new PhpFile;
        $fileType->setStrictTypes();

        return new PhpGenerator($classType, $namespaceType, $fileType);
    }
}
