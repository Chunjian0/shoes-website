@props([
    'modelId' => null,
    'modelType' => 'App\\Models\\Setting',
    'maxFiles' => 1,
    'images' => [],
    'tempId' => null
])

<div>
    <!-- It is quality rather than quantity that matters. - Lucius Annaeus Seneca -->
    
    @if($modelId)
        有modelId: {{ $modelId }}
    @endif
    
    <div>
        <!-- 测试区域 -->
        <p>常规内容</p>
    </div>
</div>
