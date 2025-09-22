<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class F0001WarehouseTest extends DuskTestCase
{
    protected User $user;

    /**
     * 设置测试环境
     * 创建测试所需的用户与权限
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::first();

    }

    /**
     * 清理测试环境
     * 注意：我们不在这里删除测试数据，因为仓库测试之间有数据依赖关系
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * 测试创建仓库功能 (供采购测试使用)
     * 
     * 测试步骤:
     * 1. 登录系统
     * 2. 访问仓库创建页面
     * 3. 填写仓库信息
     * 4. 提交表单
     * 5. 验证仓库创建成功
     * 
     * 预期结果:
     * - 仓库信息应成功保存
     * - 页面应显示成功消息
     */
    public function test_create_warehouse_for_purchase(): void
    {
        $this->browse(function (Browser $browser) {
            try {
                $browser->loginAs($this->user)
                        ->visit('/warehouses/create')
                        ->waitForText('New warehouse', 10)
                        ->type('code', 'WH-TEST')
                        ->type('name', 'Test warehouse')
                        ->type('address', 'Test address')
                        ->type('contact_person', 'Test contact')
                        ->type('contact_phone', '13800138000')
                        ->click('#status-active') // 选择Active状态
                        ->click('#type-warehouse') // 选择仓库类型
                        ->type('notes', 'Warehouse for procurement testing')
                        ->press('Save warehouse')
                        ->waitUntilMissing('.opacity-75', 10)
                        
                        ->screenshot('warehouse-create-result')
                        ->assertPathBeginsWith('/warehouses');
            } catch (\Exception $e) {
                $browser->screenshot('warehouse-create-failure');
                throw $e;
            }
        });
    }

    /**
     * 测试创建仓库功能 (供删除测试使用)
     * 
     * 测试步骤:
     * 1. 登录系统
     * 2. 访问仓库创建页面
     * 3. 填写将被删除的仓库信息
     * 4. 提交表单
     * 5. 验证仓库创建成功
     * 
     * 预期结果:
     * - 仓库信息应成功保存
     * - 页面应显示成功消息
     */
    public function test_create_warehouse_for_delete(): void
    {
        $this->browse(function (Browser $browser) {
            try {
                $uniqueId = uniqid();
                $warehouseName = 'To be deleted warehouse';
                
                $browser->loginAs($this->user)
                        ->visit('/warehouses/create')
                        ->waitForText('New warehouse', 10)
                        ->type('code', 'WH-DEL-' . $uniqueId)
                        ->type('name', $warehouseName)
                        ->type('address', 'Huaxia Road, Zhujiang New City, Tianhe District, Guangzhou')
                        ->type('contact_person', 'Zhang San')
                        ->type('contact_phone', '020-12345678')
                        ->click('#status-active') // 选择Active状态
                        ->click('#type-warehouse') // 选择仓库类型
                        ->type('notes', 'Warehouse for testing and deleting functions')
                        ->press('Save warehouse')
                        ->waitForText('Warehouse creation successfully', 10)
                        ->screenshot('warehouse-create-for-delete-success');
            } catch (\Exception $e) {
                $browser->screenshot('warehouse-create-for-delete-failure');
                throw $e;
            }
        });
    }

    /**
     * 测试查看仓库列表功能
     * 
     * 测试步骤:
     * 1. 登录系统
     * 2. 访问仓库列表页面
     * 3. 验证能看到之前创建的仓库
     * 
     * 预期结果:
     * - 应能看到之前创建的两个仓库
     */
    public function test_view_warehouses(): void
    {
        $this->browse(function (Browser $browser) {
            try {
                $browser->loginAs($this->user)
                        ->visit('/warehouses')
                        ->waitForText('Warehouse management', 10)
                        ->assertSee('Warehouse management')
                        // 验证两个仓库都存在
                        ->waitFor('.divide-y.divide-gray-200', 10)
                        ->assertSee('Test warehouse')
                        ->assertSee('To be deleted warehouse')
                        ->screenshot('warehouse-list-view');
            } catch (\Exception $e) {
                $browser->screenshot('warehouse-list-view-failure');
                throw $e;
            }
        });
    }

    /**
     * 测试编辑仓库功能
     * 
     * 测试步骤:
     * 1. 登录系统
     * 2. 访问仓库列表页面
     * 3. 找到要编辑的仓库并点击编辑
     * 4. 修改仓库信息
     * 5. 提交表单
     * 6. 验证仓库更新成功
     * 
     * 预期结果:
     * - 仓库信息应成功更新
     * - 页面应显示成功消息
     * - 应能在列表中看到更新后的仓库名称
     */
    public function test_edit_warehouse(): void
    {
        $this->browse(function (Browser $browser) {
            try {
                $browser->loginAs($this->user)
                        ->visit('/warehouses')
                        ->waitFor('.divide-y.divide-gray-200', 10)
                        ->assertSee('To be deleted warehouse')
                        ->type('#warehouse-search', 'To be deleted warehouse')
                        ->waitForText('To be deleted warehouse', 10)
                        ->click('.text-blue-700')
                        ->waitForText('Edit warehouse', 10)
                        ->type('name', 'To be deleted warehouse-Edited')
                        ->press('Update warehouse')
                        ->waitForText('Warehouse update successfully', 10)
                        ->assertSee('To be deleted warehouse-Edited')
                        ->screenshot('warehouse-edit-success');
            } catch (\Exception $e) {
                $browser->screenshot('warehouse-edit-failure');
                throw $e;
            }
        });
    }

    /**
     * 测试删除仓库功能
     * 
     * 测试步骤:
     * 1. 登录系统
     * 2. 访问仓库列表页面
     * 3. 找到要删除的仓库并点击删除
     * 4. 确认删除操作
     * 5. 验证仓库删除成功
     * 
     * 预期结果:
     * - 仓库应成功删除
     * - 页面应显示成功消息
     * - 应无法在列表中看到已删除的仓库
     * - 其他仓库应不受影响
     */
    public function test_delete_warehouse(): void
    {
        $this->browse(function (Browser $browser) {
            try {
                $browser->loginAs($this->user)
                        ->visit('/warehouses')
                        ->waitFor('.divide-y.divide-gray-200', 10)
                        ->assertSee('To be deleted warehouse-Edited')
                        ->type('#warehouse-search', 'To be deleted warehouse-Edited')
                        ->screenshot('before-warehouse-deletion')
                        ->click('.text-red-700')
                        ->waitForText('Are you sure?')
                        ->click('.swal2-confirm')
                        ->waitForText('Warehouse deletion successfully.', 10)
                        ->refresh()
                        ->waitFor('.divide-y.divide-gray-200', 10)
                        ->assertDontSee('To be deleted warehouse-Edited')
                        // 确保测试仓库仍然存在
                        ->assertSee('Test warehouse')
                        ->screenshot('after-warehouse-deletion');
            } catch (\Exception $e) {
                $browser->screenshot('warehouse-delete-failure');
                throw $e;
            }
        });
    }
    
    /**
     * 测试仓库验证功能 - 缺少必要字段
     * 
     * 测试步骤:
     * 1. 登录系统
     * 2. 访问仓库创建页面
     * 3. 提交空表单
     * 4. 验证错误信息显示
     * 
     * 预期结果:
     * - 提交应失败
     * - 页面应显示各个必填字段的错误信息
     */
    public function test_warehouse_validation_empty_fields(): void
    {
        $this->browse(function (Browser $browser) {
            try {
                $browser->loginAs($this->user)
                        ->visit('/warehouses/create')
                        ->waitForText('New warehouse', 10)
                        // 不填写任何字段，直接提交
                        ->press('Save warehouse')
                        
                        ->screenshot('warehouse-validation-errors')
                        ->assertPathIs('/warehouses/create');
            } catch (\Exception $e) {
                $browser->screenshot('warehouse-validation-failure');
                throw $e;
            }
        });
    }
} 