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


        if (!this.isAuthenticated) {
            window.Livewire.navigate('/login');
            return;
        }

        if (this.isLiked) {
            this.$wire.unlike(id);
            this.$dispatch('question.unliked', { id: id });
        } else {
            this.$wire.like(id);
            this.$dispatch('question.liked', { id: id });
            particlesEffect().executeParticlesEffect(e);
        }
    },

    initEventListeners() {
        window.addEventListener('question.liked', (event) => {
            if (event.detail.id == this.id) {
                this.isLiked = true;
                this.count++;
                this.setTitle();
                this.setText();
            }
        });

        window.addEventListener('question.unliked', (event) => {
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
