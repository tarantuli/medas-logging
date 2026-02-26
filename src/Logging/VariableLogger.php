<?php

declare(strict_types=1);

namespace Medas\Logging\Logging;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\Service,
    Interfaces\DirectoryCreator,
    StringMaker
};
use Medas\Logging\{ConfigOptions, Tracing\CallParametersFinder};

#[Service]
readonly class VariableLogger
{
    private StringMaker\Settings $stringMakerSettings;
    private string $fileName;

    public function __construct(
        private CallParametersFinder $callParametersFinder,
        DirectoryCreator             $directoryCreator,

        #[ConfigValue(ConfigOptions\LogDirectory::class)]
        string|null                  $logDirectory,

        #[ConfigValue(ConfigOptions\VariablesLogFileName::class)]
        string|null                  $variablesLogFileName,
    )
    {
        $logDirectory ??= 'var/log';
        $variablesLogFileName ??= 'variables.log';

        $directoryCreator->create($logDirectory);

        $this->stringMakerSettings = new StringMaker\Settings(
            forceUtf8: true,
            alwaysAddClass: true
        );

        $this->fileName = $logDirectory . DIRECTORY_SEPARATOR . $variablesLogFileName;
    }

    public function log(mixed ...$variables): void
    {
        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        $callParameterNames = $this->callParametersFinder->find(
            $caller['file'],
            $caller['line'],
            'log'
        );

        $header = sprintf("%s:%u\n", $caller['file'], $caller['line']);

        file_put_contents($this->fileName, $header, FILE_APPEND);

        foreach ($variables as $i => $variable) {
            $content = $this->getContent(
                $callParameterNames[$i] ?? 'argument ' . ($i + 1),
                $variable
            );

            file_put_contents($this->fileName, $content, FILE_APPEND);
        }
    }

    private function getContent(string $name, mixed $variable): string
    {
        return sprintf("%s  -  %s\n", date('Y-m-d H:i:s'), $name)
            . '   '
            . StringMaker::instance()->fromVariable($variable, $this->stringMakerSettings)
            . "\n\n";
    }
}
