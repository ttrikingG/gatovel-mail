<?php

declare(strict_types=1);

namespace Gatovel\Mail\transports;

use Gatovel\Mail\contracts\MailInterface;
use Gatovel\Mail\messages\Message;
use InvalidArgumentException;

final class ArrayTransport implements MailInterface
{
    /** @var Message[] */
    private array $messages = [];

    public function send(Message $message): void
    {
        if (
            $message->getFrom() === null
            || $message->getTo() === null
            || $message->getSubject() === null
        ) {
            throw new InvalidArgumentException(
                'Email requires sender, recipient and subject.'
            );
        }

        if (
            $message->getText() === null
            && $message->getHtml() === null
        ) {
            throw new InvalidArgumentException(
                'Email requires text or HTML content.'
            );
        }

        $this->messages[] = clone $message;
    }

    public function messages(): array
    {
        return array_map(
            static fn (Message $message): Message => clone $message,
            $this->messages
        );
    }

    public function count(): int
    {
        return count($this->messages);
    }

    public function clear(): void
    {
        $this->messages = [];
    }
}
