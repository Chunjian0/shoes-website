<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;
use Tests\Browser\Traits\WaitsForTurbolinks;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected static $baseUrl;

    /**
     * 准备Dusk测试执行
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }
        
        // 注册等待Turbolinks加载的宏方法
        Browser::macro('waitForTurbolinksLoad', function ($seconds = 10) {
            /** @var \Laravel\Dusk\Browser $this */
            $this->script("
                window.turbolinksLoaded = false;
                
                // 监听DOMContentLoaded事件
                document.addEventListener('DOMContentLoaded', function() {
                    window.turbolinksLoaded = true;
                });
                
                // 监听turbolinks:load事件
                document.addEventListener('turbolinks:load', function() {
                    window.turbolinksLoaded = true;
                });
                
                // Livewire加载
                document.addEventListener('livewire:load', function() {
                    window.turbolinksLoaded = true;
                });
                
                // 如果页面已加载完成，直接设置标志
                if (document.readyState === 'complete') {
                    window.turbolinksLoaded = true;
                }
                
                // 添加安全超时，避免永久等待
                setTimeout(function() {
                    window.turbolinksLoaded = true;
                }, {$seconds}000);
            ");
            
            return $this->waitUntil('window.turbolinksLoaded === true', $seconds);
        });
        
        // 注册访问URL并等待Turbolinks加载的宏方法
        Browser::macro('visitAndWaitForTurbolinks', function ($url, $turbolinksWait = 10, $elementWait = 10, $element = null) {
            /** @var \Laravel\Dusk\Browser $this */
            $this->visit($url);
            
            $this->waitForTurbolinksLoad($turbolinksWait);
            
            if ($element !== null) {
                // 如果元素以#, ., [ 开头，或包含空格，认为是CSS选择器
                if (preg_match('/^[#\.\[]|[[:space:]]/', $element)) {
                    $this->waitFor($element, $elementWait);
                } else {
                    // 否则认为是要等待的文本
                    $this->waitForText($element, $elementWait);
                }
            }
            
            return $this;
        });
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
        ])->unless($this->hasHeadlessDisabled(), function ($items) {
            return $items->merge([
                '--disable-gpu',
                //'--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * Get the base URL for the application.
     */
    protected function baseUrl(): string
    {
        return 'http://localhost:2268';
    }

    /**
     * Determine whether the Dusk command has disabled headless mode.
     */
    protected function hasHeadlessDisabled(): bool
    {
        return isset($_SERVER['DUSK_HEADLESS_DISABLED']) ||
               isset($_ENV['DUSK_HEADLESS_DISABLED']);
    }

    /**
     * Determine if the browser window should start maximized.
     */
    protected function shouldStartMaximized(): bool
    {
        return isset($_SERVER['DUSK_START_MAXIMIZED']) ||
               isset($_ENV['DUSK_START_MAXIMIZED']);
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    public function setUp(): void
    {
        parent::setUp();
        // Set up the correct serverURL
        static::$baseUrl = $this->baseUrl();
    }
}
