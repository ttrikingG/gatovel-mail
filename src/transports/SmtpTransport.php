<?php

declare(strict_types=1);

namespace Gatovel\Mail\transports;

use Gatovel\Mail\contracts\MailInterface;
use Gatovel\Mail\exceptions\MailException;
use Gatovel\Mail\messages\Message;
use InvalidArgumentException;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

final class SmtpTransport implements MailInterface
{
    public function __construct(
        private readonly string $host,
        private readonly int $port = 587,
        private readonly ?string $username = null,
        private readonly ?string $password = null,
        private readonly string $encryption = 'tls',
        private readonly int $timeout = 30
    ) {
        if (trim($this->host) === '') {
            throw new InvalidArgumentException(
                'SMTP host cannot be empty.'
            );
        }

        if ($this->port < 1 || $this->port > 65535) {
            throw new InvalidArgumentException(
                'Invalid SMTP port.'
            );
        }

        if (!in_array($this->encryption, ['tls', 'ssl', 'none'], true)) {
            throw new InvalidArgumentException(
                'Invalid SMTP encryption.'
            );
        }

        if ($this->timeout < 1) {
            throw new InvalidArgumentException(
                'SMTP timeout must be positive.'
            );
        }

        if (($this->username === null) !== ($this->password === null)) {
            throw new InvalidArgumentException(
                'SMTP username and password must be provided together.'
            );
        }
    }

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

        $mailer = new PHPMailer(true);

        try {
            $mailer->isSMTP();

            $mailer->Host = $this->host;
            $mailer->Port = $this->port;
            $mailer->Timeout = $this->timeout;

            $mailer->SMTPAuth = $this->username !== null;

            if ($mailer->SMTPAuth) {
                $mailer->Username = $this->username;
                $mailer->Password = $this->password;
            }

            $mailer->SMTPSecure = match ($this->encryption) {
                'tls' => PHPMailer::ENCRYPTION_STARTTLS,
                'ssl' => PHPMailer::ENCRYPTION_SMTPS,
                'none' => '',
            };

            $mailer->SMTPAutoTLS = $this->encryption !== 'none';
            $mailer->CharSet = PHPMailer::CHARSET_UTF8;

            $mailer->setFrom($message->getFrom());
            $mailer->addAddress($message->getTo());

            $mailer->Subject = $message->getSubject();

            if ($message->getHtml() !== null) {
                $mailer->isHTML(true);
                $mailer->Body = $message->getHtml();

                if ($message->getText() !== null) {
                    $mailer->AltBody = $message->getText();
                }
            } else {
                $mailer->isHTML(false);
                $mailer->Body = $message->getText();
            }

            foreach ($message->getAttachments() as $attachment) {
                $mailer->addAttachment(
                    $attachment->getPath(),
                    $attachment->getName()
                );
            }

            $mailer->send();
        } catch (PHPMailerException $exception) {
            throw new MailException(
                'SMTP delivery failed: ' . $exception->getMessage(),
                0,
                $exception
            );
        } finally {
            $mailer->smtpClose();
        }
    }
}
