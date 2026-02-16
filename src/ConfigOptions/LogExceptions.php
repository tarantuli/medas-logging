<?php

declare(strict_types=1);

namespace Medas\Logging\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class LogExceptions implements ConfigOption
{
    public function __construct(
        private LoggingGroup $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'log-exceptions';
    }

    public function description(): string
    {
        return 'Whether to log exceptions';
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
