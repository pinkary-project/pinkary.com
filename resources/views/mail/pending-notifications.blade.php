<x-mail::message>
# Hello, {{ $user->name }}!

We've noticed you have {{ $pendingNotificationsCount }} {{ Str::plural('notification', $pendingNotificationsCount) }}. You can view notifications by clicking the button below.

<x-mail::button :url="route('notifications.index')">
View Notifications
</x-mail::button>

If you no longer wish to receive these emails, you can change your "Mail Preference Time" in your [profile settings]({{ route('profile.edit') }}).

See you soon,<br>
{{ config('app.name') }}

</x-mail::message>
