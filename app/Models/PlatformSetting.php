<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Pengaturan platform yang boleh diubah dari Admin Portal. Hanya kunci di DEFAULTS yang diterima;
 * konfigurasi teknis (kredensial, URL layanan, batas unggah) tetap di .env dan config/.
 */
class PlatformSetting extends Model
{
    public const UPDATED_AT = 'updated_at';

    public const CREATED_AT = null;

    public $incrementing = false;

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    protected $fillable = ['key', 'value', 'updated_by'];

    /** Kunci yang dikenal beserta nilai bawaannya. */
    public const DEFAULTS = [
        'hide_demo_jobs' => false,
    ];

    public static function read(string $key): mixed
    {
        if (! array_key_exists($key, self::DEFAULTS)) {
            return null;
        }

        $raw = Cache::remember("platform_setting:{$key}", 300, fn () => self::find($key)?->value);

        return $raw === null ? self::DEFAULTS[$key] : json_decode($raw, true);
    }

    public static function write(string $key, mixed $value, ?int $userId): void
    {
        self::updateOrCreate(['key' => $key], ['value' => json_encode($value), 'updated_by' => $userId]);
        Cache::forget("platform_setting:{$key}");
    }

    /** Semua pengaturan yang dikenal dengan nilai saat ini. */
    public static function current(): \Illuminate\Support\Collection
    {
        return collect(self::DEFAULTS)->map(fn ($default, $key) => self::read($key));
    }
}
