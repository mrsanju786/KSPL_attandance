@component('mail::message')
# Reset Password

Reset your password using the below link

@component('mail::button', ['url' => $url])
    Reset Password Link
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
