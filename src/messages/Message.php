<?php

declare(strict_types=1);

namespace Gatovel\Mail\messages;

use Gatovel\Mail\attachments\Attachment;
use InvalidArgumentException;

final class Message
{
    private ?string $from = null;

    private ?string $to = null;

    private ?string $subject = null;

    private ?string $text = null;

    private ?string $html = null;

    /** @var Attachment[] */
    private array $attachments = [];

    public function from(string $email): self
    {
        $this->validateEmail($email);

        $this->from = $email;

        return $this;
    }

    public function to(string $email): self
    {
        $this->validateEmail($email);

        $this->to = $email;

        return $this;
    }

    public function subject(string $subject): self
    {
        if (trim($subject) === '') {
            throw new InvalidArgumentException(
                'Email subject cannot be empty.'
            );
        }

        $this->subject = $subject;

        return $this;
    }

    public function text(string $content): self
    {
        $this->text = $content;

        return $this;
    }

    public function html(string $content): self
    {
        $this->html = $content;

        return $this;
    }

    public function attach(
        string $path,
        ?string $name = null
    ): self {
        $this->attachments[] = new Attachment($path, $name);

        return $this;
    }

    public function getFrom(): ?string
    {
        return $this->from;
    }

    public function getTo(): ?string
    {
        return $this->to;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function getHtml(): ?string
    {
        return $this->html;
    }

    /**
     * @return Attachment[]
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    private function validateEmail(string $email): void
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException(
                'Invalid email address: ' . $email
            );
        }
    }
}
