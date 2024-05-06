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

        text = text
            .replace(/<pre><code.*?>.*?<\/code><\/pre>/gs, "%0A%0A[👀 see the code on Pinkary 👀]%0A%0A")
            .replace(/<\/?[^>]+(>|$)/g, "");

        window.open(
            `https://twitter.com/intent/tweet?text=${text}${options.message}:&url=${options.url}`,
            "_blank"
        )
    }
})

export { shareProfile }
