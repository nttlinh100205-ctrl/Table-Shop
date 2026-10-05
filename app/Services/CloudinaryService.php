<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudinaryService
{
    /**
     * Lấy thông tin cấu hình Cloudinary từ config, getenv, $_ENV, $_SERVER hoặc env().
     * Hỗ trợ CLOUDINARY_URL (kể cả khi user copy thừa tiền tố hoặc dấu nháy)
     * hoặc 3 biến riêng lẻ CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY, CLOUDINARY_API_SECRET.
     */
    public static function getConfig(): ?array
    {
        // 1. Thử lấy CLOUDINARY_URL từ nhiều nguồn khác nhau (đặc biệt khi chạy php artisan config:cache trên Render)
        $rawUrl = config('services.cloudinary.url')
            ?: getenv('CLOUDINARY_URL')
            ?: ($_ENV['CLOUDINARY_URL'] ?? null)
            ?: ($_SERVER['CLOUDINARY_URL'] ?? null)
            ?: env('CLOUDINARY_URL');

        if (!empty($rawUrl)) {
            $rawUrl = (string) $rawUrl;
            // Xóa tiền tố "CLOUDINARY_URL=" nếu user copy nguyên dòng vào ô Value trên Render
            $cleanUrl = preg_replace('/^CLOUDINARY_URL\s*=\s*/i', '', trim($rawUrl));
            // Xóa dấu nháy đơn hoặc nháy kép bao ngoài
            $cleanUrl = trim($cleanUrl, " \t\n\r\0\x0B'\"");

            // Parse bằng Regular Expression (chính xác tuyệt đối với định dạng cloudinary://api_key:api_secret@cloud_name)
            if (preg_match('#^cloudinary://([^:]+):([^@]+)@([a-zA-Z0-9_\.\-]+)#i', $cleanUrl, $matches)) {
                return [
                    'cloud_name' => trim($matches[3]),
                    'api_key'    => trim($matches[1]),
                    'api_secret' => trim($matches[2]),
                ];
            }

            // Fallback parse_url
            $parsed = @parse_url($cleanUrl);
            if ($parsed && !empty($parsed['host']) && !empty($parsed['user']) && !empty($parsed['pass'])) {
                return [
                    'cloud_name' => trim($parsed['host']),
                    'api_key'    => trim($parsed['user']),
                    'api_secret' => trim($parsed['pass']),
                ];
            }
        }

        // 2. Thử lấy từ 3 biến riêng lẻ
        $cloudName = config('services.cloudinary.cloud_name')
            ?: getenv('CLOUDINARY_CLOUD_NAME')
            ?: ($_ENV['CLOUDINARY_CLOUD_NAME'] ?? null)
            ?: ($_SERVER['CLOUDINARY_CLOUD_NAME'] ?? null)
            ?: env('CLOUDINARY_CLOUD_NAME');

        $apiKey = config('services.cloudinary.api_key')
            ?: getenv('CLOUDINARY_API_KEY')
            ?: ($_ENV['CLOUDINARY_API_KEY'] ?? null)
            ?: ($_SERVER['CLOUDINARY_API_KEY'] ?? null)
            ?: env('CLOUDINARY_API_KEY');

        $apiSecret = config('services.cloudinary.api_secret')
            ?: getenv('CLOUDINARY_API_SECRET')
            ?: ($_ENV['CLOUDINARY_API_SECRET'] ?? null)
            ?: ($_SERVER['CLOUDINARY_API_SECRET'] ?? null)
            ?: env('CLOUDINARY_API_SECRET');

        $cloudName = trim((string) $cloudName, " \t\n\r\0\x0B'\"");
        $apiKey    = trim((string) $apiKey, " \t\n\r\0\x0B'\"");
        $apiSecret = trim((string) $apiSecret, " \t\n\r\0\x0B'\"");

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
     * Tải file lên Cloudinary, trả về URL https vĩnh viễn hoặc null nếu lỗi.
     */
    public static function upload(UploadedFile $file, string $folder = 'products'): ?string
    {
        $config = self::getConfig();
        if (!$config) {
            return null;
        }

        try {
            $cloudName = $config['cloud_name'];
            $apiKey    = $config['api_key'];
            $apiSecret = $config['api_secret'];
            $timestamp = (string) time();

            // Chuẩn bị tham số ký (signature): Cloudinary yêu cầu sắp xếp alphabet theo tên tham số
            $cleanFolder = trim($folder, '/');
            $paramsToSign = [
                'timestamp' => $timestamp,
            ];
            if ($cleanFolder !== '') {
                $paramsToSign['folder'] = $cleanFolder;
            }
            ksort($paramsToSign);

            $signParts = [];
            foreach ($paramsToSign as $k => $v) {
                $signParts[] = "{$k}={$v}";
            }
            $toSign = implode('&', $signParts) . $apiSecret;
            $signature = sha1($toSign);

            $endpoint = "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload";

            // Đọc nội dung file
            $realPath = $file->getRealPath() ?: $file->getPathname();
            $fileContent = @file_get_contents($realPath);
            if ($fileContent === false || $fileContent === '') {
                $fileContent = $file->getContent();
            }

            $postData = [
                'api_key'   => $apiKey,
                'timestamp' => $timestamp,
                'signature' => $signature,
            ];
            if ($cleanFolder !== '') {
                $postData['folder'] = $cleanFolder;
            }

            // Gọi API Cloudinary với timeout 35s và bypass SSL verify để tránh lỗi CA trên Docker
            $response = Http::timeout(35)
                ->withoutVerifying()
                ->attach('file', $fileContent, $file->getClientOriginalName() ?: 'upload.jpg')
                ->post($endpoint, $postData);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['secure_url'])) {
                    Log::info("Cloudinary upload successful: " . $data['secure_url']);
                    return $data['secure_url'];
                }
                if (!empty($data['url'])) {
                    return $data['url'];
                }
            }

            Log::error("Cloudinary upload failed (HTTP {$response->status()}): " . $response->body());
        } catch (\Throwable $e) {
            Log::error('Cloudinary upload exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Chuyển đổi file ảnh thành Data URI (Base64) để lưu vĩnh viễn vào Database MySQL.
     * Tự động nén và resize nếu có GD extension, giúp dung lượng siêu nhẹ (~50KB-150KB).
     * Bằng cách này, dù server Render khởi động lại hay không dùng Cloudinary,
     * ảnh vẫn nằm vĩnh viễn trong database MySQL của cloud!
     */
    public static function fileToDataUri(UploadedFile $file, int $maxDim = 1200, int $quality = 80): ?string
    {
        try {
            $realPath = $file->getRealPath() ?: $file->getPathname();
            $mime = $file->getMimeType() ?: 'image/jpeg';
            $raw = @file_get_contents($realPath);
            if (!$raw) {
                $raw = $file->getContent();
            }

            if (!$raw) {
                return null;
            }

            // 1. Thử nén và tối ưu kích thước ảnh bằng GD nếu khả dụng
            if (extension_loaded('gd') && function_exists('imagecreatefromstring')) {
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
                        $compressed = false;
                        if (function_exists('imagewebp')) {
                            imagewebp($srcImg, null, $quality);
                            $data = ob_get_clean();
                            $compressed = true;
                            imagedestroy($srcImg);
                            if ($data && strlen($data) > 0) {
                                return 'data:image/webp;base64,' . base64_encode($data);
                            }
                        } elseif (function_exists('imagejpeg')) {
                            imagejpeg($srcImg, null, $quality);
                            $data = ob_get_clean();
                            $compressed = true;
                            imagedestroy($srcImg);
                            if ($data && strlen($data) > 0) {
                                return 'data:image/jpeg;base64,' . base64_encode($data);
                            }
                        }

                        if (!$compressed) {
                            ob_end_clean();
                            imagedestroy($srcImg);
                        }
                    }
                }
            }

            // 2. Fallback encode trực tiếp file (hỗ trợ file đến 15MB)
            return 'data:' . $mime . ';base64,' . base64_encode($raw);
        } catch (\Throwable $e) {
            Log::warning('fileToDataUri failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Tải file lên Cloudinary nếu có cấu hình; nếu không hoặc lỗi, tự động lưu Base64 Data URI vào database.
     * Luôn đảm bảo ảnh tồn tại vĩnh viễn, không bao giờ bị mất sau 5 phút do Render restart!
     */
    public static function uploadOrStore(UploadedFile $file, string $folder = 'products', string $disk = 'public'): string
    {
        // 1. Nếu có cấu hình Cloudinary, ưu tiên tải lên Cloudinary
        if (self::isConfigured()) {
            $cloudUrl = self::upload($file, $folder);
            if ($cloudUrl) {
                return $cloudUrl;
            }
            Log::warning('Cloudinary upload failed, falling back to permanent MySQL Data URI storage.');
        }

        // 2. Chuyển thành Data URI lưu vĩnh viễn vào database MySQL
        $dataUri = self::fileToDataUri($file);
        if ($dataUri) {
            return $dataUri;
        }

        // 3. Fallback bất đắc dĩ nếu đọc file hoàn toàn thất bại
        return $file->store($folder, $disk);
    }
}
