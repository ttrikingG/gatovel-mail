<?php

declare(strict_types=1);

namespace Gatovel\Mail;

use Gatovel\Mail\contracts\MailInterface;
use Gatovel\Mail\messages\Message;

final class MailManager implements MailInterface
{
    public function __construct(
        private MailInterface $transport
    ) {
    }

    public function send(Message $message): void
    {
        $this->transport->send($message);
    }

    public function setTransport(MailInterface $transport): void
    {
        $this->transport = $transport;
    }

    public function getTransport(): MailInterface
    {
        return $this->transport;
    }
}
