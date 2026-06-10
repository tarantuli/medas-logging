<?php

declare(strict_types=1);

namespace Medas\ErrorReporting\Printing;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\Service,
    CaseSensitiveString,
    Interfaces\ExceptionHandler,
    StringMaker
};
use Medas\ErrorReporting\ConfigOptions\TraceArgumentMaxLength;

#[Service]
readonly class CliExceptionPrinter implements ExceptionHandler
{
    public function __construct(
        #[ConfigValue(TraceArgumentMaxLength::class)]
        private int $traceArgumentMaxLength,
    )
    {
    }

    public function handleException(\Throwable $exception): bool
    {
        if (PHP_SAPI !== 'cli') {
            return false;
        }

        $this->printThrowable($exception);

        return true;
    }

    public function printThrowable(\Throwable $exception): void
    {
        foreach (array_reverse($exception->getTrace()) as $trace) {
            $this->printFile($trace);

            $parameters = $this->printMethod($trace);

            foreach ($trace['args'] ?? [] as $index => $argument) {
                $this->printArgument($parameters, $index, $argument);
            }

            printf("\n");
        }

        printf(
            "\n%s:%u [%u]\n%s\n\n",
            $exception->getFile(),
            $exception->getLine(),
            $exception->getCode(),
            $exception->getMessage()
        );
    }

    private function printFile(mixed $trace): void
    {
        if (isset($trace['file'])) {
            printf("%s:%u\n", $trace['file'], $trace['line']);
        }
        else {
            echo "[main]\n";
        }
    }

    private function printMethod(mixed $trace): array|null
    {
        if (isset($trace['class'])) {
            printf("  %s::%s()\n", $trace['class'], $trace['function']);

            try {
                $parameters = new \ReflectionMethod(
                    $trace['class'],
                    $trace['function']
                )->getParameters();
            }
            catch (\ReflectionException) {
                $parameters = null;
            }
        }
        else {
            printf("  %s()\n", $trace['function']);

            $parameters = null;
        }

        return $parameters;
    }

    private function printArgument(array|null $parameters, int|string $index, mixed $argument): void
    {
        printf(
            "    %s: ",
            $parameters && array_key_exists($index, $parameters) ? $parameters[$index]->name : $index
        );

        if (is_array($argument)) {
            $encoded = json_encode($argument);
            $argument = $encoded !== false ? $encoded : 'array (... cannot be serialized ...)';
        }

        $type = get_debug_type($argument);

        if (class_exists($type)) {
            printf("%s[%u]\n", $type, spl_object_id($argument));
        }
        elseif (is_bool($argument) || $argument === null) {
            printf("·%s\n", var_export($argument, true));
        }
        elseif (!is_scalar($argument)) {
            printf("%s\n", $type);
        }
        elseif ($argument === '') {
            printf("« empty string »\n");
        }
        elseif (is_string($argument) && mb_detect_encoding($argument, 'UTF-8')) {
            printf(
                "%s\n",
                new CaseSensitiveString($argument)->truncateToCharLength($this->traceArgumentMaxLength)
            );
        }
        else {
            printf("%s\n", StringMaker::instance()->forceUtf8((string) $argument));
        }
    }
}
