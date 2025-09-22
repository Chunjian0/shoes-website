<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CompanyProfileController extends Controller
{
    public function edit(): View
    {
        $company = CompanyProfile::first() ?? new CompanyProfile();
        return view('settings.company-profile', compact('company'));
    }

    public function update(Request $request): RedirectResponse
    {
        try {
            Log::info('Start updating company information');

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:500',
                'phone' => 'required|string|max:50',
                'email' => 'required|email|max:255',
                'registration_number' => 'nullable|string|max:100',
                'tax_number' => 'nullable|string|max:100',
                'website' => 'nullable|url|max:255',
                'bank_name' => 'nullable|string|max:255',
                'bank_account' => 'nullable|string|max:50',
                'bank_holder' => 'nullable|string|max:255',
            ]);

            Log::info('The company information verification has been passed', $validated);

            $company = CompanyProfile::first();
            
            if ($company) {
                Log::info('Update existing company information', ['company_id' => $company->id]);
                $company->update($validated);
            } else {
                Log::info('Create new company information');
                CompanyProfile::create($validated);
            }

            Log::info('Company information update successfully');

            return redirect()
                ->route('settings.company-profile.edit')
                ->with('success', 'Company information update successfully');
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
} 