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

class ParserStatus
{
    protected $ret = null;

    protected ScannerState $scannerState;

    protected int $status;

    protected int $syntaxErrorLength;

    protected ?string $syntaxError = null;

    protected ScannerToken $token;

    public function __construct(
        ScannerState $scannerState,
        ScannerToken $token,
        int $status
    ) {
        $this->scannerState = $scannerState;
        $this->token = $token;
        $this->status = $status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }
}
