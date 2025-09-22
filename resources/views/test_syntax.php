<?php
// 测试文件，用于检测语法问题
?>

@props([
    'modelId' => null,
    'modelType' => 'App\\Models\\Setting',
    'maxFiles' => 1,
    'images' => [],
    'tempId' => null
])

<div>
    @if($modelId)
        formData.append('model_id', {{ $modelId }});
    @endif

    // 其他代码
</div> 