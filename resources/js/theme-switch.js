const themeSwitch = () => ({
    theme: 'system',
    currentTheme: null,

    init() {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', updateTheme);

        const savedTheme = localStorage.getItem('theme') || this.theme;
        this.setTheme(savedTheme);
    },

    setTheme(theme) {
        this.theme = theme;

        if (theme === 'dark' || theme === 'light') {
            localStorage.setItem('theme', theme);
        } else {
            localStorage.removeItem('theme');
        }

        this.updateTheme();
    },

    getCurrentTheme() {
        if (this.theme === 'system') {
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        return this.theme;
    },

    updateTheme() {
        const newTheme = this.getCurrentTheme();

        document.documentElement.classList.remove('dark', 'light');
        document.documentElement.classList.add(newTheme);

        if (this.currentTheme !== newTheme) {
            this.currentTheme = newTheme;
        }
    },
});

export { themeSwitch };
