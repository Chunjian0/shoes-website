<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    /**
     * Display customer list
     */
    public function index(Request $request): View
    {
        $query = Customer::query();

        // Apply filters
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('ic_number', 'like', "%{$search}%");
            });
        }

        // Get pagination data
        $customers = $query->with(['salesOrders'])
                    ->orderByDesc('last_visit_at')
                    ->paginate(10);

        return view('customers.index', compact('customers'));
    }

    /**
     * Display the creation customer form
     */
    public function create(): View
    {
        return view('customers.create');
    }

    /**
     * Generate a random password
     */
    private function generateRandomPassword($length = 12) 
    {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $specialChars = '!@#$%^&*()_-+=';
        
        $all = $lowercase . $uppercase . $numbers . $specialChars;
        
        // 确保至少包含各种类型的字符
        $password = '';
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
        $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
        $password .= $specialChars[rand(0, strlen($specialChars) - 1)];
        
        // 填充剩余字符
        for ($i = strlen($password); $i < $length; $i++) {
            $password .= $all[rand(0, strlen($all) - 1)];
        }
        
        // 打乱密码字符顺序
        return str_shuffle($password);
    }

    /**
     * Save new customers
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ic_number' => 'nullable|string|max:20|unique:customers,ic_number,NULL,id,deleted_at,NULL|regex:/^[0-9]{12}$/',
            'contact_number' => 'nullable|string|max:20|unique:customers,contact_number,NULL,id,deleted_at,NULL',
            'email' => 'nullable|email|max:255',
            'customer_password' => 'nullable|string|min:6',
            'birthday' => 'nullable|date|date_format:Y-m-d|before:tomorrow',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'member_level' => 'required|string|in:normal,silver,gold,platinum',
        ], [
            'name.required' => 'Customer name cannot be empty',
            'name.max' => 'The customer\'s name cannot be exceeded 255 Characters',
            'ic_number.regex' => 'ICThe number must be12digits',
            'ic_number.unique' => 'ShouldICNumbers have been registered in the system',
            'contact_number.unique' => 'This contact number has been registered in the system',
            'email.email' => 'Please enter a valid email address',
            'customer_password.min' => 'Password must be at least 6 characters long',
            'birthday.date' => 'Please enter a valid date of birth',
            'birthday.date_format' => 'Birth date format must be YYYY-MM-DD',
            'birthday.before' => 'Birth date cannot be in the future',
            'member_level.required' => 'Membership level cannot be empty',
            'member_level.in' => 'Invalid membership level',
        ]);

        // 如果提供了电子邮件但没有设置密码，则自动生成密码
        if (!empty($validated['email']) && empty($validated['customer_password'])) {
            $validated['customer_password'] = $this->generateRandomPassword();
            Log::info('Auto-generated password for customer', [
                'email' => $validated['email'],
                'password' => $validated['customer_password'] // 仅在日志中记录密码，实际环境应移除此日志
            ]);
        }

        // 如果提供了密码，则使用哈希函数加密
        if (!empty($validated['customer_password'])) {
            $validated['customer_password'] = Hash::make($validated['customer_password']);
        }

        // 添加当前商店ID
        $validated['store_id'] = session('store_id');

        // 首先检查是否存在已删除的相同身份证号或手机号的客户
        $deletedCustomer = null;
        if (!empty($validated['ic_number'])) {
            $deletedCustomer = Customer::withTrashed()
                ->where('ic_number', $validated['ic_number'])
                ->whereNotNull('deleted_at')
                ->first();
        }

        if (!$deletedCustomer && !empty($validated['contact_number'])) {
            $deletedCustomer = Customer::withTrashed()
                ->where('contact_number', $validated['contact_number'])
                ->whereNotNull('deleted_at')
                ->first();
        }

        // 添加调试信息
        Log::info('Create customer data:', [
            'validated_data' => $validated,
            'session_store_id' => session('store_id'),
            'request_store_id' => $request->input('store_id'),
            'found_deleted_customer' => $deletedCustomer ? true : false
        ]);

        try {
            if ($deletedCustomer) {
                // 如果找到已删除的客户，恢复并更新它
                $deletedCustomer->restore();
                $deletedCustomer->update($validated);
                $message = "Customer \"" . $validated['name'] . "\" has been restored and updated successfully!";
            } else {
                // 否则创建新客户
                $customer = Customer::create($validated);
                $message = "Customer \"" . $validated['name'] . "\" has been created successfully!";
            }

            return redirect()->route('customers.index')
                            ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error creating customer: ', [
                'data' => $validated,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('customers.create')
                            ->with('error', 'Failed to create customer. Please try again.')
                            ->withInput();
        }
    }

    /**
     * Show customer details
     */
    public function show(Customer $customer): View
    {
        $customer->load(['salesOrders']);
        return view('customers.show', compact('customer'));
    }

    /**
     * Display edit customer form
     */
    public function edit(Customer $customer): View
    {
        // Verify that the customer belongs to the current store
        if ($customer->store_id !== session('store_id')) {
            abort(404, 'The customer does not exist');
        }

        return view('customers.edit', compact('customer'));
    }

    /**
     * Update customer information
     */
    public function update(Request $request, Customer $customer): RedirectResponse
    {
        // Verify that the customer belongs to the current store
        if ($customer->store_id !== session('store_id')) {
            abort(404, 'The customer does not exist');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ic_number' => 'nullable|string|max:20|unique:customers,ic_number,'.$customer->id.',id,deleted_at,NULL|regex:/^[0-9]{12}$/',
            'contact_number' => 'nullable|string|max:20|unique:customers,contact_number,'.$customer->id.',id,deleted_at,NULL',
            'email' => 'nullable|email|max:255',
            'customer_password' => 'nullable|string|min:6',
            'birthday' => 'nullable|date|date_format:Y-m-d|before:tomorrow',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'member_level' => 'required|string|in:normal,silver,gold,platinum',
        ], [
            'name.required' => 'Customer name cannot be empty',
            'name.max' => 'The customer\'s name cannot be exceeded 255 Characters',
            'ic_number.regex' => 'IC The number must be 12 digits',
            'ic_number.unique' => 'Should IC Numbers have been registered in the system',
            'contact_number.unique' => 'This contact number has been registered in the system',
            'email.email' => 'Please enter a valid email address',
            'customer_password.min' => 'Password must be at least 6 characters long',
            'birthday.date' => 'Please enter a valid date of birth',
            'birthday.date_format' => 'Birth date format must be YYYY-MM-DD',
            'birthday.before' => 'Birth date cannot be in the future',
            'member_level.required' => 'Membership level cannot be empty',
            'member_level.in' => 'Invalid membership level',
        ]);

        try {
            // If password is provided in the update, hash it before saving to the customer record.
            // Do NOT interact with the User model here.
            if (!empty($validated['customer_password'])) {
                $validated['customer_password'] = Hash::make($validated['customer_password']);
            } else {
                // Ensure password is not accidentally nulled if not provided
                unset($validated['customer_password']);
                }

            $customer->update($validated);

            return redirect()->route('customers.index')
                            ->with('success', "Customer \"$customer->name\" information updated successfully!");
        } catch (\Exception $e) {
            Log::error('Error updating customer: ', [
                'customer_id' => $customer->id,
                'data' => $validated,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('customers.edit', $customer)
                            ->with('error', 'Failed to update customer information. Please try again.')
                            ->withInput();
        }
    }

    /**
     * Delete customers
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        // Verify that the customer belongs to the current store
        if ($customer->store_id !== session('store_id')) {
            abort(404, 'The customer does not exist');
        }
        
        try {
            $customerName = $customer->name;
            $customer->delete();
            
            return redirect()->route('customers.index')
                        ->with('success', "Customer \"$customerName\" has been successfully deleted!");
        } catch (\Exception $e) {
            Log::error('Error deleting customer: ', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('customers.index')
                        ->with('error', 'Failed to delete customer. Please try again later.');
        }
    }

    /**
     * Get customer details for AJAX request
     */
    public function getCustomerDetails(Customer $customer)
    {
        // Verify that the customer belongs to the current store
        if ($customer->store_id !== session('store_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->contact_number,
                'email' => $customer->email,
                'address' => $customer->address,
                'member_level' => $customer->member_level,
                'ic_number' => $customer->ic_number,
                'birthday' => $customer->birthday,
                'notes' => $customer->notes,
            ]
        ]);
    }

    public function updatePassword(Request $request, Customer $customer)
    {
        // Verify that the customer belongs to the current store
        if ($customer->store_id !== session('store_id')) {
            abort(404, 'The customer does not exist');
        }
        
        // 验证当前密码
        if (!Hash::check($request->current_password, $customer->customer_password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect',
            ], 422);
        }
        
        // 更新密码
        $customer->update([
            'customer_password' => Hash::make($request->password),
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Password updated successfully',
        ]);
    }
} 