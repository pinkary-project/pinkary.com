const viewCreate = () => ({
    posts: [],

    addPost(postId) {
        this.posts = [...this.posts, postId];
        if (this.posts.length >= 10) {
            this.storeViewedPosts();
        }
    },

    storeViewedPosts() {
        let data = JSON.parse(localStorage.getItem('viewedPosts')) || [];
        let recentlyViewedPosts = data.filter(function (post) {
            if (post === null) {
                return false;
            }
            const twoHours = 2 * 60 * 60 * 1000;
            return new Date().getTime() - post.dateTime < twoHours;
        });
        let recentlyViewedPostIds = recentlyViewedPosts.map(post => post.postId);
        let viewedPosts = this.posts.filter(postId => !recentlyViewedPostIds.includes(postId));
        this.posts = [];
        if (viewedPosts.length > 0) {
            this.$wire.call('store', viewedPosts);
            viewedPosts = viewedPosts.map(function (postId) {
                return {
                    postId: postId,
                    dateTime: new Date().getTime()
                };
            });
            let posts = [...recentlyViewedPosts, ...viewedPosts];

            try {
                localStorage.setItem('viewedPosts', JSON.stringify(posts));
            } catch (error) {
                // If the localStorage is full, we will only store the new posts
                // and let the server handle the rest.
                localStorage.setItem('viewedPosts', JSON.stringify(viewedPosts));
            }
        }
    },

    init() {
        window.addEventListener('post-viewed', (event) => {
            this.addPost(event.detail.postId);
        });

        document.addEventListener('livewire:navigate', () => {
            this.storeViewedPosts();
        });

        window.addEventListener('beforeunload', () => {
            this.storeViewedPosts();
        });

        window.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') {
                this.storeViewedPosts();
            }
        });

        window.addEventListener('popstate', () => {
            this.storeViewedPosts();
        });
    }
});

export { viewCreate }
