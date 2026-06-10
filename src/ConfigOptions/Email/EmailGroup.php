<?php

declare(strict_types=1);

namespace Medas\ErrorReporting\ConfigOptions\Email;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup};
use Medas\ErrorReporting\ConfigOptions\ErrorReportingGroup;

#[Service]
readonly class EmailGroup implements ConfigGroup
{
    public function __construct(
        private ErrorReportingGroup $group,
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
