<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.components.head')
</head>
@php
    $dotsImagePath = asset('/img/dots.svg');
    $background = match($backgroundImage) {
        'dots' => "dark:bg-slate-950 bg-slate-100",
        'solid' => 'dark:bg-gray-900 bg-gray-100',
        default => 'none',
    };
@endphp
<body
    class="{{ $background }} h-full antialiased slate-100 bg-center bg-repeat font-sans dark:text-slate-50 text-slate-950"
    style="background-image: url({{ $backgroundImage === 'dots' ? $dotsImagePath : 'none' }})"
>
@persist('flash-messages')
<livewire:flash-messages.show/>
@endpersist

{{ $slot }}

@livewireScriptConfig

<script>
    window.onload = function () {
        const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone
        if (timezone !== '{{ session()->get('timezone', 'UTC') }}') {
            axios.post('{{ route('profile.timezone.update') }}', {timezone})
        }

        Livewire.hook('request', ({uri, options, payload, respond, succeed, fail}) => {
            fail(({status, content, preventDefault}) => {
                if (status === 419) {
                    preventDefault()
                }
            })
        })
    }
</script>
</body>
</html>
