<?php

declare(strict_types=1);

namespace Medas\ErrorLog;

use Medas\Core\AsSingleton;
use Medas\ServiceManager\{BasePackage, ServiceConfig};

class ErrorLogPackage extends BasePackage
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

    public function initialize(ServiceConfig $config): void
    {
        parent::initialize($config);

        $config->addExceptionHandler(service(CliExceptionPrinter::class));
        $config->addExceptionHandler(service(ExceptionLogger::class));
    }
}
