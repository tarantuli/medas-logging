<?php

declare(strict_types=1);

namespace Medas\ErrorLog\ConfigOptions\Email;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};
use Medas\ErrorLog\ConfigOptions\ErrorLogGroup;

#[Service]
readonly class SubjectPattern implements ConfigOption
{
    public function __construct(
        private ErrorLogGroup $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'subject-pattern';
    }

    public function description(): string
    {
        return 'The pattern used to generate the subject. Available placeholders are {message}, {file-name} and {line-number}';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): string
    {
        return '<{file-name}:{line-number}> {message}';
    }
}
