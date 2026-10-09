import { abbreviate } from './abbreviate';

const repostButton = (id, isAuthenticated) => ({
    id,
    isAuthenticated,
    isReposted: false,
    count: 0,
    repostButtonTitle: '',
    repostButtonText: '',

    init() {
        this.isReposted = this.$el.dataset.isReposted === 'true';
        this.count = parseInt(this.$el.dataset.repostsCount);
        this.setAll();
        this.initEventListeners();
    },

    setTitle() {
        this.repostButtonTitle = this.count === 1 ? '1 repost' : `${this.count} reposts`;
    },

    setText() {
        this.repostButtonText = this.count === 0 ? '' : abbreviate(this.count);
    },

    toggleRepost() {
        if (! this.isAuthenticated) {
            window.Livewire.navigate('/login');

            return;
        }

        if (this.isReposted) {
            this.$wire.unrepost(id);
            this.$dispatch('question.unreposted', { id });
        } else {
            this.$wire.repost(id);
            this.$dispatch('question.reposted', { id });
        }
    },

    initEventListeners() {
        window.addEventListener('question.reposted', (event) => {
            if (event.detail.id == this.id) {
                this.isReposted = true;
                this.count++;
                this.setAll();
            }
        });

        window.addEventListener('question.unreposted', (event) => {
            if (event.detail.id == this.id) {
                this.isReposted = false;
                this.count--;
                this.setAll();
            }
        });
    },

    setAll() {
        this.$el.dataset.isReposted = this.isReposted;
        this.$el.dataset.repostsCount = this.count;
        this.setTitle();
        this.setText();
    },
});

export { repostButton };
