<?php

declare(strict_types=1);

namespace Medas\ErrorLog\ConfigOptions\Email;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class Port implements ConfigOption
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
        return 'port';
    }

    public function description(): string
    {
        return 'The port of the e-mail SMTP server';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): int
    {
        return 465;
    }
}
