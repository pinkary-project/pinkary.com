const shareProfile = () => ({

    isVisible: false,

    init() {
        if (navigator.share) {
            this.isVisible = true
        }
    },

    share(options) {
        navigator.share(options)
    }
})

export { shareProfile }
