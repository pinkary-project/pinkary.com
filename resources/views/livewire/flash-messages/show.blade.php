<div
    x-data="{
        notify: function (message, url, actionText) {
            this.$notify(message, {
                wrapperId: 'flashMessageWrapper',
                templateId: 'flashMessageTemplate',
                autoClose: url ? 6000 : 3000,
                autoRemove: url ? 7000 : 4000,
            });

            if (url && actionText) {
                const notification = document.getElementById('flashMessageWrapper').lastElementChild;
                const action = notification.querySelector('[data-notification-action]');

                action.href = url;
                action.textContent = actionText;
                action.hidden = false;
            }
        },
    }"
    x-on:notification-created.dot.window="notify($event.detail.message, $event.detail.url, $event.detail.actionText)"
    @session('flash-message') x-init="notify('{{ $value }}')" @endsession
>
    <div id="flashMessageWrapper" class="fixed top-4 right-4 z-50 w-64 space-y-2"></div>

    <template id="flashMessageTemplate">
        <div role="alert" class="mt-12 rounded-lg bg-pink-500 px-4 py-3 text-white">
            <span>{notificationText}</span>
            <a
                data-notification-action
                hidden
                class="ml-1 inline-flex items-center rounded px-1.5 py-0.5 text-sm font-semibold text-white underline decoration-white/60 underline-offset-2 transition hover:bg-white/15 hover:no-underline focus:ring-2 focus:ring-white/70 focus:outline-none"
            >
                View update
            </a>
        </div>
    </template>
</div>
