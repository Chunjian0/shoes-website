@component('mail::message')
# Super Administrator Account has been deleted

Hello,

This is an automatic notification email to inform you that a super administrator account in the system has been deleted.

**Deleted account information:**
- Name:{{ $superAdmin->name }}
- Employee number:{{ $superAdmin->employee_id }}
- Email:{{ $superAdmin->email }}

**Operation information:**
- Administrators who perform the deletion:{{ $deletedBy->name }}
- Deletion time:{{ now()->format('Y-m-d H:i:s') }}

If this is an unauthorized operation, please contact your system administrator immediately.

@component('mail::button', ['url' => route('employees.index')])
View employee list
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent 