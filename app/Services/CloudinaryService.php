<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudinaryService
{
    /**
     * Lấy thông tin cấu hình Cloudinary từ CLOUDINARY_URL hoặc các biến riêng lẻ.
     */
    public static function getConfig(): ?array
    {
        $url = env('CLOUDINARY_URL');
        if (!empty($url)) {
            $parsed = parse_url($url);
            if ($parsed && !empty($parsed['host']) && !empty($parsed['user']) && !empty($parsed['pass'])) {
                return [
                    'cloud_name' => $parsed['host'],
                    'api_key'    => $parsed['user'],
                    'api_secret' => $parsed['pass'],
                ];
            }
        }

        $cloudName = env('CLOUDINARY_CLOUD_NAME');
        $apiKey    = env('CLOUDINARY_API_KEY');
        $apiSecret = env('CLOUDINARY_API_SECRET');

        if (!empty($cloudName) && !empty($apiKey) && !empty($apiSecret)) {
            return [
                'cloud_name' => $cloudName,
                'api_key'    => $apiKey,
                'api_secret' => $apiSecret,
            ];
        }

        return null;
    }

    /**
     * Kiểm tra xem Cloudinary đã được cấu hình chưa.
     */
    public static function isConfigured(): bool
    {
        return self::getConfig() !== null;
    }

    /**
     * Tải file lên Cloudinary, trả về secure_url hoặc null nếu lỗi / chưa cấu hình.
     */
    public static function upload(UploadedFile $file, string $folder = 'products'): ?string
    {
        $config = self::getConfig();
        if (!$config) {
            return null;
        }

        try {
            $timestamp = time();
            $cloudName = $config['cloud_name'];
            $apiKey    = $config['api_key'];
            $apiSecret = $config['api_secret'];

            // Chữ ký Cloudinary: sắp xếp tham số alpha rồi sha1(params + apiSecret)
            $toSign = "folder={$folder}&timestamp={$timestamp}{$apiSecret}";
            $signature = sha1($toSign);

            $endpoint = "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload";

            $response = Http::timeout(25)->attach(
                'file',
                file_get_contents($file->getRealPath()),
                $file->getClientOriginalName()
            )->post($endpoint, [
                'api_key'   => $apiKey,
                'timestamp' => (string) $timestamp,
                'folder'    => $folder,
                'signature' => $signature,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['secure_url'])) {
                    return $data['secure_url'];
                }
            }

            Log::warning('Cloudinary upload returned non-200: ' . $response->body());
        } catch (\Throwable $e) {
            Log::error('Cloudinary upload exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Tải file lên Cloudinary nếu có cấu hình; nếu không có hoặc lỗi thì lưu vào local disk public.
     * Trả về URL đầy đủ (Cloudinary) hoặc relative path (local).
     */
    public static function uploadOrStore(UploadedFile $file, string $folder = 'products', string $disk = 'public'): string
    {
        if (self::isConfigured()) {
            $cloudUrl = self::upload($file, $folder);
            if ($cloudUrl) {
                return $cloudUrl;
            }
        }

        return $file->store($folder, $disk);
    }
}
