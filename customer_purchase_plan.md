# 客户购买计划

## 功能概述

### 1. 购物车功能
- 支持为特定客户创建购物车
- 添加商品到购物车
- 调整商品数量
- 移除商品
- 查看购物车总价

### 2. 订单管理
- 从购物车创建订单
- product 页面直接购买
- 查看订单列表
- 查看订单详情
- 更新订单状态
- 取消订单
- 导出订单PDF

### 3. 发票管理
- 从订单创建发票
- 查看发票列表
- 查看发票详情
- 提交电子发票
- 下载发票PDF

## 数据模型

### 1. 购物车 (Cart)
- 关联客户
- 包含多个购物车项目
- 记录创建时间和更新时间

### 2. 购物车项目 (CartItem)
- 关联购物车
- 关联商品
- 记录数量和价格
- 支持商品规格选择

### 3. 订单 (SalesOrder)
- 关联客户和销售人员
- 包含多个订单项目
- 记录订单状态、支付状态和支付方式
- 记录订单总价、税额和折扣

### 4. 订单项目 (SalesOrderItem)
- 关联订单和商品
- 记录数量、单价和总价
- 支持商品规格选择

### 5. 发票 (EInvoice)
- 关联订单和客户
- 记录发票状态和提交信息
- 支持电子发票生成和下载

## 用户界面

### 1. 购物车页面
- 显示购物车中的商品列表
- 提供数量调整和移除功能
- 显示小计和总计
- 提供结账按钮

### 2. 订单列表页面
- 显示所有订单
- 支持按状态、日期和客户筛选
- 提供查看详情和创建新订单的入口

### 3. 订单详情页面
- 显示订单基本信息和项目列表
- 提供状态更新和取消功能
- 支持导出PDF和创建发票

### 4. 发票列表页面
- 显示所有发票
- 支持按状态、日期和客户筛选
- 提供查看详情和创建新发票的入口

### 5. 发票详情页面
- 显示发票基本信息和项目列表
- 提供状态更新和提交功能
- 支持下载PDF

系统已有的与客户购买相关的主要数据模型包括：

1. **Customer**：客户信息，包括基本信息、会员等级等
   - 包含客户基本信息（姓名、联系方式、地址等）
   - 支持会员等级（normal, silver, gold, platinum）

2. **Product**：产品信息，包括价格、库存等
   - 包含产品基本信息（名称、SKU、条码等）
   - 支持成本价和销售价
   - 支持产品分类和品牌
   - 支持库存管理（最小库存量等）

3. **SalesOrder**：销售订单，记录客户购买信息
   - 包含订单基本信息（订单号、客户ID、销售员ID等）
   - 支持订单状态和支付状态
   - 支持订单金额计算（小计、税额、折扣、总额等）
   - 支持处方关联

4. **SalesOrderItem**：销售订单项，记录购买的具体产品
   - 包含产品信息（产品ID、名称、SKU等）
   - 支持数量、单价、折扣、税率等
   - 支持产品规格信息

5. **Payment**：支付记录
   - 支持多种支付方式
   - 记录支付金额、日期、参考号等
   - 目前主要用于采购订单的支付

7. **Stock**：库存记录
   - 记录每个产品在每个门店的库存量
   - 支持最小库存量和最大库存量设置
   - 支持库存位置记录

8. **StockMovement**：库存变动记录
   - 记录库存变动类型、数量、成本等
   - 支持多种变动类型（采购入库、销售出库等）
   - 支持批次号记录

### 现有UI和路由分析

1. **产品浏览**：
   - 已有产品列表和详情页面
   - 缺少面向客户的产品展示页面

2. **销售管理**：
   - 在导航菜单中有销售管理入口，但相关路由未实现
   - 客户详情页面有"新销售订单"按钮，但路由未实现

3. **支付处理**：
   - 已有支付控制器，但主要用于采购订单的支付
   - 缺少面向客户的支付流程

### 缺失功能

虽然系统已有销售订单相关的数据模型，但缺少完整的客户购买流程实现，主要缺失：

1. **购物车功能**：✅
   - ✅ 已实现购物车相关模型和控制器
   - ✅ 已实现购物车与会话/用户的关联机制
   - ✅ 已实现购物车管理界面
   - ✅ 添加了与客户的关联，支持查看特定客户的购物车

2. **销售订单管理**：✅
   - ✅ 创建SalesOrderController
   - ✅ 实现订单创建和管理
   - ✅ 设计订单状态流转
   - ✅ 开发订单管理界面

3. **支付处理**：✅
   - ✅ 实现多种支付方式
   - ✅ 处理支付状态更新
   - ✅ 集成支付通知

4. **库存管理集成**：✅
   - ✅ 实现销售时库存检查
   - ✅ 实现销售后库存更新
   - ✅ 记录库存移动历史

## 当前实现情况

### 购物车功能（已完成）

购物车功能已经完整实现，包括：

1. **数据模型**：
   - 创建了Cart和CartItem模型
   - 实现了与用户/会话的关联
   - 支持产品规格存储（JSON格式）
   - 添加了与客户的关联，支持查看特定客户的购物车

2. **控制器功能**：
   - 实现了CartController，支持所有基本操作
   - 添加商品到购物车
   - 更新购物车商品数量
   - 从购物车中移除商品
   - 清空购物车
   - 获取购物车商品数量
   - 支持查看特定客户的购物车

3. **用户界面**：
   - 实现了购物车页面，显示商品列表、数量和价格
   - 支持实时更新商品数量和价格
   - 添加了导航栏购物车图标和数量显示
   - 实现了添加到购物车的表单组件
   - 支持产品规格选择
   - 添加了客户选择功能，可以查看和管理特定客户的购物车

4. **用户体验优化**：
   - 添加了Toast通知系统
   - 实现了数量增减按钮
   - 添加了确认对话框
   - 实现了实时价格计算
   - 优化了导航栏布局，减少拥挤感

### 下一步计划

接下来需要实现的功能包括：

1. **结账流程**：✅
   - ✅ 创建结账页面和流程
   - ✅ 实现订单确认和支付方式选择
   - ✅ 实现优惠券应用机制

2. **销售订单管理**：✅
   - ✅ 创建SalesOrderController
   - ✅ 实现订单创建和管理
   - ✅ 设计订单状态流转
   - ✅ 开发订单管理界面

3. **支付处理**：✅
   - ✅ 实现多种支付方式
   - ✅ 处理支付状态更新
   - ✅ 集成支付通知

4. **库存管理集成**：✅
   - ✅ 实现销售时库存检查
   - ✅ 实现销售后库存更新
   - ✅ 记录库存移动历史

## 马来西亚电子发票(E-Invoice)要求

### 基本概述

马来西亚税务局(IRBM)正在推行电子发票系统，要求企业按照特定格式生成、验证和交换电子发票。根据最新规定，从2024年8月开始，年营业额超过1亿马币的企业必须实施电子发票系统，并逐步扩展到其他企业。

### 技术要求

1. **文件格式**
   - 电子发票必须使用XML或JSON格式
   - PDF、Word文档、JPG图片或纸质文件不被视为电子发票
   - 电子发票结构包含53个必填字段

2. **验证流程**
   - 电子发票必须提交至IRBM的MyInvois平台进行验证
   - 验证后，发票将获得唯一的数字认证序列号
   - 验证后的发票必须包含嵌入式QR码，用于验证发票的存在和状态

3. **提交方式**
   - 可通过MyInvois门户手动提交
   - 可通过API自动提交（推荐用于批量处理）
   - IRBM提供了软件开发工具包(SDK)用于集成

### 交换流程

1. **发票生成**
   - 供应商创建符合要求的电子发票
   - 发票必须包含所有必填字段和信息

2. **发票提交**
   - 供应商将电子发票提交至IRBM的MyInvois平台
   - IRBM对发票进行验证，包括结构验证和纳税人识别号(TIN)验证

3. **发票验证**
   - 验证通过后，IRBM为发票分配唯一标识符和QR码
   - IRBM通知供应商和接收方验证结果

4. **发票交换**
   - 验证后，供应商负责将验证过的电子发票发送给买家
   - 交换的电子发票应包括原始验证过的电子发票、IRBM提供的验证链接(QR码)和PDF副本

5. **发票拒绝机制**
   - 买家可以在收到发票后提交拒绝请求，并说明拒绝原因
   - 供应商可以同意拒绝并在72小时内取消电子发票

### 处理机制

1. **电子签名要求**
   - 电子发票必须使用电子签名
   - 必须使用本地证书

2. **验证检查**
   - IRBM平台会执行多项验证检查
   - 包括发票结构验证和纳税人信息验证

3. **QR码验证**
   - QR码可通过MyInvois门户验证发票的存在和状态
   - 买家可以使用QR码验证发票的真实性

### 数据结构和字段要求

马来西亚电子发票必须包含以下主要数据组：

1. **发票头信息**
   - 发票号码（唯一标识符）
   - 发票日期
   - 发票类型（发票、借记单、贷记单等）
   - 发票货币
   - 付款条款和到期日
   - 参考号（如采购订单号）

2. **供应商信息**
   - 供应商名称
   - 供应商税号（Business Registration Number）
   - 供应商地址（包括邮政编码、城市、州/省、国家）
   - 供应商联系信息（电话、电子邮件）
   - 供应商银行账户信息（可选）

3. **客户信息**
   - 客户名称
   - 客户税号（如适用）
   - 客户地址（包括邮政编码、城市、州/省、国家）
   - 客户联系信息（电话、电子邮件）

4. **商品/服务明细**
   - 商品/服务描述
   - 数量和单位
   - 单价（不含税）
   - 折扣（如适用）
   - 税率和税额
   - 行小计（含税和不含税）

5. **税务信息**
   - 税种（如销售和服务税SST）
   - 税率
   - 税额
   - 税收分类代码

6. **总计信息**
   - 不含税总额
   - 税额总计
   - 折扣总额
   - 发票总额（含税）
   - 应付金额

7. **支付信息**
   - 支付方式
   - 支付状态
   - 支付参考号（如适用）
   - 支付日期（如已支付）

8. **其他必要信息**
   - 发票语言代码
   - 发票生成时间戳
   - 电子签名信息
   - 备注和附加信息

### XML格式示例

以下是马来西亚电子发票XML格式的简化示例结构：

```xml
<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" 
         xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" 
         xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">
    
    <!-- 发票头信息 -->
    <cbc:ID>INV-2024-00001</cbc:ID>
    <cbc:IssueDate>2024-08-15</cbc:IssueDate>
    <cbc:InvoiceTypeCode>380</cbc:InvoiceTypeCode>
    <cbc:DocumentCurrencyCode>MYR</cbc:DocumentCurrencyCode>
    <cbc:PaymentTerms>30 days</cbc:PaymentTerms>
    
    <!-- 供应商信息 -->
    <cac:AccountingSupplierParty>
        <cac:Party>
            <cac:PartyName>
                <cbc:Name>Optic System Sdn Bhd</cbc:Name>
            </cac:PartyName>
            <cac:PartyTaxScheme>
                <cbc:CompanyID>MY1234567890</cbc:CompanyID>
                <cac:TaxScheme>
                    <cbc:ID>SST</cbc:ID>
                </cac:TaxScheme>
            </cac:PartyTaxScheme>
            <cac:PostalAddress>
                <cbc:StreetName>123 Main Street</cbc:StreetName>
                <cbc:CityName>Kuala Lumpur</cbc:CityName>
                <cbc:PostalZone>50000</cbc:PostalZone>
                <cac:Country>
                    <cbc:IdentificationCode>MY</cbc:IdentificationCode>
                </cac:Country>
            </cac:PostalAddress>
            <cac:Contact>
                <cbc:Telephone>+60312345678</cbc:Telephone>
                <cbc:ElectronicMail>contact@opticsystem.com</cbc:ElectronicMail>
            </cac:Contact>
        </cac:Party>
    </cac:AccountingSupplierParty>
    
    <!-- 客户信息 -->
    <cac:AccountingCustomerParty>
        <cac:Party>
            <cac:PartyName>
                <cbc:Name>Customer Name</cbc:Name>
            </cac:PartyName>
            <cac:PartyTaxScheme>
                <cbc:CompanyID>MY9876543210</cbc:CompanyID>
                <cac:TaxScheme>
                    <cbc:ID>SST</cbc:ID>
                </cac:TaxScheme>
            </cac:PartyTaxScheme>
            <cac:PostalAddress>
                <cbc:StreetName>456 Customer Street</cbc:StreetName>
                <cbc:CityName>Penang</cbc:CityName>
                <cbc:PostalZone>10000</cbc:PostalZone>
                <cac:Country>
                    <cbc:IdentificationCode>MY</cbc:IdentificationCode>
                </cac:Country>
            </cac:PostalAddress>
        </cac:Party>
    </cac:AccountingCustomerParty>
    
    <!-- 商品/服务明细 -->
    <cac:InvoiceLine>
        <cbc:ID>1</cbc:ID>
        <cbc:InvoicedQuantity unitCode="EA">2</cbc:InvoicedQuantity>
        <cbc:LineExtensionAmount currencyID="MYR">1000.00</cbc:LineExtensionAmount>
        <cac:Item>
            <cbc:Name>Optical Lens - Premium</cbc:Name>
            <cbc:Description>High quality optical lens with anti-glare coating</cbc:Description>
            <cac:SellersItemIdentification>
                <cbc:ID>LENS-001</cbc:ID>
            </cac:SellersItemIdentification>
            <cac:ClassifiedTaxCategory>
                <cbc:Percent>6</cbc:Percent>
                <cac:TaxScheme>
                    <cbc:ID>SST</cbc:ID>
                </cac:TaxScheme>
            </cac:ClassifiedTaxCategory>
        </cac:Item>
        <cac:Price>
            <cbc:PriceAmount currencyID="MYR">500.00</cbc:PriceAmount>
        </cac:Price>
    </cac:InvoiceLine>
    
    <!-- 税务信息和总计 -->
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="MYR">60.00</cbc:TaxAmount>
        <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="MYR">1000.00</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="MYR">60.00</cbc:TaxAmount>
            <cac:TaxCategory>
                <cbc:Percent>6</cbc:Percent>
                <cac:TaxScheme>
                    <cbc:ID>SST</cbc:ID>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
    </cac:TaxTotal>
    
    <cac:LegalMonetaryTotal>
        <cbc:LineExtensionAmount currencyID="MYR">1000.00</cbc:LineExtensionAmount>
        <cbc:TaxExclusiveAmount currencyID="MYR">1000.00</cbc:TaxExclusiveAmount>
        <cbc:TaxInclusiveAmount currencyID="MYR">1060.00</cbc:TaxInclusiveAmount>
        <cbc:PayableAmount currencyID="MYR">1060.00</cbc:PayableAmount>
    </cac:LegalMonetaryTotal>
</Invoice>
```

### 实现注意事项

1. **合规性考虑**
   - 确保发票格式严格遵循IRBM的要求
   - 使用IRBM提供的SDK进行验证
   - 定期检查IRBM的更新和变更

2. **技术实现**
   - 创建专门的电子发票生成服务
   - 实现XML/JSON格式转换功能
   - 集成QR码生成库
   - 实现与MyInvois平台的API集成

3. **安全考虑**
   - 实现安全的电子签名机制
   - 保护发票数据的传输和存储
   - 实现访问控制和审计日志

4. **用户体验**
   - 提供发票预览功能
   - 支持发票下载（XML和PDF格式）
   - 实现发票状态追踪和通知
   - 提供发票历史查询功能

## 客户购买功能需求分析

### 核心需求

1. **浏览产品**：客户能够浏览可购买的产品
   - 产品列表页面，支持分类和筛选
   - 产品详情页面，显示详细信息和规格
   - 支持产品搜索功能

2. **购物车**：客户能够将产品添加到购物车，并管理购物车
   - 添加/移除产品
   - 修改产品数量
   - 查看购物车小计和总计
   - 保存购物车状态（会话或数据库）

3. **结账流程**：✅
   - ✅ 创建结账页面和流程
   - ✅ 实现订单确认和支付方式选择
   - ✅ 实现优惠券应用机制

4. **支付处理**：支持多种支付方式
   - 现金支付
   - 银行转账
   - 支票支付
   - 支付状态更新和通知

5. **订单管理**：客户和管理员能够查看和管理订单
   - 查看订单历史
   - 查看订单详情
   - 取消/修改订单
   - 订单状态追踪

6. **库存管理**：销售后自动更新库存
   - 销售时检查库存
   - 销售后减少库存
   - 记录库存移动历史
   - 库存不足时的提示

### 扩展需求

2. **会员折扣**：根据客户会员等级提供不同折扣
   - 不同会员等级的折扣规则
   - 折扣计算和显示
   - 会员积分累计

3. **处方关联**：对于处方类产品，关联客户处方
   - 选择客户现有处方
   - 创建新处方
   - 处方产品的特殊处理

4. **订单状态追踪**：客户能够追踪订单状态
   - 订单状态更新通知
   - 订单状态历史记录
   - 订单完成时间估计

5. **退货/退款处理**：支持订单退货和退款流程
   - 退货申请
   - 退款处理
   - 库存更新
   - 财务记录更新

## 可行性分析

### 技术可行性

系统已经具备实现客户购买功能的基础架构：

1. **数据模型**：已有Customer、Product、SalesOrder等核心模型
   - 数据结构设计合理，支持客户购买流程
   - 已有模型间关系定义清晰
   - 支持软删除和时间戳

2. **认证系统**：已实现用户认证
   - 基于Laravel的认证系统
   - 支持权限管理
   - 支持多角色

3. **库存管理**：已有库存和库存移动相关功能
   - 库存记录和更新机制
   - 库存移动历史
   - 多仓库/门店支持


5. **UI框架**：系统使用现代化的UI框架
   - 基于Tailwind CSS
   - 响应式设计
   - 一致的UI组件

### 集成可行性

需要集成的主要功能：

1. **购物车功能**：需要新增购物车相关模型和控制器
   - 设计购物车数据模型
   - 实现购物车与会话/用户的关联
   - 开发购物车管理界面

2. **支付网关**：需要集成支付处理功能
   - 利用现有Payment模型
   - 扩展支付方式
   - 实现支付状态更新

3. **订单处理流程**：需要实现完整的订单处理流程
   - 创建SalesOrderController
   - 实现订单状态管理
   - 开发订单管理界面

4. **库存更新机制**：需要实现销售后的库存自动更新
   - 利用现有StockMovement机制
   - 实现销售相关的库存移动
   - 处理库存不足情况

## 实现计划

### 第一阶段：基础功能实现 ✅

1. **创建购物车模型和控制器** ✅
   - ✅ 设计购物车数据模型
   - ✅ 实现购物车CRUD操作
   - ✅ 实现购物车与用户/会话的关联
   - ✅ 开发购物车管理界面

2. **实现销售订单控制器** ✅
   - ✅ 创建SalesOrderController
   - ✅ 实现订单创建、查看、更新、取消功能
   - ✅ 实现订单项管理
   - ✅ 开发订单管理界面

3. **设计结账流程** ✅
   - ✅ 创建结账页面
   - ✅ 实现订单信息确认
   - ✅ 实现客户选择/确认
   - ✅ 实现订单金额计算

### 第二阶段：支付和库存集成 ✅

1. **实现支付处理** ✅
   - ✅ 扩展现有Payment模型
   - ✅ 实现支付方式选择
   - ✅ 实现支付状态更新
   - ✅ 实现支付通知处理

2. **库存管理集成** ✅
   - ✅ 实现销售前库存检查
   - ✅ 实现销售后库存自动减少
   - ✅ 实现库存不足时的处理机制
   - ✅ 记录库存移动历史

### 第三阶段：高级功能和优化


#### 订单管理优化 ✅
- [x] 实现订单状态更新和通知
- [x] 添加订单搜索和过滤功能
- [x] 创建订单详情页面
- [x] 实现订单导出功能（PDF/Excel）

#### 退货/退款流程 ✅
- [x] 设计退货请求流程和数据结构
- [x] 实现退货申请表单和验证
- [x] 创建退货管理界面
- [x] 集成退款处理和库存更新

### 第四阶段：电子发票集成

1. **电子发票数据模型设计**
   - 创建EInvoice模型和数据库迁移
   - 定义与销售订单的关系
   - 实现发票状态管理
   - 设计发票编号生成规则

2. **电子发票生成服务**
   - 实现XML/JSON格式发票生成
   - 集成电子签名功能
   - 实现发票数据验证
   - 支持发票预览和测试

3. **MyInvois API集成**
   - 创建与MyInvois平台的API客户端
   - 实现发票提交和验证流程
   - 处理验证响应和错误
   - 实现QR码处理

4. **电子发票用户界面**
   - 创建发票管理界面
   - 实现发票状态显示
   - 支持发票搜索和筛选
   - 提供发票下载选项

5. **电子发票通知系统**
   - 集成电子邮件发送功能
   - 实现发票状态变更通知
   - 支持发票拒绝和取消处理
   - 创建通知设置界面

## 技术实现细节

### 数据模型设计

1. **购物车模型(Cart)**
   ```php
   // 字段：id, user_id/session_id, store_id, created_at, updated_at
   
   Schema::create('carts', function (Blueprint $table) {
       $table->id();
       $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
       $table->string('session_id')->nullable();
       $table->foreignId('store_id')->nullable()->constrained('warehouses')
           ->where('is_store', true)
           ->nullOnDelete();
       $table->timestamps();
       
       // 确保用户或会话ID至少有一个
       $table->index(['user_id', 'session_id']);
   });
   ```

2. **购物车项模型(CartItem)**
   ```php
   // 字段：id, cart_id, product_id, quantity, price, specifications, created_at, updated_at
   
   Schema::create('cart_items', function (Blueprint $table) {
       $table->id();
       $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
       $table->foreignId('product_id')->constrained();
       $table->integer('quantity')->default(1);
       $table->decimal('price', 10, 2);
       $table->json('specifications')->nullable();
       $table->timestamps();
       
       // 确保购物车中每个产品只有一个条目
       $table->unique(['cart_id', 'product_id']);
   });
   ```

3. **电子发票模型(EInvoice)**
   ```php
   // 字段：id, sales_order_id, invoice_number, status, format, content, qr_code, certification_number, submitted_at, validated_at, rejection_reason, sent_to_customer, sent_at, created_at, updated_at, deleted_at
   
   Schema::create('e_invoices', function (Blueprint $table) {
       $table->id();
       $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();
       $table->string('invoice_number')->unique();
       $table->string('status')->default('draft'); // draft, submitted, validated, rejected, cancelled
       $table->string('format')->default('xml'); // xml, json
       $table->text('content')->nullable(); // 存储XML/JSON内容
       $table->string('qr_code')->nullable(); // 存储QR码URL或数据
       $table->string('certification_number')->nullable(); // IRBM验证号
       $table->timestamp('submitted_at')->nullable();
       $table->timestamp('validated_at')->nullable();
       $table->text('rejection_reason')->nullable();
       $table->boolean('sent_to_customer')->default(false);
       $table->timestamp('sent_at')->nullable();
       $table->timestamps();
       $table->softDeletes();
   });
   ```

4. **电子发票历史模型(EInvoiceHistory)**
   ```php
   // 字段：id, e_invoice_id, status, notes, created_by, created_at, updated_at
   
   Schema::create('e_invoice_histories', function (Blueprint $table) {
       $table->id();
       $table->foreignId('e_invoice_id')->constrained()->cascadeOnDelete();
       $table->string('status');
       $table->text('notes')->nullable();
       $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
       $table->timestamps();
   });
   ```

### 控制器设计

1. **CartController**：管理购物车操作
   - `index()`: 显示购物车
   - `store()`: 添加商品到购物车
   - `update()`: 更新购物车商品数量
   - `destroy()`: 从购物车中移除商品
   - `clear()`: 清空购物车

2. **SalesOrderController**：管理销售订单
   - `index()`: 显示订单列表
   - `create()`: 显示创建订单表单
   - `store()`: 创建新订单
   - `show()`: 显示订单详情
   - `update()`: 更新订单状态
   - `cancel()`: 取消订单

3. **CheckoutController**：处理结账流程
   - `index()`: 显示结账页面
   - `confirm()`: 确认订单信息
   - `processPayment()`: 处理支付
   - `complete()`: 完成订单

4. **EInvoiceController**：管理电子发票
   - `index()`: 显示发票列表
   - `show()`: 显示发票详情
   - `generate()`: 生成电子发票
   - `submit()`: 提交电子发票到IRBM
   - `download()`: 下载电子发票
   - `sendToCustomer()`: 发送电子发票给客户
   - `cancel()`: 取消电子发票
   - `history()`: 查看发票历史

### 路由设计

需要添加以下路由：

1. 购物车相关路由
   ```php
   Route::middleware(['auth', 'store'])->group(function () {
       Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
       Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
       Route::put('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
       Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
       Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
   });
   ```

2. 结账流程路由
   ```php
   Route::middleware(['auth', 'store'])->group(function () {
       Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
       Route::post('/checkout/confirm', [CheckoutController::class, 'confirm'])->name('checkout.confirm');
       Route::post('/checkout/coupon', [CheckoutController::class, 'applyCoupon'])->name('checkout.coupon');
       Route::post('/checkout/payment', [CheckoutController::class, 'processPayment'])->name('checkout.payment');
       Route::get('/checkout/complete', [CheckoutController::class, 'complete'])->name('checkout.complete');
   });
   ```

3. 订单管理路由
   ```php
   Route::middleware(['auth', 'store'])->group(function () {
       Route::resource('sales', SalesOrderController::class);
       Route::post('/sales/{sale}/cancel', [SalesOrderController::class, 'cancel'])->name('sales.cancel');
   });
   ```

4. 电子发票路由
   ```php
   Route::middleware(['auth', 'store'])->group(function () {
       Route::get('/invoices', [EInvoiceController::class, 'index'])->name('invoices.index');
       Route::get('/invoices/{invoice}', [EInvoiceController::class, 'show'])->name('invoices.show');
       Route::post('/sales/{sale}/invoices', [EInvoiceController::class, 'generate'])->name('invoices.generate');
       Route::post('/invoices/{invoice}/submit', [EInvoiceController::class, 'submit'])->name('invoices.submit');
       Route::get('/invoices/{invoice}/download', [EInvoiceController::class, 'download'])->name('invoices.download');
       Route::post('/invoices/{invoice}/send', [EInvoiceController::class, 'sendToCustomer'])->name('invoices.send');
       Route::post('/invoices/{invoice}/cancel', [EInvoiceController::class, 'cancel'])->name('invoices.cancel');
       Route::get('/invoices/{invoice}/history', [EInvoiceController::class, 'history'])->name('invoices.history');
   });
   ```

### 视图设计

需要创建以下视图：

1. 产品列表/详情页面
   - `products/index.blade.php`: 产品列表，支持分类和筛选
   - `products/show.blade.php`: 产品详情，显示规格和价格

2. 购物车页面
   - `cart/index.blade.php`: 显示购物车内容，支持数量修改和移除

3. 结账页面
   - `checkout/index.blade.php`: 结账信息填写
   - `checkout/confirm.blade.php`: 订单确认
   - `checkout/payment.blade.php`: 支付方式选择
   - `checkout/complete.blade.php`: 订单完成

4. 订单管理页面
   - `sales/index.blade.php`: 订单列表
   - `sales/show.blade.php`: 订单详情
   - `sales/create.blade.php`: 创建订单

5. 电子发票页面
   - `invoices/index.blade.php`: 发票列表
   - `invoices/show.blade.php`: 发票详情
   - `invoices/preview.blade.php`: 发票预览
   - `invoices/history.blade.php`: 发票历史

## 风险评估

1. **库存同步问题**：多用户同时购买可能导致库存不一致
   - 解决方案：使用数据库事务和锁机制
   - 实现库存预留机制
   - 定期库存同步检查

2. **支付集成复杂性**：支付网关集成可能面临技术挑战
   - 解决方案：先实现基本支付方式（现金、银行转账）
   - 逐步集成更复杂的支付网关
   - 使用队列处理支付通知

3. **订单状态管理**：复杂的订单状态流转需要仔细设计
   - 解决方案：定义清晰的状态转换规则
   - 实现状态变更日志
   - 使用状态机模式管理状态转换

4. **性能考虑**：高并发购买可能带来性能压力
   - 解决方案：实现缓存机制
   - 优化数据库查询
   - 考虑使用队列处理非即时任务

5. **数据一致性**：订单、支付、库存等数据需要保持一致
   - 解决方案：使用数据库事务
   - 实现补偿机制
   - 定期数据一致性检查

6. **电子发票合规风险**：电子发票必须符合马来西亚税务要求
   - 解决方案：使用IRBM提供的SDK进行验证
   - 定期检查IRBM的更新和变更
   - 实现完整的发票验证和状态追踪

7. **API集成风险**：与MyInvois平台的API集成可能面临技术挑战
   - 解决方案：实现重试机制和错误处理
   - 使用队列处理发票提交
   - 实现发票状态同步机制

8. **数据安全风险**：电子发票包含敏感的商业和税务信息
   - 解决方案：实现数据加密
   - 实现访问控制和审计日志
   - 遵循数据保护最佳实践

## 下一步行动

1. 详细设计购物车数据模型
   - 创建购物车和购物车项迁移文件
   - 实现模型关系和方法
   - 编写单元测试

2. 实现购物车基本功能
   - 创建CartController
   - 实现购物车CRUD操作
   - 开发购物车页面

3. 设计销售订单控制器
   - 创建SalesOrderController
   - 实现订单创建和管理功能
   - 开发订单管理页面

4. 创建结账流程页面
   - 设计结账页面布局
   - 实现订单确认功能
   - 集成客户选择功能

5. 研究马来西亚电子发票要求
   - 详细了解IRBM的电子发票技术规范
   - 获取MyInvois平台的API文档和SDK
   - 分析电子发票XML/JSON格式要求
   - 了解电子签名和QR码生成要求

6. 设计电子发票数据模型
   - 创建电子发票和发票历史迁移文件
   - 实现模型关系和方法
   - 编写单元测试

以上是初步分析和计划，将根据进一步的代码分析和需求讨论进行调整和细化。 

3. **StockService**：处理库存业务逻辑
   - `checkStockAvailability()`: 检查库存可用性
   - `reduceStock()`: 减少库存
   - `recordStockMovement()`: 记录库存移动
   - `handleLowStock()`: 处理低库存情况

4. **EInvoiceService**：处理电子发票业务逻辑
   - `generateInvoice()`: 生成电子发票
   - `formatInvoiceData()`: 格式化发票数据
   - `generateXML()`: 生成XML格式发票
   - `generateJSON()`: 生成JSON格式发票
   - `validateInvoiceData()`: 验证发票数据
   - `processValidationResponse()`: 处理验证响应
   - `generateQRCode()`: 生成QR码
   - `createPDF()`: 创建PDF版本发票
   - `sendInvoiceEmail()`: 发送发票邮件
   - `recordInvoiceHistory()`: 记录发票历史 