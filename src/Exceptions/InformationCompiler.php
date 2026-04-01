<?php

declare(strict_types=1);

namespace Medas\Logging\Exceptions;

use Medas\Logging\InformationCompilers\ExceptionCompiler;

/**
 * @deprecated Use ExceptionCompiler or RequestCompiler from InformationCompilers\ instead.
 */
readonly class InformationCompiler extends ExceptionCompiler
{
}
