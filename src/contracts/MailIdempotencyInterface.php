<?php

declare(strict_types=1);

namespace Gatovel\Mail\contracts;

interface MailIdempotencyInterface
{
    /**
     * Check whether an email operation has already been completed.
     */
    public function isCompleted(string $key): bool;

    /**
     * Mark an email operation as completed.
     *
     * Returns true when the key is registered for the first time.
     * Returns false when the key already exists.
     *
     * Implementations must throw an exception on storage failures.
     */
    public function markCompleted(string $key): bool;
}
