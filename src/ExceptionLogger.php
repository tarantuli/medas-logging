<?php

declare(strict_types=1);

namespace Medas\Logging;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\Service,
    Interfaces\BadRequestException,
    Interfaces\DirectoryCreator
};
use Medas\ServiceManager\ErrorHandling\ExceptionHandler;

#[Service]
readonly class ExceptionLogger implements ExceptionHandler
{
    private string $logDirectory;

    public function __construct(
        private ExceptionInformation $exceptionInformation,

        #[ConfigValue(ConfigOptions\LogBadRequests::class)]
        private bool                 $logBadRequests,

        #[ConfigValue(ConfigOptions\LogExceptions::class)]
        private bool                 $logExceptions,

        #[ConfigValue(ConfigOptions\FileNamePattern::class)]
        private string               $fileNamePattern,
        DirectoryCreator             $directoryCreator,

        #[ConfigValue(ConfigOptions\LogDirectory::class)]
        string|null                  $logDirectory,
    )
    {
        $this->logDirectory = $logDirectory ?? 'var/log';

        $directoryCreator->create($this->logDirectory);
    }

    public function handleException(\Throwable $exception): void
    {
        if (!$this->logExceptions) {
            return;
        }

        if (!$this->logBadRequests && $exception instanceof BadRequestException) {
            return;
        }

        $filename = $this->getFileName($exception);
        $content = $this->exceptionInformation->gather($exception);

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

        $fileName = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $this->fileNamePattern
        );

        return $this->logDirectory . DIRECTORY_SEPARATOR . $fileName;
    }
}
