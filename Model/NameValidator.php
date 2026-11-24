<?php declare(strict_types=1);

namespace Yireo\CodeGenerator\Model;

use InvalidArgumentException;

class NameValidator
{
    private const MODULE_NAME_PATTERN = '/^[A-Z][A-Za-z0-9]*_[A-Z][A-Za-z0-9]*$/D';
    private const CLASS_NAME_PATTERN = '/^[A-Za-z_][A-Za-z0-9_]*(\\\\[A-Za-z_][A-Za-z0-9_]*)+$/D';
    private const RELATIVE_FILE_PATTERN = '#^[A-Za-z0-9_]+(/[A-Za-z0-9_]+)*\.php$#D';
    private const TEST_TYPES = ['unit' => 'Unit', 'integration' => 'Integration'];

    public function validateModuleName(string $moduleName): void
    {
        if (1 !== preg_match(self::MODULE_NAME_PATTERN, $moduleName)) {
            throw new InvalidArgumentException('Invalid module name "'.$moduleName.'"');
        }
    }

    public function validateClassName(string $moduleName, string $className): void
    {
        $this->validateModuleName($moduleName);

        if (1 !== preg_match(self::CLASS_NAME_PATTERN, $className)) {
            throw new InvalidArgumentException('Invalid class name "'.$className.'"');
        }

        $modulePrefix = str_replace('_', '\\', $moduleName).'\\';
        if (false === str_starts_with($className, $modulePrefix)) {
            throw new InvalidArgumentException(
                'Class "'.$className.'" does not belong to module "'.$moduleName.'"'
            );
        }
    }

    public function validateRelativeFile(string $relativeFile): void
    {
        if (1 !== preg_match(self::RELATIVE_FILE_PATTERN, $relativeFile)) {
            throw new InvalidArgumentException('Invalid file path "'.$relativeFile.'"');
        }
    }

    public function normalizeTestType(string $testType): string
    {
        $testType = strtolower($testType);
        if (false === isset(self::TEST_TYPES[$testType])) {
            throw new InvalidArgumentException('Unsupported test type "'.$testType.'"');
        }

        return self::TEST_TYPES[$testType];
    }
}
