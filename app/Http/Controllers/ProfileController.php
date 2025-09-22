<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * Show user profile form
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update user profile information
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            
            // Update basic information
            $user->fill($request->validated());

            // If the mailbox is modified, reset the mailbox verification status
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            // Process avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }
                
                // Save a new avatar
                $path = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $path;
                $user->save();
            }

            DB::commit();

            return Redirect::route('profile.edit')->with('success', 'The profile has been updated successfully。');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update your profile: ' . $e->getMessage());
            
            return back()->withInput()->with('error', 'Failed to update your profile: ' . $e->getMessage());
        }
    }

    /**
     * Update user avatar
     */
    public function updateAvatar(Request $request)
    {
        try {
            $request->validate([
                'media_id' => 'required|exists:media,id'
            ]);

            $user = $request->user();
            $media = Media::findOrFail($request->media_id);

            // Update user avatar
            if ($user->avatar) {
                $user->avatar->delete();
            }

            $media->update([
                'model_id' => $user->id,
                'model_type' => get_class($user),
                'temp_id' => null
            ]);

            return response()->json([
                'message' => 'Avatar updated successfully',
                'avatar_url' => $media->url
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update avatar: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update avatar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user avatar
     */
    public function deleteAvatar(Request $request)
    {
        try {
            $user = $request->user();
            
            if ($user->avatar) {
                $user->avatar->delete();
            }

            return response()->json([
                'message' => 'Avatar has been deleted'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete avatar: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete avatar: ' . $e->getMessage()
            ], 500);
        }
    }
} 