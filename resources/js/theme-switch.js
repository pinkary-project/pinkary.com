const themeSwitch = () => ({
    
    theme: 'dark',

    init() {
        const currentTheme = localStorage.getItem('theme') || this.theme

        if (['dark', 'light', 'system'].indexOf(currentTheme) < 0) {
            this.setTheme(this.theme)
            return
        }

        this.setTheme(currentTheme)
    },

    setTheme(theme) {
        this.theme = theme
        localStorage.setItem('theme', theme)

        if (theme === 'dark') {
            this.setDarkMode()
            return
        }

        if (theme === 'light') {
            this.setLightMode()
            return
        }

        this.setSystemMode()
    },

    setLightMode() {
        document.documentElement.classList.remove('dark')
        document.documentElement.classList.add(this.theme)
    },

    setDarkMode() {
        document.documentElement.classList.remove('light')
        document.documentElement.classList.add(this.theme)
    },

    setSystemMode() {
        document.documentElement.classList.remove('dark', 'light')
    },

})

export { themeSwitch }