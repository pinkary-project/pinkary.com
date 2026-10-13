<!-- Lightbox Modal -->
<x-modal name="image-lightbox" close-button-outside-modal should-center-modal-content focusable>
    <div x-data="lightBox" class="relative md:flex md:items-center">
        <img :src="imgSrc" alt="image" class="max-h-[85vh] rounded-lg object-contain" />
        <button
            x-show="shouldShowPrevButton"
            type="button"
            aria-label="{{ __('Previous image') }}"
            class="absolute left-0 cursor-pointer text-2xl text-black md:-ml-8 dark:text-white"
            @click="prevImage"
        >
            &larr;
        </button>
        <button
            x-show="shouldShowNextButton"
            type="button"
            aria-label="{{ __('Next image') }}"
            class="absolute right-0 cursor-pointer text-2xl text-black md:-mr-8 dark:text-white"
            @click="nextImage"
        >
            &rarr;
        </button>
    </div>
</x-modal>
