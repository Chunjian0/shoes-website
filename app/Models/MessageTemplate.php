<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MessageTemplate extends Model
{
    use HasFactory;

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'channel',
        'type',
        'subject',
        'content',
        'status',
        'is_default',
        'supplier_id',
    ];

    /**
     * 额外的属性
     *
     * @var array
     */
    protected $appends = ['variables'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * 获取模板的变量列表
     *
     * @return array
     */
    public function getVariablesAttribute()
    {
        // 如果已经设置过，直接返回
        if (isset($this->attributes['variables'])) {
            return $this->attributes['variables'];
        }

        // 默认返回空数组，变量列表将在控制器中设置
        return [];
    }

    /**
     * 设置模板的变量列表
     *
     * @param array $value
     * @return void
     */
    public function setVariablesAttribute($value)
    {
        $this->attributes['variables'] = $value;
    }

    /**
     * 关联到供应商
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * 根据名称查找模板
     *
     * @param string $name
     * @return self|null
     */
    public static function findByName(string $name)
    {
        return static::where('name', $name)->first();
    }

    /**
     * 根据名称和供应商ID查找模板
     *
     * @param string $name
     * @param int|null $supplierId
     * @return self|null
     */
    public static function findByNameAndSupplier(string $name, ?int $supplierId = null)
    {
        $query = static::where('name', $name);
        
        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        } else {
            $query->whereNull('supplier_id');
        }
        
        return $query->first();
    }

    /**
     * 查找默认模板
     *
     * @param string $name
     * @param string $channel
     * @return self|null
     */
    public static function findDefault(string $name, string $channel = 'email')
    {
        return static::where('name', $name)
            ->where('channel', $channel)
            ->where('is_default', true)
            ->first();
    }

    /**
     * 获取活跃状态的模板
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function active()
    {
        return static::where('status', 'active');
    }

    /**
     * 检查模板是否处于活跃状态
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * 检查模板是否是默认模板
     *
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->is_default;
    }

    /**
     * 获取指定名称的全局或特定供应商的模板
     *
     * @param string $name 模板名称
     * @param int|null $supplierId 供应商ID，如果为null则获取全局模板
     * @param string $channel 渠道类型
     * @return self|null
     */
    public static function getTemplateFor(string $name, ?int $supplierId = null, string $channel = 'email')
    {
        // 1. Try finding active template for the specific supplier (using supplier_id)
        if ($supplierId) {
            $template = static::active()
                ->where('name', $name)
                ->where('channel', $channel)
                ->where('supplier_id', $supplierId)
                ->first();

            if ($template) {
                return $template;
            }
        }

        // 2. Try finding active global template (supplier_id is null)
        $template = static::active()
            ->where('name', $name)
            ->where('channel', $channel)
            ->whereNull('supplier_id')
            ->first();

        if ($template) {
            return $template;
        }

        // 3. Fallback to default template (active, is_default = true)
        return static::active()
            ->where('name', $name)
            ->where('channel', $channel)
            ->where('is_default', true)
            ->first();
    }
} 