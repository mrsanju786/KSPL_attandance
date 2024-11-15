@component('mail::message')
# Hello {{$user->name}}

Please find attached  attendance details of the employees for the  month.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
