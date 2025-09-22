<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MessageTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupDuplicateTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'templates:cleanup-duplicates {--dry-run : Only show what would be deleted}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup duplicate message templates, keeping one per type';

    // 模板类型映射，将带有前缀的模板名映射到基本类型
    protected $templateTypeMap = [
        'purchase_order_generated' => 'purchase_order_generated',
        'default_purchase_order_generated' => 'purchase_order_generated',
        'inventory_alert' => 'inventory_alert',
        'default_inventory_alert' => 'inventory_alert',
        'payment_overdue' => 'payment_overdue',
        'default_payment_overdue' => 'payment_overdue',
        'quality_inspection_created' => 'quality_inspection_created',
        'default_quality_inspection_created' => 'quality_inspection_created',
        'supplier_order_notification' => 'supplier_order_notification',
        'default_supplier_order_notification' => 'supplier_order_notification',
        'system_notification' => 'system_notification',
        'default_system_notification' => 'system_notification',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting message template cleanup...');
        
        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->warn('Running in dry-run mode. No changes will be made.');
        }
        
        try {
            // 开启事务，确保操作的原子性
            DB::beginTransaction();
            
            // 按照模板基本类型分组处理
            foreach ($this->getBaseTypes() as $baseType) {
                $this->cleanupTemplatesOfType($baseType, $isDryRun);
            }
            
            if ($isDryRun) {
                // 如果是演习模式，回滚所有变更
                DB::rollBack();
                $this->info('Dry run completed. Rolled back all changes.');
            } else {
                // 应用所有变更
                DB::commit();
                $this->info('Template cleanup completed successfully!');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error during template cleanup: ' . $e->getMessage());
            Log::error('Template cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
        
        return 0;
    }
    
    /**
     * 获取所有基本模板类型
     */
    protected function getBaseTypes(): array
    {
        return array_unique(array_values($this->templateTypeMap));
    }
    
    /**
     * 清理特定类型的重复模板
     */
    protected function cleanupTemplatesOfType(string $baseType, bool $isDryRun): void
    {
        $this->info("Processing templates of type: {$baseType}");
        
        // 获取此基本类型的所有模板名称
        $templateNames = array_keys(array_filter($this->templateTypeMap, function($type) use ($baseType) {
            return $type === $baseType;
        }));
        
        // 获取这些模板
        $templates = MessageTemplate::whereIn('name', $templateNames)
            ->orderBy('id')
            ->get();
        
        $this->info("Found " . $templates->count() . " templates of type {$baseType}");
        
        if ($templates->count() <= 1) {
            $this->info("No duplicates found for {$baseType}. Skipping.");
            return;
        }
        
        // 按通道（channel）分组处理
        $templatesByChannel = $templates->groupBy('channel');
        
        foreach ($templatesByChannel as $channel => $channelTemplates) {
            $this->info("Processing {$channel} templates for {$baseType}...");
            
            // 如果只有一个模板，保留它
            if ($channelTemplates->count() <= 1) {
                $this->info("Only one {$channel} template for {$baseType}. Keeping it.");
                continue;
            }
            
            // 查找活动的默认模板
            $activeDefaultTemplate = $channelTemplates->first(function($template) {
                return $template->status === 'active' && $template->is_default;
            });
            
            // 如果没有找到活动的默认模板，按优先级选择一个模板保留
            $templateToKeep = $activeDefaultTemplate;
            
            if (!$templateToKeep) {
                // 优先选择活动的模板
                $activeTemplates = $channelTemplates->filter(function($template) {
                    return $template->status === 'active';
                });
                
                if ($activeTemplates->count() > 0) {
                    // 优先选择非前缀的模板名称 (例如，'inventory_alert' 而非 'default_inventory_alert')
                    $nonPrefixedTemplates = $activeTemplates->filter(function($template) {
                        return !str_starts_with($template->name, 'default_');
                    });
                    
                    $templateToKeep = $nonPrefixedTemplates->first() ?? $activeTemplates->first();
                } else {
                    // 如果没有活动模板，选择ID最小的模板
                    $templateToKeep = $channelTemplates->sortBy('id')->first();
                }
            }
            
            // 如果找到要保留的模板，确保它标记为默认和活动状态
            if ($templateToKeep) {
                $this->info("Selected template to keep: ID={$templateToKeep->id}, name={$templateToKeep->name}");
                
                // 如果不是默认模板，将其设为默认
                if (!$templateToKeep->is_default) {
                    $this->info("Setting template {$templateToKeep->id} as default");
                    if (!$isDryRun) {
                        $templateToKeep->is_default = true;
                        $templateToKeep->save();
                    }
                }
                
                // 如果不是活动状态，将其设为活动
                if ($templateToKeep->status !== 'active') {
                    $this->info("Setting template {$templateToKeep->id} as active");
                    if (!$isDryRun) {
                        $templateToKeep->status = 'active';
                        $templateToKeep->save();
                    }
                }
                
                // 删除其他同类型模板
                $templateIdsToDelete = $channelTemplates->filter(function($template) use ($templateToKeep) {
                    return $template->id !== $templateToKeep->id;
                })->pluck('id');
                
                if ($templateIdsToDelete->count() > 0) {
                    $this->info("Deleting duplicate templates: " . implode(', ', $templateIdsToDelete->toArray()));
                    if (!$isDryRun) {
                        MessageTemplate::whereIn('id', $templateIdsToDelete)->delete();
                    }
                }
            } else {
                $this->warn("Could not determine which template to keep for {$baseType} {$channel}. Skipping.");
            }
        }
    }
}
