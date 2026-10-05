<?php

namespace App\Mail\Transport;

use App\Services\EmailApiService;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class ApiTransport extends AbstractTransport
{
    protected array $config;

    public function __construct(array $config = [])
    {
        parent::__construct();
        $this->config = $config;
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $from = $email->getFrom();
        $fromEmail = !empty($from) ? $from[0]->getAddress() : config('mail.from.address');
        $fromName  = !empty($from) ? $from[0]->getName() : config('mail.from.name');

        $toAddresses = array_map(fn ($recipient) => $recipient->getAddress(), $email->getTo());
        $subject = $email->getSubject() ?: 'Thông báo từ Table Shop';
        $html = $email->getHtmlBody();
        $text = $email->getTextBody();

        if (empty($html) && !empty($text)) {
            $html = nl2br(htmlspecialchars($text));
        }

        $sent = EmailApiService::sendDirect(
            to: $toAddresses,
            subject: $subject,
            html: (string) ($html ?: $text ?: ''),
            fromName: $fromName ?: config('mail.from.name', 'Table Shop'),
            fromEmail: $fromEmail ?: config('mail.from.address'),
            config: $this->config
        );
        if (!$sent) {
            throw new \Symfony\Component\Mailer\Exception\TransportException('Email API rejected delivery. Check provider configuration; queued delivery will retry.');
        }
    }

    public function __toString(): string
    {
        return 'api';
    }
}
