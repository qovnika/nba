<x-mail::message>
# Password Reset

Hi {{ $user->lname." ".$user->fname }},

You can use the following password to login. Please be sure to change your password after logging in.

Password: <strong>{{ $password }}</strong>

<x-mail::button :url="'https://portal.nbaunitybar.org'">
Continue to login
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
