# medas-error-reporting

Part of the [Medas framework](https://github.com/tarantuli/medas-core).

## Description

Exception logging, exception email alerting, and debug variable logging. All three services implement `ExceptionHandler` or are called explicitly, and they compose with the framework's exception handler chain.

**Services:**

| Class                 | Purpose                                                                               |
|-----------------------|---------------------------------------------------------------------------------------|
| `ExceptionLogger`     | Writes formatted exception reports to timestamped files in `var/log/`                 |
| `ExceptionMailer`     | Sends exception reports via SMTP (PHPMailer)                                          |
| `VariableLogger`      | Appends variable dumps with caller location to `var/log/variables.log`                |
| `ThrowableNormalizer` | Converts a `\Throwable` to a plain array (used by `JsonHandler`)                      |
| `TraceNormalizer`     | Converts a stack trace to a plain array of `{file, line, function, arguments}` frames |

`ExceptionLogger` writes one file per exception, naming it via a configurable `{dateYmd}_{timeHi}_{message}.log` pattern. `BadRequestException` (4xx HTTP errors) can be suppressed independently of other exceptions.

`ExceptionMailer` is only active when all SMTP fields are configured. It clones the PHPMailer instance per sending so the service remains stateless between calls. Mailing failures are silently discarded to ensure that error handling itself never throws.

`VariableLogger` reads the source file at the call site to extract the actual argument expressions passed to `log()`, so the log file entry shows the variable name alongside its value rather than just a positional index.

## Configuration options

**Logging:**

| Option                                 | Default                            | Description                                                   |
|----------------------------------------|------------------------------------|---------------------------------------------------------------|
| `error-reporting.log-exceptions`       | `true`                             | Write exception files to disk                                 |
| `error-reporting.log-bad-requests`             | `false`                            | Also log 4xx `BadRequestException` instances                  |
| `error-reporting.log-directory`                | `var/log`                          | Directory for log files                                       |
| `error-reporting.file-name-pattern`            | `{dateYmd}_{timeHi}_{message}.log` | Log file name pattern                                         |
| `error-reporting.file-name-message-max-length` | `50`                               | Maximum characters from the exception message in the filename |
| `error-reporting.variables-log-file-name`      | `variables.log`                    | File name for `VariableLogger` output                         |
| `error-reporting.trace-argument-max-length`    | `200`                              | Max string length per trace argument                          |

**Email:**

| Option                             | Default              | Description                                                                     |
|------------------------------------|----------------------|---------------------------------------------------------------------------------|
| `error-reporting.email-exceptions`         | `false`              | Send exception emails                                                           |
| `error-reporting.email.email-bad-requests` | `false`              | Also email 4xx exceptions                                                       |
| `error-reporting.email.host`               | none                 | SMTP host                                                                       |
| `error-reporting.email.port`               | none                 | SMTP port                                                                       |
| `error-reporting.email.username`           | none                 | SMTP username                                                                   |
| `error-reporting.email.password`           | none                 | SMTP password                                                                   |
| `error-reporting.email.sender`             | none                 | Display name for the From field                                                 |
| `error-reporting.email.receiver`           | none                 | Recipient email address                                                         |
| `error-reporting.email.subject-pattern`    | `{sender} {message}` | Email subject; supports `{sender}`, `{file-name}`, `{line-number}`, `{message}` |

## Usage

### Package developer context

Register the package and wire `ExceptionLogger` and `ExceptionMailer` as exception handlers:

```php
use Medas\ErrorReporting\ErrorReportingPackage;

ErrorReportingPackage::instance();
```

**Registering exception handlers:**

```php
use Medas\ErrorReporting\Logging\ExceptionLogger;
use Medas\ErrorReporting\Mailing\ExceptionMailer;

// In your ServiceConfig or bootstrap
$config->addExceptionHandlerClasses(
    ExceptionLogger::class,
    ExceptionMailer::class,
);
```

The framework's exception dispatcher calls each registered handler in order until one returns `true`.

**Debug variable logging:**

```php
use Medas\ErrorReporting\Logging\VariableLogger;
use Medas\Core\Attributes\Service;

#[Service]
readonly class InvoiceProcessor
{
    public function __construct(
        private VariableLogger $logger,
    ) {}

    public function process(Invoice $invoice, array $lines): void
    {
        // Logs both variables with their names extracted from source
        $this->logger->log($invoice, $lines);
        // Writes to var/log/variables.log:
        //   /path/to/InvoiceProcessor.php:18
        //   2026-05-22 14:30:00  -  $invoice
        //      Invoice { id: "...", status: "draft", ... }
        //
        //   2026-05-22 14:30:00  -  $lines
        //      array(2) { [0] => ... }
    }
}
```

**Normalising an exception for JSON responses:**

```php
use Medas\ErrorReporting\Normalizing\ThrowableNormalizer;
use Medas\Core\Attributes\Service;

#[Service]
readonly class ApiErrorHandler
{
    public function __construct(
        private ThrowableNormalizer $normalizer,
    ) {}

    public function toArray(\Throwable $e): array
    {
        // Returns: {message, code, fileName, lineNumber, trace: [{file, line, function, arguments}]}
        return $this->normalizer->normalize($e);
    }
}
```

**Custom `ExceptionHandler`** — implement the interface and register it the same way:

```php
use Medas\Core\Interfaces\ExceptionHandler;
use Medas\Core\Attributes\Service;

#[Service]
readonly class SlackExceptionNotifier implements ExceptionHandler
{
    public function handleException(\Throwable $exception): bool
    {
        // Return true if handled, false to let the next handler try
        $this->slack->notify($exception->getMessage());

        return false; // Allow other handlers to also run
    }
}
```

### Backend user context

**Configuring file logging:**

```yaml
error-reporting:
  log-exceptions: true
  log-bad-requests: false
  log-directory: var/log
  file-name-pattern: "{dateYmd}_{timeHi}_{message}.log"
  file-name-message-max-length: 50
```

Produces files like: `var/log/20260522_1430_invalid-invoice-amount.log`

**Configuring email alerts:**

```yaml
error-reporting:
  email-exceptions: true
  email:
    host: smtp.example.com
    port: 465
    username: alerts@example.com
    password: $env(SMTP_PASSWORD)
    sender: "My App"
    receiver: dev@example.com
    subject-pattern: "[{sender}] {file-name}:{line-number} — {message}"
    email-bad-requests: false
```

All SMTP fields (`host`, `port`, `username`, `password`, `receiver`) must be set for email to activate. If any are missing, the mailer silently disables itself.

**`BadRequestException` suppression** — `log-bad-requests` and `email-bad-requests` are `false` by default so that routine client errors (404s, validation failures) do not flood logs or inboxes. Set them to `true` only when debugging client-side issues.
