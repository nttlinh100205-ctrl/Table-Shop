<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmailApiService
{
    /**
     * Lấy API Key từ config, getenv, $_ENV, $_SERVER hoặc env().
     */
    public static function getApiKey(): ?string
    {
        $key = config('services.resend.key')
            ?: config('services.brevo.key')
            ?: config('services.mail_api.key')
            ?: getenv('RESEND_API_KEY')
            ?: getenv('BREVO_API_KEY')
            ?: getenv('MAIL_API_KEY')
            ?: ($_ENV['RESEND_API_KEY'] ?? null)
            ?: ($_ENV['BREVO_API_KEY'] ?? null)
            ?: ($_ENV['MAIL_API_KEY'] ?? null)
            ?: ($_SERVER['RESEND_API_KEY'] ?? null)
            ?: ($_SERVER['BREVO_API_KEY'] ?? null)
            ?: ($_SERVER['MAIL_API_KEY'] ?? null)
            ?: env('RESEND_API_KEY')
            ?: env('BREVO_API_KEY')
            ?: env('MAIL_API_KEY');

        if (empty($key)) {
            return null;
        }

        $key = (string) $key;
        $key = preg_replace('/^(RESEND_API_KEY|BREVO_API_KEY|MAIL_API_KEY)\s*=\s*/i', '', trim($key));
        return trim($key, " \t\n\r\0\x0B'\"");
    }

    /**
     * Xác định nhà cung cấp (Resend hay Brevo).
     */
    public static function getProvider(?string $key = null): string
    {
        $explicit = config('services.mail_api.provider')
            ?: getenv('MAIL_API_PROVIDER')
            ?: ($_ENV['MAIL_API_PROVIDER'] ?? null)
            ?: env('MAIL_API_PROVIDER');

        if (!empty($explicit)) {
            $explicit = strtolower(trim((string) $explicit));
            if (in_array($explicit, ['resend', 'brevo'])) {
                return $explicit;
            }
        }

        $key = $key ?: self::getApiKey();
        if (!$key) {
            return 'resend';
        }

        // Brevo API key thường bắt đầu bằng "xkeysib-"
        if (str_starts_with($key, 'xkeysib-')) {
            return 'brevo';
        }

        // Resend API key thường bắt đầu bằng "re_"
        return 'resend';
    }

    /**
     * Kiểm tra xem dịch vụ Email API đã được cấu hình chưa.
     */
    public static function isConfigured(): bool
    {
        $key = self::getApiKey();
        return !empty($key);
    }

    /**
     * Gửi email trực tiếp qua HTTPS API (Resend hoặc Brevo), hoàn toàn không bị chặn bởi Render.
     *
     * @param string|array $to Email người nhận (hoặc mảng email)
     * @param string $subject Tiêu đề email
     * @param string $html Nội dung HTML
     * @param string|null $fromName Tên người gửi
     * @param string|null $fromEmail Địa chỉ người gửi
     * @param array $config Cấu hình phụ trợ (tuỳ chọn)
     * @return bool
     */
    public static function sendDirect(
        string|array $to,
        string $subject,
        string $html,
        ?string $fromName = null,
        ?string $fromEmail = null,
        array $config = []
    ): bool {
        $apiKey = $config['api_key'] ?? self::getApiKey();
        if (empty($apiKey)) {
            Log::warning('EmailApiService: Không tìm thấy API Key (RESEND_API_KEY hoặc BREVO_API_KEY). Không thể gửi email.');
            return false;
        }

        $provider = $config['provider'] ?? self::getProvider($apiKey);
        $fromName = $fromName ?: config('mail.from.name', 'Table Shop');
        $fromEmail = $fromEmail ?: config('mail.from.address');

        $recipients = is_array($to) ? array_values($to) : [$to];

        if ($provider === 'brevo') {
            return self::sendViaBrevo($apiKey, $recipients, $subject, $html, $fromName, $fromEmail);
        }

        return self::sendViaResend($apiKey, $recipients, $subject, $html, $fromName, $fromEmail);
    }

    /**
     * Gửi qua Resend API (https://resend.com)
     */
    protected static function sendViaResend(
        string $apiKey,
        array $recipients,
        string $subject,
        string $html,
        string $fromName,
        ?string $fromEmail
    ): bool {
        // Resend: Nếu người dùng chưa verify domain riêng trên Resend,
        // bắt buộc phải gửi từ "onboarding@resend.dev"
        if (empty($fromEmail) || str_ends_with($fromEmail, '@example.com') || str_ends_with($fromEmail, '@localhost')) {
            $senderString = "{$fromName} <onboarding@resend.dev>";
        } else {
            $senderString = "{$fromName} <{$fromEmail}>";
        }

        try {
            $response = Http::timeout(25)
                ->withoutVerifying()
                ->withToken($apiKey)
                ->post('https://api.resend.com/emails', [
                    'from'    => $senderString,
                    'to'      => $recipients,
                    'subject' => $subject,
                    'html'    => $html,
                ]);

            if ($response->successful()) {
                $id = $response->json('id');
                Log::info("Resend email sent successfully (ID: {$id}) to: " . implode(', ', $recipients));
                return true;
            }

            Log::error("Resend API error (HTTP {$response->status()}): " . $response->body());
        } catch (\Throwable $e) {
            Log::error("Resend API exception: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Gửi qua Brevo API (https://brevo.com - Sendinblue)
     */
    protected static function sendViaBrevo(
        string $apiKey,
        array $recipients,
        string $subject,
        string $html,
        string $fromName,
        ?string $fromEmail
    ): bool {
        $senderEmail = $fromEmail ?: 'support@table-shop.com';
        $toFormatted = array_map(fn ($email) => ['email' => $email], $recipients);

        try {
            $response = Http::timeout(25)
                ->withoutVerifying()
                ->withHeaders([
                    'api-key'      => $apiKey,
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ])
                ->post('https://api.brevo.com/v3/smtp/email', [
                    'sender'      => [
                        'name'  => $fromName,
                        'email' => $senderEmail,
                    ],
                    'to'          => $toFormatted,
                    'subject'     => $subject,
                    'htmlContent' => $html,
                ]);

            if ($response->successful()) {
                $messageId = $response->json('messageId');
                Log::info("Brevo email sent successfully (MessageId: {$messageId}) to: " . implode(', ', $recipients));
                return true;
            }

            Log::error("Brevo API error (HTTP {$response->status()}): " . $response->body());
        } catch (\Throwable $e) {
            Log::error("Brevo API exception: " . $e->getMessage());
        }

        return false;
    }
}
