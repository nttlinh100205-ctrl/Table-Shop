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
     * Chuyển đổi file ảnh thành Data URI nén tối ưu (Base64) để lưu vĩnh viễn vào Database MySQL.
     * Bằng cách này, bất kỳ người dùng nào up ảnh từ máy khác mà không cần biết code hay git,
     * ảnh vẫn tồn tại vĩnh viễn trong database MySQL của cloud, không bao giờ bị mất khi Render khởi động lại!
     */
    public static function fileToDataUri(UploadedFile $file, int $maxDim = 1200, int $quality = 80): ?string
    {
        try {
            $realPath = $file->getRealPath();
            $mime = $file->getMimeType() ?: 'image/jpeg';

            // Nếu có extension GD, thử nén và resize nhẹ nhàng
            if (extension_loaded('gd') && function_exists('imagecreatefromstring')) {
                $raw = @file_get_contents($realPath);
                if ($raw) {
                    $srcImg = @imagecreatefromstring($raw);
                    if ($srcImg) {
                        $w = imagesx($srcImg);
                        $h = imagesy($srcImg);
                        if ($w > 0 && $h > 0) {
                            if ($w > $maxDim || $h > $maxDim) {
                                if ($w >= $h) {
                                    $newW = $maxDim;
                                    $newH = (int) round(($h / $w) * $maxDim);
                                } else {
                                    $newH = $maxDim;
                                    $newW = (int) round(($w / $h) * $maxDim);
                                }
                                $dstImg = imagecreatetruecolor($newW, $newH);
                                if ($mime === 'image/png' || $mime === 'image/webp') {
                                    imagealphablending($dstImg, false);
                                    imagesavealpha($dstImg, true);
                                }
                                imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newW, $newH, $w, $h);
                                imagedestroy($srcImg);
                                $srcImg = $dstImg;
                            }

                            ob_start();
                            if (function_exists('imagewebp')) {
                                imagewebp($srcImg, null, $quality);
                                $data = ob_get_clean();
                                imagedestroy($srcImg);
                                return 'data:image/webp;base64,' . base64_encode($data);
                            } elseif (function_exists('imagejpeg')) {
                                imagejpeg($srcImg, null, $quality);
                                $data = ob_get_clean();
                                imagedestroy($srcImg);
                                return 'data:image/jpeg;base64,' . base64_encode($data);
                            }
                            ob_end_clean();
                            imagedestroy($srcImg);
                        }
                    }
                }
            }

            // Fallback đọc trực tiếp file và encode base64 nếu file <= 4MB
            if ($file->getSize() <= 4 * 1024 * 1024) {
                $content = @file_get_contents($realPath);
                if ($content) {
                    return 'data:' . $mime . ';base64,' . base64_encode($content);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('fileToDataUri failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Tải file lên Cloudinary nếu có cấu hình; nếu không có, tự động nén và lưu Base64 vào database.
     * Trả về URL đầy đủ (Cloudinary / Data URI) hoặc relative path.
     */
    public static function uploadOrStore(UploadedFile $file, string $folder = 'products', string $disk = 'public'): string
    {
        // 1. Nếu có cấu hình Cloudinary, tải lên Cloudinary
        if (self::isConfigured()) {
            $cloudUrl = self::upload($file, $folder);
            if ($cloudUrl) {
                return $cloudUrl;
            }
        }

        // 2. Tự động chuyển thành Data URI lưu vào database MySQL
        // (Lưu trực tiếp trong cloud database Aiven, vĩnh viễn không bị xóa khi Render container reset)
        $dataUri = self::fileToDataUri($file);
        if ($dataUri) {
            try { $file->store($folder, $disk); } catch (\Throwable $e) {}
            return $dataUri;
        }

        // 3. Fallback cuối cùng: lưu local disk
        return $file->store($folder, $disk);
    }
}
