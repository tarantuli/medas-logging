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
        $this->addTraceInformation($output, $exception);
        $this->addDebugInformation($output);
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
