const shareProfile = () => ({

    isVisible: false,

    init() {
        if (navigator.share) {
            this.isVisible = true
        }
    },

    share(options) {
        navigator.share(options)
    },

    twitter(options) {
        let text = options.question ? options.question + '%0A%0A' : ''
        window.open(
            `https://twitter.com/intent/tweet?text=${text}${options.message}:&url=${options.url}`,
            "_blank"
        )
    }
})

export { shareProfile }
