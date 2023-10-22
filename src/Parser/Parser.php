<?php

declare(strict_types=1);

namespace Phalcon\Volt\Parser;

use Phalcon\Volt\Scanner\State;

class Parser
{
    public function __construct(private string $code)
    {
    }

    public function parseView(string $templatePath): array
    {
        if (strlen($this->code) === 0) {
            return [];
        }

        $parserStatus = new Status(new State($this->code));
    }
}
