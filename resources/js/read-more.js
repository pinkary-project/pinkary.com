const readMore = () => ({

    isOpen: false,
    maxLength: 255,
    originalText: '',
    truncatedText: '',

    init() {
        this.originalText = this.$el.firstElementChild.textContent; 
        this.truncatedText = this.originalText.slice(0, this.maxLength) + '...';
    },

    toggleText() {
        this.isOpen = ! this.isOpen;
    },

    isLongText() {
        return this.originalText.length > this.maxLength;
    },

    textContent() {
        if (! this.isLongText()) {
            return this.originalText;
        }

        if (this.isOpen === true) {
            return this.originalText;
        }

        return this.truncatedText;
    },

    buttonContent() {
        return this.isOpen === false ? 'read more' : 'read less';
    }
})

export { readMore }
