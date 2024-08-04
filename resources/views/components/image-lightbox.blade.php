<!-- Lightbox Modal -->
<x-modal name="image-lightbox" :should-center="true" :close-button-outside-modal="true" :should-center-modal-content="true">
    <div x-data="lightBox" class="relative md:flex md:items-center">
        <img :src="imgSrc" alt="image" class="max-w-full rounded-lg"/>
        <button x-show="shouldShowPrevButton" class="absolute left-0 md:-ml-8 text-white cursor-pointer text-2xl" @click="prevImage">&larr;</button>
        <button x-show="shouldShowNextButton" class="absolute right-0 md:-mr-8 text-white cursor-pointer text-2xl" @click="nextImage">&rarr;</button>
    </div>
</x-modal>
