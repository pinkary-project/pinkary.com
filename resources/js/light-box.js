const lightBox = () => ({
    open: false,
    imgSrc: '',
    currentIndex: 0,
    images: [],
    init() {
        let self = this;

        let hasLightboxImageElements = document.querySelectorAll('.has-lightbox-images');

        hasLightboxImageElements.forEach((lightboxImageElement) => {
            let images = lightboxImageElement.querySelectorAll('img');

            images.forEach((img, index) => {
                img.classList.add('cursor-pointer');
                img.dataset.navigateIgnore = true;
                img.addEventListener('click', function (e) {
                    console.log(images);
                    self.currentIndex = index;
                    self.images = images;
                    self.updateImageSrc();
                    self.$dispatch('open-modal', 'image-lightbox');
                });
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
    }
});

export {lightBox}
