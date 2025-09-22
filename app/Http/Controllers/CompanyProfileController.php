<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;

class CompanyProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view company settings')->only(['edit']);
        $this->middleware('permission:manage company settings')->only(['update']);
    }

    public function edit(): View
    {
        $company = CompanyProfile::first();
        
        // Add debug log
        Log::info('Company information loading', [
            'company_exists' => (bool)$company,
            'company_id' => $company?->id,
            'logo_path' => $company?->logo_path,
            'logo_url' => $company?->logo_url,
        ]);
        
        // If the company information does not exist,Create a new record
        if (!$company) {
            $company = CompanyProfile::create([
                'company_name' => config('app.name'),
            ]);
            Log::info('Create a new company record', [
                'company_id' => $company->id,
            ]);
        }

        $modelType = CompanyProfile::class;
        
        return view('settings.company-profile', compact('company', 'modelType'));
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        try {
            Log::info('Start updating company information', [
                'request_data' => $request->all(),
                'has_update_logo' => $request->has('update_logo'),
                'has_media' => $request->has('media'),
                'media_ids' => $request->input('media', [])
            ]);

            // If it is only updated logo
            if ($request->has('update_logo')) {
                $company = CompanyProfile::first() ?? new CompanyProfile();
                
                Log::info('Current company information', [
                    'company_id' => $company->id,
                    'old_logo_path' => $company->logo_path
                ]);
                
                // deal withLogo
                if ($request->has('media')) {
                    $mediaIds = $request->input('media', []);
                    $media = Media::whereIn('id', $mediaIds)
                        ->where('collection_name', 'logo')
                        ->first();

                    Log::info('deal withLogorenew', [
                        'media_ids' => $mediaIds,
                        'found_media' => $media ? true : false,
                        'media_id' => $media?->id,
                        'media_path' => $media?->path ?? null,
                        'media_collection_name' => $media?->collection_name ?? null,
                        'media_disk' => $media?->disk ?? null
                    ]);

                    if ($media) {
                        // Delete oldLogo
                        $oldMedia = Media::where('path', $company->logo_path)
                            ->where('collection_name', 'logo')
                            ->first();
                            
                        if ($oldMedia) {
                            $oldMedia->delete();
                            Log::info('Delete oldLogo MediaRecord', [
                                'old_media_id' => $oldMedia->id,
                                'old_path' => $oldMedia->path
                            ]);
                        }
                        
                        // renew Media Recorded association
                        $media->model_id = $company->id;
                        $media->model_type = CompanyProfile::class;
                        $media->save();
                        
                        // use Media The path
                        $company->logo_path = $media->path;
                        $saved = $company->save();
                        
                        Log::info('LogoUpdate results', [
                            'new_logo_path' => $company->logo_path,
                            'save_result' => $saved,
                            'company_fresh' => $company->fresh()->logo_path,
                            'logo_url' => $company->fresh()->logo_url
                        ]);
                    }
                } else {
                    // If not provided media, clear logo
                    $oldMedia = Media::where('path', $company->logo_path)
                        ->where('collection_name', 'logo')
                        ->first();
                        
                    if ($oldMedia) {
                        $oldMedia->delete();
                        Log::info('Delete oldLogo MediaRecord', [
                            'old_media_id' => $oldMedia->id,
                            'old_path' => $oldMedia->path
                        ]);
                    }
                    
                    $company->logo_path = null;
                    $saved = $company->save();
                    
                    Log::info('LogoCleared', [
                        'save_result' => $saved,
                        'company_fresh' => $company->fresh()->logo_path
                    ]);
                }

                return response()->json(['success' => true]);
            }

            // Regular company information updates
            $validated = $request->validate([
                'company_name' => 'required|string|max:255',
                'registration_number' => 'required|string|max:50',
                'tax_number' => 'required|string|max:50',
                'phone' => 'required|string|max:20',
                'email' => 'required|email|max:255',
                'address' => 'required|string|max:500',
                'city' => 'required|string|max:100',
                'state' => 'required|string|max:100',
                'postal_code' => 'required|string|max:20',
                'country' => 'required|string|max:100',
                'website' => 'nullable|url|max:255',
            ]);

            Log::info('The company information verification has been passed', $validated);

            $company = CompanyProfile::first() ?? new CompanyProfile();
            $company->fill($validated);
            $company->save();

            Log::info('Company information update successfully');

            return redirect()
                ->route('settings.company-profile.edit')
                ->with('success', 'Company information has been updated');
        } catch (\Exception $e) {
            Log::error('Company information update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Company information update failed:' . $e->getMessage());
        }
    }

    /**
     * Get the company address
     */
    public function getAddress(): JsonResponse
    {
        $company = CompanyProfile::first();
        return response()->json([
            'address' => $company->address
        ]);
    }
} 