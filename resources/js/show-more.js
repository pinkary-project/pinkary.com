const showMore = () => ({

    maxHeight: 600,

    initialHeight: 0,

    open: false,

    showMore: false,

    init() {
        this.initialHeight = this.$refs.parentDiv.scrollHeight;

        if (this.initialHeight > this.maxHeight + 40) {
            this.$refs.parentDiv.style.maxHeight = this.maxHeight + 'px';
            this.showMore = true;
        }
    },

    showButtonAction() {
        let height = this.open === false ? this.initialHeight : this.maxHeight;

        this.$refs.parentDiv.style.maxHeight = height + 'px';

        this.open = ! this.open;
    },

    showMoreButtonText() {
        if (this.open === false) {
            return 'Show more';
        }

        return 'Show less';
    },
})

export { showMore }
