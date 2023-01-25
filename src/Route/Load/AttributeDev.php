<?php
/**
 * This is a playground to start working on
 */

namespace Metrol\Route\Load;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class AttributeDev
{
    const METHOD_GET    = 'GET';
    const METHOD_POST   = 'POST';
    const METHOD_DELETE = 'DELETE';
    const METHOD_PUT    = 'PUT';

    public string $name;
    public string $match;
    public string $method;

    public function __construct(string $name = null,
                                string $match = null,
                                string $method = self::METHOD_GET
    )
    {
        $this->name = $name;
        $this->match = $match;
        $this->setMethod($method);
    }

    private function setMethod(string $method): void
    {
        $this->method = strtolower($method);
    }

    public function dump(): string
    {
        return 'Name: ' . $this->name . PHP_EOL .
            'Match: ' . $this->match . PHP_EOL .
            'Method: ' . $this->method . PHP_EOL;
    }
}
