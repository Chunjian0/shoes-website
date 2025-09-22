<!-- 
Toast通知组件 - 提供全局通知功能
使用SweetAlert2实现
-->

<script>
// 确保SweetAlert2已加载
document.addEventListener('DOMContentLoaded', function() {
    // 如果页面中没有Swal对象，我们加载SweetAlert2库
    if (typeof Swal === 'undefined') {
        // 动态加载SweetAlert2
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
        script.async = true;
        document.head.appendChild(script);
        
        script.onload = function() {
            // 初始化Toast
            initToast();
        };
    } else {
        // SweetAlert2已存在，直接初始化Toast
        initToast();
    }
    
    function initToast() {
        // 创建Toast对象
        window.Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        
        // 提供简便的调用方法
        window.showToast = function(message, type = 'success') {
            Toast.fire({
                icon: type, // 'success', 'error', 'warning', 'info', 'question'
                title: message
            });
        };
        
        console.log('Toast通知组件已初始化');
    }
});
</script> 