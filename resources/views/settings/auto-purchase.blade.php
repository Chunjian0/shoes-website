@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>自动采购设置</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('settings.update-auto-purchase') }}" method="POST">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="auto_purchase_enabled" 
                                               name="auto_purchase_enabled" value="true"
                                               {{ $settings['auto_purchase_enabled'] === 'true' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="auto_purchase_enabled">启用自动采购</label>
                                    </div>
                                    <small class="form-text text-muted">
                                        启用后，系统将按设定的频率自动检查库存并生成采购单
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="auto_purchase_frequency">自动采购频率</label>
                                    <select class="form-control" id="auto_purchase_frequency" name="auto_purchase_frequency">
                                        <option value="daily" {{ $settings['auto_purchase_frequency'] === 'daily' ? 'selected' : '' }}>
                                            每天
                                        </option>
                                        <option value="weekly" {{ $settings['auto_purchase_frequency'] === 'weekly' ? 'selected' : '' }}>
                                            每周（周一）
                                        </option>
                                        <option value="twice_weekly" {{ $settings['auto_purchase_frequency'] === 'twice_weekly' ? 'selected' : '' }}>
                                            每周两次（周一和周四）
                                        </option>
                                    </select>
                                    <small class="form-text text-muted">
                                        系统将在每天上午8:00执行自动采购检查
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="auto_purchase_quantity_method">采购数量计算方法</label>
                                    <select class="form-control" id="auto_purchase_quantity_method" name="auto_purchase_quantity_method">
                                        <option value="min_stock" {{ $settings['auto_purchase_quantity_method'] === 'min_stock' ? 'selected' : '' }}>
                                            按最小库存水平采购
                                        </option>
                                        <option value="double_min_stock" {{ $settings['auto_purchase_quantity_method'] === 'double_min_stock' ? 'selected' : '' }}>
                                            按两倍最小库存水平采购
                                        </option>
                                        <option value="replenish_only" {{ $settings['auto_purchase_quantity_method'] === 'replenish_only' ? 'selected' : '' }}>
                                            仅补充至最小库存
                                        </option>
                                    </select>
                                    <small class="form-text text-muted">
                                        决定系统如何计算每个商品的采购数量
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="default_warehouse_id">默认仓库</label>
                                    <select class="form-control" id="default_warehouse_id" name="default_warehouse_id">
                                        <option value="">-- 选择仓库 --</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" {{ $settings['default_warehouse_id'] == $warehouse->id ? 'selected' : '' }}>
                                                {{ $warehouse->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">
                                        系统自动检查库存时使用的默认仓库
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="auto_purchase_blacklist">自动采购黑名单</label>
                                    <select class="form-control select2" id="auto_purchase_blacklist" name="auto_purchase_blacklist[]" multiple>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" 
                                                {{ in_array($product->id, json_decode($settings['auto_purchase_blacklist'] ?: '[]')) ? 'selected' : '' }}>
                                                {{ $product->name }} ({{ $product->sku }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">
                                        选择不参与自动采购的商品
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="auto_purchase_notify_users">自动采购通知用户</label>
                                    <select class="form-control select2" id="auto_purchase_notify_users" name="auto_purchase_notify_users[]" multiple>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" 
                                                {{ in_array($user->id, json_decode($settings['auto_purchase_notify_users'] ?: '[]')) ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">
                                        选择在系统自动生成采购单后需要通知的用户
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">保存设置</button>
                                    <a href="{{ route('settings.index') }}" class="btn btn-secondary">返回</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "请选择...",
            allowClear: true
        });
    });
</script>
@endpush 