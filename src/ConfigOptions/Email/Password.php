<?php

declare(strict_types=1);

namespace Medas\ErrorReporting\ConfigOptions\Email;

use Medas\Core\{
    Attributes\Service,
    Interfaces\ConfigGroup,
    Interfaces\ConfigOption,
    Interfaces\IsSensitive
};

#[Service]
readonly class Password implements ConfigOption, IsSensitive
{
    public function __construct(
        private EmailGroup $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'password';
    }

    public function description(): string
    {
        return 'The password to use for the e-mail server';
    }

    public function hasDefault(): bool
    {
        return false;
    }

    public function default(): null
    {
        return null;
    }
}
