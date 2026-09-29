<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'hex',
        'group',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const GROUPS = [
        'van_go'        => 'Vân gỗ / Melamine',
        'son_tinh_dien' => 'Sơn tĩnh điện',
        'son_van_bong'   => 'Sơn vân bông',
        'vai'           => 'Vải / Lưới',
        'khac'          => 'Khác',
        'da'           => 'Đá ốp bàn',
    ];

    
    public const IMAGE_DIR = 'storage/images/colors';

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getGroupLabelAttribute(): string
    {
        return self::GROUPS[$this->group] ?? $this->group;
    }

    
    protected function imageSearchDirs(): array
    {
        return array_values(array_unique(array_filter([
            public_path(self::IMAGE_DIR),
        ])));
    }

    /**
     * Tìm file theo mã màu.
     */
    protected function findImageFile(): ?array
    {
        if (!$this->code) {
            return null;
        }

        $names = array_unique([
            $this->code,
            strtoupper($this->code),
            strtolower($this->code),
        ]);

        $exts = ['png', 'jpg', 'jpeg', 'webp', 'PNG', 'JPG', 'JPEG', 'WEBP'];

        foreach ($this->imageSearchDirs() as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            foreach ($names as $name) {
                foreach ($exts as $ext) {
                    $path = $dir . DIRECTORY_SEPARATOR . $name . '.' . $ext;
                    if (is_file($path)) {
                        return [$path, $name . '.' . $ext];
                    }
                }
            }
        }

        return null;
    }

    public function getImageUrlAttribute(): ?string
    {
        $found = $this->findImageFile();
        if (!$found) {
            return null;
        }

        return asset(self::IMAGE_DIR . '/' . $found[1]);
    }

    public function getSwatchStyleAttribute(): string
    {
        if ($url = $this->image_url) {
            return 'background-image:url(' . $url . ');background-size:cover;background-position:center;';
        }
        if ($this->hex) {
            return 'background:' . $this->hex . ';';
        }
        return 'background:#e2e8f0;';
    }
}
