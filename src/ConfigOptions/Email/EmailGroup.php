<?php

declare(strict_types=1);

namespace Medas\ErrorLog\ConfigOptions\Email;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup};
use Medas\ErrorLog\ConfigOptions\ErrorLogGroup;

#[Service]
readonly class EmailGroup implements ConfigGroup
{
    public function __construct(
        private ErrorLogGroup $group,
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
