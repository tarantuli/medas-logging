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
        $variable->foo = 'bar';

        $serviceManager = service(ServiceManager::class);

        $dumper = service(VariableDumper::class);

        $dumper->dump($variable, $serviceManager);
        self::assertTrue(true);
    }
}
