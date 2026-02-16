<?php

declare(strict_types=1);

namespace Medas\Logging\VariableDumper;

use Medas\Core\Attributes\Service;

#[Service]
readonly class CallParameters
{
    public function find(string $file, int $line): array
    {
        $tokens = \PhpToken::tokenize(file_get_contents($file), TOKEN_PARSE);
        $names = [];
        $foundStart = false;
        $depth = 0;
        $stack = '';

        foreach ($tokens as $index => $token) {
            if ($token->line < $line) {
                continue;
            }

            if (!$foundStart && $tokens[$index - 1]->is(T_OBJECT_OPERATOR) && $token->text === 'dump') {
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
