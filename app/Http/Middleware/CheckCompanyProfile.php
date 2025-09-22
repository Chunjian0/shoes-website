<?php

namespace App\Http\Middleware;

use App\Models\CompanyProfile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCompanyProfile
{
    public function handle(Request $request, Closure $next): Response
    {
        $company = CompanyProfile::first();
        
        // If you are currently on the company settings page,Then pass directly
        if ($request->routeIs('settings.company-profile.*')) {
            return $next($request);
        }

        // Check whether there is any company information
        if (!$company) {
            return redirect()
                ->route('settings.company-profile.edit')
                ->with('warning', 'Please improve the company information first,To ensure the system is running normally.');
        }

        // Check whether all required fields have been filled in
        $requiredFields = [
            'company_name' => 'Company Name',
            'registration_number' => 'Registration number',
            'tax_number' => 'Tax number',
            'phone' => 'Contact number',
            'email' => 'Email',
            'address' => 'Company Address',
            'city' => 'City',
            'state' => 'State',
            'postal_code' => 'post code',
            'country' => 'nation',
        ];

        $missingFields = [];
        foreach ($requiredFields as $field => $label) {
            if (empty($company->$field)) {
                $missingFields[] = $label;
            }
        }

        if (!empty($missingFields)) {
            $message = 'Please improve the following company information: ' . implode(', ', $missingFields);
            return redirect()
                ->route('settings.company-profile.edit')
                ->with('warning', $message);
        }

        return $next($request);
    }
} 