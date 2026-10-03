const feedContent = () => ({
    overflowing: false,
    observer: null,

    init() {
        this.observer = new ResizeObserver(() => this.measure());
        this.observer.observe(this.$refs.text);
        this.$nextTick(() => this.measure());
    },

    measure() {
        this.overflowing = this.$refs.text.scrollHeight > this.$refs.text.clientHeight + 1;
    },

    destroy() {
        this.observer.disconnect();
    },
});

export { feedContent };
