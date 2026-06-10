<?php

declare(strict_types=1);

namespace Medas\ErrorReporting\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class EmailExceptions implements ConfigOption
{
    public function __construct(
        private ErrorReportingGroup $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'e-mail-exceptions';
    }

    public function description(): string
    {
        return 'Whether to e-mail exceptions';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): true
    {
        return true;
    }
}
