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
            this.renderTweets(newTheme);
        }
    },

    renderTweets(theme) {
        const tweetContainers = document.querySelectorAll('div[data-tweet-id]');

        if (tweetContainers.length > 0) {
            tweetContainers.forEach(container => {
                container.innerHTML = '';

                window.twttr.widgets.createTweet(container.dataset.tweetId, container, {
                    theme: theme,
                    conversation: 'none',
                    align: 'center',
                });
            });
        }
    },
});

export { themeSwitch };
