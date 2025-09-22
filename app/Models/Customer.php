<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasFactory, SoftDeletes, Notifiable, HasApiTokens;

    protected $fillable = [
        'store_id',
        'ic_number',
        'name',
        'birthday',
        'contact_number',
        'email',
        'customer_password',
        'address',
        'points',
        'last_login_ip',
        'remarks',
        'tags',
        'member_level',
        'email_verified_at',
        'last_visit_at',
    ];

    protected $casts = [
        'birthday' => 'date',
        'points' => 'decimal:2',
        'tags' => 'array',
        'last_visit_at' => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    protected $hidden = [
        'customer_password',
    ];

    /**
     * Get the storefront to which the customer belongs
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'store_id')->where('is_store', true);
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    /**
     * Get the invoices associated with the customer through sales orders
     */
    public function invoices()
    {
        return $this->hasManyThrough(
            EInvoice::class,
            SalesOrder::class,
            'customer_id', // Foreign key on sales_orders table
            'sales_order_id', // Foreign key on e_invoices table
            'id', // Local key on customers table
            'id' // Local key on sales_orders table
        );
    }

    /**
     * Get the cart items associated with the customer
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'id'; // 或者你的主键字段名
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->customer_password; // 返回你的密码字段值
    }
    
    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        // Customer 模型通常没有 remember token 功能
        return ''; 
    }

    /**
     * 重写此方法以告知 Laravel 你的密码字段名
     * 如果你的字段名就是 'password'，则不需要此方法
     *
     * @return string
     */
    public function getAuthPasswordName()
    {
        // 确保返回你数据库中存储客户密码的实际字段名
        return 'customer_password'; 
    }

    // --- 定义关联关系 (如果需要) ---
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(SalesOrder::class); // 假设订单模型是 SalesOrder
    }

    // Relationship with Addresses
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
    // --- 结束关联关系 ---
} 