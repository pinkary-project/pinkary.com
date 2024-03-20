const shareProfile = () => ({

    isVisible: false,

    init() {
        if (navigator.share) {
            this.isVisible = true
        }
    },

    share(options) {
        if (navigator.share) {
            navigator.share(options)
        } else {
            this.$clipboard(options.url)

            this.$notify('Copied to clipboard.', {
                wrapperId: 'notificationWrapper',
                templateId: 'notificationAlert',
                autoClose: 3000,
                autoRemove: 4000
            });
        }
    }
})

export { shareProfile}
