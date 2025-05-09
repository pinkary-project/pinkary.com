import { abbreviate } from "./abbreviate";
import { particlesEffect } from "./particles-effect";

const repostButton = (id, isReposted, count, isAuthenticated) => ({
    id,
    isReposted,
    count,
    isAuthenticated,
    repostButtonTitle: '',
    repostButtonText: '',

    init() {
        this.setTitle();
        this.setText();
        this.initEventListeners();
    },

    setTitle() {
        this.repostButtonTitle = this.count === 1 ? '1 repost' : `${this.count} reposts`;
    },

    setText() {
        this.repostButtonText = this.count === 0 ? '' : abbreviate(this.count);
    },

    toggleRepost(e) {
        if (!this.isAuthenticated) {
            window.Livewire.navigate('/login');
            return;
        }

        if (this.isReposted) {
            this.$wire.unRepost(id);
            this.$dispatch('question.unreposted', { id: id });
        } else {
            this.$wire.repost(id);
            this.$dispatch('question.reposted', { id: id });
            particlesEffect().executeParticlesEffect(e);
        }
    },

    initEventListeners() {
        window.addEventListener('question.reposted', (event) => {
            if (event.detail.id == this.id) {
                this.isReposted = true;
                this.count++;
                this.setTitle();
                this.setText();
            }
        });

        window.addEventListener('question.unreposted', (event) => {
            if (event.detail.id == this.id) {
                this.isReposted = false;
                this.count--;
                this.setTitle();
                this.setText();
            }
        });
    }
});

export { repostButton };
