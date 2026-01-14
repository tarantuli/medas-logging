<?php

declare(strict_types=1);

namespace Medas\ErrorLog;

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
        DirectoryCreator    $directoryCreator,

        #[ConfigValue(ConfigOptions\LogDirectory::class)]
        private string|null $logDirectory,
    )
    {
        $directoryCreator->create($this->logDirectory);

        $this->stringMakerSettings = new StringMaker\Settings(forceUtf8: true);
    }

    public function dump(mixed ...$variables): void
    {
        $caller = debug_backtrace()[0];
        $code = explode("\n", file_get_contents($caller['file']))[$caller['line'] - 1];

        if (preg_match('/->dump\((.+?)\)/', $code, $matches)) {
            $names = preg_split('/, ?/', $matches[1]);
        }
        else {
            $names = null;
        }

        foreach ($variables as $i => $variable) {
            $this->dumpVariable($variable, $names[$i] ?? 'argument ' . ($i + 1));
        }
    }

    private function dumpVariable(mixed $variable, string $name): void
    {
        $filename = $this->getFileName($name);
        $content = $this->getContent($variable);

        file_put_contents($filename, $content);
    }

    private function getFileName(string $name): string
    {
        return $this->logDirectory . DIRECTORY_SEPARATOR . date('His') . '-' . $name . '.log';
    }

    private function getContent(mixed $variable): string
    {
        return StringMaker::instance()->fromVariable($variable, $this->stringMakerSettings);
    }
}
