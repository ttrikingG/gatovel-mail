<?php

declare(strict_types=1);

namespace Gatovel\Mail\serialization;

use Gatovel\Mail\messages\Message;
use InvalidArgumentException;

final class MessageSerializer
{
    public function serialize(Message $message): array
    {
        if (
            $message->getFrom() === null
            || $message->getTo() === null
            || $message->getSubject() === null
        ) {
            throw new InvalidArgumentException(
                'Message must have sender, recipient and subject.'
            );
        }

        $attachments = [];

        foreach ($message->getAttachments() as $attachment) {
            $attachments[] = [
                'path' => $attachment->getPath(),
                'name' => $attachment->getName(),
            ];
        }

        return [
            'from' => $message->getFrom(),
            'to' => $message->getTo(),
            'subject' => $message->getSubject(),
            'text' => $message->getText(),
            'html' => $message->getHtml(),
            'attachments' => $attachments,
        ];
    }

    public function deserialize(array $payload): Message
    {
        foreach (['from', 'to', 'subject'] as $field) {
            if (
                !isset($payload[$field])
                || !is_string($payload[$field])
            ) {
                throw new InvalidArgumentException(
                    "Invalid message field: {$field}."
                );
            }
        }

        $message = (new Message())
            ->from($payload['from'])
            ->to($payload['to'])
            ->subject($payload['subject']);

        foreach (['text', 'html'] as $field) {
            $value = $payload[$field] ?? null;

            if ($value !== null && !is_string($value)) {
                throw new InvalidArgumentException(
                    "Invalid message field: {$field}."
                );
            }

            if ($value !== null) {
                $message->{$field}($value);
            }
        }

        $attachments = $payload['attachments'] ?? [];

        if (!is_array($attachments)) {
            throw new InvalidArgumentException(
                'Invalid attachments payload.'
            );
        }

        foreach ($attachments as $attachment) {
            if (
                !is_array($attachment)
                || !isset($attachment['path'])
                || !is_string($attachment['path'])
                || (
                    isset($attachment['name'])
                    && !is_string($attachment['name'])
                )
            ) {
                throw new InvalidArgumentException(
                    'Invalid attachment payload.'
                );
            }

            $message->attach(
                $attachment['path'],
                $attachment['name'] ?? null
            );
        }

        return $message;
    }
}
