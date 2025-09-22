## SpecStory

## Laravel Daily Log File Configuration (3/27/2025, 7:34:16 AM)

_**User**_

帮我更改一下laravel.log的存储 我想要他每一天创建一个新的文件来存储log 避免一个文件太大

---

_**Assistant**_

我来帮你实现每天创建新的Laravel日志文件。先查看当前的日志配置。

---

_****_

<details>
            <summary>Searched codebase "laravel log configuration daily" • **25** results</summary>
        
| File | Lines |
|------|-------|
| `config/logging.php` | L54-115 |
| `config/logging.php` | L1-54 |
| `config/activitylog.php` | L1-53 |
| `config/logging.php` | L115-132 |
| `app/Console/Kernel.php` | L51-98 |
| `config/backup.php` | L277-321 |
| `app/Console/Kernel.php` | L1-51 |
| `app/Console/Kernel.php` | L98-139 |
| `config/backup.php` | L250-277 |
| `app/Http/Controllers/MediaController.php` | L43-84 |
| `.specstory/history/2025-03-15_09-42-notification-history-page-issues-and-solutions.md` | L7032-7111 |
| `database/migrations/2025_02_01_172018_create_activity_log_table.php` | L1-28 |
| `app/Http/Controllers/MediaDebugController.php` | L54-104 |
| `app/Listeners/LogSentMail.php` | L35-75 |
| `app/Models/NotificationLog.php` | L1-47 |
| `app/Services/ActivityLogService.php` | L1-73 |
| `app/Listeners/LogSentMail.php` | L1-35 |
| `test_controller_email.php` | L1-60 |
| `config/backup.php` | L150-197 |
| `test_email_fix.php` | L52-82 |
| `app/Services/NotificationService.php` | L574-621 |
| `.specstory/history/2025-03-21_14-03-收集文件信息并生成-md文件.md` | L2426-2471 |
| `config/backup.php` | L49-102 |
| `database/migrations/2024_01_17_000001_create_activity_logs_table.php` | L1-39 |
| `app/Http/Controllers/NotificationLogController.php` | L64-121 |

</details>

---

_****_

让我看一下.env文件中的当前日志配置：

---

_****_

<details>
            <summary>Searched codebase ".env" • **2** results</summary>
        
| File |
|------|
| `undefined` |
| `undefined` |

</details>

---

_****_

Read file: undefined

---

_****_

<details>
            <summary>Searched codebase ".env" • **2** results</summary>
        
| File |
|------|
| `undefined` |
| `undefined` |

</details>

---

_****_

根据检查的内容，我需要修改以下文件：

1. 首先，让我们修改项目的.env文件，将日志驱动从"stack"更改为"daily"
2. 然后更新config/logging.php文件中的配置，确保正确设置"daily"驱动

---

_****_

Read file: undefined

---

_****_

创建一个PHP文件来查看当前环境的配置：

---

_****_

```diff
- 
+ <?php
+ 
+ // 显示当前的日志配置
+ echo "Current .env file content:\n";
+ echo file_get_contents('.env') . "\n\n";
+ 
+ // 显示当前的日志驱动
+ echo "Current LOG_CHANNEL: " . env('LOG_CHANNEL', 'not set directly in .env') . "\n";
+ echo "Current LOG_LEVEL: " . env('LOG_LEVEL', 'not set directly in .env') . "\n";
+ 
+ // 显示当前日志配置
+ echo "\nLogging configuration from config/logging.php:\n";
+ print_r(config('logging'));
+ 
+ // 显示storage/logs目录中的文件
+ echo "\nCurrent log files in storage/logs directory:\n";
+ $logFiles = glob(storage_path('logs/*'));
+ foreach ($logFiles as $logFile) {
+     echo basename($logFile) . " - " . round(filesize($logFile) / 1024, 2) . " KB\n";
+ } 
```

---

_****_

让我们运行这个检查脚本：

---

_****_

```bash
cd C:\Apache24\htdocs\Laravel\optic-system && php artisan tinker --execute="require 'check_logging_config.php';" | cat
```

```
inker --execute="require 'check_logging_config.php';" | cat
                       所在位置 行:1 字符: 44
+ cd C:\Apache24\htdocs\Laravel\optic-system && php artisan tinker --ex ...
    + CategoryInfo          : ParserError: (:) [], ParentContainsErrorRecordExce
ption
    + FullyQualifiedErrorId : InvalidEndOfLine

PS C:\Apache24\htdocs\Laravel\optic-system>


```

---

_****_

Windows PowerShell中需要使用分号而不是&&：

---

_****_

```bash
cd C:\Apache24\htdocs\Laravel\optic-system; php artisan tinker --execute="require 'check_logging_config.php';" | cat
```

```
PS C:\Apache24\htdocs\Laravel\optic-system> cd C:\Apache24\htdocs\Laravel\optic-system && php artisan t
inker --execute="require 'check_logging_confcd C:\Apache24\htdocs\Laravel\optic-system && php artisan t
inker --execute="require 'check_logging_config.php';" | cat
所在位置 行:1 字符: 44
+ cd C:\Apache24\htdocs\Laravel\optic-system && php artisan tinker --ex ...
    + CategoryInfo          : ParserError: (:) [], ParentContainsErrorRecordException
    + FullyQualifiedErrorId : InvalidEndOfLine

PS C:\Apache24\htdocs\Laravel\optic-system> cd C:\Apache24\htdocs\Laravel\optic-system; php artisan tin
PS C:\Apache24\htdocs\Laravel\optic-system> cd C:\Apache24\htdocs\Laravel\optic-system; php artisan tin
PS C:\Apache24\htdocs\Laravel\optic-system> cd C:\Apache24\htdocs\Laravel\optic-system; php artisan tin
PS C:\Apache24\htdocs\Laravel\optic-system> cd C:\Apache24\htdocs\Laravel\optic-system; php artisan tin
PS C:\Apache24\htdocs\Laravel\optic-system> cd C:\Apache24\htdocs\Laravel\optic-system; php artisan tin

```

---

_****_

让我们重新尝试运行 artisan 命令：

---

_****_

```bash
php artisan tinker --execute="require 'check_logging_config.php';" | cat
```

```
.php';" | cat
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (               ...  [0] => single:PSObject) [Get-Cont

   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand

cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道 
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                        ):PSObject)
[Get-Content]，Par    ameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道 
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (:PSObject) [Get-Content]，ParameterBindingException    
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (               ...exceptions] => :PSObject) [Get-Cont

   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                ):PSObject)
[Get-Content]，ParameterBi    ndingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (:PSObject) [Get-Content]，ParameterBindingException    
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand

cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道 
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (            [single] => Array:PSObject) [Get-Content]

   ，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                (:PSObject)
[Get-Content]，ParameterBi    ndingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                    [driver] => single:PSObject) [Get

   -Content]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (               ...ogs/laravel.log:PSObject) [Get-Cont

   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand

cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道 
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                    [level] => debug:PSObject) [Get-C

   ontent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (               ...ceholders] => 1:PSObject) [Get-Cont 
   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                ):PSObject)
[Get-Content]，ParameterBi    ndingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (:PSObject) [Get-Content]，ParameterBindingException    
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand

 
cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (            [daily] => Array:PSObject)
[Get-Content]，    ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                (:PSObject)
[Get-Content]，ParameterBi    ndingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand

 
cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道 
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                    [driver] => daily:PSObject) [Get-

   Content]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (               ...ogs/laravel.log:PSObject) [Get-Cont

   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand

 
cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道 
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                    [level] => debug:PSObject) [Get-C

   ontent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                    [days] => 14:PSObject) [Get-Conte

   nt]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (               ...ceholders] => 1:PSObject) [Get-Cont

   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                ):PSObject)
[Get-Content]，ParameterBi    ndingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (:PSObject) [Get-Content]，ParameterBindingException    
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (            [slack] => Array:PSObject)
[Get-Content]，    ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                (:PSObject)
[Get-Content]，ParameterBi    ndingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                    [driver] => slack:PSObject) [Get-

   Content]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                    [url] => :PSObject) [Get-Content]

   ，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (               ... => Laravel Log:PSObject) [Get-Cont

   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                    [emoji] => :boom::PSObject) [Get-

   Content]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                    [level] => debug:PSObject) [Get-C

   ontent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (               ...ceholders] => 1:PSObject) [Get-Cont

   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                ):PSObject) 
[Get-Content]，ParameterBi    ndingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (:PSObject) [Get-Content]，ParameterBindingException    
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand

cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (            [papertrail] => Array:PSObject) [Get-Cont 
   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                (:PSObject)
[Get-Content]，ParameterBi    ndingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                    [driver] => monolog:PSObject) [Ge

   t-Content]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                    [level] => debug:PSObject) [Get-C

   ontent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand

 
cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (               ...yslogUdpHandler:PSObject) [Get-Cont

   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (               ..._with] => Array:PSObject) [Get-Cont

   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand

 
cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                        (:PSObject)
[Get-Content]，Par    ameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                            [host] => :PSObject) [Get

   -Content]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                            [port] => :PSObject) [Get 
   -Content]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (               ...ing] => tls://::PSObject) [Get-Cont 
   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                        ):PSObject)
[Get-Content]，Par    ameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (:PSObject) [Get-Content]，ParameterBindingException    
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (               ...ssors] => Array:PSObject) [Get-Cont

   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                        (:PSObject)
[Get-Content]，Par    ameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (               ...essageProcessor:PSObject) [Get-Cont

   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                        ):PSObject)
[Get-Content]，Par    ameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (:PSObject) [Get-Content]，ParameterBindingException    
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                ):PSObject)
[Get-Content]，ParameterBi    ndingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand

 
cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道 
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (:PSObject) [Get-Content]，ParameterBindingException    
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (            [stderr] => Array:PSObject) [Get-Content] 
   ，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道 
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                (:PSObject)
[Get-Content]，ParameterBi    ndingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                    [driver] => monolog:PSObject) [Ge

   t-Content]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                    [level] => debug:PSObject) [Get-C

   ontent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (               ...r\StreamHandler:PSObject) [Get-Cont 
   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                    [formatter] => :PSObject) [Get-Co

   ntent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand

cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                    [with] => Array:PSObject) [Get-Co

   ntent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                        (:PSObject)
[Get-Content]，Par    ameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (               ...=> php://stderr:PSObject) [Get-Cont

   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                        ):PSObject)
[Get-Content]，Par    ameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (:PSObject) [Get-Content]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (               ...ssors] => Array:PSObject) [Get-Cont 
   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                        (:PSObject)
[Get-Content]，Par    ameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (               ...essageProcessor:PSObject) [Get-Cont

   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand

 
cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道 
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                        ):PSObject)
[Get-Content]，Par    ameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (:PSObject) [Get-Content]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                ):PSObject)
[Get-Content]，ParameterBi    ndingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand

 
cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道 
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (:PSObject) [Get-Content]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道 
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (            [syslog] => Array:PSObject) [Get-Content]

   ，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                (:PSObject)
[Get-Content]，ParameterBi    ndingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                    [driver] => syslog:PSObject) [Get

   -Content]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                    [level] => debug:PSObject) [Get-C

   ontent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand

 
cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道 
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                    [facility] => 8:PSObject) [Get-Co

   ntent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (               ...ceholders] => 1:PSObject) [Get-Cont

   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                ):PSObject)
[Get-Content]，ParameterBi    ndingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (:PSObject) [Get-Content]，ParameterBindingException    
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (            [errorlog] => Array:PSObject) [Get-Conten

   t]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                (:PSObject)
[Get-Content]，ParameterBi    ndingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                    [driver] => errorlog:PSObject) [G

   et-Content]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand

 
cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                    [level] => debug:PSObject) [Get-C

   ontent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (               ...ceholders] => 1:PSObject) [Get-Cont 
   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                ):PSObject)
[Get-Content]，ParameterBi    ndingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand

cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道 
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (:PSObject) [Get-Content]，ParameterBindingException    
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand

cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道 
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (            [null] => Array:PSObject)
[Get-Content]，P    arameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道 
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                (:PSObject)
[Get-Content]，ParameterBi    ndingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                    [driver] => monolog:PSObject) [Ge

   t-Content]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (               ...ler\NullHandler:PSObject) [Get-Cont

   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                ):PSObject)
[Get-Content]，ParameterBi    ndingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand

 
cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道 
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (:PSObject) [Get-Content]，ParameterBindingException    
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (            [emergency] => Array:PSObject) [Get-Conte

   nt]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                (:PSObject)
[Get-Content]，ParameterBi    ndingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (               ...ogs/laravel.log:PSObject) [Get-Cont

   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (                ):PSObject)
[Get-Content]，ParameterBi    ndingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand

 
cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (:PSObject) [Get-Content]，ParameterBindingException    
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (        ):PSObject)
[Get-Content]，ParameterBindingExc    eption
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (:PSObject) [Get-Content]，ParameterBindingException    
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand

 
cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道 
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: ():PSObject) [Get-Content]，ParameterBindingException   
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand

cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (:PSObject) [Get-Content]，ParameterBindingException    
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (Current log fil...logs directory::PSObject) [Get-Cont

   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand

 
cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (api_auth-2025-03-20.log - 27.8 KB:PSObject) [Get-Cont

   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (api_products_debug.log - 0.06 KB:PSObject) [Get-Conte 
   nt]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (auto-add-discounted.log - 2.07 KB:PSObject) [Get-Cont

   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (backup-cleanup.log - 11.44 KB:PSObject) [Get-Content]

   ，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (laravel-2025-03-17.log - 18.46 KB:PSObject) [Get-Cont 
   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (laravel-2025-03-18.log - 62.67 KB:PSObject) [Get-Cont

   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (laravel-2025-03-19.log - 8.46 KB:PSObject) [Get-Conte

   nt]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (laravel-2025-03-20.log - 25.12 KB:PSObject) [Get-Cont

   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (laravel-2025-03-21.log - 19.9 KB:PSObject) [Get-Conte

   nt]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (laravel-2025-03-22.log - 5.58 KB:PSObject) [Get-Conte 
   nt]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (laravel.log - 93616.54 KB:PSObject)
[Get-Content]，Par    ameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (new-products-status.log - 2.71 KB:PSObject) [Get-Cont

   ent]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (overdue-payments.log - 8.03 KB:PSObject) [Get-Content

   ]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand


cat : 无法将输入对象绑定到命令的任何参数，可能是因为该命令没有采用管道输入，或者输入及其属性与采用管道
输入的任何参数均不匹配。
所在位置 行:1 字符: 114
+ ...  artisan tinker --execute="require 'check_logging_config.php';" | cat
+                                                                       ~~~
    + CategoryInfo          : InvalidArgument: (quality-inspection-debug.log - 16.71 KB:PSObject) [Ge

   t-Content]，ParameterBindingException
    + FullyQualifiedErrorId : InputObjectNotBound,Microsoft.PowerShell.Commands.GetContentCommand



```

---

_****_

我注意到从上面的错误输出中，我们已经可以看到一些有用的信息，包括以下几点：

1. 当前系统中已经存在一些日期格式的日志文件(如 laravel-2025-03-22

---



---

