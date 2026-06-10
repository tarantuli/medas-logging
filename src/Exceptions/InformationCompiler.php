<?php

declare(strict_types=1);

namespace Medas\ErrorReporting\Exceptions;

use Medas\ErrorReporting\InformationCompilers\ExceptionCompiler;

/**
 * @deprecated Use ExceptionCompiler or RequestCompiler from InformationCompilers\ instead.
 */
readonly class InformationCompiler extends ExceptionCompiler
{
}
