<?php

declare(strict_types=1);

namespace Medas\ErrorReporting\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup};

#[Service]
readonly class ErrorReportingGroup implements ConfigGroup
{
    public function parent(): ConfigGroup|null
    {
        return null;
    }

    public function name(): string
    {
        return 'error-reporting';
    }
}
