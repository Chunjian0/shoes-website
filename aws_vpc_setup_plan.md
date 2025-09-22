# AWS VPC、安全组、ALB、ASG、EC2、ElastiCache、RDS 设置计划 (V2 - Auto Scaling)

本文档旨在规划在 AWS 上从头开始设置一个包含 VPC、安全组、Application Load Balancer (ALB)、Auto Scaling Group (ASG)、EC2 实例、ElastiCache for Redis 和 RDS 数据库的完整、高可用环境。目标是构建一个安全、隔离、可扩展且功能齐全的网络环境来托管应用程序。

## 目标架构 (V2 - Auto Scaling)

*   **VPC**: `project-vpc` (已创建) ✅
*   **子网 (Subnets)**: (已创建) ✅
    *   `project-subnet-public1-us-east-1a`
    *   `project-subnet-private1-us-east-1a`
    *   `project-subnet-public2-us-east-1b`
    *   `project-subnet-private2-us-east-1b`
*   **互联网网关 (IGW)**: `project-igw` (已创建并附加) ✅
*   **NAT 网关 (NAT Gateway)**: `project-nat-public1-us-east-1a` (已创建, 位于公共子网 AZ1) ✅ (基于用户选择 In 1 AZ)
*   **路由表 (Route Tables)**: (结构已创建并修正) ✅
    *   `project-rtb-public`: 关联两个公共子网，`0.0.0.0/0` -> `project-igw`。
    *   `project-rtb-private` (统一私有路由表): 关联两个私有子网，`0.0.0.0/0` -> `project-nat-public1-us-east-1a`。
*   **VPC 端点 (VPC Endpoints)**:
    *   `project-vpce-s3` (S3 网关端点): 已选择并创建。✅
*   **安全组 (Security Groups, SG)**: (待创建/修改)
    *   **ALB 安全组 (`sg-alb`)**: 关联到 ALB。允许来自互联网的 HTTP (80) / HTTPS (443) 入站流量。允许出站到 EC2 实例端口 (80)。
    *   **Web 服务器安全组 (`sg-webserver`)**: 关联到 ASG 启动的 EC2 实例。**只允许**来自 `sg-alb` 的 HTTP (80) / HTTPS (443) 入站流量。允许来自你 IP 的 SSH (22) 流量。允许出站到 `sg-database` (3306) 和 `sg-elasticache` (6379) 以及互联网。
    *   **数据库安全组 (`sg-database`)**: 关联到 RDS 实例。**只允许**来自 `sg-webserver` 的数据库端口 (3306) 入站流量。
    *   **ElastiCache 安全组 (`sg-elasticache`)**: 关联到 ElastiCache 集群。**只允许**来自 `sg-webserver` 的 Redis 端口 (6379) 入站流量。
*   **Application Load Balancer (ALB)**: (待创建) 部署在两个**公共子网**中，接收 Web 流量，关联 `sg-alb`。
*   **Target Group**: (待创建) 定义 ALB 将流量转发到哪里，与 ASG 关联。
*   **Launch Template**: (待创建) 定义 ASG 启动 EC2 实例的配置 (AMI, 类型, `sg-webserver`, User Data 等)。
*   **Auto Scaling Group (ASG)**: (待创建) 管理一组运行 Laravel 的 EC2 实例，跨越两个**公共子网**，关联 Launch Template 和 Target Group。
*   **EC2 实例**: 由 ASG 自动启动和管理，放置在**公共子网**中，关联 `sg-webserver`。**不需要**单独的 EIP 接收 Web 流量。
*   **ElastiCache for Redis**: (待创建) 放置在**私有子网**中，使用**私有子网组**，关联 `sg-elasticache`。
*   **RDS 实例**: (待创建) 放置在**私有子网**中，使用**私有子网组**，关联 `sg-database`，配置为不可公开访问。

## 架构图描述 (V3 - Auto Scaling with Frontend on S3/CloudFront)

```mermaid
graph TD
    subgraph "User Interaction"
        UserBrowser[User Browser]
    end

    subgraph "AWS Cloud"
        subgraph CDN [CDN Layer]
           CloudFront[CloudFront Distribution]
        end

        subgraph Storage [Static Content Storage]
           S3_Frontend[S3 Bucket: project-frontend-bucket <br/>(Website Hosting Enabled)]
        end

        subgraph VPC [VPC: project-vpc]
            direction LR

            ALB[ALB: project-alb <br/>sg-alb (Public Subnets)]
            IGW[Internet Gateway: project-igw]
            NAT_GW[NAT Gateway: project-nat... <br/>(in Public AZ1)]
            S3_Endpoint[S3 Endpoint: project-vpce-s3]

            subgraph PublicRouteTable [Public Route Table: project-rtb-public]
                direction TB
                PublicRT_Rule1[0.0.0.0/0 -> IGW]
            end

            subgraph PrivateRouteTable [Private Route Table: project-rtb-private]
                 direction TB
                 PrivateRT_Rule1[0.0.0.0/0 -> NAT GW]
                 PrivateRT_Rule2[S3 -> S3 Endpoint]
            end

            subgraph AZ1 [可用区 1: us-east-1a]
                direction TB
                PublicSubnet1[Public Subnet 1: ...public1...]
                PrivateSubnet1[Private Subnet 1: ...private1...]
                ALB_Node1[ALB Node]
                ASG_EC2_1[EC2 (ASG) <br/>sg-webserver <br/>(in Public Subnet)]
                ElastiCache_Node1[ElastiCache Node <br/>sg-elasticache <br/>(in Private Subnet)]
                RDS_Primary[RDS Primary <br/>sg-database <br/>(in Private Subnet)]

                PublicSubnet1 -- Associated --> PublicRouteTable
                PublicSubnet1 --> ALB_Node1
                PublicSubnet1 --> ASG_EC2_1
                PrivateSubnet1 -- Associated --> PrivateRouteTable
                PrivateSubnet1 --> ElastiCache_Node1
                PrivateSubnet1 --> RDS_Primary
            end

            subgraph AZ2 [可用区 2: us-east-1b]
                direction TB
                PublicSubnet2[Public Subnet 2: ...public2...]
                PrivateSubnet2[Private Subnet 2: ...private2...]
                ALB_Node2[ALB Node]
                ASG_EC2_2[EC2 (ASG) <br/>sg-webserver <br/>(in Public Subnet)]
                ElastiCache_Node2[(ElastiCache Node - Optional Replica)]
                RDS_Standby[(RDS Standby - Optional)]

                PublicSubnet2 -- Associated --> PublicRouteTable
                PublicSubnet2 --> ALB_Node2
                PublicSubnet2 --> ASG_EC2_2
                PrivateSubnet2 -- Associated --> PrivateRouteTable
                PrivateSubnet2 --> ElastiCache_Node2
                PrivateSubnet2 --> RDS_Standby
            end

            TargetGroup[Target Group: project-laravel-tg]
            ASG[ASG: project-laravel-asg] --> LaunchTemplate[Launch Template: project-laravel-lt]
            LaunchTemplate -- Defines --> ASG_EC2_1 & ASG_EC2_2

            ALB --> TargetGroup
            ASG --> TargetGroup

            ASG_EC2_1 & ASG_EC2_2 -- API Calls --> ElastiCache_Node1
            ASG_EC2_1 & ASG_EC2_2 -- API Calls --> RDS_Primary

            VPC -- Attached --> IGW
            VPC -- Contains --> S3_Endpoint
        end
    end

    UserBrowser -- Request Website --> CloudFront
    CloudFront -- Get Static Files --> S3_Frontend
    S3_Frontend -- Serves Files --> CloudFront
    CloudFront -- Deliver Website Files --> UserBrowser
    UserBrowser -- API Calls (e.g., /api/*) --> ALB

    %% Connections between VPC components remain similar to V2
```

*   **用户访问流程**:
    1.  用户的浏览器请求网站域名，该域名指向 **CloudFront** 分发。
    2.  **CloudFront** 从配置的源（**S3 存储桶 `project-frontend-bucket`**）获取前端静态文件 (HTML, CSS, JS, 图片等)。如果文件已在 CloudFront 边缘节点缓存，则直接从缓存提供。
    3.  CloudFront 将前端文件发送给用户浏览器。
    4.  浏览器加载并执行前端代码 (例如 React, Vue, Angular 或纯 JS)。
    5.  当前端应用需要与后端交互时 (例如获取数据、提交表单)，它会向**后端的 API 端点** (即 **ALB** 的 DNS 名称或配置的 API 子域名) 发送 API 请求。**注意：需要配置 CORS 策略允许来自 CloudFront 域的请求。**
*   **后端处理流程**:
    6.  **ALB** (位于公共子网，受 `sg-alb` 保护) 接收到 API 请求。
    7.  ALB 将请求转发到注册的健康 **Target Group** (`project-laravel-tg`)。
    8.  **ASG** (`project-laravel-asg`) 确保健康的 **EC2 实例** (运行 Laravel, 位于公共子网, 受 `sg-webserver` 保护) 在 Target Group 中处理请求。
    9.  **EC2 实例** 处理 API 请求，可能需要访问 **RDS 数据库** (位于私有子网, 受 `sg-database` 保护) 和 **ElastiCache for Redis** (位于私有子网, 受 `sg-elasticache` 保护)。
    10. EC2 实例通过 **NAT 网关** (如果需要访问外部服务) 或 **S3 VPC 端点** (访问 S3) 进行出站连接。
    11. API 响应通过 EC2 -> Target Group -> ALB 返回给用户的浏览器。
*   资源分布在**两个可用区**以提高可用性。

---

## 详细设置步骤 (V2 - Auto Scaling - V3 Frontend Update)

**(步骤 1-14 基本保持不变，关注点在于组件配置)**

**1. VPC 创建** ✅
    *   已创建: `project-vpc`
    *   DNS 主机名/解析: 已启用

**2. 子网 (Subnets) 创建** ✅
    *   已创建:
        *   `project-subnet-public1-us-east-1a`
        *   `project-subnet-private1-us-east-1a`
        *   `project-subnet-public2-us-east-1b`
        *   `project-subnet-private2-us-east-1b`
    *   公共子网: 已启用自动分配公有 IP。

**3. 互联网网关 (IGW) 创建与附加** ✅
    *   已创建: `project-igw`, 已附加到 VPC。

**4. 弹性 IP (EIP) for NAT 网关** ✅
    *   已确认: EIP 已分配并关联到 NAT 网关。

**5. NAT 网关 (NAT Gateway) 创建** ✅
    *   已确认: `project-nat-public1-us-east-1a` 已创建在公共子网 AZ1，关联 EIP。

**6. 路由表 (Route Tables) 配置** ✅
    *   **公共路由表 (`project-rtb-public`)**:
        *   路由: `0.0.0.0/0` -> `project-igw`。
        *   关联: 两个公共子网。
    *   **私有路由表 (`project-rtb-private`)**: (已合并和确认)
        *   路由: `0.0.0.0/0` -> `project-nat-public1-us-east-1a`。
        *   路由: S3 -> `project-vpce-s3`。
        *   关联: 两个私有子网。

**7. 安全组 (Security Groups) 配置** (部分完成，需新增和修改)
    *   **去哪里**: EC2 控制台 -> 安全组
    *   **创建 ALB 安全组 (`sg-alb`)**: (待完成)
        *   名称: `sg-alb` (或 `project-sg-alb`)
        *   描述: `Allows HTTP/HTTPS from internet to ALB`
        *   VPC: `project-vpc`
        *   **入站规则**:
            *   HTTP (80), 源: `0.0.0.0/0`
            *   HTTPS (443), 源: `0.0.0.0/0` (如果计划使用 HTTPS)
        *   **出站规则**: 允许所有。
    *   **创建 ElastiCache 安全组 (`sg-elasticache`)**: (待完成)
        *   名称: `sg-elasticache` (或 `project-sg-elasticache`)
        *   描述: `Allows Redis access from webserver SG`
        *   VPC: `project-vpc`
        *   **入站规则**:
            *   Custom TCP (6379), 源: `sg-webserver` 的 ID (待创建后获取)
        *   **出站规则**: 允许所有。
    *   **创建/确认 Web 服务器安全组 (`sg-webserver`)**: (待完成)
        *   名称: `sg-webserver` (或 `project-sg-webserver`)
        *   描述: `Allows traffic from ALB, SSH, and outbound to DB/Cache`
        *   VPC: `project-vpc`
        *   **入站规则**:
            *   HTTP (80), **源**: `sg-alb` 的 ID (待创建后获取)
            *   HTTPS (443), **源**: `sg-alb` 的 ID (待创建后获取) (如果 ALB 使用 HTTPS 转发)
            *   SSH (22), **源**: `你的公网 IP 地址/32`
        *   **出站规则**: 允许所有 (`0.0.0.0/0`)。 (默认规则通常允许访问 `sg-database` 和 `sg-elasticache`，因为它们在同一 VPC 内，或者可以更精细地限制目标为对应安全组 ID)。
    *   **创建/确认 数据库安全组 (`sg-database`)**: (待完成)
        *   名称: `sg-database` (或 `project-sg-database`)
        *   描述: `Allows DB access from webserver SG`
        *   VPC: `project-vpc`
        *   **入站规则**:
            *   MySQL/Aurora (3306), **源**: `sg-webserver` 的 ID (待创建后获取)
        *   **出站规则**: 允许所有。

**8. RDS 数据库实例创建** (待完成)
    *   **去哪里**: RDS 控制台 -> 数据库
    *   按照原计划步骤创建，确保选择正确的 VPC (`project-vpc`), **私有**子网组, 并关联 `sg-database` 安全组。**公开访问设为 "否"**。
    *   记下**数据库端点 (Endpoint)**。

**9. ElastiCache for Redis 创建** (新增)
    *   **创建 ElastiCache 子网组**: (待完成)
        *   **去哪里**: ElastiCache -> 子网组
        *   创建包含**两个私有子网**的子网组 (例如 `project-elasticache-subnet-group`)。
    *   **创建 ElastiCache Redis 集群**: (待完成)
        *   **去哪里**: ElastiCache -> Redis clusters
        *   创建集群，选择节点类型。
        *   **连接**: 选择 `project-vpc`。
        *   **子网组**: 选择上面创建的 `project-elasticache-subnet-group`。
        *   **安全**: 选择 `sg-elasticache` 安全组。
        *   记下**主端点 (Primary Endpoint)**。

**10. 启动配置 (Launch Template) 创建** (新增)
    *   **去哪里**: EC2 -> 启动模板
    *   **做什么**: 创建 `project-laravel-launch-template`。
        *   配置 AMI, 实例类型, 密钥对。
        *   网络设置: 选择 `project-vpc`, **安全组选择 `sg-webserver`**。
        *   (推荐) 配置 **User Data** 脚本自动安装和配置 Apache/PHP/Composer/Git 等。

**11. 目标组 (Target Group) 创建** (新增)
    *   **去哪里**: EC2 -> 目标组
    *   **做什么**: 创建 `project-laravel-tg`。
        *   目标类型: `Instances`。
        *   协议: `HTTP`, 端口: `80`。
        *   VPC: `project-vpc`。
        *   配置健康检查路径 (例如 `/health`)。

**12. Application Load Balancer (ALB) 创建** (新增)
    *   **去哪里**: EC2 -> 负载均衡器
    *   **做什么**: 创建 `project-alb`。
        *   类型: Application Load Balancer。
        *   Scheme: **Internet-facing**。
        *   网络映射: 选择 `project-vpc` 和**两个公共子网** (`...-public1...`, `...-public2...`)。
        *   安全组: **选择 `sg-alb`**。
        *   侦听器:
            *   HTTP:80 -> 转发到 `project-laravel-tg`。
            *   (推荐) HTTPS:443 -> 转发到 `project-laravel-tg` (需要 ACM 证书)。
    *   记下 **ALB 的 DNS 名称**。

**13. Auto Scaling Group (ASG) 创建** (新增)
    *   **去哪里**: EC2 -> Auto Scaling 组
    *   **做什么**: 创建 `project-laravel-asg`。
        *   选择 `project-laravel-launch-template`。
        *   网络: 选择 `project-vpc` 和**两个公共子网** (`...-public1...`, `...-public2...`)。
        *   负载均衡: 附加到 `project-laravel-tg`。
        *   配置组大小 (Desired, Min, Max)。
        *   (推荐) 配置扩展策略。

**14. Web 服务器安全组 (`sg-webserver`) 规则调整** (重要)
    *   **去哪里**: EC2 -> 安全组
    *   **做什么**: 编辑 `sg-webserver` 的**入站规则**。
        *   将 HTTP/80 和 HTTPS/443 规则的**源**修改为 `sg-alb` 的安全组 ID。
        *   确保 SSH/22 规则的源是你自己的 IP。

**15. 部署与配置 Laravel (后端)** (保持不变，强调 CORS)
    *   部署策略: ...
    *   配置 `.env`:
        *   `APP_URL`: 指向 ALB 的 DNS 名称或你的 API 域名。
        *   数据库凭证 (使用 RDS Endpoint)。
        *   Redis 凭证 (使用 ElastiCache Endpoint)。
        *   ...
    *   **重要**: 在 Laravel 应用中配置 **CORS**，允许来自你的 CloudFront 分发域名的请求。可以使用 `fruitcake/laravel-cors` 包。
    *   运行初始化命令: ...

**16. 部署前端应用到 S3** (新增)
    *   **创建 S3 存储桶**:
        *   创建一个 S3 存储桶 (例如 `project-frontend-bucket`) 用于托管静态文件。
        *   **启用静态网站托管**。
        *   配置存储桶策略，允许公开读取对象 (如果直接用 S3 网站端点) 或只允许 CloudFront 读取 (如果使用 OAI)。
    *   **构建前端应用**: 生成生产环境的前端静态文件 (HTML, CSS, JS 包)。
    *   **上传文件**: 将构建好的文件上传到 S3 存储桶的根目录。
    *   **配置 CloudFront (推荐)**:
        *   创建一个 CloudFront 分发。
        *   **源**: 指向你的 S3 存储桶。考虑使用 Origin Access Identity (OAI) 来限制只有 CloudFront 能访问 S3 内容。
        *   配置缓存行为、默认根对象 (`index.html`)、错误页面、HTTPS (使用 ACM 证书) 等。
        *   将你的网站域名指向 CloudFront 分发的域名。

**17. 测试访问** (调整)
    *   使用**你的网站域名** (指向 CloudFront) 访问应用。
    *   检查前端是否正确加载。
    *   测试前端与后端 API 的交互是否正常 (检查浏览器开发者工具的网络请求和控制台是否有 CORS 错误)。
    *   测试后端功能、数据库连接、Redis 缓存、Auto Scaling (可选)。

---

## 重要注意事项 (更新)

*   **CORS**: 务必正确配置 CORS，这是前后端分离架构的关键。
*   **CloudFront 缓存**: 理解并配置 CloudFront 缓存策略，优化性能并确保前端更新能及时生效 (缓存失效)。
*   **S3 权限**: 仔细配置 S3 存储桶权限，如果使用 CloudFront OAI，则 S3 存储桶不需要公开访问。
*   **安全组**: ... (保持不变)
*   **成本**: ... (保持不变，注意 CloudFront 流量成本)
*   **HTTPS**: ... (确保 CloudFront 配置了 HTTPS)
*   **凭证管理**: ... (保持不变)
*   **IAM 角色**: ... (保持不变)
*   **监控与日志**: ... (保持不变，增加 CloudFront 日志和监控)
*   **备份**: ... (保持不变，考虑 S3 版本控制)
*   **部署自动化**: ... (扩展到包含前端 S3/CloudFront 的部署)
*   **健康检查**: ... (保持不变)
*   **基础设施即代码 (IaC)**: ... (扩展到包含 S3/CloudFront 资源)

---

Security group (sg-042822c49f59825e3 | ass-sg-webserver)