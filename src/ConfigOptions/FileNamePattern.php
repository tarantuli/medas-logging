<?php

declare(strict_types=1);

namespace Medas\ErrorReporting\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class FileNamePattern implements ConfigOption
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
        return 'file-name-pattern';
    }

    public function description(): string
    {
        return 'The pattern used to generate the file name. Available placeholders are {dateYmd}, {timeHi} and {message}';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): string
    {
        return '{dateYmd}-{timeHi}_{message}.log';
    }
}
