const viewCreate = () => ({
    posts: [],
    addPost(postId) {
        this.posts = [...this.posts, postId];
        if (this.posts.length >= 10) {
            this.store();
        }
    },
    store() {
        let data = JSON.parse(localStorage.getItem('viewedPosts')) || [];
        let recentlyViewedPosts = data.filter(function (post) {
            if (post === null) {
                return false;
            }
            const twoHours = 2 * 60 * 60 * 1000;
            return new Date().getTime() - post.dateTime < twoHours;
        });
        let recentlyViewedPostIds = recentlyViewedPosts.map(post => post.postId);
        let posts = this.posts.filter(postId => !recentlyViewedPostIds.includes(postId));
        this.posts = [];
        if (posts.length > 0) {
            this.$wire.call('store', posts);
            posts = posts.map(function (postId) {
                return {
                    postId: postId,
                    dateTime: new Date().getTime()
                };
            });
            newPosts = [...recentlyViewedPosts, ...posts];

            try {
                localStorage.setItem('viewedPosts', JSON.stringify(newPosts));
            } catch (error) {
                // If the localStorage is full, we will only store the new posts
                // and let the server handle the rest.
                localStorage.setItem('viewedPosts', JSON.stringify(posts));
            }
        }
    },
    init() {
        window.addEventListener('post-viewed', (event) => {
            this.addPost(event.detail.postId);
        });

        document.addEventListener('livewire:navigate', () => {
            this.store();
        });

        window.addEventListener('beforeunload', () => {
            this.store();
        });

        window.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') {
                this.store();
            }
        });

        window.addEventListener('popstate', () => {
            this.store();
        });
    }
});

export { viewCreate }
