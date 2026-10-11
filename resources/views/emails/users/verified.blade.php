<x-mail::message>
# Your email has been verified successfully.

Your email address has been successfully verified.
<x-mail::button :url="'https://portal.nbaunitybar.org/nba/login'">
Continue to Login
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
