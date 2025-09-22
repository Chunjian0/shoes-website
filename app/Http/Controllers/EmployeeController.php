<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\EmployeeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Media;
use App\Mail\SuperAdminDeleted;
use Illuminate\Support\Facades\Mail;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Check if it is super-admin operate
            $employee = $request->route('employee');
            if ($employee && $employee->hasRole('super-admin')) {
                if (!auth()->user()->hasRole('super-admin')) {
                    abort(403, 'Only the Super Administrator can manage other Super Administrator accounts.');
                }
            }
            return $next($request);
        })->only(['edit', 'update', 'destroy']);

        $this->authorizeResource(User::class, 'employee');
    }

    /**
     * Show employee list
     */
    public function index(): View
    {
        $employees = User::with('roles')
            ->latest()
            ->paginate(10);

        return view('employees.index', compact('employees'));
    }

    /**
     * Show Create Employee Form
     */
    public function create(): View
    {
        $roles = Role::all();
        return view('employees.create', compact('roles'));
    }

    /**
     * Save new employees
     */
    public function store(EmployeeRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Create an employee
            $employee = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'employee_id' => $request->employee_id,
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Assign roles
            if ($request->has('roles')) {
                $employee->syncRoles($request->roles);
            }

            // Process avatar
            $tempId = $request->input('temp_id');
            if ($tempId) {
                $media = Media::where('temp_id', $tempId)
                    ->where('collection_name', 'avatar')
                    ->first();

                if ($media) {
                    // Update media records
                    $newPath = str_replace($tempId, $employee->id, $media->path);
                    
                    // Move the file to a new location
                    if (Storage::exists($media->path)) {
                        Storage::move($media->path, $newPath);
                    }
                    
                    // Update media records
                    $media->update([
                        'model_id' => $employee->id,
                        'model_type' => User::class,
                        'temp_id' => null,
                        'path' => $newPath
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('employees.index')
                ->with('success', 'Employee creation successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create an employee: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create an employee:' . $e->getMessage());
        }
    }

    /**
     * Show employee details
     */
    public function show(User $employee): View
    {
        $employee->load('roles');
        return view('employees.show', compact('employee'));
    }

    /**
     * Show editing employee form
     */
    public function edit(User $employee): View
    {
        $roles = Role::all();
        $employee->load('roles');
        return view('employees.edit', compact('employee', 'roles'));
    }

    /**
     * Update employee information
     */
    public function update(EmployeeRequest $request, User $employee): RedirectResponse
    {
        try {
            $data = $request->validated();
            
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            if ($request->hasFile('avatar')) {
                if ($employee->avatar) {
                    Storage::disk('public')->delete($employee->avatar);
                }
                $path = $request->file('avatar')->store('avatars', 'public');
                $data['avatar'] = $path;
            }

            $employee->update($data);
            
            if (isset($data['roles'])) {
                // Make sure the role exists
                $roles = Role::whereIn('id', $data['roles'])->pluck('id')->toArray();
                if (!empty($roles)) {
                    $employee->syncRoles($roles);
                }
            }

            return redirect()
                ->route('employees.show', $employee)
                ->with('success', 'Employee information has been updated.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update employee information: ' . $e->getMessage());
        }
    }

    /**
     * Delete employees
     */
    public function destroy(User $employee): RedirectResponse
    {
        try {
            // Check whether to delete super-admin
            if ($employee->hasRole('super-admin')) {
                // Send email notification
                $notificationEmail = Setting::where('key', 'admin_notification_email')->value('value');
                if ($notificationEmail) {
                    Mail::to($notificationEmail)->send(new SuperAdminDeleted($employee));
                }
            }

            if ($employee->avatar) {
                Storage::disk('public')->delete($employee->avatar);
            }
            
            $employee->delete();

            return redirect()
                ->route('employees.index')
                ->with('success', 'Employees have been deleted.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Delete employees failed: ' . $e->getMessage());
        }
    }

    /**
     * Update employee avatars
     */
    public function updateAvatar(Request $request, User $employee)
    {
        try {
            Log::info('Start updating employee profile pictures', [
                'employee_id' => $employee->id,
                'request_data' => $request->all()
            ]);

            $request->validate([
                'media_id' => 'required|exists:media,id'
            ]);

            // Get new media files
            $media = Media::findOrFail($request->media_id);
            
            Log::info('Find the media file', [
                'media_id' => $media->id,
                'media_path' => $media->path,
                'media_url' => $media->url,
                'media_exists' => Storage::disk('public')->exists($media->path)
            ]);

            // Delete old avatar
            if ($employee->avatar) {
                Log::info('Prepare to delete the old avatar', [
                    'old_avatar' => $employee->avatar,
                    'old_avatar_exists' => Storage::disk('public')->exists($employee->avatar)
                ]);
                Storage::disk('public')->delete($employee->avatar);
            }

            // renew Media Recorded association
            $media->model_id = $employee->id;
            $media->model_type = User::class;
            $media->collection_name = 'avatar';
            $media->save();

            Log::info('MediaRecords have been updated', [
                'media_id' => $media->id,
                'new_path' => $media->path,
                'new_url' => $media->url
            ]);

            // Update employee avatar path
            $employee->avatar = $media->path;
            $employee->save();

            Log::info('Employee profile picture has been updated', [
                'employee_id' => $employee->id,
                'new_avatar' => $employee->avatar,
                'avatar_exists' => Storage::disk('public')->exists($employee->avatar)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Avatar update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete employee avatars
     */
    public function deleteAvatar(User $employee)
    {
        try {
            // Delete old avatar
            if ($employee->avatar) {
                Storage::disk('public')->delete($employee->avatar);
            }

            // Delete the associated Media Record
            Media::where('model_type', User::class)
                ->where('model_id', $employee->id)
                ->where('collection_name', 'avatar')
                ->delete();

            // Clear the avatar path
            $employee->avatar = null;
            $employee->save();

            return response()->json([
                'success' => true,
                'message' => 'Avatar has been deleted'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete avatar: ' . $e->getMessage()
            ], 500);
        }
    }
} 