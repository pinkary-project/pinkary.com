<div x-data>
    <div id="flashMessageWrapper" class="fixed right-4 top-4 -z-10 w-64 space-y-2"></div>

    @if (session('flash-message'))
        <div
            x-init="
                $notify('{{ session('flash-message') }}', {
                    wrapperId: 'flashMessageWrapper',
                    templateId: 'flashMessageTemplate',
                    autoClose: 3000,
                    autoRemove: 4000,
                })
            "
        ></div>
    @endif

    <template id="flashMessageTemplate">
        <div role="alert" class="mt-12 bg-pink-500 p-4 text-white">{notificationText}</div>
    </template>
</div>
