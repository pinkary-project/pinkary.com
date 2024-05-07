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
        let text = options.question ? options.question + '\n\n' : ''

        text = encodeURIComponent(
            text
                .replace(/<pre><code.*?>.*?<\/code><\/pre>/gs, "\n\n[👀 see the code on Pinkary 👀]\n\n")
                .replace(/<\/?[^>]+(>|$)/g, "")
        );

        window.open(
            `https://twitter.com/intent/tweet?text=${text}${options.message}:&url=${options.url}`,
            "_blank"
        )
    }
})

export { shareProfile }
