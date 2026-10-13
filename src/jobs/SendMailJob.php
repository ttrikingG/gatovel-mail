<?php

declare(strict_types=1);

namespace Gatovel\Mail\jobs;

use Gatovel\Mail\contracts\MailIdempotencyInterface;
use Gatovel\Mail\contracts\MailInterface;
use Gatovel\Mail\serialization\MessageSerializer;
use Gatovel\Queue\contracts\JobInterface;
use InvalidArgumentException;

final class SendMailJob implements JobInterface
{
    public function __construct(
        private readonly MailInterface $mailer,
        private readonly MessageSerializer $serializer = new MessageSerializer(),
        private readonly ?MailIdempotencyInterface $idempotency = null
    ) {
    }

    public function handle(array $payload): void
    {
        $messagePayload = $payload['message'] ?? null;

        if (!is_array($messagePayload)) {
            throw new InvalidArgumentException(
                'Invalid mail job payload: message must be an array.'
            );
        }

        $key = $payload['idempotency_key'] ?? null;

        if (
            $key !== null &&
            (!is_string($key) || trim($key) === '')
        ) {
            throw new InvalidArgumentException(
                'Invalid mail idempotency key.'
            );
        }

        if (
            $key !== null &&
            $this->idempotency !== null &&
            $this->idempotency->isCompleted($key)
        ) {
            return;
        }

        $message = $this->serializer->deserialize(
            $messagePayload
        );

        $this->mailer->send($message);

        if (
            $key !== null &&
            $this->idempotency !== null
        ) {
            $this->idempotency->markCompleted($key);
        }
    }
}
