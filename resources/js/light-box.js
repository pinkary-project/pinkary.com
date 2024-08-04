const lightBox = () => ({
    open: false,
    imgSrc: '',
    init() {
        let images = document.querySelectorAll('.has-lightbox-images img');
        let self = this;
        images.forEach(img => {
            img.classList.add('cursor-pointer');
            img.dataset.navigateIgnore = true;
            img.addEventListener('click', function (e) {
                self.imgSrc = img.src;
                self.$dispatch('open-modal', 'image-lightbox');
            });
        });
    }
});

export {lightBox}
