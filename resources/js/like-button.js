import { abbreviate } from "./abbreviate";
import { particlesEffect } from "./particles-effect";

const likeButton = (id, isLiked, count, isAuthenticated) => ({
    id,
    isLiked,
    count,
    isAuthenticated,
    likeButtonTitle: '',
    likeButtonText: '',

    init() {
        this.count = 1150;
        this.setTitle();
        this.setText();
        this.initEventListeners();
    },

    setTitle() {
        this.likeButtonTitle = this.count === 1 ? '1 like' : `${this.count} likes`;
    },

    setText() {
        this.likeButtonText = this.count === 0 ? '' : abbreviate(this.count);
    },

    toggleLike(e) {

        particlesEffect().executeParticlesEffect(e);

        if (!this.isAuthenticated) {
            window.Livewire.navigate('/login');
            return;
        }

        if (this.isLiked) {
            this.$wire.unlike(id);
            this.$dispatch('likeButton.unliked', { id: id });
        } else {
            this.$wire.like(id);
            this.$dispatch('likeButton.liked', { id: id });
        }
    },

    initEventListeners() {
        window.addEventListener('likeButton.liked', (event) => {
            if (event.detail.id == this.id) {
                this.isLiked = true;
                this.count++;
                this.setTitle();
                this.setText();
            }
        });

        window.addEventListener('likeButton.unliked', (event) => {
            if (event.detail.id == this.id) {
                this.isLiked = false;
                this.count--;
                this.setTitle();
                this.setText();
            }
        });
    }
});

export { likeButton };
