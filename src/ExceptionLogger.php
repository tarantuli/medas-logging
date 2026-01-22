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

        #[ConfigValue(ConfigOptions\FileNamePattern::class)]
        private string                   $fileNamePattern,
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
        $replacements = [
            '{dateYmd}' => date('Ymd'),
            '{timeHi}' => date('Hi'),
            '{message}' => mb_substr(
                preg_replace('/\W+/', '-', strtolower($exception->getMessage())),
                0,
                32
            ),
        ];

        return $this->logDirectory
            . DIRECTORY_SEPARATOR

            . str_replace(
                array_keys($replacements),
                array_values($replacements),
                $this->fileNamePattern
            );
    }

    private function getContent(\Throwable $exception): string
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
        $output .= $this->traceFormatter->toString($exception);
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
                $event = $matches[2];
            }
            else {
                $source = null;
            }

            if ($source !== $previousSource) {
                $output .= "$source\n";
                $previousSource = $source;
            }

            $output .= "   $event\n";
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
