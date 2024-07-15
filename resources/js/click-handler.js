const clickHandler = () => ({

    handleNavigation(event)
    {
        if(window.getSelection().toString().length !== 0) {
            return;
        }

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
            const parentLink = this.$refs.parentLink
            if (parentLink) {
                parentLink.click();
            }

        }

    }
})

export { clickHandler }
