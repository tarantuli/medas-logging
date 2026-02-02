<?php

declare(strict_types=1);

namespace Medas\ErrorLog;

use Medas\Core\{Attributes\ConfigValue, Attributes\Service, Interfaces\BadRequestException};
use Medas\ServiceManager\ErrorHandling\ExceptionHandler;
use PHPMailer\PHPMailer\PHPMailer;

#[Service]
readonly class ExceptionMailer implements ExceptionHandler
{
    private PHPMailer $mailer;

    public function __construct(
        private ExceptionInformation $exceptionInformation,

        #[ConfigValue(ConfigOptions\EmailExceptions::class)]
        private bool                 $emailExceptions,

        #[ConfigValue(ConfigOptions\Email\EmailBadRequests::class)]
        private bool                 $emailBadRequests,

        #[ConfigValue(ConfigOptions\Email\SubjectPattern::class)]
        private string|null          $subjectPattern,

        #[ConfigValue(ConfigOptions\Email\Port::class)]
        int|null                     $port,

        #[ConfigValue(ConfigOptions\Email\Host::class)]
        string|null                  $host,

        #[ConfigValue(ConfigOptions\Email\Username::class)]
        string|null                  $username,

        #[ConfigValue(ConfigOptions\Email\Password::class)]
        string|null                  $password,

        #[ConfigValue(ConfigOptions\Email\Receiver::class)]
        string|null                  $receiver,
    )
    {
        if ($this->emailExceptions) {
            $this->mailer = new PHPMailer(true);

            $this->mailer->isSMTP();
            $this->mailer->isHTML();

            $this->mailer->CharSet = PHPMailer::CHARSET_UTF8;
            $this->mailer->Host = $host;
            $this->mailer->Port = $port;
            $this->mailer->SMTPAuth = true;
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $this->mailer->Username = $username;
            $this->mailer->Password = $password;

            $this->mailer->addAddress($receiver);
        }
    }

    public function handleException(\Throwable $exception): void
    {
        if (!$this->emailExceptions) {
            return;
        }

        if (!$this->emailBadRequests && $exception instanceof BadRequestException) {
            return;
        }

        $this->mailer->Subject = $this->getSubject($exception);
        $this->mailer->Body = $this->exceptionInformation->gather($exception);

        $this->mailer->send();
    }

    private function getSubject(\Throwable $exception): string
    {
        $replacements = [
            '{file-name}' => basename($exception->getFile()),
            '{line-number}' => $exception->getLine(),
            '{message}' => mb_substr($exception->getMessage(), 0, 100),
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $this->subjectPattern
        );
    }
}
