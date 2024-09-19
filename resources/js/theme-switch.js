const themeSwitch = () => ({
    
    theme: 'dark', // default theme

    availableModes: ['dark', 'light'], // e.g. system

    modeIndex: 0,

    init() {
        const currentTheme = localStorage.getItem('theme') || this.theme

        this.modeIndex = this.availableModes.indexOf(currentTheme)

        if (this.modeIndex < 0) {
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

    toggleTheme() {
        const newModeIndex = (this.modeIndex + 1) % this.availableModes.length
        
        this.modeIndex = newModeIndex
        this.setTheme(this.availableModes[newModeIndex])
    },

    toggleThemeButtonText() {
        return this.theme.charAt(0).toUpperCase() + this.theme.slice(1)
    }

})

export { themeSwitch }