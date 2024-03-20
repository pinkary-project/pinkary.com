<x-mail::message>
# Hello, {{ $user->name }}!

We've noticed you have {{ $user->unreadNotifications->count() }} pending {{ Str::plural('question', $user->unreadNotifications->count()) }}. You can answer them by clicking the button below.

<x-mail::button :url="route('notifications.index')">
View Questions
</x-mail::button>

If you no longer wish to receive these emails, you can change your "Mail Preference Time" in your [profile settings]({{ route('profile.edit') }}).

See you soon,<br>
{{ config('app.name') }}

</x-mail::message>
