<?php

declare(strict_types=1);

namespace Medas\Logging\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class FileNameMessageMaxLength implements ConfigOption
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
        return 'file-name-message-max-length';
    }

    public function description(): string
    {
        return 'Maximum number of characters from the exception message used in the log file name';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): int
    {
        return 32;
    }
}
