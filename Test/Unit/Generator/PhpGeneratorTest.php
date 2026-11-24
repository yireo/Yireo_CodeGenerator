<?php declare(strict_types=1);

namespace Yireo\CodeGenerator\Test\Unit\Generator;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;
use PHPUnit\Framework\TestCase;
use Yireo\CodeGenerator\Generator\PhpGeneratorFactory;
use Yireo\CodeGenerator\Model\ClassStub;
use Yireo\CodeGenerator\Model\NameValidator;

final class PhpGeneratorTest extends TestCase
{
    public function testPlaceholderArgumentsAreExportedAsLiterals(): void
    {
        $value = "foo'; system('id'); //";
        $phpGenerator = (new PhpGeneratorFactory())->create($this->createClassStub());
        $phpGenerator->addClassMethod('testFoo', '$this->assertSame(?, $value);', [$value]);

        $output = $phpGenerator->output();
        $this->assertStringContainsString('namespace Yireo\\Example\\Test\\Unit;', $output);
        $this->assertStringContainsString('final class FooTest extends TestCase', $output);
        $this->assertStringContainsString(var_export($value, true), $output);
        $this->assertStringNotContainsString("'foo'; system", $output);
    }

    private function createClassStub(): ClassStub
    {
        return new ClassStub(
            $this->createMock(ComponentRegistrar::class),
            $this->createMock(Filesystem::class),
            new NameValidator(),
            'Yireo_Example',
            'Yireo\\Example\\Test\\Unit\\FooTest'
        );
    }
}
