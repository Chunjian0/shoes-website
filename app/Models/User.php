<?php

declare(strict_types=1);

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'employee_id',
        'is_active',
        'avatar',
        'settings',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'settings' => 'array',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    protected $appends = [
        'avatar_url',
    ];

    /**
     * 获取用户的个人资料
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get the user's media file
     */
    public function media()
    {
        Log::info('Building media relationship query', [
            'user_id' => $this->id,
            'model_type' => str_replace('\\', '', self::class),
            'raw_model_type' => self::class
        ]);

        $relation = $this->morphMany(Media::class, 'model');

        // Add a query listener to debug the actual executionSQL
        $relation->macro('toRawSql', function () use ($relation) {
            $bindings = $relation->getBindings();
            $sql = str_replace('?', "'%s'", $relation->toSql());
            $sql = vsprintf($sql, $bindings);
            Log::info('MediarelationSQL', ['sql' => $sql]);
            return $sql;
        });
        $relation->toRawSql();

        return $relation;
    }

    /**
     * Get the complete avatarURL
     */
    public function getAvatarUrlAttribute(): ?string
    {
        $modelType = 'App\\Models\\User';
        
        Log::info('Get user profile pictureURL', [
            'user_id' => $this->id,
            'model_type' => $modelType,
            'has_media_relation' => $this->media()->exists()
        ]);

        // Try to query the database directly
        $avatar = Media::where('model_type', $modelType)
            ->where('model_id', $this->id)
            ->where('collection_name', 'avatar')
            ->latest()
            ->first();

        Log::info('Avatar query results', [
            'found_avatar' => $avatar ? true : false,
            'avatar_path' => $avatar ? $avatar->path : null,
            'avatar_url' => $avatar ? $avatar->url : null,
            'query_conditions' => [
                'model_type' => $modelType,
                'model_id' => $this->id,
                'collection_name' => 'avatar'
            ]
        ]);

        return $avatar ? $avatar->url : null;
    }

    /**
     * Get avatar Media Object
     */
    public function getAvatarAttribute(): ?Media
    {
        Log::info('Get user profile picture Media Object', [
            'user_id' => $this->id,
            'has_media_relation' => $this->media()->exists()
        ]);

        $modelType = 'App\\Models\\User';
        
        $avatar = $this->media()
            ->where('model_type', $modelType)
            ->where('model_id', $this->id)
            ->where('collection_name', 'avatar')
            ->latest()
            ->first();

        Log::info('avatarMediaObject query results', [
            'found_avatar' => $avatar ? true : false,
            'avatar_id' => $avatar ? $avatar->id : null,
            'avatar_path' => $avatar ? $avatar->path : null,
            'query_conditions' => [
                'model_type' => $modelType,
                'model_id' => $this->id,
                'collection_name' => 'avatar'
            ]
        ]);

        return $avatar;
    }

    /**
     * Add logging for user creation to debug unexpected creation.
     */
    protected static function booted()
    {
        static::creating(function ($user) {
            // Ensure Log facade is used correctly
            \Illuminate\Support\Facades\Log::info('Creating User record:', [
                'user_data' => $user->toArray(),
                'stack_trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) // Get call stack
            ]);
        });
    }
}
