const followButton = (id, isFollowing, isFollower, isAuthenticated) => ({
    id,
    isFollowing,
    isFollower,
    isAuthenticated,
    buttonText: '',

    init() {
        this.setButtonText();
        this.initEventListeners();
    },

    toggleFollow() {
        if (!this.isAuthenticated) {
            window.Livewire.navigate('/login');
            return;
        }

        if (this.isFollowing) {
            this.$wire.unfollow(id);
            this.$dispatch('user.unfollowed', { id: id });
        } else {
            this.$wire.follow(id);
            this.$dispatch('user.followed', { id: id });
        }
    },

    setButtonText() {
        this.buttonText = this.isFollowing ? 'Unfollow' : (this.isFollower ? 'Follow Back' : 'Follow');
    },

    initEventListeners() {
        window.addEventListener('user.followed', (event) => {
            if (event.detail.id == this.id) {
                this.isFollowing = true;
                this.setButtonText();
            }
        });
        window.addEventListener('user.unfollowed', (event) => {
            if (event.detail.id == id) {
                this.isFollowing = false;
                this.setButtonText();
            }
        });
    }
});

export { followButton }


