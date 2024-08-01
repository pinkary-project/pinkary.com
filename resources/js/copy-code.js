const copyCode = () => ({

    init() {
        this.addCopyButtons();
        Livewire.hook('commit', (event) => {
            event.succeed((e) => {
                if (event.component.el.getAttribute('id') === this.$el.getAttribute('id')) {
                    requestAnimationFrame(() => {
                        this.addCopyButtons();
                    });
                }
            });
        });
    },

    copyToClipboard(code) {
        this.$clipboard(code);
        this.$notify('Copied to clipboard.', {
            wrapperId: 'flashMessageWrapper',
            templateId: 'flashMessageTemplate',
            autoClose: 3000,
            autoRemove: 4000
        });
    },

    addCopyButtons() {
        const codeElements = this.$el.querySelectorAll('code');

        const copyIcon = new DOMParser().parseFromString(
            `<svg xmlns="http://www.w3.org/2000/svg" class="opacity-0 group-hover/code:opacity-80 size-5" viewBox="0 0 24 24" fill="currentColor"><path d="M20 2H10c-1.103 0-2 .897-2 2v4H4c-1.103 0-2 .897-2 2v10c0 1.103.897 2 2 2h10c1.103 0 2-.897 2-2v-4h4c1.103 0 2-.897 2-2V4c0-1.103-.897-2-2-2zM4 20V10h10l.002 10H4zm16-6h-4v-4c0-1.103-.897-2-2-2h-4V4h10v10z"></path><path d="M6 12h6v2H6zm0 4h6v2H6z"></path></svg>`
            , 'image/svg+xml'
        ).documentElement;

        const positionButton = (button, el) => {
            el.parentNode.style.position = 'relative';

            const m = 6;
            button.style.position = 'absolute';
            button.style.right = `${m}px`;
            button.style.top = `${m}px`;
        }

        codeElements.forEach((codeElement) => {

            if (codeElement.querySelector('button')) {
                codeElement.querySelector('button').remove();
            }

            codeElement.classList.add('group/code');
            const button = document.createElement('button');
            const setupButton = () => {
                button.classList.add(
                    'opacity-0',
                    'group-hover/code:opacity-100',
                    'group-hover/code:bg-opacity-50',
                    'text-xs',
                    'text-pink-400',
                    'cursor-pointer',
                    'focus:outline-none',
                    'transition-colors',
                    'hover:text-white',
                    'z-10',
                    'p-1',
                    'rounded-md',
                    'bg-pink-900',
                    'bg-opacity-0',
                    'transition-opacity',
                    'duration-200',
                );
                button.title = 'Copy';
                button.setAttribute(
                    'data-navigate-ignore',
                    'true'
                );
                button.appendChild(copyIcon.cloneNode(true));
                positionButton(button, codeElement);
            }

            this.$nextTick(() => {
                setupButton();
            });

            window.addEventListener(
                'resize', () =>
                    positionButton(button, codeElement)
            );

            button.addEventListener('click', () => {
                this.copyToClipboard(codeElement.innerText.trim());
            });

            codeElement.appendChild(button);
        });
    }
});

export {copyCode}
