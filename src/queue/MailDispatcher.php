<?php

declare(strict_types=1);

namespace Gatovel\Mail\queue;

use Gatovel\Mail\jobs\SendMailJob;
use Gatovel\Mail\messages\Message;
use Gatovel\Mail\serialization\MessageSerializer;
use Gatovel\Queue\contracts\QueueInterface;
use InvalidArgumentException;

final class MailDispatcher
{
    public function __construct(
        private readonly QueueInterface $queue,
        private readonly MessageSerializer $serializer = new MessageSerializer()
    ) {
    }

    public function dispatch(
        Message $message,
        int $delay = 0,
        ?string $idempotencyKey = null
    ): string {
        if ($delay < 0) {
            throw new InvalidArgumentException(
                'Mail job delay cannot be negative.'
            );
        }

        if (
            $idempotencyKey !== null &&
            trim($idempotencyKey) === ''
        ) {
            throw new InvalidArgumentException(
                'Mail idempotency key cannot be empty.'
            );
        }

        $payload = [
            'message' => $this->serializer->serialize($message),
        ];

        if ($idempotencyKey !== null) {
            $payload['idempotency_key'] = $idempotencyKey;
        }

        return $this->queue->push(
            SendMailJob::class,
            $payload,
            $delay
        );
    }
}
