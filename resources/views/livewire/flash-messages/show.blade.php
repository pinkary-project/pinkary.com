<div
x-data="{
    notify: function(message) {
        this.$notify(message, {
            wrapperId: 'flashMessageWrapper',
            templateId: 'flashMessageTemplate',
            autoClose: 3000,
            autoRemove: 4000,
        })
    }
}"
    x-on:notification-created.dot.window="notify($event.detail.message)"
    @session('flash-message') x-init="notify('{{ $value }}')" @endsession
>
    <div
        id="flashMessageWrapper"
        class="fixed right-4 top-4 z-50 w-64 space-y-2"
    ></div>

    <template id="flashMessageTemplate">
        <div
            role="alert"
            class="mt-12 rounded-lg bg-pink-500 px-4 py-3 text-white"
        >
            {notificationText}
        </div>
    </template>
</div>
