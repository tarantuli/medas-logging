<?php

declare(strict_types=1);

namespace Medas\ErrorReporting\InformationCompilers;

use Medas\Core\Events\DebugInformationGatherer;

abstract readonly class BaseCompiler
{
    public function __construct(
        protected DebugInformationGatherer $debugInformationGatherer,
    )
    {
    }

    protected function addDebugInformation(string &$output): void
    {
        if (!$this->debugInformationGatherer->events) {
            return;
        }

        $output .= "\n=== DEBUG EVENTS ===\n\n";
        $previousSource = null;

        foreach ($this->debugInformationGatherer->events as $event) {
            if (preg_match('/^(\[.+?]) ?(.+)$/', $event, $matches)) {
                $source = $matches[1];
                $message = $matches[2];
            }
            else {
                $source = null;
                $message = $event;
            }

            if ($source !== $previousSource) {
                $output .= "$source\n";
                $previousSource = $source;
            }

            $output .= "   $message\n";
        }
    }

    protected function addRequestHeaders(string &$output): void
    {
        $headers = [];

        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $header = str_replace('_', '-', substr($name, 5));
                $header = ucwords(strtolower($header), '-');
                $headers[$header] = $value;
            }
        }

        if (!$headers) {
            return;
        }

        $output .= "\n=== REQUEST HEADERS ===\n\n";

        foreach ($headers as $name => $value) {
            $output .= sprintf("%-30s   %s\n", $name, $value);
        }
    }

    protected function addQueryParameters(string &$output): void
    {
        if (empty($_GET)) {
            return;
        }

        $output .= "\n=== QUERY PARAMS ===\n\n";

        foreach ($_GET as $name => $value) {
            $output .= sprintf(
                "%-30s   %s\n",
                $name,
                is_scalar($value) ? $value : json_encode($value)
            );
        }
    }

    protected function addPostBodyInformation(string &$output): void
    {
        $input = file_get_contents('php://input');

        if (!$input) {
            return;
        }

        $output .= "\n=== POST BODY ===\n\n";
        $output .= $input . "\n";
    }

    protected function addServerInformation(string &$output): void
    {
        $output .= "\n=== SERVER ===\n\n";

        foreach ($_SERVER as $name => $value) {
            $output .= sprintf("%-20s   %s\n", $name, is_scalar($value) ? $value : gettype($value));
        }
    }
}
