<?php

declare(strict_types=1);

namespace Medas\Logging\InformationCompilers;

use Medas\Core\Attributes\Service;

#[Service]
readonly class RequestCompiler extends BaseCompiler
{
    public function compile(string $method, string $uri, int $statusCode): string
    {
        $output = sprintf("%s %s -> %d\n", $method, $uri, $statusCode);

        $this->addDebugInformation($output);
        $this->addRequestHeaders($output);
        $this->addQueryParameters($output);
        $this->addPostBodyInformation($output);
        $this->addServerInformation($output);

        return $output;
    }
}
