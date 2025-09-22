<?php

/**
 * inventory表移除计划
 * 
 * 本文件列出了移除inventory表所需的步骤和需要修改的文件。
 * 执行本计划前，请确保系统中没有依赖inventory表的功能正在使用。
 */

echo "inventory表移除计划：\n\n";

echo "步骤1: 创建迁移脚本删除inventory表\n";
echo "-------------------------------------\n";
echo "创建迁移文件: php artisan make:migration drop_inventory_table\n";
echo "修改迁移文件内容：\n";
echo <<<'EOT'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('inventory');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 此处不需要重新创建表，因为我们已决定不再使用此表
    }
};
EOT;
echo "\n\n";

echo "步骤2: 修改或删除以下文件\n";
echo "-------------------------------------\n";
echo "1. app/Models/Inventory.php - 删除此文件\n";
echo "2. app/Http/Controllers/Stock/InventoryController.php - 修改此控制器使用Stock模型\n";
echo "3. app/Repositories/InventoryRepository.php - 修改此仓库类使用Stock模型\n\n";

echo "步骤3: 处理inventory表相关的路由\n";
echo "-------------------------------------\n";
echo "修改 routes/web.php 中的相关路由，使其指向新的控制器方法或删除\n\n";

echo "步骤4: 执行迁移\n";
echo "-------------------------------------\n";
echo "php artisan migrate\n\n";

echo "步骤5: 清除缓存\n";
echo "-------------------------------------\n";
echo "php artisan cache:clear\n";
echo "php artisan config:clear\n";
echo "php artisan route:clear\n\n";

echo "注意事项：\n";
echo "---------\n";
echo "1. 在执行此计划前，确保系统中没有依赖inventory表的功能正在使用\n";
echo "2. 如果有使用inventory表的功能，请先将其迁移到使用Stock模型\n";
echo "3. 建议在测试环境中先执行此计划，确认无误后再在生产环境中执行\n";
echo "4. 执行后请进行全面测试，确保系统所有功能正常\n"; 