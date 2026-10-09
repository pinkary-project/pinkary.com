import { abbreviate } from './abbreviate';

const repostButton = (id, isAuthenticated) => ({
    id,
    isAuthenticated,
    isReposted: false,
    count: 0,
    pending: false,
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

    async toggleRepost() {
        if (! this.isAuthenticated) {
            window.Livewire.navigate('/login');

            return;
        }

        if (this.pending) {
            return;
        }

        this.pending = true;

        try {
            if (this.isReposted) {
                await this.$wire.unrepost(id);
            } else {
                await this.$wire.repost(id);
            }
        } finally {
            this.pending = false;
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
