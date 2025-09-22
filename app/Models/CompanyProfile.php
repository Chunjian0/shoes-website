<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Media;

class CompanyProfile extends Model
{
    protected $fillable = [
        'company_name',
        'description',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'phone',
        'email',
        'website',
        'registration_number',
        'tax_number',
        'bank_name',
        'bank_account',
        'bank_holder',
        'logo_path',
        'business_hours',
        'facebook_url',
        'instagram_url',
        'currency',
        'timezone',
    ];

    protected $appends = [
        'logo_url',
    ];

    /**
     * GetLogoCompleteURL
     */
    public function getLogoUrlAttribute(): ?string
    {
        Log::info('GetLogo URL', [
            'id' => $this->id,
            'logo_path' => $this->logo_path,
            'has_logo' => (bool)$this->logo_path
        ]);

        if (!$this->logo_path) {
            return null;
        }

        // pass Media Model acquisition logo
        $media = Media::where('model_type', self::class)
            ->where('model_id', $this->id)
            ->where('collection_name', 'logo')
            ->first();

        Log::info('Find Media Record', [
            'found_media' => (bool)$media,
            'media_id' => $media?->id,
            'media_path' => $media?->path,
            'media_disk' => $media?->disk
        ]);

        if ($media) {
            return asset('storage/' . $media->path);
        }

        return null;
    }

    /**
     * Delete the old oneLogodocument
     */
    public function deleteOldLogo(): void
    {
        Log::info('Try to delete the old oneLogo', [
            'id' => $this->id,
            'logo_path' => $this->logo_path,
            'exists' => $this->logo_path ? Storage::disk('public')->exists($this->logo_path) : false
        ]);

        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            Storage::disk('public')->delete($this->logo_path);
            Log::info('OldLogoDeleted');
        }
    }
} 