const copyUrl = () => ({

    isVisible: false,

    init() {
        if (!navigator.share) {
            this.isVisible = true
        }
    },

    copyToClipboard(url) {
        this.$clipboard(url)

        this.$notify('Copied to clipboard.', {
            wrapperId: 'flashMessageWrapper',
            templateId: 'flashMessageTemplate',
            autoClose: 3000,
            autoRemove: 4000
        })
    }
})

export { copyUrl }
