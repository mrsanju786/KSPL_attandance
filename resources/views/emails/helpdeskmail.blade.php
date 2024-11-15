@component('mail::message')
# Hello

A new ticket has been raised!

User : {{$helpdesk->user->name}}

Topic: {{$helpdesk->topic}}

Description : {{$helpdesk->description}}


Thanks,<br>
{{ config('app.name') }}
@endcomponent
