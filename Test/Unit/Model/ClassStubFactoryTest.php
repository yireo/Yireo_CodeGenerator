<?php declare(strict_types=1);

namespace Yireo\CodeGenerator\Test\Unit\Model;

use InvalidArgumentException;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;
use Yireo\CodeGenerator\Model\ClassStub;
use Yireo\CodeGenerator\Model\ClassStubFactory;
use Yireo\CodeGenerator\Model\NameValidator;

final class ClassStubFactoryTest extends TestCase
{
    /**
     * @dataProvider classNameProvider
     */
    public function testCreateTest(string $originalClass, string $type, string $expectedTestClass): void
    {
        $factory = new ClassStubFactory($this->createObjectManager(), new NameValidator());
        $testStub = $factory->createTest($this->createClassStub($originalClass), $type);

        $this->assertSame($expectedTestClass, $testStub->getFullQualifiedClassName());
    }

    public static function classNameProvider(): array
    {
        return [
            ['Yireo\\Example\\Model\\Foo', 'integration', 'Yireo\\Example\\Test\\Integration\\Model\\FooTest'],
            ['Yireo\\Example\\Model\\Foo', 'Unit', 'Yireo\\Example\\Test\\Unit\\Model\\FooTest'],
            ['Yireo\\Example\\Foo', 'unit', 'Yireo\\Example\\Test\\Unit\\FooTest'],
        ];
    }

    public function testCreateTestRejectsUnknownType(): void
    {
        $factory = new ClassStubFactory($this->createObjectManager(), new NameValidator());

        $this->expectException(InvalidArgumentException::class);
        $factory->createTest($this->createClassStub('Yireo\\Example\\Model\\Foo'), 'Unit\\..\\Evil');
    }

    private function createObjectManager(): ObjectManagerInterface
    {
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->method('create')->willReturnCallback(
            fn (string $class, array $arguments) => $this->createClassStub($arguments['fullQualifiedClassName'])
        );

        return $objectManager;
    }

    private function createClassStub(string $className): ClassStub
    {
        return new ClassStub(
            $this->createMock(ComponentRegistrar::class),
            $this->createMock(Filesystem::class),
            new NameValidator(),
            'Yireo_Example',
            $className
        );
    }
}
