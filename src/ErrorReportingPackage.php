<?php

declare(strict_types=1);

namespace Medas\ErrorReporting;

use Medas\Core\{AsSingleton, BasePackage, Interfaces};

class ErrorReportingPackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return [];
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }

    public function initialize(Interfaces\ServiceConfig $config): void
    {
        require_once __DIR__ . '/GlobalFunctions.php';

        parent::initialize($config);
    }
}
