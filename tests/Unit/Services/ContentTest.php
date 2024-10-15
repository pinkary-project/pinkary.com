<?php

declare(strict_types=1);

test('link', function () {
    $content = 'Sure, here is the link: example.com. Let me know if you have any questions.';

    $provider = new App\Services\ParsableContent();

    expect($provider->parse($content))->toBe('Sure, here is the link: <a data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" target="_blank" href="https://example.com">example.com</a>. Let me know if you have any questions.');
});

test('mention', function () {
    $content = '@nunomaduro, let me know if you have any questions. Thanks @xiCO2k.';

    $provider = new App\Services\ParsableContent();

    expect($provider->parse($content))->toBe('<a href="/@nunomaduro" data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" wire-navigate>@nunomaduro</a>, let me know if you have any questions. Thanks <a href="/@xiCO2k" data-navigate-ignore="true" class="text-blue-500 hover:underline hover:text-blue-700 cursor-pointer" wire-navigate>@xiCO2k</a>.');
});

it('ignores mention inside <a>', function () {
    $content = 'https://pinkary.com/@nunomaduro';

    $provider = new App\Services\ParsableContent();

    expect($provider->parse($content))->toBe('<div
        id="link-preview-card"
        class="mx-auto mt-2 min-w-full group/preview" data-navigate-ignore="true">
        <a href="https://pinkary.com/@nunomaduro" target="_blank" rel="noopener noreferrer">
            <div
                title="Click to visit: pinkary.com"
                class="relative w-full
                bg-slate-100/90
                border
                border-slate-300
                dark:border-0
                rounded-lg
                dark:group-hover/preview:border-0
                overflow-hidden">
                <img
                    src="https://pinkary.com/storage/avatars/120f8d175fd0146ca0541625b8bd6c742e838632951a7e58dc7fbdc8c2170c4f.png"
                    alt="Nuno Maduro (@nunomaduro) / Pinkary"
                    class="object-cover object-center h-[228px] w-[513px]"
                />
                <div
                    class="absolute right-0 bottom-0 left-0 w-full rounded-b-lg border-0 bg-pink-100 bg-opacity-75 p-2 backdrop-blur-sm backdrop-filter dark:bg-opacity-45 dark:bg-pink-800">
                    <h3 class="text-sm font-semibold truncate text-slate-500/90 dark:text-white/90
                    ">
                        Nuno Maduro (@nunomaduro) / Pinkary</h3>
                </div>
            </div>
        </a>
        <div class="flex items-center justify-between pt-4">
            <a href="https://pinkary.com/@nunomaduro" target="_blank" rel="noopener noreferrer"
               class="text-xs text-slate-500 group-hover/preview:text-pink-600">From: pinkary.com</a>
        </div>
    </div>
');
});
