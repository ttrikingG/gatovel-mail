# Gatovel Mail

A modular and independent PHP library for sending emails through SMTP, with support for HTML templates, built-in themes, attachments, and exception handling.

Designed for Gatovel Framework, but compatible with any PHP 8.3+ application.

## Features

- SMTP email delivery powered by PHPMailer
- Plain-text and HTML email messages
- File attachments
- HTML templates with variable substitution
- Automatic HTML escaping for template variables
- Two built-in email themes
- Gatovel theme enabled by default
- Array transport for testing
- Transport abstraction through interfaces
- Dependency injection
- Dedicated mail exception handling
- Independent and modular architecture

## Requirements

- PHP 8.3 or higher
- Composer
- SMTP server access for real email delivery

## Installation

Once published on Packagist, install Gatovel Mail using Composer:

    composer require gatovel/mail

## Quick Start

Create an SMTP transport and send your first email.

~~~php
<?php

require 'vendor/autoload.php';

use Gatovel\Mail\MailManager;
use Gatovel\Mail\messages\Message;
use Gatovel\Mail\transports\SmtpTransport;

$transport = new SmtpTransport(
    host: 'smtp.example.com',
    port: 587,
    username: 'your-username',
    password: 'your-password',
    encryption: 'tls'
);

$mail = new MailManager($transport);

$message = (new Message())
    ->from('sender@example.com')
    ->to('recipient@example.com')
    ->subject('Welcome to Gatovel')
    ->text('Hello from Gatovel Mail!');

$mail->send($message);
~~~

## SMTP Configuration

The SMTP transport supports the following settings:

| Parameter | Description |
|-----------|-------------|
| `host` | SMTP server hostname |
| `port` | SMTP server port |
| `username` | SMTP authentication username |
| `password` | SMTP authentication password |
| `encryption` | `tls`, `ssl`, or `none` |

For secure SMTP connections, use the encryption mode supported by your provider.

Never commit real SMTP credentials to version control.

## HTML Emails

Gatovel Mail supports both HTML content and plain-text alternatives.

~~~php
$message = (new Message())
    ->from('sender@example.com')
    ->to('recipient@example.com')
    ->subject('Welcome')
    ->text('Welcome to our application!')
    ->html('<h1>Welcome!</h1><p>Thanks for joining us.</p>');

$mail->send($message);
~~~

## HTML Templates

Gatovel Mail includes a simple template renderer for reusable email content.

### Creating a Template

Create the file:

`templates/welcome.html`

~~~html
<h2>Hello, {{ name }}!</h2>

<p>Welcome to {{ app }}.</p>

<p>Thank you for joining us!</p>
~~~

### Rendering a Template

~~~php
use Gatovel\Mail\templates\TemplateRenderer;

$renderer = new TemplateRenderer(
    __DIR__ . '/templates'
);

$html = $renderer->render('welcome.html', [
    'name' => 'Carlos',
    'app' => 'Gatovel Framework',
]);
~~~

Template variables are automatically HTML-escaped to help prevent HTML injection.

The template renderer also applies a built-in email theme.

### Sending a Rendered Template

~~~php
$message = (new Message())
    ->from('sender@example.com')
    ->to('recipient@example.com')
    ->subject('Welcome to Gatovel')
    ->text('Welcome to Gatovel Framework!')
    ->html($html);

$mail->send($message);
~~~

## Built-in Themes

Gatovel Mail includes two email themes.

### Gatovel Theme

The Gatovel theme is the default theme.

It features a dark-blue background with green accents.

~~~php
$renderer = new TemplateRenderer(
    __DIR__ . '/templates'
);
~~~

You can also select it explicitly:

~~~php
$renderer = new TemplateRenderer(
    __DIR__ . '/templates',
    theme: 'gatovel'
);
~~~

### Default Theme

The alternative theme provides a clean, light appearance with a white background and dark text.

~~~php
$renderer = new TemplateRenderer(
    __DIR__ . '/templates',
    theme: 'default'
);
~~~

Both themes are built into the package.

## File Attachments

Attach files to outgoing messages.

~~~php
$message = (new Message())
    ->from('sender@example.com')
    ->to('recipient@example.com')
    ->subject('Your Documents')
    ->text('Please find the attached documents.')
    ->attach(__DIR__ . '/documents/invoice.pdf')
    ->attach(
        __DIR__ . '/documents/report.pdf',
        'monthly-report.pdf'
    );

$mail->send($message);
~~~

Attachment files must exist and be readable.

## Array Transport

The Array Transport stores messages in memory instead of sending them through SMTP.

This is useful for testing and development.

~~~php
use Gatovel\Mail\MailManager;
use Gatovel\Mail\messages\Message;
use Gatovel\Mail\transports\ArrayTransport;

$transport = new ArrayTransport();

$mail = new MailManager($transport);

$message = (new Message())
    ->from('sender@example.com')
    ->to('recipient@example.com')
    ->subject('Test Email')
    ->text('This is a test message.');

$mail->send($message);

// Number of stored messages
echo $transport->count();

// Retrieve stored messages
$messages = $transport->messages();

// Clear stored messages
$transport->clear();
~~~

No real email is sent when using ArrayTransport.

## Error Handling

SMTP delivery errors are wrapped in a dedicated `MailException`.

~~~php
use Gatovel\Mail\exceptions\MailException;

try {
    $mail->send($message);
} catch (MailException $exception) {
    echo $exception->getMessage();

    // Retrieve the original exception
    $previous = $exception->getPrevious();
}
~~~

Invalid arguments or configuration values may throw `InvalidArgumentException`.

## Architecture

Gatovel Mail follows a modular architecture.

~~~text
src/
├── MailManager.php
├── attachments/
│   └── Attachment.php
├── contracts/
│   └── MailInterface.php
├── exceptions/
│   └── MailException.php
├── messages/
│   └── Message.php
├── templates/
│   └── TemplateRenderer.php
├── themes/
│   ├── DefaultTheme.php
│   └── GatovelTheme.php
└── transports/
    ├── ArrayTransport.php
    └── SmtpTransport.php
~~~

### Core Components

**MailManager**

Coordinates email delivery through a transport implementing MailInterface.

**Message**

Represents an outgoing email, including recipients, subject, content, and attachments.

**MailInterface**

Defines the contract for email transports.

**SmtpTransport**

Delivers emails using PHPMailer.

**ArrayTransport**

Stores outgoing messages for testing.

**TemplateRenderer**

Renders HTML templates with escaped variables and applies a built-in theme.

**Attachment**

Represents and validates email attachments.

## Testing

Gatovel Mail has been tested with automated checks covering:

- Message creation and validation
- Mail manager behavior
- SMTP transport configuration
- Array transport behavior
- File attachments
- HTML template rendering
- Built-in themes
- Exception handling

SMTP integration has also been tested with Mailtrap.

## Roadmap

The following features are planned for future releases:

- Optional integration with Gatovel Queue
- Asynchronous email delivery
- Additional extensibility options

Queue integration will remain optional to preserve the package's independence.

## Contributing

Contributions, suggestions, and bug reports are welcome.

Please open an issue or submit a pull request through the GitHub repository.

## License

Gatovel Mail is open-source software licensed under the [MIT License](LICENSE).

Copyright (c) 2026 Tom Garcia.

---

Developed as part of the Gatovel ecosystem.
