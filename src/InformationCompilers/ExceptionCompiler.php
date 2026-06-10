<?php

declare(strict_types=1);

namespace Medas\ErrorReporting\InformationCompilers;

use Medas\Core\{Attributes\Service, Events\DebugInformationGatherer};
use Medas\ErrorReporting\Tracing\TraceFormatter;

#[Service]
readonly class ExceptionCompiler extends BaseCompiler
{
    public function __construct(
        private TraceFormatter   $traceFormatter,
        DebugInformationGatherer $debugInformationGatherer,
    )
    {
        parent::__construct($debugInformationGatherer);
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
}
