<div class="block p-10">
    <div class="mb-4">
        <h2 class="text-xl font-semibold dark:text-white text-black mb-4">Who to follow</h2>
    </div>
    <ul class="flex flex-col gap-4">
        @for ($i = 0; $i < 5; $i++)
            <li class="block">
                <div class="animate-pulse group p-4 mt-3 rounded-2xl bg-slate-900">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center w-full">
                            <div class="flex-shrink-0 mr-3">
                                <div class="w-10 h-10 bg-slate-700 rounded-full"></div>
                            </div>
                            <div class="flex flex-col">
                                <div class="w-20 h-2 mt-2 bg-slate-700 rounded"></div>
                                <div class="w-16 h-2 mt-2.5 bg-slate-700 rounded"></div>
                            </div>
                        </div>
                        <div class="w-16 h-8 bg-slate-700 rounded"></div>
                    </div>
                </div>
            </li>
        @endfor
    </ul>
</div>
