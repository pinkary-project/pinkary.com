<!-- Lightbox Modal -->
<x-modal name="image-lightbox" close-button-outside-modal should-center-modal-content>
    <div x-data="lightBox" class="relative md:flex md:items-center">
            <img :src="imgSrc" alt="image" class="object-contain max-h-[85vh] rounded-lg"/>
        <button x-show="shouldShowPrevButton" class="absolute left-0 md:-ml-8 dark:text-white text-black cursor-pointer text-2xl" @click="prevImage">&larr;</button>
        <button x-show="shouldShowNextButton" class="absolute right-0 md:-mr-8 dark:text-white text-black cursor-pointer text-2xl" @click="nextImage">&rarr;</button>
    </div>
</x-modal>
