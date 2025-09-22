import './bootstrap';
import Turbolinks from 'turbolinks';
import Swal from 'sweetalert2';

// 将Swal添加到全局window对象
window.Swal = Swal;

// 创建一个Toast预设
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


Turbolinks.start();
