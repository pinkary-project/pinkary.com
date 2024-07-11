const clickHandler = () => ({

    handleNavigation(event)
    {
        const hasDataNavigateIgnore = (el) => {
            if (!el || el.dataset.parent === 'true') {
                return false;
            }
            if (el.dataset.navigateIgnore === 'true') {
                return true;
            }
            return hasDataNavigateIgnore(el.parentElement);
        };

        if (! hasDataNavigateIgnore(event.target)) {
            const commentLink = this.$refs.commentLink;
            if (commentLink) {
                commentLink.click();
            }

        }

    }
})

export { clickHandler }
