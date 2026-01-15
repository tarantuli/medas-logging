<?php

declare(strict_types=1);

namespace Functional;

use Medas\ErrorLog\VariableDumper;
use Medas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

class VariableDumperTest extends TestCase
{
    public function testBasicLog(): void
    {
        $variable = new \stdClass();

        $variable->foo = ['bar', 'lala'];
        $serviceManager = service(ServiceManager::class);
        $dumper = service(VariableDumper::class);
        $fileName = $dumper->dump();

        unlink($fileName);

        $fileName = $dumper->dump($variable->foo[1], $serviceManager);
        $dumpFileContent = file_get_contents($fileName);

        self::assertStringContainsString('$variable->foo[1]', $dumpFileContent);
        self::assertStringContainsString('lala', $dumpFileContent);
        self::assertStringContainsString('Medas\ServiceManager\ServiceManager', $dumpFileContent);
    }
}
