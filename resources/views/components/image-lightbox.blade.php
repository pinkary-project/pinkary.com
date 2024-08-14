<!-- Lightbox Modal -->
<x-modal name="image-lightbox" close-button-outside-modal should-center-modal-content>
    <div x-data="lightBox" class="relative md:flex md:items-center">
        <div class="overflow-y-auto max-h-[85vh]">
            <img :src="imgSrc" alt="image" class="max-w-full rounded-lg"/>
        </div>
        <button x-show="shouldShowPrevButton" class="absolute left-0 md:-ml-8 text-white cursor-pointer text-2xl" @click="prevImage">&larr;</button>
        <button x-show="shouldShowNextButton" class="absolute right-0 md:-mr-8 text-white cursor-pointer text-2xl" @click="nextImage">&rarr;</button>
    </div>
</x-modal>
