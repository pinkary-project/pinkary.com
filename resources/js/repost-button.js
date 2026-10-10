import { abbreviate } from './abbreviate';

const repostButton = (id, isAuthenticated) => ({
    id,
    isAuthenticated,
    isReposted: false,
    count: 0,
    pending: false,
    repostButtonTitle: '',
    repostButtonText: '',
    onReposted: null,
    onUnreposted: null,
    onPendingChanged: null,

    init() {
        this.isReposted = this.$el.dataset.isReposted === 'true';
        this.count = parseInt(this.$el.dataset.repostsCount) || 0;
        this.setAll();
        this.initEventListeners();
    },

    destroy() {
        window.removeEventListener('question.reposted', this.onReposted);
        window.removeEventListener('question.unreposted', this.onUnreposted);
        window.removeEventListener('question.repost-pending', this.onPendingChanged);
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

        const wasReposted = this.isReposted;
        const previousCount = this.count;
        const eventName = wasReposted ? 'question.unreposted' : 'question.reposted';

        this.$dispatch('question.repost-pending', { id, pending: true });
        this.$dispatch(eventName, {
            id,
            count: Math.max(0, previousCount + (wasReposted ? -1 : 1)),
        });

        try {
            if (wasReposted) {
                await this.$wire.unrepost(id);
            } else {
                await this.$wire.repost(id);
            }
        } catch {
            this.$dispatch(wasReposted ? 'question.reposted' : 'question.unreposted', {
                id,
                count: previousCount,
            });
        } finally {
            this.$dispatch('question.repost-pending', { id, pending: false });
        }
    },

    initEventListeners() {
        this.onReposted = (event) => {
            if (event.detail.id == this.id) {
                this.updateState(true, event.detail.count);
            }
        };

        this.onUnreposted = (event) => {
            if (event.detail.id == this.id) {
                this.updateState(false, event.detail.count);
            }
        };

        this.onPendingChanged = (event) => {
            if (event.detail.id == this.id) {
                this.pending = event.detail.pending;
            }
        };

        window.addEventListener('question.reposted', this.onReposted);
        window.addEventListener('question.unreposted', this.onUnreposted);
        window.addEventListener('question.repost-pending', this.onPendingChanged);
    },

    updateState(isReposted, count) {
        if (Number.isInteger(count)) {
            this.count = Math.max(0, count);
        } else if (this.isReposted !== isReposted) {
            this.count = Math.max(0, this.count + (isReposted ? 1 : -1));
        }

        this.isReposted = isReposted;
        this.setAll();
    },

    setAll() {
        this.$el.dataset.isReposted = this.isReposted;
        this.$el.dataset.repostsCount = this.count;
        this.setTitle();
        this.setText();
    },
});

export { repostButton };
