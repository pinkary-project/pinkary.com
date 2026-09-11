const themeSwitch = () => ({
    theme: 'system',
    currentTheme: null,

    init() {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            if (this.theme === 'system') {
                this.updateTheme();
            }
        });

        window.addEventListener('theme-changed', (event) => {
            if (event.detail?.theme && this.theme !== event.detail.theme) {
                this.theme = event.detail.theme;
                this.updateTheme();
            }
        });

        const savedTheme = localStorage.getItem('theme') || this.theme;
        this.setTheme(savedTheme, false);
    },

    setTheme(theme, broadcast = true) {
        this.theme = theme;

        if (theme === 'dark' || theme === 'light') {
            localStorage.setItem('theme', theme);
        } else {
            localStorage.removeItem('theme');
        }

        this.updateTheme();

        if (broadcast) {
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme } }));
        }
    },

    getCurrentTheme() {
        if (this.theme === 'system') {
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        return this.theme;
    },

    updateTheme() {
        const newTheme = this.getCurrentTheme();

        if (typeof window.updateTheme === 'function') {
            window.updateTheme();
        } else {
            document.documentElement.classList.remove('dark', 'light');
            document.documentElement.classList.add(newTheme);
        }

        if (this.currentTheme !== newTheme) {
            this.currentTheme = newTheme;
        }
    },
});

export { themeSwitch };
