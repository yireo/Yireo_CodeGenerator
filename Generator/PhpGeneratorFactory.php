<?php declare(strict_types=1);

namespace Yireo\CodeGenerator\Generator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Yireo\CodeGenerator\Model\ClassStub;

class PhpGeneratorFactory
{
    private const TEST_CASE_CLASS = 'PHPUnit\\Framework\\TestCase';

    public function create(ClassStub $classStub): PhpGeneratorInterface
    {
        $className = $classStub->getClassName();
        $classNamespace = $classStub->getNamespace();

        $classType = new ClassType($className);
        $classType->setFinal();
        $classType->setExtends(self::TEST_CASE_CLASS);

        $namespaceType = new PhpNamespace($classNamespace);
        $namespaceType->add($classType);
        $namespaceType->addUse(self::TEST_CASE_CLASS);

        $fileType = new PhpFile;
        $fileType->setStrictTypes();

        return new PhpGenerator($classType, $namespaceType, $fileType);
    }
}
