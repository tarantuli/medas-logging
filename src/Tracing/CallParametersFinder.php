<?php

declare(strict_types=1);

namespace Medas\ErrorReporting\Tracing;

use Medas\Core\Attributes\Service;

#[Service]
readonly class CallParametersFinder
{
    public function find(string $file, int $line, string $methodName): array
    {
        $source = file_get_contents($file);

        if ($source === false) {
            return [];
        }

        $tokens = \PhpToken::tokenize($source, TOKEN_PARSE);
        $names = [];
        $foundStart = false;
        $depth = 0;
        $stack = '';

        foreach ($tokens as $index => $token) {
            if ($token->line < $line) {
                continue;
            }

            if (!$foundStart
                    && $index > 0
                    && $tokens[$index - 1]->is(T_OBJECT_OPERATOR)
                    && $token->text === $methodName) {
                $foundStart = true;

                continue;
            }

            if (!$foundStart) {
                continue;
            }

            if ($token->is('(')) {
                ++$depth;

                if ($depth === 1) {
                    continue;
                }
            }

            if ($token->is(')')) {
                --$depth;
            }

            if ($depth === 0) {
                break;
            }

            if ($depth === 1 && $token->is(',')) {
                $names[] = trim($stack);
                $stack = '';

                continue;
            }

            $stack .= $token->text;
        }

        if ($stack) {
            $names[] = trim($stack);
        }

        return $names;
    }
}
