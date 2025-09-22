<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'options',
        'is_public'
    ];

    /**
     * 获取设置值
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * 设置值
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $group
     * @return bool
     */
    public static function setValue(string $key, $value, ?string $group = null): bool
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group
            ]
        );

        return $setting->wasRecentlyCreated || $setting->wasChanged();
    }

    /**
     * 获取指定组的所有设置
     *
     * @param string $group
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getGroup(string $group)
    {
        return self::where('group', $group)->get();
    }

    /**
     * 获取指定组的所有设置，作为关联数组返回
     * 
     * @param string $group
     * @return array
     */
    public static function getGroupAsArray(string $group): array
    {
        $settings = self::where('group', $group)->get();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->value;
        }

        return $result;
    }
}
