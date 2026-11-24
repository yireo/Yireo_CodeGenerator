<?php declare(strict_types=1);

namespace Yireo\CodeGenerator\Test\Unit\Model;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Yireo\CodeGenerator\Model\NameValidator;

final class NameValidatorTest extends TestCase
{
    /**
     * @dataProvider validModuleNames
     */
    public function testValidModuleName(string $moduleName): void
    {
        (new NameValidator())->validateModuleName($moduleName);
        $this->addToAssertionCount(1);
    }

    public static function validModuleNames(): array
    {
        return [['Yireo_CodeGenerator'], ['LokiCheckout_Core'], ['Foo2_Bar3']];
    }

    /**
     * @dataProvider invalidModuleNames
     */
    public function testInvalidModuleName(string $moduleName): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new NameValidator())->validateModuleName($moduleName);
    }

    public static function invalidModuleNames(): array
    {
        return [
            [''],
            ['Yireo'],
            ['yireo_CodeGenerator'],
            ['Yireo_Code_Generator'],
            ['Yireo_../../etc'],
            ['Yireo_Foo/Bar'],
            ["Yireo_Foo\n"],
            ['Yireo_Foo Bar'],
        ];
    }

    /**
     * @dataProvider validClassNames
     */
    public function testValidClassName(string $className): void
    {
        (new NameValidator())->validateClassName('Yireo_Example', $className);
        $this->addToAssertionCount(1);
    }

    public static function validClassNames(): array
    {
        return [
            ['Yireo\\Example\\Foo'],
            ['Yireo\\Example\\Model\\Foo_Bar'],
            ['Yireo\\Example\\Test\\Unit\\Model\\FooTest'],
        ];
    }

    /**
     * @dataProvider invalidClassNames
     */
    public function testInvalidClassName(string $className): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new NameValidator())->validateClassName('Yireo_Example', $className);
    }

    public static function invalidClassNames(): array
    {
        return [
            [''],
            ['Foo'],
            ['Yireo\\Example'],
            ['Yireo\\Other\\Foo'],
            ['Yireo\\ExampleOther\\Foo'],
            ['Magento\\Framework\\App\\ObjectManager'],
            ['Yireo\\Example\\..\\..\\app\\etc\\env'],
            ['Yireo\\Example\\Foo/Bar'],
            ['Yireo\\Example\\Foo.php'],
            ['Yireo\\Example\\\\Foo'],
            ['Yireo\\Example\\1Foo'],
            ["Yireo\\Example\\Foo\n"],
        ];
    }

    /**
     * @dataProvider validRelativeFiles
     */
    public function testValidRelativeFile(string $relativeFile): void
    {
        (new NameValidator())->validateRelativeFile($relativeFile);
        $this->addToAssertionCount(1);
    }

    public static function validRelativeFiles(): array
    {
        return [['FooTest.php'], ['Model/FooTest.php'], ['Model/Sub_Dir/FooTest.php']];
    }

    /**
     * @dataProvider invalidRelativeFiles
     */
    public function testInvalidRelativeFile(string $relativeFile): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new NameValidator())->validateRelativeFile($relativeFile);
    }

    public static function invalidRelativeFiles(): array
    {
        return [
            [''],
            ['Foo'],
            ['Foo.phtml'],
            ['Foo.php.txt'],
            ['/etc/Foo.php'],
            ['../Foo.php'],
            ['Model/../../Foo.php'],
            ['Model//Foo.php'],
            ['Model\\Foo.php'],
            ["Foo.php\n"],
            ['.htaccess'],
        ];
    }

    public function testNormalizeTestType(): void
    {
        $nameValidator = new NameValidator();
        $this->assertSame('Unit', $nameValidator->normalizeTestType('unit'));
        $this->assertSame('Integration', $nameValidator->normalizeTestType('Integration'));
    }

    /**
     * @dataProvider invalidTestTypes
     */
    public function testInvalidTestType(string $testType): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new NameValidator())->normalizeTestType($testType);
    }

    public static function invalidTestTypes(): array
    {
        return [[''], ['functional'], ['Unit\\..\\Foo'], ['../unit']];
    }
}
