<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\User;
use App\Models\NotificationLog;
use App\Models\MessageTemplate;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Facades\DB;

class I0001NotificationTest extends DuskTestCase
{
    protected User $user;
    protected ?MessageTemplate $template = null;

    /**
     * 设置测试环境
     * 创建测试所需的用户与模板
     */
    public function setUp(): void
    {
        parent::setUp();
        
        // 创建测试用户或使用已存在的用户
        $this->user = User::where('email', 'ethankhoo09@gmail.com')->first();
        if (!$this->user) {
            $this->user = User::factory()->create([
                'email' => 'ethankhoo09@gmail.com',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]);
            echo "创建了测试用户: {$this->user->email}\n";
        } else {
            echo "使用已存在的测试用户: {$this->user->email}\n";
        }
        
        // 获取或创建测试邮件模板
        $this->template = MessageTemplate::where('name', 'purchase_order_generated')->first();
        if (!$this->template) {
            $this->template = MessageTemplate::create([
                'name' => 'test_mail',
                'subject' => 'Test Email Template',
                'content' => '<h1>Test Email</h1><p>This is a test email template.</p>',
                'description' => 'Template for test emails',
                'is_active' => true,
            ]);
            echo "创建了测试邮件模板: {$this->template->name}\n";
        } else {
            echo "使用已存在的测试邮件模板: {$this->template->name}\n";
        }
    }

    /**
     * 清理测试环境
     */
    public function tearDown(): void
    {
        // 清理测试过程中创建的通知日志
        NotificationLog::where('recipient', 'ethankhoo09@gmail.com')
            ->where('created_at', '>', now()->subHours(1))
            ->delete();
        
        echo "清理了测试通知日志\n";
        
        parent::tearDown();
    }

    /**
     * 测试发送测试邮件功能
     *
     * 测试步骤:
     * 1. 登录系统
     * 2. 访问通知历史页面
     * 3. 打开测试邮件表单
     * 4. 填写邮件信息并发送
     * 5. 验证发送结果和通知记录
     * 
     * 预期结果:
     * - 测试邮件应成功发送
     * - 通知历史中应有对应记录
     * - 记录状态应为"sent"
     */
    public function testSendTestEmail(): void
    {
        $this->browse(function (Browser $browser) {
            try {
                echo "开始测试发送测试邮件功能...\n";
                
                // 1. 登录系统
                $browser->loginAs($this->user)
                    ->waitForTurbolinksLoad(10)
                    ->visitAndWaitForTurbolinks('/notification-history', 10)
                    ->waitForText('Notification History', 10)
                    ->assertSee('Notification History')
                    ->screenshot('notification-history-page');
                
                // 2. 打开测试邮件表单
                $browser->click('#toggle-test-email')
                    ->waitUntilMissing('.test-email-container.hidden', 5)
                    ->screenshot('test-email-form');
                
                // 3. 选择接收人
                $browser->click('.toggle-recipients[data-type="test_email_recipient"]')
                    ->waitFor('#selector-test_email_recipient:not(.hidden)', 10)
                    ->radio('input[name="recipient"]', 'ethankhoo09@gmail.com')
                    ->screenshot('recipient-selected');
                
                // 4. 填写邮件内容
                $testSubject = 'Dusk Test Email ' . date('Y-m-d H:i:s');
                $browser->select('#template_type', 'test_mail')
                    ->type('#subject', $testSubject)
                    ->type('#content', 'This is a test email sent from Dusk automated testing.')
                    ->screenshot('email-form-filled');
                
                // 5. 发送测试邮件
                echo "发送测试邮件...\n";
                $browser->press('Send Test Email')
                    ->waitFor('#result:not(.hidden)', 10)
                    ->screenshot('email-sent-result');
                
                // 6. 验证发送结果
                $browser->assertSee('Sending test email...')
                ->waitForText('Test email sent successfully!', 15)
                ->assertSee('Test email sent successfully!'); 
                
                // 7. 验证通知历史记录
                echo "验证通知历史记录...\n";
                $browser->waitForText("$testSubject", 10)
                        ->assertSee($testSubject)
                    ->assertSee('test_mail')
                    ->assertSee('ethankhoo09@gmail.com')
                    ->assertSee('Sent')
                    ->screenshot('notification-history-updated');
                
                // 8. 验证记录状态
                $hasSuccessStatus = $browser->resolver->findOrFail('span.bg-green-100.text-green-800')->isDisplayed();
                if ($hasSuccessStatus) {
                    echo "通知记录状态为'sent'\n";
                } else {
                    echo "未找到成功状态的通知记录\n";
                }
                
                echo "发送测试邮件功能测试完成\n";
            } catch (\Exception $e) {
                echo "测试过程中出错: " . $e->getMessage() . "\n";
                $browser->screenshot('send-test-email-error-' . time());
                throw $e;
            }
        });
    }
    
    /**
     * 测试通知历史筛选功能
     *
     * 测试步骤:
     * 1. 登录系统
     * 2. 访问通知历史页面
     * 3. 使用不同筛选条件查询
     * 4. 验证筛选结果
     * 
     * 预期结果:
     * - 筛选功能应正常工作
     * - 应显示符合条件的记录
     */
    public function testNotificationHistoryFilters(): void
    {
        $this->browse(function (Browser $browser) {
            try {
                echo "开始测试通知历史筛选功能...\n";
                
                // 确保有测试数据
                $this->ensureTestNotificationExists();
                
                // 1. 登录系统
                $browser->loginAs($this->user)
                    ->visit('/notification-history')
                    ->waitForText('Notification History', 10)
                    ->assertSee('Notification History')
                    ->screenshot('notification-history-filter-page');
                
                // 2. 测试类型筛选
                echo "测试类型筛选...\n";
                $browser->select('#notification_type', 'test_mail')
                    ->pause(1000)
                    ->screenshot('filter-by-type');
                
                // 验证筛选结果
                $hasResults = $browser->resolver->findOrFail('table tbody tr')->isDisplayed();
                if ($hasResults) {
                    $browser->assertSee('test_mail')
                        ->assertSee('ethankhoo09@gmail.com');
                    echo "类型筛选显示了正确的结果\n";
                } else {
                    echo "类型筛选没有找到结果\n";
                }
                
                // 3. 测试状态筛选
                echo "测试状态筛选...\n";
                $browser->select('#status', 'sent')
                    ->pause(1000)
                    ->screenshot('filter-by-status');
                
                // 验证筛选结果
                $hasStatusResults = $browser->resolver->findOrFail('table tbody tr')->isDisplayed();
                if ($hasStatusResults) {
                    $browser->assertSee('test_mail')
                        ->assertSee('Sent');
                    echo "状态筛选显示了正确的结果\n";
                } else {
                    echo "状态筛选没有找到结果\n";
                }
                
                // 4. 测试重置筛选
                echo "测试重置筛选...\n";
                $browser->press('Reset')
                    ->pause(1000)
                    ->screenshot('filter-reset');
                
                // 验证重置后显示所有记录
                $browser->assertSee('test_mail');
                
                echo "通知历史筛选功能测试完成\n";
            } catch (\Exception $e) {
                echo "测试过程中出错: " . $e->getMessage() . "\n";
                $browser->screenshot('notification-filters-error-' . time());
                throw $e;
            }
        });
    }
    
    /**
     * 测试查看通知详情功能
     *
     * 测试步骤:
     * 1. 登录系统
     * 2. 访问通知历史页面
     * 3. 点击查看按钮
     * 4. 验证详情模态框内容
     * 
     * 预期结果:
     * - 详情模态框应正确显示
     * - 应包含完整的通知内容
     */
    public function testViewNotificationDetails(): void
    {
        $this->browse(function (Browser $browser) {
            try {
                echo "开始测试查看通知详情功能...\n";
                
                // 确保有测试数据
                $this->ensureTestNotificationExists();
                
                // 1. 登录系统
                $browser->loginAs($this->user)
                    ->visit('/notification-history')
                    ->waitForText('Notification History', 10)
                    ->assertSee('Notification History')
                    ->screenshot('notification-details-page');
                
                // 2. 点击查看按钮
                echo "点击查看通知详情...\n";
                $browser->click('.view-notification')
                    ->waitFor('#notification-modal:not(.hidden)', 5)
                    ->screenshot('notification-details-modal');
                
                // 3. 验证模态框内容
                $browser->assertSee('Notification Detail')
                    ->assertSee('ethankhoo09@gmail.com');
                
                // 检查内容区域
                $hasContent = $browser->resolver->findOrFail('#modal-content')->isDisplayed();
                if ($hasContent) {
                    echo "模态框显示了通知内容\n";
                } else {
                    echo "未找到通知内容区域\n";
                }
                
                // 4. 关闭模态框
                $browser->click('.close-modal')
                    ->waitUntilMissing('#notification-modal:not(.hidden)', 5)
                    ->screenshot('notification-modal-closed');
                
                echo "查看通知详情功能测试完成\n";
            } catch (\Exception $e) {
                echo "测试过程中出错: " . $e->getMessage() . "\n";
                $browser->screenshot('view-notification-details-error-' . time());
                throw $e;
            }
        });
    }
    
    /**
     * 确保存在测试通知记录
     * 如果没有，则创建一条用于测试的记录
     */
    private function ensureTestNotificationExists(): void
    {
        $hasTestNotification = NotificationLog::where('recipient', 'ethankhoo09@gmail.com')
            ->where('type', 'test_mail')
            ->exists();
            
        if (!$hasTestNotification) {
            echo "创建测试通知记录...\n";
            NotificationLog::create([
                'type' => 'test_mail',
                'recipient' => 'ethankhoo09@gmail.com',
                'subject' => 'Test Notification for Dusk',
                'content' => '<h1>Test Content</h1><p>This is a test notification created for Dusk testing.</p>',
                'status' => 'sent',
                'user_id' => $this->user->id,
                'message_template_id' => $this->template->id,
                'sent_at' => now(),
            ]);
        } else {
            echo "已存在测试通知记录\n";
        }
    }
}
