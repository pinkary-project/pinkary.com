<x-app-layout>
<div class="max-w-4xl mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-4">OpenGraph Card Preview</h1>
    
    <div class="mb-6">
        <input type="text" id="url-field" placeholder="Enter URL" class="w-full p-2 border rounded" />
    </div>

    <div id="preview-section" class="mb-6 flex flex-col items-start">
        <h2 class="text-xl font-semibold mb-2">Preview</h2>
        <div id="preview-card">
            <!-- Preview will be injected here -->
        </div>
    </div>

    <div id="error-messages" class="text-red-500"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const urlField = document.getElementById('url-field');
    const previewCard = document.getElementById('preview-card');
    const errorMessages = document.getElementById('error-messages');

    // Debounce function to limit API calls
    function debounce(func, delay) {
        let debounceTimer;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => func.apply(context, args), delay);
        }
    }

    // Fetch and render preview
    const fetchPreview = debounce(async () => {
        const input = urlField.value.trim();

        if (!input) {
            previewCard.innerHTML = '';
            errorMessages.innerHTML = '';
            return;
        }

        try {
            const response = await fetch('{{ route('opengraph.validate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ type: 'url', input }),
            });

            const data = await response.json();

            if (!response.ok) {
                errorMessages.innerHTML = data.errors.input ? data.errors.input.join('<br>') : 'An error occurred.';
                previewCard.innerHTML = '';
                return;
            }

            errorMessages.innerHTML = '';
            renderPreview(data.metadata);
        } catch (error) {
            errorMessages.innerHTML = 'Failed to fetch preview. Please try again.';
            previewCard.innerHTML = '';
        }
    }, 500);

    urlField.addEventListener('input', fetchPreview);

    // Render the preview using existing Pinkary card component
    function renderPreview(metadata) {
        let cardHtml = '';

        if (metadata.html) {
            cardHtml = metadata.html;
        } else if (metadata.image) {
            const title = metadata.title || metadata.site_name || metadata.url;
            const shortUrl = new URL(metadata.url).hostname;

            cardHtml = `
                <div id="link-preview-card" data-url="${metadata.url}" class="mx-auto mt-2 w-fit group/preview" data-navigate-ignore="true">
                    <a href="${metadata.url}" target="_blank" rel="noopener noreferrer">
                        <div title="Click to visit: ${shortUrl}" class="relative w-full bg-slate-100/90 border border-slate-300 
                            dark:border-0 rounded-lg dark:group-hover/preview:border-0 overflow-hidden">
                            <img src="${metadata.image}" alt="${title}" class="object-cover object-center w-full h-56">
                            <div class="absolute right-0 bottom-0 left-0 w-full rounded-b-lg border-0 bg-pink-100 bg-opacity-75 p-2 backdrop-blur-sm backdrop-filter dark:bg-opacity-45 dark:bg-pink-800">
                                <h3 class="text-sm font-semibold truncate text-slate-500/90 dark:text-white/90">
                                    ${title}
                                </h3>
                            </div>
                        </div>
                    </a>
                    <div class="flex items-center justify-between pt-4">
                        <a href="${metadata.url}" target="_blank" rel="noopener noreferrer" class="text-xs text-slate-500 group-hover/preview:text-pink-600">From: ${shortUrl}</a>
                    </div>
                </div>`;
        }

        previewCard.innerHTML = cardHtml;
    }
});
</script>
</x-app-layout>