<x-mail::message>
# {{ $mailSubject }}

{{ $mailMessage }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
