<div>
    <x-modal
            max-width="md"
            show-close-button="false"
            name="comment.edit.{{ $commentId }}"
    >
        <div class="p-8">
            <h2 class="text-md font-medium text-slate-50">Edit Comment</h2>
            <x-comment-form action="update"/>
        </div>
    </x-modal>
</div>
