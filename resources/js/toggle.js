const toggle = () => ({
    sidebar: null,
    tempDisabled: false,

    init() {
        this.sidebar = this.$refs.sidebar;

        const mediaQuery = window.matchMedia('(min-width: 1280px)');

        if (mediaQuery.matches) {
            this.sidebar.classList.remove('hidden');
        }

        mediaQuery.addEventListener('change', (e) => {
            if (e.matches) {
                this.sidebar.classList.remove('hidden');
            } else {
                this.sidebar.classList.add('hidden');
            }
        });
    },

    toggleSidebar(e) {
        if (e.target !== this.sidebar) {
            this.tempDisabled = true;
            this.sidebar.classList.toggle('hidden');
            setTimeout(() => {
                this.tempDisabled = false;
            }, 100);
        }
    },

    closeSidebar(e) {
        if (!this.sidebar.classList.contains('hidden') && !this.tempDisabled) {
            if (e.target !== this.sidebar) {
                console.log('close sidebar');
                this.sidebar.classList.add('hidden');
            }
        }
    }
});

export { toggle };
