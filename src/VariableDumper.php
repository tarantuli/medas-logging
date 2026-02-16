<?php

declare(strict_types=1);

namespace Medas\Logging;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\Service,
    Interfaces\DirectoryCreator,
    StringMaker
};

#[Service]
readonly class VariableDumper
{
    private StringMaker\Settings $stringMakerSettings;

    public function __construct(
        private VariableDumper\CallParameters $callParameters,

        #[ConfigValue(ConfigOptions\LogDirectory::class)]
        private string|null                   $logDirectory,
        DirectoryCreator                      $directoryCreator,
    )
    {
        $directoryCreator->create($this->logDirectory);

        $this->stringMakerSettings = new StringMaker\Settings(
            forceUtf8: true,
            alwaysAddClass: true
        );
    }

    public function dump(mixed ...$variables): string
    {
        $caller = debug_backtrace()[0];
        $names = $this->callParameters->find($caller['file'], $caller['line']);
        $fileName = $this->getFileName();
        $header = sprintf("%s:%u\n", $caller['file'], $caller['line']);

        file_put_contents($fileName, $header, FILE_APPEND);

        foreach ($variables as $i => $variable) {
            $content = $this->getContent($names[$i] ?? 'argument ' . ($i + 1), $variable);

            file_put_contents($fileName, $content, FILE_APPEND);
        }

        return $fileName;
    }

    private function getFileName(): string
    {
        return $this->logDirectory . DIRECTORY_SEPARATOR . 'variables.log';
    }

    private function getContent(string $name, mixed $variable): string
    {
        return sprintf("%s  -  %s\n", date('Y-m-d H:i:s'), $name)
            . '   '
            . StringMaker::instance()->fromVariable($variable, $this->stringMakerSettings)
            . "\n\n";
    }
}
