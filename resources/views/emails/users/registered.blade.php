<x-mail::message>
# Verify your email address.

Please click on the button below and enter this code <strong>{{ $user->email_code }}</strong>.

<x-mail::button :url="'https://portal.nbaunitybar.org/email_confirmation_code?id='.$user->id">
Verify Email
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
