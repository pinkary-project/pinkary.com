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

    addCopyButtons() {
        const codeElements = this.$el.querySelectorAll('code');
        codeElements.forEach((codeElement) => {
            if (codeElement.querySelector('button')) {
                codeElement.querySelector('button').remove();
            }

            let button = document.createElement('button');
            button.innerHTML = 'Copy';
            button.classList.add(
                'text-xs',
                'text-pink-500',
                'cursor-pointer',
                'focus:outline-none',
                'transition-colors',
                'hover:text-pink-400',
                'z-10',
                'p-1',
                'rounded-md',
                'bg-pink-900',
                'bg-opacity-50',
                'text-white',
                'transition-opacity',
                'duration-200',
            );

            // to make it appear on hover
            codeElement.classList.add('group/code');
            button.classList.add(
                'opacity-0',
                'group-hover/code:opacity-100',
            );

            // to make the button stick to the top right corner
            button.style.position = 'sticky';
            button.style.left = 'calc(100% - 1.5rem)';
            button.style.bottom = '100%';
            button.style.margin = '-0.5rem';

            // This is to prevent comment-box chick
            button.setAttribute('data-navigate-ignore', 'true');

            button.addEventListener('click', () => {
                let text = codeElement.innerText;
                // removing the last 4 characters which are the word 'Copy'
                text = text.substring(0, text.length - 4);
                navigator.clipboard.writeText(text);
                button.style.left = 'calc(100% - 2.3rem)';
                button.innerHTML = 'Copied';
                setTimeout(() => {
                    button.style.left = 'calc(100% - 1.5rem)';
                    button.innerHTML = 'Copy';
                }, 400);
            });

            codeElement.appendChild(button);
        });
    }
});

export { copyCode }
