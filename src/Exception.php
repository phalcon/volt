<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Volt;

class Exception extends \Exception
{
    /** @var array<mixed> */
    protected array $statement = [];

    /**
     * @param array<mixed> $statement
     */
    public function __construct(
        string $message = '',
        array $statement = [],
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        $this->statement = $statement;

        parent::__construct($message, $code, $previous);
    }

    /** @return array<mixed> */
    public function getStatement(): array
    {
        return $this->statement;
    }
}
