<?php

declare(strict_types=1);

namespace Medas\Logging\ConfigOptions\Email;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup};
use Medas\Logging\ConfigOptions\LoggingGroup;

#[Service]
readonly class EmailGroup implements ConfigGroup
{
    public function __construct(
        private LoggingGroup $group,
    )
    {
    }

    public function parent(): ConfigGroup|null
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'e-mail';
    }
}
