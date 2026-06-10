<?php

declare(strict_types=1);

namespace Medas\ErrorReporting\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class VariablesLogFileName implements ConfigOption
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
        return 'variables-log-file-name';
    }

    public function description(): string
    {
        return 'The name of the file where logged variables will be saved';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): string
    {
        return 'variables.log';
    }
}
