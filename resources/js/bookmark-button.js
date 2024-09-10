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
        // fade it from top to bottom
        this.$el.querySelector('svg').animate([
            { transform: 'translateY(-100%)', opacity: 0 },
            { transform: 'translateY(0)', opacity: 1 }
        ], {
            duration: 500,
            easing: 'ease-in-out',
            fill: 'forwards'
        });
    }
});

export { bookmarkButton };
