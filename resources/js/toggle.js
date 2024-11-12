const toggle = () => ({
    sidebar: null,
    tempDisabled: false,

    init() {
        this.sidebar = this.$refs.sidebar;

        const mediaQuery = window.matchMedia('(min-width: 1280px)');

        this.checkMatch(mediaQuery);

        mediaQuery.addEventListener('change', (e) => {
            this.checkMatch(e);
        });
    },

    checkMatch(event) {
        if (event.matches) {
            this.sidebar.classList.remove('hidden');
            this.sidebar.classList.add('flex');
            this.sidebar.classList.add('bg-black/10');
            this.sidebar.classList.remove('bg-section-black');
        } else {
            this.sidebar.classList.add('hidden');
            this.sidebar.classList.remove('flex');
            this.sidebar.classList.remove('bg-black/10');
            this.sidebar.classList.add('bg-section-black');
        }
    },

    toggleSidebar(e) {
        if (e.target !== this.sidebar) {
            this.tempDisabled = true;
            this.sidebar.classList.toggle('hidden');
            this.sidebar.classList.contains('hidden')
                ? this.sidebar.classList.remove('flex')
                : this.sidebar.classList.add('flex');
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
