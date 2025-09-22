<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VerificationCode extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'code',
        'expires_at',
        'is_used'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    /**
     * 生成一个新的验证码
     *
     * @param string $email
     * @param int $length
     * @param int $expiresInMinutes
     * @return VerificationCode
     */
    public static function generateFor(string $email, int $length = 6, int $expiresInMinutes = 30): self
    {
        // 删除该邮箱之前未使用的验证码
        self::where('email', $email)
            ->where('is_used', false)
            ->delete();

        // 生成新的验证码
        $code = self::generateRandomCode($length);
        
        // 创建并返回验证码记录
        return self::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => now()->addMinutes($expiresInMinutes),
            'is_used' => false
        ]);
    }

    /**
     * 验证验证码是否有效
     *
     * @param string $email
     * @param string $code
     * @return bool
     */
    public static function validate(string $email, string $code): bool
    {
        // 记录开始验证
        \Illuminate\Support\Facades\Log::info('开始验证验证码', [
            'email' => $email,
            'code' => $code,
            'current_time' => now()->toDateTimeString()
        ]);
        
        // 查询验证码
        $query = self::where('email', $email)
            ->where('code', $code)
            ->where('is_used', false);
            
        // 记录查询条件
        \Illuminate\Support\Facades\Log::info('验证码查询条件', [
            'email' => $email,
            'code' => $code,
            'is_used' => false,
            'current_time' => now()->toDateTimeString()
        ]);
        
        // 先检查是否有任何验证码
        $anyCode = self::where('email', $email)->first();
        if (!$anyCode) {
            \Illuminate\Support\Facades\Log::error('验证失败：没有找到该邮箱的验证码', [
                'email' => $email
            ]);
            return false;
        }
        
        // 查询未过期的验证码
        $verificationCode = $query->where('expires_at', '>', now())->first();

        if (!$verificationCode) {
            // 尝试查找已过期或已使用的验证码
            $expiredCode = self::where('email', $email)
                ->where('code', $code)
                ->first();
                
            if ($expiredCode) {
                if ($expiredCode->is_used) {
                    \Illuminate\Support\Facades\Log::error('验证失败：验证码已被使用', [
                        'email' => $email,
                        'code' => $code,
                        'used_at' => $expiredCode->updated_at
                    ]);
                } else if ($expiredCode->expires_at <= now()) {
                    \Illuminate\Support\Facades\Log::error('验证失败：验证码已过期', [
                        'email' => $email,
                        'code' => $code,
                        'expires_at' => $expiredCode->expires_at,
                        'current_time' => now()
                    ]);
                }
            } else {
                \Illuminate\Support\Facades\Log::error('验证失败：验证码不正确', [
                    'email' => $email,
                    'code' => $code
                ]);
            }
            
            return false;
        }

        // 记录验证成功
        \Illuminate\Support\Facades\Log::info('验证码验证成功', [
            'email' => $email,
            'code' => $code,
            'verification_code_id' => $verificationCode->id,
            'created_at' => $verificationCode->created_at,
            'expires_at' => $verificationCode->expires_at
        ]);

        // 标记为已使用
        $verificationCode->update(['is_used' => true]);
        
        return true;
    }

    /**
     * 生成随机验证码
     *
     * @param int $length
     * @return string
     */
    protected static function generateRandomCode(int $length): string
    {
        // 只使用数字
        return (string) random_int(pow(10, $length - 1), pow(10, $length) - 1);
    }
}
