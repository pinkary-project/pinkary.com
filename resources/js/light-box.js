const hasLightBoxImages = () => ({
    init() {
        const images = this.$el.querySelectorAll('img');

        images.forEach((img, index) => {
            img.classList.add('cursor-pointer');
            img.dataset.navigateIgnore = true;
            img.addEventListener('click', (e) => {
                this.$dispatch('open-lightbox', {
                    images: images,
                    currentIndex: index,
                });
            });
        });
    }
});

const lightBox = () => ({
    open: false,
    imgSrc: '',
    currentIndex: 0,
    images: [],
    init() {
        window.addEventListener('open-lightbox', (e) => {
            this.open = true;
            this.currentIndex = e.detail.currentIndex;
            this.images = e.detail.images;
            this.updateImageSrc();
            this.$dispatch('open-modal', 'image-lightbox')
        });

        window.addEventListener('modal-closed', (e) => {
            if (e.detail === 'image-lightbox') {
                this.open = false;
                this.currentIndex = 0;
                this.images = [];
                this.imgSrc = '';
            }
        });

        this.attachKeyboardEvents();
    },
    nextImage() {
        this.currentIndex = (this.currentIndex + 1) % this.images.length;
        this.updateImageSrc()
    },
    prevImage() {
        this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
        this.updateImageSrc()
    },
    updateImageSrc() {
        this.imgSrc = this.images[this.currentIndex].src;
    },
    shouldShowNextButton() {
        return this.canScrollImages() && this.images.length - 1 !== this.currentIndex;
    },
    shouldShowPrevButton() {
        return this.canScrollImages() && this.currentIndex !== 0;
    },
    canScrollImages() {
        return this.images.length > 1;
    },
    attachKeyboardEvents() {
        document.addEventListener('keydown', (e) => {
            if (this.open && this.canScrollImages()) {
                if (e.key === 'ArrowRight' && this.shouldShowNextButton()) {
                    this.nextImage();
                } else if (e.key === 'ArrowLeft' && this.shouldShowPrevButton()) {
                    this.prevImage();
                }
            }
        });
    }
});

export {hasLightBoxImages, lightBox}
