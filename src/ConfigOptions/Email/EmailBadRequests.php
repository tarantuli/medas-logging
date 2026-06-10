<?php

declare(strict_types=1);

namespace Medas\ErrorReporting\ConfigOptions\Email;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class EmailBadRequests implements ConfigOption
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
        return 'e-mail-bad-requests';
    }

    public function description(): string
    {
        return 'Whether to e-mail bad requests';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): false
    {
        return false;
    }
}
