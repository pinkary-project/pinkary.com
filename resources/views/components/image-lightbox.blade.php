<!-- Lightbox Modal -->
<x-modal name="image-lightbox" :should-center="true" :close-button-outside-content="true" :should-center-modal-content="true">
    <div x-data="lightBox">
        <img :src="imgSrc" alt="image" class="max-w-full rounded-lg"/>
    </div>
</x-modal>
