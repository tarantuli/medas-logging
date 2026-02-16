<?php

declare(strict_types=1);

namespace Medas\Logging\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class LogDirectory implements ConfigOption
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
        return 'log-directory';
    }

    public function description(): string
    {
        return 'The directory where the logs will be saved';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): string
    {
        return 'var/log';
    }
}
