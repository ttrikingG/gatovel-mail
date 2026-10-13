# Gatovel Mail

A modular and independent PHP library for sending emails through SMTP, with support for HTML templates, built-in themes, attachments, asynchronous delivery, and optional queue integration.

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
- Optional Gatovel Queue integration
- Asynchronous email delivery
- Delayed email jobs
- Queue retry support
- Optional idempotency for queued emails
- Independent and modular architecture

## Requirements

- PHP 8.3 or higher
- Composer
- SMTP server access for real email delivery
- Gatovel Queue (optional, for asynchronous delivery)

## Installation

Once published on Packagist, install Gatovel Mail using Composer:

```bash
composer require gatovel/mail
```

For asynchronous email delivery, install the optional Queue package:

```bash
composer require gatovel/mail gatovel/queue
```

Gatovel Mail works independently without Gatovel Queue for synchronous email delivery.

## Quick Start

Create an SMTP transport and send your first email.

```php
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
```

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

```php
$message = (new Message())
    ->from('sender@example.com')
    ->to('recipient@example.com')
    ->subject('Welcome')
    ->text('Welcome to our application!')
    ->html('<h1>Welcome!</h1><p>Thanks for joining us.</p>');

$mail->send($message);
```

## HTML Templates

Gatovel Mail includes a simple template renderer for reusable email content.

### Creating a Template

Create the file:

`templates/welcome.html`

```html
<h2>Hello, {{ name }}!</h2>

<p>Welcome to {{ app }}.</p>

<p>Thank you for joining us!</p>
```

### Rendering a Template

```php
use Gatovel\Mail\templates\TemplateRenderer;

$renderer = new TemplateRenderer(
    __DIR__ . '/templates'
);

$html = $renderer->render('welcome.html', [
    'name' => 'Carlos',
    'app' => 'Gatovel Framework',
]);
```

Template variables are automatically HTML-escaped to help prevent HTML injection.

The template renderer also applies a built-in email theme.

### Sending a Rendered Template

```php
$message = (new Message())
    ->from('sender@example.com')
    ->to('recipient@example.com')
    ->subject('Welcome to Gatovel')
    ->text('Welcome to Gatovel Framework!')
    ->html($html);

$mail->send($message);
```

## Built-in Themes

Gatovel Mail includes two email themes.

### Gatovel Theme

The Gatovel theme is the default theme.

It features a dark-blue background with green accents.

```php
$renderer = new TemplateRenderer(
    __DIR__ . '/templates'
);
```

You can also select it explicitly:

```php
$renderer = new TemplateRenderer(
    __DIR__ . '/templates',
    theme: 'gatovel'
);
```

### Default Theme

The alternative theme provides a clean, light appearance with a white background and dark text.

```php
$renderer = new TemplateRenderer(
    __DIR__ . '/templates',
    theme: 'default'
);
```

Both themes are built into the package.

## File Attachments

Attach files to outgoing messages.

```php
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
```

Attachment files must exist and be readable.

For queued emails, attachment files must remain accessible to the worker until the job is processed.

## Array Transport

The Array Transport stores messages in memory instead of sending them through SMTP.

This is useful for testing and development.

```php
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
```

No real email is sent when using ArrayTransport.

## Optional Queue Integration

Gatovel Mail supports asynchronous email delivery through the optional `gatovel/queue` package.

The core SMTP functionality does not require Gatovel Queue.

### Installing Queue Support

```bash
composer require gatovel/mail gatovel/queue
```

### Dispatching an Email

```php
<?php

require 'vendor/autoload.php';

use Gatovel\Mail\messages\Message;
use Gatovel\Mail\queue\MailDispatcher;
use Gatovel\Queue\drivers\DatabaseDriver;

$pdo = new PDO(
    'sqlite:' . __DIR__ . '/queue.sqlite'
);

$queue = new DatabaseDriver($pdo);

$queue->createTable();

$dispatcher = new MailDispatcher($queue);

$message = (new Message())
    ->from('store@example.com')
    ->to('customer@example.com')
    ->subject('Order Confirmation')
    ->text('Your order has been confirmed.');

$jobId = $dispatcher->dispatch($message);
```

Dispatching stores the email job in the queue. It does not send the email immediately.

### Delayed Emails

Schedule an email for later processing by specifying a delay in seconds.

```php
$dispatcher->dispatch(
    message: $message,
    delay: 30
);
```

The job becomes available for processing after the configured delay.

### Processing Queued Emails

A Queue worker processes pending jobs and delivers messages through a configured mail transport.

```php
<?php

require 'vendor/autoload.php';

use Gatovel\Mail\MailManager;
use Gatovel\Mail\jobs\SendMailJob;
use Gatovel\Mail\transports\SmtpTransport;
use Gatovel\Queue\drivers\DatabaseDriver;
use Gatovel\Queue\workers\Worker;

$pdo = new PDO(
    'sqlite:' . __DIR__ . '/queue.sqlite'
);

$queue = new DatabaseDriver($pdo);

$queue->createTable();

$transport = new SmtpTransport(
    host: 'smtp.example.com',
    port: 587,
    username: 'your-username',
    password: 'your-password',
    encryption: 'tls'
);

$mailer = new MailManager($transport);

$worker = new Worker(
    queue: $queue,
    jobFactory: static function (
        string $className
    ) use ($mailer): object {
        if ($className !== SendMailJob::class) {
            throw new RuntimeException(
                'Unsupported job class.'
            );
        }

        return new SendMailJob($mailer);
    }
);

$worker->runOnce();
```

The worker should run in a separate process in production.

### Queue Retry Support

Gatovel Queue supports retrying jobs after temporary failures.

If an SMTP delivery attempt fails, the Queue worker can release the job for another attempt.

The default RetryPolicy supports:

- Maximum of 3 attempts
- Initial retry delay of 5 seconds
- Exponential backoff
- Maximum retry delay of 300 seconds

Retry behavior is controlled by Gatovel Queue.

A successful SMTP delivery followed by a worker failure may result in the same email being delivered again.

### Message Serialization

`MessageSerializer` converts email messages into queue-compatible arrays.

The worker reconstructs the original message before sending it.

Supported message information includes:

- Sender
- Recipient
- Subject
- Plain-text content
- HTML content
- Attachment paths and names

Attachments are serialized by file path, not by file contents.

The application must ensure that attachment files remain available until the worker processes the job.

## Optional Idempotency

Gatovel Mail supports optional idempotency keys for queued email operations.

Idempotency helps suppress repeated deliveries when a previous successful delivery has already been recorded.

### Dispatching with an Idempotency Key

```php
$dispatcher->dispatch(
    message: $message,
    idempotencyKey: 'order-1001-confirmation'
);
```

The idempotency key should uniquely identify a logical email operation.

For example:

- `order-1001-confirmation`
- `order-1001-shipping`
- `user-500-welcome`

Different operations should use different keys.

### Idempotency Contract

Gatovel Mail defines the following interface:

```php
namespace Gatovel\Mail\contracts;

interface MailIdempotencyInterface
{
    public function isCompleted(string $key): bool;

    public function markCompleted(string $key): bool;
}
```

Applications may implement this interface using SQLite, Redis, or another persistent storage mechanism.

### Integrating with Gatovel Queue

Gatovel Queue includes an `IdempotencyStore` implementation backed by SQLite.

An adapter can connect it to Gatovel Mail.

```php
use Gatovel\Mail\contracts\MailIdempotencyInterface;
use Gatovel\Queue\idempotency\IdempotencyStore;

final class QueueIdempotencyAdapter implements MailIdempotencyInterface
{
    public function __construct(
        private readonly IdempotencyStore $store
    ) {
    }

    public function isCompleted(string $key): bool
    {
        return $this->store->isCompleted($key);
    }

    public function markCompleted(string $key): bool
    {
        return $this->store->markCompleted($key);
    }
}
```

Create the storage and adapter:

```php
$store = new IdempotencyStore($pdo);

$store->createTable();

$adapter = new QueueIdempotencyAdapter($store);
```

Then inject the adapter into the email job:

```php
$job = new SendMailJob(
    mailer: $mailer,
    idempotency: $adapter
);
```

When using a Queue worker, provide this configured job through its job factory.

### Idempotency Behavior

When an idempotency key is provided and an idempotency implementation is configured:

1. The job checks whether the key is already completed.
2. If completed, the email is not sent again.
3. If not completed, the email is sent.
4. After successful delivery, the key is registered as completed.

Without an idempotency implementation, queued emails work normally without deduplication.

### Delivery Guarantees and Limitations

Idempotency reduces duplicate email deliveries, but it does not guarantee exactly-once delivery.

Important limitations:

- A worker may crash after SMTP acceptance but before recording completion.
- A retry after that crash may send the email again.
- Concurrent workers may check the same key before either records completion.
- SMTP delivery and SQLite transactions cannot be committed atomically.

Applications requiring stronger guarantees should consider provider-specific idempotency features, delivery tracking, and additional coordination mechanisms.

## Error Handling

SMTP delivery errors are wrapped in a dedicated `MailException`.

```php
use Gatovel\Mail\exceptions\MailException;

try {
    $mail->send($message);
} catch (MailException $exception) {
    echo $exception->getMessage();

    // Retrieve the original exception
    $previous = $exception->getPrevious();
}
```

Invalid arguments or configuration values may throw `InvalidArgumentException`.

Queue processing errors and retry behavior are handled by Gatovel Queue.

## Architecture

Gatovel Mail follows a modular architecture.

```text
src/
├── MailManager.php
├── attachments/
│   └── Attachment.php
├── contracts/
│   ├── MailIdempotencyInterface.php
│   └── MailInterface.php
├── exceptions/
│   └── MailException.php
├── jobs/
│   └── SendMailJob.php
├── messages/
│   └── Message.php
├── queue/
│   └── MailDispatcher.php
├── serialization/
│   └── MessageSerializer.php
├── templates/
│   └── TemplateRenderer.php
├── themes/
│   ├── DefaultTheme.php
│   └── GatovelTheme.php
└── transports/
    ├── ArrayTransport.php
    └── SmtpTransport.php
```

### Core Components

**MailManager**

Coordinates email delivery through a transport implementing MailInterface.

**Message**

Represents an outgoing email, including sender, recipient, subject, content, and attachments.

**MailInterface**

Defines the contract for email transports.

**SmtpTransport**

Delivers emails using PHPMailer.

**ArrayTransport**

Stores outgoing messages in memory for testing.

**TemplateRenderer**

Renders HTML templates with escaped variables and applies a built-in theme.

**Attachment**

Represents and validates email attachments.

**MailDispatcher**

Dispatches serialized email messages to Gatovel Queue.

**SendMailJob**

Processes queued email jobs and sends reconstructed messages through MailInterface.

**MessageSerializer**

Converts email messages into queue-compatible payloads and reconstructs them during processing.

**MailIdempotencyInterface**

Defines the optional contract for tracking completed email operations.

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
- Queue integration
- SQLite job persistence
- Worker processing
- Retry after temporary failures
- Sequential idempotency
- Duplicate delivery risk scenarios

SMTP integration has also been tested with Mailtrap.

### Queue Integration Tests

The local development test suite includes:

```text
tests/
├── MailQueueIntegrationTest.php
├── MailQueueDatabaseTest.php
├── MailQueueRetryTest.php
├── MailQueueIdempotencyTest.php
└── MailQueueDuplicateTest.php
```

These tests validate asynchronous processing and queue integration.

The duplicate delivery test intentionally reproduces a scenario in which the same message is delivered more than once.

The `tests/` directory is excluded from the published Git repository in the current development configuration.

## Roadmap

Current capabilities include SMTP delivery, HTML templates, built-in themes, attachments, optional Queue integration, asynchronous delivery, retry support, and optional idempotency.

Potential future improvements include:

- Additional email transports
- Delivery metrics and monitoring
- Provider-specific delivery deduplication
- Improved concurrent processing safeguards
- Additional extensibility options

Queue integration will remain optional to preserve the package's independence.

## Contributing

Contributions, suggestions, and bug reports are welcome.

Please open an issue or submit a pull request through the GitHub repository:

https://github.com/ttrikingG/gatovel-mail

## License

Gatovel Mail is open-source software licensed under the [MIT License](LICENSE).

Copyright (c) 2026 Tom Garcia.

---

Developed as part of the Gatovel ecosystem.