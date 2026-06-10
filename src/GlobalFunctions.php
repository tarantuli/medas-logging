<?php

declare(strict_types=1);

use Medas\ErrorReporting\Logging\VariableLogger;

if (!function_exists('varlog')) {
    function varlog(mixed ...$values): void
    {
        service(VariableLogger::class)->log(...$values);
    }
}
