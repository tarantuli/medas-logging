<?php

declare(strict_types=1);

namespace Medas\ErrorLog;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\Service,
    Events\DebugInformationGatherer,
    Interfaces\DirectoryCreator
};
use Medas\ServiceManager\ErrorHandling\ExceptionHandler;

#[Service]
readonly class ExceptionLogger implements ExceptionHandler
{
    private string $logDirectory;

    public function __construct(
        DirectoryCreator                 $directoryCreator,

        #[ConfigValue(ConfigOptions\LogDirectory::class)]
        string|null                      $logDirectory,
        private DebugInformationGatherer $debugInformationGatherer,
        private TraceFormatter           $traceFormatter,
    )
    {
        $this->logDirectory = $logDirectory ?? 'var/log';

        $directoryCreator->create($this->logDirectory);
    }

    public function handleException(\Throwable $exception): void
    {
        $filename = $this->getFileName($exception);
        $content = $this->getContent($exception);

        file_put_contents($filename, $content);
    }

    private function getFileName(\Throwable $exception): string
    {
        return $this->logDirectory
            . date('Ymd-Hi')
            . '_'
            . mb_substr(preg_replace('/\W+/', '-', strtolower($exception->getMessage())), 0, 32)
            . '.log';
    }

    private function getContent(\Throwable $exception): string
    {
        $output = '';

        $this->addExceptionMessage($output, $exception);
        $this->addTraceInformation($output, $exception);
        $this->addDebugInformation($output);
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
        $output .= $this->traceFormatter->toString($exception);
    }

    private function addDebugInformation(string &$output): void
    {
        if (!$this->debugInformationGatherer->events) {
            return;
        }

        $output .= "\n=== DEBUG EVENTS ===\n\n";

        foreach ($this->debugInformationGatherer->events as $event) {
            $output .= $event . "\n";
        }
    }

    private function addServerInformation(string &$output): void
    {
        $output .= "\n=== SERVER ===\n\n";

        foreach ($_SERVER as $name => $value) {
            $output .= sprintf("%-20s   %s\n", $name, $value);
        }
    }
}
