<?php declare(strict_types=1);

namespace Yireo\CodeGenerator\Test\Unit\Generator;

use InvalidArgumentException;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Filesystem\File\WriteFactory as FileWriteFactory;
use PHPUnit\Framework\TestCase;
use Yireo\CodeGenerator\Generator\ModuleContext;
use Yireo\CodeGenerator\Model\NameValidator;

final class ModuleContextTest extends TestCase
{
    private string $modulePath = '';

    protected function setUp(): void
    {
        $this->modulePath = sys_get_temp_dir().'/yireo-code-generator-'.uniqid();
        mkdir($this->modulePath, 0777, true);
    }

    protected function tearDown(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->modulePath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->modulePath);
    }

    public function testNamespaces(): void
    {
        $moduleContext = $this->createModuleContext('Yireo_Example', 'unit');
        $this->assertSame('Yireo\\Example', $moduleContext->getNamespace());
        $this->assertSame('Yireo\\Example\\Test\\Unit', $moduleContext->getTestNamespace());
    }

    public function testTestPathFollowsTestType(): void
    {
        $this->assertSame(
            $this->modulePath.'/Test/Unit/',
            $this->createModuleContext('Yireo_Example', 'unit')->getTestPath()
        );
        $this->assertDirectoryExists($this->modulePath.'/Test/Unit');

        $this->assertSame(
            $this->modulePath.'/Test/Integration/',
            $this->createModuleContext('Yireo_Example')->getTestPath()
        );
    }

    public function testWriteTestFile(): void
    {
        $this->createModuleContext('Yireo_Example', 'unit')->writeTestFile('Model/FooTest.php', '<?php // test');
        $this->assertStringEqualsFile($this->modulePath.'/Test/Unit/Model/FooTest.php', '<?php // test');
    }

    /**
     * @dataProvider rejectedFiles
     */
    public function testWriteTestFileRejectsPathsOutsideTestFolder(string $relativeFile): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createModuleContext('Yireo_Example')->writeTestFile($relativeFile, '<?php // evil');
    }

    public static function rejectedFiles(): array
    {
        return [['../../registration.php'], ['/tmp/evil.php'], ['shell.phtml'], ['.htaccess']];
    }

    /**
     * @dataProvider invalidArguments
     */
    public function testRejectsInvalidArguments(string $moduleName, string $testType): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createModuleContext($moduleName, $testType);
    }

    public static function invalidArguments(): array
    {
        return [
            ['Yireo', 'unit'],
            ['Yireo_Example_Foo', 'unit'],
            ['Yireo_Example', 'functional'],
            ['Yireo_Example', 'Unit\\..\\Evil'],
        ];
    }

    private function createModuleContext(string $moduleName, string $testType = 'integration'): ModuleContext
    {
        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $componentRegistrar->method('getPath')->willReturn($this->modulePath);

        $writeFactory = $this->createMock(WriteFactory::class);
        $writeFactory->method('create')->willReturnCallback(
            fn (string $path) => new Write(
                new FileWriteFactory(new DriverPool()),
                new FileDriver(),
                $path,
                0770
            )
        );

        return new ModuleContext($componentRegistrar, $writeFactory, new NameValidator(), $moduleName, $testType);
    }
}
