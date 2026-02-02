<?php

declare(strict_types=1);

namespace Medas\ErrorLog\ConfigOptions\Email;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class Username implements ConfigOption
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
        return 'username';
    }

    public function description(): string
    {
        return 'The username to use for the e-mail server';
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
