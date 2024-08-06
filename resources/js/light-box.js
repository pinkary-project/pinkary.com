const lightBox = () => ({
    open: false,
    imgSrc: '',
    currentIndex: 0,
    images: [],
    observer: null,
    init() {
        let self = this;

        let hasLightboxImageElements = document.querySelectorAll('[data-has-lightbox-images]');

        hasLightboxImageElements.forEach((lightboxImageElement) => {
            self.registerEventForLightboxElement(lightboxImageElement);

            if (self.observer === null) {
                const observer = new MutationObserver((mutations) => {
                    self.$nextTick(() => {
                        mutations.forEach((mutation) => {
                            mutation.addedNodes.forEach((node) => {
                                if (node.nodeType === 1) {
                                    self.registerEventForLightboxElement(node);
                                }
                            });
                        });
                    });
                });

                let section = lightboxImageElement.closest('section');

                if (section) {
                    observer.observe(section, {childList: true, subtree: true});
                }
            }
        });

        window.addEventListener('modal-opened', (e) => {
            if (e.detail === 'image-lightbox') {
                this.open = true;
            }
        });

        window.addEventListener('modal-closed', (e) => {
            if (e.detail === 'image-lightbox') {
                this.open = false;
            }
        });
    },
    registerEventForLightboxElement(lightboxImageElement) {
        let images = lightboxImageElement.querySelectorAll('img');
        let self = this;

        images.forEach((img, index) => {
            img.classList.add('cursor-pointer');
            img.dataset.navigateIgnore = true;
            img.addEventListener('click', function (e) {
                self.currentIndex = index;
                self.images = images;
                self.updateImageSrc();
                self.$dispatch('open-modal', 'image-lightbox');
                self.attachKeyboardEvents();
            });
        });
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

export {lightBox}
