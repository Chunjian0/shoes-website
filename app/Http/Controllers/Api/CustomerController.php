<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\Notifications\CustomerEmailVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Laravel\Sanctum\HasApiTokens;

class CustomerController extends Controller
{
    /**
     * Register a new customer
     */
    public function register(Request $request)
    {
        try {
            // 记录请求数据
            Log::info('Registration request', [
                'request_data' => $request->all(),
                'headers' => $request->header()
            ]);
            
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:customers',
                'password' => 'required|string|min:8|confirmed',
                'contact_number' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'verification_code' => 'required|string',
            ]);

            if ($validator->fails()) {
                // 记录验证失败信息
                Log::error('Registration validation failed', [
                    'validation_errors' => $validator->errors()->toArray(),
                    'request_data' => $request->all()
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }
            
            // 验证验证码
            $isValid = \App\Models\VerificationCode::validate(
                $request->email,
                $request->verification_code
            );

            if (!$isValid) {
                // 记录验证码验证失败
                Log::error('CAPTCHA verification failed', [
                    'email' => $request->email,
                    'verification_code' => $request->verification_code
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Verification code is invalid or expired',
                ], 422);
            }

            // 查询是否已存在相同邮箱的用户
            $existingUser = User::where('email', $request->email)->first();
            if ($existingUser) {
                Log::error('Registration Failed - Mailbox already exists', [
                    'email' => $request->email
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'This email address is already registered',
                ], 422);
            }

            // Create customer with password
            $customer = Customer::create([
                'name' => $request->name,
                'email' => $request->email,
                'contact_number' => $request->contact_number ?? '',
                'address' => $request->address,
                'customer_password' => Hash::make($request->password), // 使用Hash加密密码
                'points' => 0,
                'member_level' => 'normal',
            ]);

            // 记录注册成功
            Log::info('Registration successful', [
                'customer_id' => $customer->id,
                'email' => $request->email
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Registration successful',
                'data' => [
                    'customer' => [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'contact_number' => $customer->contact_number,
                        'created_at' => $customer->created_at,
                    ]
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Customer registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed. Please try again.',
                'debug' => $e->getMessage() // 添加调试信息
            ], 500);
        }
    }

    /**
     * Login customer
     */
    public function login(Request $request)
    {
        try {
            Log::info('Customer login attempt', [
                'email' => $request->email,
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent')
            ]);
            
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
            }

            // Find the customer by email
            $customer = Customer::where('email', $request->email)->first();

            // Verify customer exists and password is correct
            if (!$customer || !Hash::check($request->password, $customer->getAuthPassword())) { // Use getAuthPassword() for safety
                Log::error('Customer login failed - invalid credentials', ['email' => $request->email]);
                return response()->json(['status' => 'error', 'message' => 'Invalid credentials'], 401);
            }

            // --- Use Sanctum to create a token --- 
            // Ensure the Customer model uses HasApiTokens trait
            // Revoke existing tokens if needed (optional, depends on your strategy)
            // $customer->tokens()->delete(); 
            $tokenResult = $customer->createToken('customer-login-token'); // Create a new token
            $token = $tokenResult->plainTextToken; // Get the plain text token (e.g., ID|...) 
            $expiresAt = $tokenResult->accessToken->expires_at ?? null; // Get expiration if set
            // --- End Sanctum Token Creation --- 

            // Update last login IP and visit time
            $customer->last_login_ip = $request->ip();
            $customer->last_visit_at = now();
            $customer->save();

            // Custom cache/session logic is no longer needed with Sanctum
            
            Log::info('Customer login successful via Sanctum', [
                'customer_id' => $customer->id,
                'email' => $customer->email,
                'ip' => $request->ip(),
                'token_generated' => true // Indicates Sanctum token was generated
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'token' => $token, // Return the Sanctum plain text token
                    // 'session_id' => $sessionId, // Remove session_id
                    'expires_at' => $expiresAt ? $expiresAt->toDateTimeString() : null, // Return Sanctum token expiration
                    'customer' => [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'contact_number' => $customer->contact_number,
                        'created_at' => $customer->created_at,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Customer login failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $request->email
            ]);

            return response()->json(['status' => 'error', 'message' => 'Login failed. Please try again.', 'debug' => $e->getMessage()], 500);
        }
    }

    /**
     * Get customer profile
     */
    public function profile(Request $request)
    {
        try {
            // Get the authenticated customer using the correct guard
            $customer = $request->user('customer'); // Or Auth::guard('customer')->user();
            
            if (!$customer) {
                Log::error('Customer profile request failed - customer not authenticated or middleware failed');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized', // Correct status code for auth failure
                ], 401); 
            }

            // Customer is available, proceed to return profile data
            return response()->json([
                'status' => 'success',
                'data' => [
                    'customer' => [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'contact_number' => $customer->contact_number,
                        'address' => $customer->address,
                        'birthday' => $customer->birthday,
                        'points' => $customer->points,
                        'member_level' => $customer->member_level,
                        'created_at' => $customer->created_at,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get customer profile', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'customer_id' => $request->attributes->get('customer_id', 'N/A') // Get ID from attribute if available
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get profile. Please try again.',
            ], 500);
        }
    }

    /**
     * Update customer profile
     */
    public function updateProfile(Request $request)
    {
        try {
            /** @var Customer $customer */
            $customer = $request->user('customer');

            if (!$customer) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255', // Allow partial update
                'contact_number' => 'sometimes|nullable|string|max:20', // Allow partial update, map from 'phone' too
                'phone' => 'sometimes|nullable|string|max:20', // Accept 'phone' as well
                'address' => 'sometimes|nullable|string|max:500', // Add address validation
                'birthday' => 'sometimes|nullable|date_format:Y-m-d', // Optional birthday
            ]);

            if ($validator->fails()) {
                Log::warning('Customer updateProfile validation failed', [
                    'customer_id' => $customer->id,
                    'errors' => $validator->errors()
                ]);
                return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
            }

            $validatedData = $validator->validated();

            // Update fields if they are present in the validated data
            if (isset($validatedData['name'])) {
                $customer->name = $validatedData['name'];
            }
            // Handle phone/contact_number flexibility
            if (isset($validatedData['phone'])) {
                $customer->contact_number = $validatedData['phone'];
            } elseif (isset($validatedData['contact_number'])) {
                $customer->contact_number = $validatedData['contact_number'];
            }
            if (isset($validatedData['address'])) {
                $customer->address = $validatedData['address'];
            }
             if (isset($validatedData['birthday'])) {
                 $customer->birthday = $validatedData['birthday'];
             }

            $customer->save();

            Log::info('Customer profile updated', ['customer_id' => $customer->id]);

            // Return the updated customer data
            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => [
                    'customer' => [
                         'id' => $customer->id,
                         'name' => $customer->name,
                         'email' => $customer->email, // Email usually not changed here
                         'contact_number' => $customer->contact_number,
                         'address' => $customer->address,
                         'birthday' => $customer->birthday,
                         'points' => $customer->points,
                         'member_level' => $customer->member_level,
                         'updated_at' => $customer->updated_at,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Customer profile update failed', [
                'customer_id' => $customer->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except('password')
            ]);

            return response()->json(['status' => 'error', 'message' => 'Profile update failed. Please try again.', 'debug' => $e->getMessage()], 500);
        }
    }

    /**
     * Logout customer
     */
    public function logout(Request $request)
    {
        try {
            // --- Use Sanctum Guard to get authenticated customer ---
            // The 'auth:customer' middleware should have authenticated the user
            $customer = Auth::guard('customer')->user();
            
            if ($customer instanceof \App\Models\Customer) {
                $token = $customer->currentAccessToken();

                // Check if the token exists and is the correct type before attempting to delete it
                if ($token instanceof \Laravel\Sanctum\PersonalAccessToken) {
                    $token->delete();
                    Log::info('Sanctum token revoked for customer', ['customer_id' => $customer->id]);
                } else {
                    Log::warning('No valid current access token found for customer during logout.', ['customer_id' => $customer->id]);
                }
            } else {
                Log::warning('Logout called but no authenticated customer found via Sanctum guard.', ['ip' => $request->ip()]);
            }
            // --- End Sanctum Logic ---

            // Session clearing is not needed for token-based authentication

            return response()->json([
                'status' => 'success', 
                'message' => 'Logout successful'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Customer logout failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'customer_id' => Auth::guard('customer')->id() ?? 'N/A' // Try to get ID if available
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Logout failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Update customer password
     */
    public function updatePassword(Request $request)
    {
        try {
            // Get the authenticated customer using the correct guard
            $customer = $request->user('customer'); // Or Auth::guard('customer')->user();
            
            if (!$customer) {
                 Log::error('Customer updatePassword request failed - customer not authenticated');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                ], 401);
            }
            
            // Customer found, proceed
            // $customer = Customer::find($customerId); // No need to find again
            
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 422);
            }
            
            // Validate current password using Hash::check
            if (!Hash::check($request->current_password, $customer->customer_password)) {
                Log::warning('Customer password update attempt failed - incorrect current password', ['customer_id' => $customer->id]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Current password is incorrect',
                ], 422);
            }
            
            // Update password using Hash::make
            $customer->update([
                'customer_password' => Hash::make($request->password),
            ]);
            
            Log::info('Customer password updated', [
                'customer_id' => $customer->id,
                'email' => $customer->email
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Password updated successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Customer password update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'customer_id' => $request->attributes->get('customer_id', 'N/A') // Get ID from attribute
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update password. Please try again.',
            ], 500);
        }
    }

    /**
     * Generate a random password
     */
    private function generateRandomPassword($length = 10)
    {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $specialChars = '!@#$%^&*()_-+=';
        
        $all = $lowercase . $uppercase . $numbers . $specialChars;
        
        // Ensure at least one of each character type
        $password = substr(str_shuffle($lowercase), 0, 1);
        $password .= substr(str_shuffle($uppercase), 0, 1);
        $password .= substr(str_shuffle($numbers), 0, 1);
        $password .= substr(str_shuffle($specialChars), 0, 1);
        
        // Fill the rest of the password
        $remainingLength = $length - 4;
        $password .= substr(str_shuffle(str_repeat($all, $remainingLength)), 0, $remainingLength);
        
        // Shuffle the entire password
        return str_shuffle($password);
    }

    /**
     * Create a customer account with auto-generated password
     */
    public function createWithAutoPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:customers',
                'contact_number' => 'nullable|string|max:20',
                'address' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Generate random password
            $password = $this->generateRandomPassword(12);

            // Create customer
            $customer = Customer::create([
                'name' => $request->name,
                'email' => $request->email,
                'contact_number' => $request->contact_number ?? '',
                'address' => $request->address,
                'customer_password' => Hash::make($password), // 使用Hash加密密码
                'points' => 0,
                'member_level' => 'normal',
            ]);

            // Here you might want to send an email with the generated password

            return response()->json([
                'status' => 'success',
                'message' => 'Customer account created successfully',
                'data' => [
                    'customer' => [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'contact_number' => $customer->contact_number,
                        'created_at' => $customer->created_at,
                    ],
                    'password' => $password // Only return the password in response, not in real production
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Customer creation with auto password failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create customer account. Please try again.',
            ], 500);
        }
    }

    /**
     * Send email verification code
     */
    public function sendVerificationCode(Request $request)
    {
        try {
            // 记录验证码发送请求
            Log::info('验证码发送请求', [
                'email' => $request->email,
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent')
            ]);
            
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
            ]);

            if ($validator->fails()) {
                Log::error('验证码发送验证失败', [
                    'errors' => $validator->errors()->toArray(),
                    'request_data' => $request->all() 
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            // 这里不再检查用户是否存在，允许未注册邮箱验证
            $email = $request->email;
            
            // 尝试查询该邮箱是否有最近发送的验证码
            $recentCode = \App\Models\VerificationCode::where('email', $email)
                ->where('created_at', '>', now()->subMinutes(1))
                ->first();
                
            if ($recentCode) {
                Log::warning('验证码请求过于频繁', [
                    'email' => $email,
                    'last_sent' => $recentCode->created_at
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => '请求过于频繁，请1分钟后再试',
                ], 429);
            }
            
            // 直接发送验证码，不依赖Laravel内置验证
            Notification::route('mail', $email)
                ->notify(new CustomerEmailVerification($email));
                
            // 查询最新生成的验证码以记录日志
            $latestCode = \App\Models\VerificationCode::where('email', $email)
                ->latest()
                ->first();
            
            if ($latestCode) {
                Log::info('验证码发送成功', [
                    'email' => $email,
                    'code' => $latestCode->code, // 仅在日志中记录，便于调试
                    'expires_at' => $latestCode->expires_at
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => '验证码已发送到您的邮箱，请注意查收',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send verification code', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $request->email
            ]);

            return response()->json([
                'status' => 'error',
                'message' => '发送验证码失败，请稍后重试',
                'debug' => $e->getMessage() // 添加调试信息
            ], 500);
        }
    }

    /**
     * Verify email with verification code
     */
    public function verifyEmail(Request $request)
    {
        try {
            // 记录请求数据
            Log::info('验证邮箱请求', [
                'email' => $request->email,
                'verification_code' => $request->verification_code,
                'ip' => $request->ip()
            ]);
            
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'verification_code' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::error('验证邮箱验证失败', [
                    'errors' => $validator->errors()->toArray(),
                    'request_data' => $request->all()
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            // 验证验证码
            $isValid = \App\Models\VerificationCode::validate(
                $request->email,
                $request->verification_code
            );

            if (!$isValid) {
                Log::error('验证码验证失败', [
                    'email' => $request->email,
                    'verification_code' => $request->verification_code
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => '验证码无效或已过期',
                ], 422);
            }

            // 验证成功，处理后续操作
            // 如果是已存在用户，可以标记邮箱为已验证
            $user = User::where('email', $request->email)->first();
            if ($user && !$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
                Log::info('用户邮箱已验证', [
                    'user_id' => $user->id,
                    'email' => $request->email
                ]);
            }

            Log::info('邮箱验证成功', [
                'email' => $request->email
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => '邮箱验证成功',
            ]);
        } catch (\Exception $e) {
            Log::error('Email verification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $request->email,
                'verification_code' => $request->verification_code
            ]);

            return response()->json([
                'status' => 'error',
                'message' => '邮箱验证失败，请稍后重试',
                'debug' => $e->getMessage() // 添加调试信息
            ], 500);
        }
    }
} 