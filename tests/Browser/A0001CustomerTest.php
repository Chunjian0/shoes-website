<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Customer;
use App\Models\Warehouse;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Pages\LoginPage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class A0001CustomerTest extends DuskTestCase
{
        protected $store;
        // 存储创建的客户数据，便于不同测试方法间共享
        protected static $customer1Name;
        protected static $customer1Phone;
        protected static $customer2Name;
        protected static $customer2Phone;

        /**
         * 在测试类开始前设置环境
         */
        public function setUp(): void
        {
                parent::setUp();
                // 在setUp开始时记录日志
                $testName = (new ReflectionClass($this))->getShortName() . '::' . $this->name();
                Log::info('开始执行测试: ' . $testName);
        }

        /**
         * 测试创建商店
         */
        public function test_01_create_store(): void
        {
                $this->browse(function (Browser $browser) {
                        // 登录
                        $this->artisan('migrate:fresh --seed');
                        $browser->visit('/login')
                                ->type('email', 'ethankhoo09@gmail.com')
                                ->type('password', '106428')
                                ->press('Log in')
                                ->waitForLocation('/dashboard')
                                ->assertPathIs('/dashboard')
                                ->assertSee('Dashboard');

                        echo '登录成功，准备切换商店';
                        $browser->visit('/customers/create')
                                ->waitForText('Create a store')
                                ->assertSee('Create a store')
                                ->type('code', 'STORE001')
                                ->type('name', 'Test Store')
                                ->type('address', 'Test Store Address')
                                ->type('contact_person', 'Store Manager')
                                ->type('contact_phone', '0123456789')
                                ->press('Create a store')
                                ->assertPathIs('/dashboard');
                        
                        // 验证商店是否成功创建
                        $storeExists = DB::table('warehouses')
                                ->where('code', 'STORE001')
                                ->where('name', 'Test Store')
                                ->exists();
                        
                        $this->assertTrue($storeExists, '商店未成功创建到数据库');
                        echo "\n商店创建成功，已验证数据库中存在记录";
                });
        }

        /**
         * 测试客户生日验证
         */
        public function test_02_customer_birthday_validation(): void
        {
                echo '验证错误测试开始';

                // 测试生日日期范围限制 - 直接创建客户，主要测试目标
                
                // 创建带有未来日期的客户
                $this->browse(function (Browser $browser) {
                $futureBirthday = date('Y-m-d', strtotime('+1 year'));
                $browser->visit('/customers/create')
                                ->waitForText('Personal Information', 10)
                                ->type('name', 'Future Date Test')
                                ->type('ic_number', '123456789012')
                                ->type('contact_number', '9876543210')
                                ->type('email', 'test1@example.com')
                                ->type('customer_password', 'test123456')
                                ->type('birthday', $futureBirthday)
                                ->type('address', 'Test Address ' . uniqid())
                                ->type('notes', 'Test customer notes')
                                // For the custom select component
                                ->click('#memberLevelButton')
                                ->waitFor('#memberLevelDropdown') 
                                ->click('.memberLevelOption[data-value="silver"]')
                                ->press('Save Customer');
                                
                // 验证数据库中不存在该客户
                $customerExists = DB::table('customers')
                        ->where('name', 'Future Date Test')
                        ->where('contact_number', '9876543210')
                        ->exists();
                
                $this->assertFalse($customerExists, '带有未来日期的客户不应存在于数据库中');
                echo "\n验证错误测试完成";
                });
        }

        /**
         * 测试创建客户
         */
        public function test_03_create_customers(): void
        {
                echo "\n开始创建客户测试";

                $this->browse(function (Browser $browser) {
                        // 创建第一个客户
                        self::$customer1Name = 'Test Customer ' . uniqid();
                        self::$customer1Phone = '123-' . rand(1000000, 9999999);

                        echo "\n开始创建第一个客户: " . self::$customer1Name;

                        $browser->visit('/customers/create')
                                ->waitForTurbolinksLoad(10)
                                ->waitForText('Personal Information', 10)
                                ->type('name', self::$customer1Name)
                                ->type('ic_number', '123456789012')
                                ->type('contact_number', self::$customer1Phone)
                                ->type('email', 'test1@example.com')
                                ->type('customer_password', 'test123456')
                                ->type('birthday', '1990-01-01')
                                ->type('address', 'Test Address ' . uniqid())
                                ->type('notes', 'Test customer notes')
                                // For the custom select component
                                ->click('#memberLevelButton')
                                ->waitFor('#memberLevelDropdown') 
                                ->click('.memberLevelOption[data-value="silver"]')
                                ->press('Save Customer');
                                
                        // 尝试等待重定向到客户列表页，但即使未重定向也继续检查数据库
                        try {
                            $browser->waitForLocation('/customers', 10)
                                   ->assertPathIs('/customers');
                            echo "\n成功重定向到客户列表页";
                        } catch (\Exception $e) {
                            echo "\n未能重定向到客户列表页，但将继续检查数据库";
                        }

                        // 验证客户是否成功创建到数据库
                        $customer1Exists = DB::table('customers')
                                ->where('name', self::$customer1Name)
                                ->where('contact_number', self::$customer1Phone)
                                ->exists();
                        
                        $this->assertTrue($customer1Exists, '第一个客户未成功创建到数据库');
                        echo "\n第一个客户创建成功，已验证数据库中存在记录";

                        // 创建第二个客户
                        self::$customer2Name = 'Test Customer ' . uniqid();
                        self::$customer2Phone = '123-' . rand(1000000, 9999999);

                        echo "\n开始创建第二个客户: " . self::$customer2Name;

                        $browser->visit('/customers/create')
                                ->waitForText('Personal Information', 10)
                                ->type('name', self::$customer2Name)
                                ->type('ic_number', '987654321012')
                                ->type('contact_number', self::$customer2Phone)
                                ->type('email', 'test2@example.com')
                                ->type('customer_password', 'test456789')
                                ->type('birthday', '1985-05-05')
                                ->type('address', '456 Test Avenue')
                                ->type('notes', 'Another test customer')
                                // For the custom select component
                                ->click('#memberLevelButton')
                                ->waitFor('#memberLevelDropdown')
                                ->click('.memberLevelOption[data-value="gold"]')
                                ->press('Save Customer')
                                // 验证是否成功重定向到客户列表页
                                ->waitForLocation('/customers', 10)
                                ->assertPathIs('/customers');
                                
                        // 验证客户是否成功创建到数据库
                        $customer2Exists = DB::table('customers')
                                ->where('name', self::$customer2Name)
                                ->where('contact_number', self::$customer2Phone)
                                ->exists();
                        
                        $this->assertTrue($customer2Exists, '第二个客户未成功创建到数据库');
                        echo "\n第二个客户创建成功，已验证数据库中存在记录";
                });
        }

        /**
         * 测试客户列表和搜索功能
         */
        public function test_04_customer_list_and_search(): void
        {
                echo "\n开始测试客户列表和搜索功能";

                $this->browse(function (Browser $browser) {
                        // 搜索第一个客户
                        $browser->visit('/customers')
                                ->waitForText('Customers', 10)
                                ->type('#search', self::$customer1Name)
                                ->keys('#search', '{enter}')
                                ->waitForText(self::$customer1Name, 10)
                                ->assertSee(self::$customer1Name)
                                ->assertDontSee(self::$customer2Name);

                        // 搜索第二个客户
                        $browser->visit('/customers')
                                ->waitForText('Customers', 10)
                                ->type('#search', self::$customer2Name)
                                ->keys('#search', '{enter}')
                                ->waitForText(self::$customer2Name, 10)
                                ->assertSee(self::$customer2Name)
                                ->assertDontSee(self::$customer1Name);

                        echo "\n客户列表和搜索功能测试完成";
                });
        }

        /**
         * 测试查看客户详情
         */
        public function test_05_view_customer_details(): void
        {
                echo "\n开始测试客户详情页面";

                $this->browse(function (Browser $browser) {
                        $browser->visit('/customers')
                                ->waitForText('Customers', 10)
                                ->type('#search', self::$customer1Name)
                                ->keys('#search', '{enter}')
                                ->waitForText(self::$customer1Name, 10)
                                ->click('.action-btn-view')
                                ->waitForText('Customer information', 10)
                                ->assertSee(self::$customer1Name)
                                ->assertSee('123456789012')
                                ->assertSee(self::$customer1Phone);

                        echo "\n客户详情页面测试完成";
                });
        }

        /**
         * 测试编辑客户功能
         */
        public function test_06_edit_customer(): void
        {
                echo "\n开始测试编辑客户功能";

                $this->browse(function (Browser $browser) {
                        // 先进入编辑页面
                        $browser->visit('/customers')
                                ->waitForText('Customers', 10)
                                ->type('#search', self::$customer1Name)
                                ->keys('#search', '{enter}')
                                ->waitForText(self::$customer1Name, 10)
                                ->click('.action-btn-edit')
                                ->waitForText('Personal Information', 10);

                        // 更新客户信息
                        $updatedAddress = 'Updated Address - ' . uniqid();

                        $browser->type('address', $updatedAddress)
                                ->press('Save Customer')
                                ->waitForLocation('/customers', 10)
                                ->assertPathIs('/customers');

                        // 验证更新是否成功
                        $browser->visit('/customers')
                                ->waitForText('Customers', 10)
                                ->type('#search', self::$customer1Name)
                                ->keys('#search', '{enter}')
                                ->waitForText(self::$customer1Name, 10)
                                ->click('.action-btn-view')
                                ->waitForText('Customer information', 10)
                                ->assertSee($updatedAddress);
                                
                        // 验证数据库中的地址是否已更新
                        $customerAddress = DB::table('customers')
                                ->where('name', self::$customer1Name)
                                ->value('address');
                                
                        $this->assertEquals($updatedAddress, $customerAddress, '数据库中的地址未更新');
                        echo "\n编辑客户功能测试完成，已验证数据库中地址已更新";
                });
        }

        /**
         * 测试编辑客户验证错误
         */
        public function test_07_edit_customer_validation(): void
        {
                echo "\n开始测试编辑客户验证错误";

                $this->browse(function (Browser $browser) {
                        $browser->visit('/customers')
                                ->waitForText('Customers', 10)
                                ->type('#search', self::$customer2Name)
                                ->keys('#search', '{enter}')
                                ->waitForText(self::$customer2Name, 10)
                                ->click('.action-btn-edit')
                                ->waitForText('Personal Information', 10)
                                ->type('name', '') // 空名称
                                ->press('Save Customer')
                                // 验证是否仍在编辑页面（表示验证失败）
                                ->waitForText('Personal Information', 10)
                                ->assertSee('Personal Information');
                                
                        // 验证数据库中的名称是否保持不变
                        $customerName = DB::table('customers')
                                ->where('contact_number', self::$customer2Phone)
                                ->value('name');
                                
                        $this->assertEquals(self::$customer2Name, $customerName, '验证失败但数据库中的名称被错误更改');
                        echo "\n编辑客户验证错误测试完成，已验证数据库中名称未被修改";
                });
        }

        /**
         * 测试删除客户功能
         */
        public function test_08_delete_customer(): void
        {
                echo "\n开始测试删除客户功能";

                $this->browse(function (Browser $browser) {
                        $browser->visit('/customers')
                                ->waitForText('Customers', 10)
                                ->type('#search', self::$customer1Name)
                                ->keys('#search', '{enter}')
                                ->waitForText(self::$customer1Name, 10)
                                ->click('.action-btn-delete')
                                ->waitForText('Are you sure you want to delete', 10)
                                ->press('Delete')
                                ->waitForLocation('/customers', 10)
                                ->assertPathIs('/customers');

                        // 验证客户是否已被删除
                        $browser->visit('/customers')
                                ->waitForText('Customers', 10)
                                ->type('#search', self::$customer1Name)
                                ->keys('#search', '{enter}')
                                ->pause(1000) // 等待搜索结果加载
                                ->assertSee("No customers found"); // 确认页面中不存在该客户名称
                                
                        // 验证数据库中客户被软删除（deleted_at不为空）
                        $deletedCustomer = DB::table('customers')
                                ->where('name', self::$customer1Name)
                                ->whereNotNull('deleted_at')
                                ->exists();
                                
                        $this->assertTrue($deletedCustomer, '客户未被软删除');
                        echo "\n删除客户功能测试完成，已验证数据库中客户已被软删除";
                });
        }

        /**
         * 测试极端情况 - 创建包含极长数据的客户
         */
        public function test_09_extreme_input_validation(): void
        {
                echo "\n开始测试极长输入数据";

                $this->browse(function (Browser $browser) {
                        $veryLongName = str_repeat('Very Long Name ', 20); // 超过255个字符
                        $veryLongNote = str_repeat('This is a very long note. ', 100); // 超过1000个字符

                        $browser->visit('/customers/create')
                                ->waitForText('Personal Information', 10)
                                ->type('name', $veryLongName)
                                ->type('ic_number', '123456789023')
                                ->type('contact_number', uniqid('test_'))
                                ->type('notes', $veryLongNote)
                                ->press('Save Customer')
                                ->waitForText('The customer\'s name cannot be exceeded 255 Characters', 10)
                                // 验证是否仍在创建页面（表示验证失败）
                                ->assertPathIs('/customers/create');
                                
                        // 验证数据库中不存在该客户
                        $customerExists = DB::table('customers')
                                ->where('name', 'like', 'Very Long Name%')
                                ->where('ic_number', '123456789023')
                                ->exists();
                                
                        $this->assertFalse($customerExists, '极长名称的客户不应存在于数据库中');
                        echo "\n极长输入数据测试完成，已验证数据库中不存在该记录";
                });
        }
}
