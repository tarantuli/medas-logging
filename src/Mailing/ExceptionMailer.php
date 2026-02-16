<?php

declare(strict_types=1);

namespace Medas\Logging\Mailing;

use Medas\Core\{Attributes\ConfigValue, Attributes\Service, Interfaces\BadRequestException};
use Medas\Logging\{ConfigOptions, Exceptions\InformationCompiler};
use Medas\ServiceManager\ErrorHandling\ExceptionHandler;
use PHPMailer\PHPMailer\PHPMailer;

#[Service]
readonly class ExceptionMailer implements ExceptionHandler
{
    private PHPMailer $mailer;

    public function __construct(
        private InformationCompiler $informationCompiler,

        #[ConfigValue(ConfigOptions\EmailExceptions::class)]
        private bool                $emailExceptions,

        #[ConfigValue(ConfigOptions\Email\EmailBadRequests::class)]
        private bool                $emailBadRequests,

        #[ConfigValue(ConfigOptions\Email\SubjectPattern::class)]
        private string|null         $subjectPattern,

        #[ConfigValue(ConfigOptions\Email\Sender::class)]
        private string|null         $sender,

        #[ConfigValue(ConfigOptions\Email\Port::class)]
        int|null                    $port,

        #[ConfigValue(ConfigOptions\Email\Host::class)]
        string|null                 $host,

        #[ConfigValue(ConfigOptions\Email\Username::class)]
        string|null                 $username,

        #[ConfigValue(ConfigOptions\Email\Password::class)]
        string|null                 $password,

        #[ConfigValue(ConfigOptions\Email\Receiver::class)]
        string|null                 $receiver,
    )
    {
        if ($this->emailExceptions
                && $host !== null
                && $port !== null
                && $username !== null
                && $password !== null
                && $receiver !== null) {
            $this->mailer = new PHPMailer(true);

            $this->mailer->isSMTP();

            $this->mailer->CharSet = PHPMailer::CHARSET_UTF8;
            $this->mailer->Host = $host;
            $this->mailer->Port = $port;
            $this->mailer->SMTPAuth = true;
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $this->mailer->Username = $username;
            $this->mailer->Password = $password;

            if ($sender !== null) {
                $this->mailer->FromName = $sender;
            }

            $this->mailer->addAddress($receiver);
        }
    }

    public function handleException(\Throwable $exception): void
    {
        if (!$this->emailExceptions || !isset($this->mailer)) {
            return;
        }

        if (!$this->emailBadRequests && $exception instanceof BadRequestException) {
            return;
        }

        $mailer = clone $this->mailer;

        $mailer->Subject = $this->getSubject($exception);
        $mailer->Body = $this->informationCompiler->compile($exception);

        $mailer->send();
    }

    private function getSubject(\Throwable $exception): string
    {
        $replacements = [
            '{sender}' => $this->sender ?? 'medas',
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
