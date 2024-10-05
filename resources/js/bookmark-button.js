import { abbreviate } from "./abbreviate";

const bookmarkButton = (id, isBookmarked, count, isAuthenticated) => ({
    id,
    isBookmarked,
    count,
    isAuthenticated,
    bookmarkButtonTitle: '',
    bookmarkButtonText: '',

    init() {
        this.setTitle();
        this.setText();
        this.initEventListeners();
    },

    setTitle() {
        this.bookmarkButtonTitle = this.count === 1 ? '1 bookmark' : `${this.count} bookmarks`;
    },

    setText() {
        this.bookmarkButtonText = this.count === 0 ? '' : abbreviate(this.count);
    },

    toggleBookmark() {

        if (!this.isAuthenticated) {
            window.Livewire.navigate('/login');
            return;
        }

        if (this.isBookmarked) {
            this.$wire.unbookmark(id);
            this.$dispatch('question.unbookmarked', { id: id });
            if (this.count === 0) {
                this.animateUnBookmarkButton();
            }
        } else {
            this.$wire.bookmark(id);
            this.$dispatch('question.bookmarked', { id: id });
            this.animateBookmarkButton();
        }
    },

    initEventListeners() {
        window.addEventListener('question.bookmarked', (event) => {
            if (event.detail.id == this.id) {
                this.isBookmarked = true;
                this.count++;
                this.setTitle();
                this.setText();
            }
        });

        window.addEventListener('question.unbookmarked', (event) => {
            if (event.detail.id == this.id) {
                this.isBookmarked = false;
                this.count--;
                this.setTitle();
                this.setText();
            }
        });
    },

    animateBookmarkButton() {
        // fade it from left to right and bounce
        this.$el.querySelector('svg').animate([
            { transform: 'translateX(20%)', opacity: 0 },
            { transform: 'translateX(-15%)', opacity: .8 },
            { transform: 'translateX(0%)', opacity: 1 }
        ], {
            duration: 500,
            easing: 'ease-in-out',
            fill: 'forwards'
        });

        this.$el.querySelector('span').animate([
            { transform: 'translateY(20%)', opacity: 0 },
            { transform: 'translateY(-15%)', opacity: .8 },
            { transform: 'translateY(0%)', opacity: 1 }
        ], {
            duration: 500,
            easing: 'ease-in-out',
        });
    },

    animateUnBookmarkButton() {
        // fade it from right to left and bounce
        // but only when count is 0.
        this.$el.animate([
            { transform: 'translateX(-50%)', opacity: 0 },
            { transform: 'translateX(15%)', opacity: .8 },
            { transform: 'translateX(0%)', opacity: 1 }
        ], {
            duration: 500,
            easing: 'ease-in-out',
        });
    }
});

export { bookmarkButton };
