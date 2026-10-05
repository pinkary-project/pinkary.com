const galleryHeight = (width, images, maximum = 256) => {
    const ratios = images.filter(image => image.width > 0 && image.height > 0)
        .map(image => image.width / image.height);
    const availableWidth = Math.max(0, width - (images.length > 1 ? 32 : 0));

    return Math.min(maximum, availableWidth / Math.max(1, ...ratios));
};

const galleryScrollTarget = (positions, current, maximum, direction) => {
    const stops = [...new Set([0, ...positions.map(position => Math.min(maximum, Math.max(0, position))), maximum])]
        .sort((left, right) => left - right);

    return direction > 0
        ? stops.find(position => position > current + 1) ?? maximum
        : stops.reverse().find(position => position < current - 1) ?? 0;
};

const imageGallery = () => ({
    height: 192,
    currentImage: 1,
    canPrevious: false,
    canNext: false,
    dragging: false,
    pointerId: null,
    startX: 0,
    startScroll: 0,
    suppressClick: false,
    observer: null,

    init() {
        this.observer = new ResizeObserver(() => this.measure());
        this.observer.observe(this.$refs.viewport);
        this.$nextTick(() => this.measure());
    },

    measure() {
        const viewport = this.$refs.viewport;
        const images = Array.from(viewport.querySelectorAll('img'))
            .map(image => ({ width: image.naturalWidth, height: image.naturalHeight }));

        this.height = galleryHeight(viewport.clientWidth, images);
        this.updateControls();
    },

    updateControls() {
        const viewport = this.$refs.viewport;
        const images = Array.from(viewport.firstElementChild.children);
        const left = viewport.getBoundingClientRect().left;
        const firstVisibleImage = images.findIndex(image =>
            image.getBoundingClientRect().right > left + Math.min(viewport.clientWidth, image.clientWidth) / 2
        );

        this.canPrevious = viewport.scrollLeft > 1;
        this.canNext = viewport.scrollLeft < viewport.scrollWidth - viewport.clientWidth - 1;
        this.currentImage = this.canPrevious && ! this.canNext
            ? images.length
            : Math.max(0, firstVisibleImage) + 1;
    },

    scroll(direction) {
        const viewport = this.$refs.viewport;
        const left = viewport.getBoundingClientRect().left;
        const positions = Array.from(viewport.firstElementChild.children)
            .map(image => image.getBoundingClientRect().left - left + viewport.scrollLeft);

        viewport.scrollTo({
            left: galleryScrollTarget(positions, viewport.scrollLeft, viewport.scrollWidth - viewport.clientWidth, direction),
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth',
        });
    },

    startDrag(event) {
        if (event.pointerType !== 'mouse' || event.button !== 0 || ! (this.canPrevious || this.canNext)) {
            return;
        }

        this.pointerId = event.pointerId;
        this.startX = event.clientX;
        this.startScroll = this.$refs.viewport.scrollLeft;
        this.suppressClick = false;
    },

    drag(event) {
        if (event.pointerId !== this.pointerId) {
            return;
        }

        const distance = event.clientX - this.startX;

        if (! this.dragging && Math.abs(distance) < 5) {
            return;
        }

        this.dragging = true;
        this.suppressClick = true;
        this.$refs.viewport.setPointerCapture(event.pointerId);
        this.$refs.viewport.scrollLeft = this.startScroll - distance;
        event.preventDefault();
    },

    endDrag(event) {
        if (event.pointerId !== this.pointerId) {
            return;
        }

        this.pointerId = null;
        this.dragging = false;

        if (this.$refs.viewport.hasPointerCapture(event.pointerId)) {
            this.$refs.viewport.releasePointerCapture(event.pointerId);
        }
    },

    preventDragClick(event) {
        if (this.suppressClick) {
            event.preventDefault();
            event.stopImmediatePropagation();
            this.suppressClick = false;
        }
    },

    destroy() {
        this.observer.disconnect();
    },
});

export { galleryHeight, galleryScrollTarget, imageGallery };
