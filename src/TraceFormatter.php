<?php

declare(strict_types=1);

namespace Medas\ErrorLog;

use Medas\Core\{Attributes\Service, StringMaker};

#[Service]
readonly class TraceFormatter
{
    public function toString(\Throwable $exception): string
    {
        $output = '';

        foreach (array_reverse($exception->getTrace()) as $trace) {
            if (isset($trace['file'])) {
                $output .= sprintf("%s:%u\n", $trace['file'], $trace['line']);
            }
            else {
                $output .= "[main]\n";
            }

            $output .= sprintf("   %s::%s()\n", $trace['class'] ?? '[main]', $trace['function']);

            foreach ($trace['args'] ?? [] as $index => $argument) {
                $output .= sprintf('    %u: ', $index);
                $type = get_debug_type($argument);

                if (class_exists($type)) {
                    $output .= sprintf("%s[%u]\n", $type, spl_object_id($argument));
                }
                elseif (!is_scalar($argument)) {
                    $output .= sprintf("%s\n", $type);
                }
                elseif (is_string($argument) && mb_detect_encoding($argument, 'UTF-8')) {
                    $output .= sprintf("%s\n", mb_substr($argument, 0, 156));
                }
                else {
                    $output .= sprintf(
                        "%s\n",
                        StringMaker::instance()->forceUtf8((string) $argument)
                    );
                }
            }

            $output .= "\n";
        }

        return $output;
    }
}
