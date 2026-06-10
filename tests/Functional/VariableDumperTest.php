<?php

declare(strict_types=1);

namespace Medas\ErrorReportingTest\Functional;

use Medas\ConfigOptions\OptionController;
use Medas\ErrorReporting\{
    ConfigOptions\LogDirectory,
    ConfigOptions\VariablesLogFileName,
    Logging\VariableLogger
};
use Medas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

class VariableDumperTest extends TestCase
{
    public function testBasicLog(): void
    {
        $fileName = $this->getFileName();
        $variable = new \stdClass();

        $variable->foo = ['bar', 'lala'];
        $serviceManager = service(ServiceManager::class);
        $variableLogger = service(VariableLogger::class);

        if (file_exists($fileName)) {
            unlink($fileName);
        }

        $variableLogger->log($variable->foo[1], $serviceManager);

        $dumpFileContent = file_get_contents($fileName);

        self::assertStringContainsString('$variable->foo[1]', $dumpFileContent);
        self::assertStringContainsString('lala', $dumpFileContent);
        self::assertStringContainsString('Medas\ServiceManager\ServiceManager', $dumpFileContent);
    }

    private function getFileName(): string
    {
        $optionResolver = service(OptionController::class);

        return $optionResolver->getValue(service(LogDirectory::class))
            . DIRECTORY_SEPARATOR
            . $optionResolver->getValue(service(VariablesLogFileName::class));
    }
}
