<?php

declare(strict_types=1);

namespace Medas\ErrorReporting\Logging;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\Service,
    Interfaces\BadRequestException,
    Interfaces\DirectoryCreator,
    Interfaces\ExceptionHandler
};
use Medas\ErrorReporting\{ConfigOptions, InformationCompilers\ExceptionCompiler};

#[Service]
readonly class ExceptionLogger implements ExceptionHandler
{
    private string $logDirectory;

    public function __construct(
        private ExceptionCompiler $exceptionCompiler,

        #[ConfigValue(ConfigOptions\LogBadRequests::class)]
        private bool              $logBadRequests,

        #[ConfigValue(ConfigOptions\LogExceptions::class)]
        private bool              $logExceptions,

        #[ConfigValue(ConfigOptions\FileNamePattern::class)]
        private string            $fileNamePattern,

        #[ConfigValue(ConfigOptions\FileNameMessageMaxLength::class)]
        private int               $fileNameMessageMaxLength,
        DirectoryCreator          $directoryCreator,

        #[ConfigValue(ConfigOptions\LogDirectory::class)]
        string|null               $logDirectory,
    )
    {
        $this->logDirectory = $logDirectory ?? 'var/log';

        $directoryCreator->create($this->logDirectory);
    }

    public function handleException(\Throwable $exception): bool
    {
        if (!$this->logExceptions) {
            return false;
        }

        if (!$this->logBadRequests && $exception instanceof BadRequestException) {
            return false;
        }

        $filename = $this->getFileName($exception);
        $content = $this->exceptionCompiler->compile($exception);
        $bytes = file_put_contents($filename, $content);

        return $bytes !== false;
    }

    private function getFileName(\Throwable $exception): string
    {
        $replacements = [
            '{dateYmd}' => date('Ymd'),
            '{timeHi}' => date('Hi'),
            '{message}' => mb_substr(
                (string) preg_replace('/\W+/', '-', strtolower($exception->getMessage())),
                0,
                $this->fileNameMessageMaxLength
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
