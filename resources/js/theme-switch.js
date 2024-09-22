const themeSwitch = () => ({

    theme: 'system',

    init() {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', updateTheme);

        const currentTheme = localStorage.getItem('theme') || this.theme;

        this.setTheme(currentTheme);
    },

    setTheme(theme) {
        this.theme = theme

        if (theme == 'dark' || theme == 'light') {
            localStorage.setItem('theme', theme)
        } else {
            localStorage.removeItem('theme');
        }

        updateTheme();
    },
})

export { themeSwitch }
