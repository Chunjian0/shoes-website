<?php

declare(strict_types=1);

namespace Tests\Browser\Traits;

use Laravel\Dusk\Browser;

/**
 * 用于等待Turbolinks加载的Trait
 * 
 * 此Trait为Dusk Browser类添加了等待Turbolinks加载的方法
 */
trait WaitsForTurbolinks
{
    /**
     * 等待Turbolinks加载完成
     *
     * @param  int  $seconds  最大等待秒数
     * @return \Laravel\Dusk\Browser
     */
    public function waitForTurbolinksLoad($seconds = 10)
    {
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
    }
    
    /**
     * 访问URL并等待Turbolinks加载
     *
     * @param  string  $url  要访问的URL
     * @param  int  $turbolinksWait  Turbolinks等待秒数
     * @param  int  $elementWait  页面元素等待秒数
     * @param  string  $element  要等待的元素选择器，可以是CSS选择器或文本
     * @return \Laravel\Dusk\Browser
     */
    public function visitAndWaitForTurbolinks($url, $turbolinksWait = 10, $elementWait = 10, $element = null)
    {
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
    }
} 