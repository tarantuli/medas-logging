<?php

declare(strict_types=1);

namespace Medas\Logging\Exceptions;

use Medas\Core\{Attributes\Service, Events\DebugInformationGatherer};
use Medas\Logging\Tracing\TraceFormatter;

#[Service]
readonly class InformationCompiler
{
    public function __construct(
        private DebugInformationGatherer $debugInformationGatherer,
        private TraceFormatter           $traceFormatter,
    )
    {
    }

    public function compile(\Throwable $exception): string
    {
        $output = '';

        $this->addExceptionMessage($output, $exception);
        $this->addChainedExceptions($output, $exception);
        $this->addTraceInformation($output, $exception);
        $this->addDebugInformation($output);
        $this->addRequestHeaders($output);
        $this->addQueryParameters($output);
        $this->addPostBodyInformation($output);
        $this->addServerInformation($output);

        return $output;
    }

    private function addExceptionMessage(string &$output, \Throwable $exception): void
    {
        $output .= sprintf(
            "%s\n\n%s:%u\n   %s\n\n",
            $exception::class,
            $exception->getFile(),
            $exception->getLine(),
            $exception->getMessage()
        );
    }

    private function addChainedExceptions(string &$output, \Throwable $exception): void
    {
        $previous = $exception->getPrevious();

        if ($previous === null) {
            return;
        }

        $output .= "\n=== CAUSED BY ===\n\n";

        while ($previous !== null) {
            $output .= sprintf(
                "%s\n\n%s:%u\n   %s\n\n",
                $previous::class,
                $previous->getFile(),
                $previous->getLine(),
                $previous->getMessage()
            );

            $previous = $previous->getPrevious();
        }
    }

    private function addTraceInformation(string &$output, \Throwable $exception): void
    {
        $output .= "\n=== TRACE ===\n\n";
        $output .= $this->traceFormatter->toString($exception->getTrace());
    }

    private function addDebugInformation(string &$output): void
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

    private function addRequestHeaders(string &$output): void
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

    private function addQueryParameters(string &$output): void
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

    private function addPostBodyInformation(string &$output): void
    {
        $input = file_get_contents('php://input');

        if (!$input) {
            return;
        }

        $output .= "\n=== POST BODY ===\n\n";
        $output .= $input . "\n";
    }

    private function addServerInformation(string &$output): void
    {
        $output .= "\n=== SERVER ===\n\n";

        foreach ($_SERVER as $name => $value) {
            $output .= sprintf("%-20s   %s\n", $name, is_scalar($value) ? $value : gettype($value));
        }
    }
}
