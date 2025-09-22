# 迁移文件删除计划

## 需要分析的情况
1. 检查数据库架构中是否存在对应表
2. 检查迁移文件是否冗余（如vendors与suppliers表）
3. 确认可以安全删除的文件

## 需要删除的迁移文件（基于初步分析）
- [x] `2023_01_01_000011_create_vendors_table.php`（与suppliers表冗余）
- [x] `2023_01_01_000013_create_brands_table.php`（数据库架构中无对应表）
- [x] `2023_01_01_000014_create_units_table.php`（数据库架构中无对应表）
- [x] `2023_01_01_000015_create_taxes_table.php`（数据库架构中无对应表）
- [x] `2023_01_01_000016_create_categories_table.php`（与product_categories表功能重叠）

## 行动步骤
1. [x] 确认suppliers表与vendors表的关系
2. [x] 逐一确认每个表在架构中是否真的不存在
3. [x] 删除确认无用的迁移文件 