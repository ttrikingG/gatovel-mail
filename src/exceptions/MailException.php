<?php

declare(strict_types=1);

namespace Gatovel\Mail\exceptions;

use RuntimeException;
use Throwable;

class MailException extends RuntimeException
{
    public function __construct(
        string $message = 'Mail operation failed.',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
