<?php

declare(strict_types=1);

namespace Gatovel\Mail\contracts;

use Gatovel\Mail\messages\Message;

interface MailInterface
{
    public function send(Message $message): void;
}
