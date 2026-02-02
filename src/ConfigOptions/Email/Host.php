<?php

declare(strict_types=1);

namespace Medas\ErrorLog\ConfigOptions\Email;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class Host implements ConfigOption
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
        return 'host';
    }

    public function description(): string
    {
        return 'The host of the e-mail SMTP server';
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
