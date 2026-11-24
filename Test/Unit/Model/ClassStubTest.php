<?php declare(strict_types=1);

namespace Yireo\CodeGenerator\Test\Unit\Model;

use InvalidArgumentException;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yireo\CodeGenerator\Model\ClassStub;
use Yireo\CodeGenerator\Model\NameValidator;

final class ClassStubTest extends TestCase
{
    private string $modulePath = '';
    private string $outsidePath = '';

    protected function setUp(): void
    {
        $base = sys_get_temp_dir().'/yireo-code-generator-'.uniqid();
        $this->modulePath = $base.'/module';
        $this->outsidePath = $base.'/outside';
        mkdir($this->modulePath.'/Model', 0777, true);
        mkdir($this->outsidePath, 0777, true);
        file_put_contents($this->modulePath.'/Model/Foo.php', '<?php // foo');
        file_put_contents($this->outsidePath.'/Secret.php', '<?php // secret');
        symlink($this->outsidePath.'/Secret.php', $this->modulePath.'/Model/Evil.php');
    }

    protected function tearDown(): void
    {
        @unlink($this->modulePath.'/Model/Evil.php');
        @unlink($this->modulePath.'/Model/Foo.php');
        @unlink($this->outsidePath.'/Secret.php');
        @rmdir($this->modulePath.'/Model');
        @rmdir($this->modulePath);
        @rmdir($this->outsidePath);
        @rmdir(dirname($this->modulePath));
    }

    public function testNameParts(): void
    {
        $classStub = $this->createClassStub('\\Yireo\\Example\\Model\\Foo');
        $this->assertSame('Yireo\\Example\\Model\\Foo', $classStub->getFullQualifiedClassName());
        $this->assertSame('Foo', $classStub->getClassName());
        $this->assertSame('Yireo\\Example\\Model', $classStub->getNamespace());
        $this->assertSame('Model', $classStub->getRelativeNamespace());
        $this->assertSame('Model/Foo.php', $classStub->getRelativePath());
        $this->assertSame($this->modulePath.'/Model/Foo.php', $classStub->getAbsolutePath());
    }

    public function testClassInModuleRootNamespace(): void
    {
        $classStub = $this->createClassStub('Yireo\\Example\\Foo');
        $this->assertSame('', $classStub->getRelativeNamespace());
        $this->assertSame('Foo.php', $classStub->getRelativePath());
    }

    public function testModulePrefixLaterInNameIsKept(): void
    {
        $classStub = $this->createClassStub('Yireo\\Example\\Yireo\\Example\\Foo');
        $this->assertSame('Yireo\\Example', $classStub->getRelativeNamespace());
    }

    /**
     * @dataProvider rejectedClassNames
     */
    public function testRejectsClassOutsideModule(string $className): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createClassStub($className);
    }

    public static function rejectedClassNames(): array
    {
        return [
            ['Magento\\Framework\\App\\ObjectManager'],
            ['Yireo\\Example\\..\\..\\app\\etc\\env'],
            ['Yireo\\ExampleOther\\Foo'],
        ];
    }

    public function testGetContentsReadsFileInsideModule(): void
    {
        $reader = $this->createMock(ReadInterface::class);
        $reader->expects($this->once())->method('readFile')->with('Model/Foo.php')->willReturn('<?php // foo');

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('getDirectoryReadByPath')
            ->with(realpath($this->modulePath))
            ->willReturn($reader);

        $this->assertSame('<?php // foo', $this->createClassStub('Yireo\\Example\\Model\\Foo', $filesystem)->getContents());
    }

    public function testGetContentsRejectsSymlinkOutsideModule(): void
    {
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->never())->method('getDirectoryReadByPath');

        $this->expectException(RuntimeException::class);
        $this->createClassStub('Yireo\\Example\\Model\\Evil', $filesystem)->getContents();
    }

    public function testGetContentsRejectsMissingFile(): void
    {
        $this->expectException(RuntimeException::class);
        $this->createClassStub('Yireo\\Example\\Model\\Missing')->getContents();
    }

    public function testGetContentsRejectsUnregisteredModule(): void
    {
        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $componentRegistrar->method('getPath')->willReturn(null);
        $classStub = new ClassStub(
            $componentRegistrar,
            $this->createMock(Filesystem::class),
            new NameValidator(),
            'Yireo_Example',
            'Yireo\\Example\\Model\\Foo'
        );

        $this->expectException(RuntimeException::class);
        $classStub->getContents();
    }

    private function createClassStub(string $className, ?Filesystem $filesystem = null): ClassStub
    {
        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $componentRegistrar->method('getPath')
            ->with(ComponentRegistrar::MODULE, 'Yireo_Example')
            ->willReturn($this->modulePath);

        return new ClassStub(
            $componentRegistrar,
            $filesystem ?? $this->createMock(Filesystem::class),
            new NameValidator(),
            'Yireo_Example',
            $className
        );
    }
}
