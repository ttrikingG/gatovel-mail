<?php

declare(strict_types=1);

namespace Gatovel\Mail\attachments;

use InvalidArgumentException;

final class Attachment
{
    private readonly string $path;

    private readonly string $name;

    public function __construct(
        string $path,
        ?string $name = null
    ) {
        if (!is_file($path)) {
            throw new InvalidArgumentException(
                'Attachment file does not exist: ' . $path
            );
        }

        if (!is_readable($path)) {
            throw new InvalidArgumentException(
                'Attachment file is not readable: ' . $path
            );
        }

        $this->path = $path;

        $this->name = $name ?? basename($path);

        if (trim($this->name) === '') {
            throw new InvalidArgumentException(
                'Attachment name cannot be empty.'
            );
        }

        if (
            str_contains($this->name, "\r")
            || str_contains($this->name, "\n")
        ) {
            throw new InvalidArgumentException(
                'Attachment name contains invalid characters.'
            );
        }
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
