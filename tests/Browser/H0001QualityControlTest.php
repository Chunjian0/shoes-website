<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\User;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\QualityInspection;
use App\Enums\QualityInspectionStatus;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class H0001QualityControlTest extends DuskTestCase
{
    protected User $user;
    protected ?Purchase $purchase = null;
    protected ?Product $product = null;

    /**
     * 设置测试环境
     * 创建测试所需的用户与权限
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::first();


        // 获取一个已接收但未检验的采购单
        $this->purchase = Purchase::whereIn('purchase_status', ['received', 'partially_received'])
            ->whereNull('inspection_status')
            ->orderBy('received_at', 'desc')
            ->first();

        if (!$this->purchase) {
            echo "警告：没有找到可用于质量检查的采购单，某些测试可能会失败\n";
        } else {
            echo "找到可用于质量检查的采购单: {$this->purchase->purchase_number}\n";
        }
    }

    /**
     * 清理测试环境
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * 测试查看质量检查列表
     * 
     * 测试步骤:
     * 1. 登录系统
     * 2. 访问质量检查列表页面
     * 3. 验证页面元素和内容
     * 
     * 预期结果:
     * - 页面应显示质量检查列表
     * - 应包含预期的表头和操作按钮
     */
    public function test_view_quality_inspections(): void
    {
        $this->browse(function (Browser $browser) {
            try {
                echo "开始查看质量检查列表测试...\n";

                // 1. 登录系统
                $browser->loginAs($this->user)
                    ->visit('/quality-inspections')
                    ->waitForText('Quality Inspection List', 10)
                    ->screenshot('quality-inspection-list');

                // 2. 验证页面元素
                $browser->assertSee('Quality Inspection List')
                    ->assertSee('New inspection')
                    ->assertPresent('table')
                    ->assertSee('Admin')
                    ->assertSee('pass');

                // 3. 检查是否有数据
                $hasData = $browser->resolver->findOrFail('table tbody tr')->isDisplayed();
                if ($hasData) {
                    echo "质量检查列表中有数据\n";

                    // 点击第一个检查记录的查看链接
                    $browser->click('table tbody tr:first-child a[href*="quality-inspections"]')
                        ->waitForText('Quality inspection details', 10)
                        ->screenshot('quality-inspection-details')
                        ->assertPathIsNot('/quality-inspections');

                    // 返回列表页面
                    $browser->visit('/quality-inspections')
                        ->waitForText('Quality Inspection List', 10);
                } else {
                    echo "质量检查列表中没有数据\n";
                }

                echo "质量检查列表查看测试完成\n";
            } catch (\Exception $e) {
                echo "测试过程中出错: " . $e->getMessage() . "\n";
                $browser->screenshot('view-quality-inspections-error-' . \time());
                throw $e;
            }
        });
    }

    /**
     * 测试创建质量检查记录
     * 
     * 测试步骤:
     * 1. 登录系统
     * 2. 访问创建质量检查页面
     * 3. 选择采购单
     * 4. 填写检查信息
     * 5. 提交表单
     * 6. 验证创建成功
     * 
     * 预期结果:
     * - 质量检查记录应成功创建
     * - 页面应显示成功消息
     * - 应能在详情页看到创建的记录
     */
    public function test_create_quality_inspection(): void
    {
        // 如果没有可用的采购单，跳过测试
        if (!$this->purchase) {
            $this->markTestSkipped('没有可用于质量检查的采购单，跳过测试');
            return;
        }

        $this->browse(function (Browser $browser) {
            try {
                echo "开始创建质量检查记录测试...\n";

                // 1. 登录系统并访问创建页面
                $browser->loginAs($this->user)
                    ->visit('/quality-inspections/create')
                    ->waitForText('Create quality inspection', 10)
                    ->screenshot('quality-inspection-create-page');

                // 2. 选择采购单
                echo "选择采购单...\n";
                $browser->click('#purchase_display')
                    ->press('choose')

                     // 等待采购单项目加载
                    ->screenshot('quality-inspection-purchase-selected');

                $browser->press('Add a project')
                    ->waitForText('choose')
                    ->press('choose')
                    
                    ->screenshot('quality-inspection-project-selected');

                // 4. 填写检查项目信息
                echo "填写检查项目信息...\n";
                // 获取第一个检查项目的行
                if ($browser->resolver->findOrFail('.inspection-item')->isDisplayed()) {
                    // 填写第一个项目的检查数量、合格数量和不合格数量
                    $browser->type("items[{$this->purchase->id}][passed_quantity]", '90')
                        ->type("items[{$this->purchase->id}][defect_description]", '部分产品有轻微划痕')
                        ->screenshot('quality-inspection-items-filled');
                }

                // 5. 填写备注
                $browser->type('remarks', '这是一个自动化测试创建的质量检查记录')
                    ->screenshot('quality-inspection-form-completed');

                // 6. 提交表单
                echo "提交质量检查表单...\n";
                $browser->press('keep')
                    ->waitForText('Quality inspection details', 15)
                    ->screenshot('quality-inspection-created');

                // 7. 验证创建成功
                $browser->assertPathIsNot('/quality-inspections/create')
                    ->assertSee('Quality inspection details')
                    ->assertSee('90') // 合格数量
                    ->assertSee('10') // 不合格数量
                    ->assertSee('90%') // 合格率
                    ->assertSee('部分产品有轻微划痕')
                    ->assertSee('这是一个自动化测试创建的质量检查记录');

                echo "质量检查记录创建测试完成\n";
            } catch (\Exception $e) {
                echo "测试过程中出错: " . $e->getMessage() . "\n";
                $browser->screenshot('create-quality-inspection-error-' . \time());
                throw $e;
            }
        });
    }
}
