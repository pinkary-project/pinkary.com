const themeSwitch = () => ({
    theme: 'system',
    currentTheme: null,
    themeMediaQuery: null,
    onSystemThemeChanged: null,
    onThemeChanged: null,

    init() {
        this.themeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        this.onSystemThemeChanged = () => {
            if (this.theme === 'system') {
                this.updateTheme();
            }
        };

        this.onThemeChanged = (event) => {
            if (event.detail?.theme && this.theme !== event.detail.theme) {
                this.theme = event.detail.theme;
                this.updateTheme();
            }
        };

        this.themeMediaQuery.addEventListener('change', this.onSystemThemeChanged);
        window.addEventListener('theme-changed', this.onThemeChanged);

        const savedTheme = localStorage.getItem('theme') || this.theme;
        this.setTheme(savedTheme, false);
    },

    destroy() {
        this.themeMediaQuery?.removeEventListener('change', this.onSystemThemeChanged);
        window.removeEventListener('theme-changed', this.onThemeChanged);
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
