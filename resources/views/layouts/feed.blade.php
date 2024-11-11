<x-main-layout backgroundImage="solid">
    <div
        x-data="toggle()"
        class="mx-auto max-w-7xl relative h-full"
    >
        <x-sidebar
            @click.outside="closeSidebar($event)"
            x-ref="sidebar"
            class="hidden xl:fixed top-16 bg-slate-900"
        />

        <div class="xl:pl-72">
            <!-- Sticky search header -->
            <div class="sticky top-0 z-40 flex w-full h-16 shrink-0 items-center gap-x-6 border-b border-r border-white/5 bg-gray-900 shadow-sm px-6 xl:px-8">
                <button
                    @click="toggleSidebar($event)"
                    class="-m-2.5 p-2.5 text-white xl:hidden">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon"><path fill-rule="evenodd" d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"></path></svg>
                </button>

                <div class="flex flex-1 gap-x-4 self-stretch xl:gap-x-6">
                    <form class="flex flex-1" action="#" method="GET">
                        <label for="search-field" class="sr-only">Search</label>
                        <div class="relative w-full">
                            <svg class="pointer-events-none absolute inset-y-0 left-0 h-full w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z" clip-rule="evenodd"></path></svg>

                            <input id="search-field" class="block h-full w-full border-0 bg-transparent py-0 pl-8 pr-0 text-white focus:ring-0 xl:text-sm" placeholder="Search for users or hashtags..." type="search" name="search">
                        </div>
                    </form>
                </div>
            </div>

            <div class="xl:grid xl:grid-cols-3 h-full">
                <main class="xl:max-w-2xl w-full col-span-2">
                    <header class="flex items-center justify-between border-b border-white/5 p-6 xl:px-8">
                        <h1 class="text-base/7 font-medium text-white">Feed</h1>

                        <div class="space-x-1 text-sm">
                            <a href="" class="bg-gray-800 rounded-md px-2.5 py-1.5 text-white">Trending</a>
                            <a href="" class="hover:bg-gray-800/30 rounded-md px-2.5 py-1.5 text-gray-400">Following</a>
                            <a href="" class="hover:bg-gray-800/30 rounded-md px-2.5 py-1.5 text-gray-400">Recent</a>
                        </div>
                    </header>

                    <form action="#" class="p-6 xl:p-8">
                        <div>
                            <div class="flex space-x-4">
                                <img src="https://pinkary.com/storage/avatars/bec85860bae95878772479fb9febce97b67444f7aa3dcbf0129958d43886f5ac.png" alt="developermithu" class="rounded-full size-10">

                                <label for="post" class="sr-only">Post</label>
                                <div class="flex flex-col flex-1">
                                    <textarea rows="3" name="post" id="post" class="transition block w-full rounded-lg border border-white/10 bg-white/5 py-1.5 text-white shadow-sm focus:ring-4 focus:border-pink-500 focus:ring-pink-500/20 xl:text-sm" placeholder="Share an update..."></textarea>

                                    <div class="flex justify-between mt-4">
                                        <div class="flex space-x-2">
                                            <button type="submit" class="inline-flex items-center rounded-lg bg-pink-600 px-12 py-2 text-sm font-semibold text-white shadow-sm hover:bg-pink-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-600">Post</button>

                                            <button class="flex items-center justify-center text-gray-400 hover:bg-gray-800 rounded-lg size-10">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-6" fill="none"><path d="M2.5 12C2.5 7.52166 2.5 5.28249 3.89124 3.89124C5.28249 2.5 7.52166 2.5 12 2.5C16.4783 2.5 18.7175 2.5 20.1088 3.89124C21.5 5.28249 21.5 7.52166 21.5 12C21.5 16.4783 21.5 18.7175 20.1088 20.1088C18.7175 21.5 16.4783 21.5 12 21.5C7.52166 21.5 5.28249 21.5 3.89124 20.1088C2.5 18.7175 2.5 16.4783 2.5 12Z" stroke="currentColor" stroke-width="1.5"></path><circle cx="16.5" cy="7.5" r="1.5" stroke="currentColor" stroke-width="1.5"></circle><path d="M16 22C15.3805 19.7749 13.9345 17.7821 11.8765 16.3342C9.65761 14.7729 6.87163 13.9466 4.01569 14.0027C3.67658 14.0019 3.33776 14.0127 3 14.0351" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"></path><path d="M13 18C14.7015 16.6733 16.5345 15.9928 18.3862 16.0001C19.4362 15.999 20.4812 16.2216 21.5 16.6617" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"></path></svg>
                                            </button>
                                        </div>
                                        <div class="text-xs text-gray-400 text-right">0 / 1000</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <ul role="list" class="divide-y divide-white/5 border-t border-white/5">
                        <li class="p-6 xl:p-8 cursor-pointer hover:bg-gray-800/20">
                            <div class="flex justify-between">
                                <a href="https://pinkary.com/@developermithu" class="group/profile flex items-center gap-3">
                                    <figure class="rounded-full size-10 flex-shrink-0 bg-gray-800 transition-opacity group-hover/profile:opacity-90">
                                        <img src="https://pinkary.com/storage/avatars/3d344ad52744ea44975f46f815a6299c7fc2c3e007831d4a5217069b8f04f13a.png" alt="developermithu" class="rounded-full size-10">
                                    </figure>
                                    <div class="overflow-hidden text-sm">
                                        <div class="items flex">
                                            <p class="truncate font-medium text-gray-50">Mithu Das</p>
                                        </div>

                                        <p class="truncate text-gray-500 transition-colors group-hover/profile:text-gray-400">@developermithu</p>
                                    </div>
                                </a>
                            </div>

                            <div class="text-sm mt-3 break-words text-gray-200 overflow-hidden">
                                <p>How do you deploy <a class="text-pink-500 hover:underline cursor-pointer" href="/hashtag/Laravel">#Laravel</a> applications?<br><br>A) Laravel Forge<br>B) Self-hosted solutions</p>
                            </div>

                            <div class="mt-3 flex items-center justify-between text-sm text-gray-500">
                                <div class="flex items-center gap-2">
                                    <a href="https://pinkary.com/@developermithu/questions/9d6ceade-3e53-4211-8063-a923d1bae5c1" class="flex items-center transition-colors group-hover:text-pink-500 hover:text-gray-400 focus:outline-none cursor-pointer">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M8 13.5H16M8 8.5H12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.09881 19C4.7987 18.8721 3.82475 18.4816 3.17157 17.8284C2 16.6569 2 14.7712 2 11V10.5C2 6.72876 2 4.84315 3.17157 3.67157C4.34315 2.5 6.22876 2.5 10 2.5H14C17.7712 2.5 19.6569 2.5 20.8284 3.67157C22 4.84315 22 6.72876 22 10.5V11C22 14.7712 22 16.6569 20.8284 17.8284C19.6569 19 17.7712 19 14 19C13.4395 19.0125 12.9931 19.0551 12.5546 19.155C11.3562 19.4309 10.2465 20.0441 9.14987 20.5789C7.58729 21.3408 6.806 21.7218 6.31569 21.3651C5.37769 20.6665 6.29454 18.5019 6.5 17.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg>

                                        <span class="mx-1 text-xs">2</span>
                                    </a>

                                    <span>•</span>

                                    <button class="flex items-center transition-colors hover:text-gray-400 focus:outline-none">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M19.4626 3.99415C16.7809 2.34923 14.4404 3.01211 13.0344 4.06801C12.4578 4.50096 12.1696 4.71743 12 4.71743C11.8304 4.71743 11.5422 4.50096 10.9656 4.06801C9.55962 3.01211 7.21909 2.34923 4.53744 3.99415C1.01807 6.15294 0.221721 13.2749 8.33953 19.2834C9.88572 20.4278 10.6588 21 12 21C13.3412 21 14.1143 20.4278 15.6605 19.2834C23.7783 13.2749 22.9819 6.15294 19.4626 3.99415Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg>

                                        <span class="mx-1 text-xs">21</span>
                                    </button>

                                    <span>•</span>

                                    <div class="inline-flex cursor-help items-center">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M3.5 9.5V18.5C3.5 18.9659 3.5 19.1989 3.57612 19.3827C3.67761 19.6277 3.87229 19.8224 4.11732 19.9239C4.30109 20 4.53406 20 5 20C5.46594 20 5.69891 20 5.88268 19.9239C6.12771 19.8224 6.32239 19.6277 6.42388 19.3827C6.5 19.1989 6.5 18.9659 6.5 18.5V9.5C6.5 9.03406 6.5 8.80109 6.42388 8.61732C6.32239 8.37229 6.12771 8.17761 5.88268 8.07612C5.69891 8 5.46594 8 5 8C4.53406 8 4.30109 8 4.11732 8.07612C3.87229 8.17761 3.67761 8.37229 3.57612 8.61732C3.5 8.80109 3.5 9.03406 3.5 9.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"></path><path d="M10.5 5.5V18.4995C10.5 18.9654 10.5 19.1984 10.5761 19.3822C10.6776 19.6272 10.8723 19.8219 11.1173 19.9234C11.3011 19.9995 11.5341 19.9995 12 19.9995C12.4659 19.9995 12.6989 19.9995 12.8827 19.9234C13.1277 19.8219 13.3224 19.6272 13.4239 19.3822C13.5 19.1984 13.5 18.9654 13.5 18.4995V5.5C13.5 5.03406 13.5 4.80109 13.4239 4.61732C13.3224 4.37229 13.1277 4.17761 12.8827 4.07612C12.6989 4 12.4659 4 12 4C11.5341 4 11.3011 4 11.1173 4.07612C10.8723 4.17761 10.6776 4.37229 10.5761 4.61732C10.5 4.80109 10.5 5.03406 10.5 5.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"></path><path d="M17.5 12.5V18.5C17.5 18.9659 17.5 19.1989 17.5761 19.3827C17.6776 19.6277 17.8723 19.8224 18.1173 19.9239C18.3011 20 18.5341 20 19 20C19.4659 20 19.6989 20 19.8827 19.9239C20.1277 19.8224 20.3224 19.6277 20.4239 19.3827C20.5 19.1989 20.5 18.9659 20.5 18.5V12.5C20.5 12.0341 20.5 11.8011 20.4239 11.6173C20.3224 11.3723 20.1277 11.1776 19.8827 11.0761C19.6989 11 19.4659 11 19 11C18.5341 11 18.3011 11 18.1173 11.0761C17.8723 11.1776 17.6776 11.3723 17.5761 11.6173C17.5 11.8011 17.5 12.0341 17.5 12.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"></path></svg>

                                        <span class="mx-1 text-xs">12</span>
                                    </div>

                                    <span>•</span>

                                    <button class="flex items-center transition-colors duration-150 ease-in-out focus:outline-none text-gray-500 hover:text-gray-400">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M4 17.9808V9.70753C4 6.07416 4 4.25748 5.17157 3.12874C6.34315 2 8.22876 2 12 2C15.7712 2 17.6569 2 18.8284 3.12874C20 4.25748 20 6.07416 20 9.70753V17.9808C20 20.2867 20 21.4396 19.2272 21.8523C17.7305 22.6514 14.9232 19.9852 13.59 19.1824C12.8168 18.7168 12.4302 18.484 12 18.484C11.5698 18.484 11.1832 18.7168 10.41 19.1824C9.0768 19.9852 6.26947 22.6514 4.77285 21.8523C4 21.4396 4 20.2867 4 17.9808Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>

                                        <span class="mx-1 text-xs">4</span>
                                    </button>

                                    <span>•</span>

                                    <button class="flex items-center transition-colors duration-150 ease-in-out focus:outline-none text-gray-500 hover:text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-4" fill="none"><path d="M21.0477 3.05293C18.8697 0.707363 2.48648 6.4532 2.50001 8.551C2.51535 10.9299 8.89809 11.6617 10.6672 12.1581C11.7311 12.4565 12.016 12.7625 12.2613 13.8781C13.3723 18.9305 13.9301 21.4435 15.2014 21.4996C17.2278 21.5892 23.1733 5.342 21.0477 3.05293Z" stroke="currentColor" stroke-width="1.5"></path><path d="M11.5 12.5L15 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>

                                        <span class="mx-1 text-xs">1</span>
                                    </button>
                                </div>

                                <div class="flex items-center text-gray-500">
                                    <time class="cursor-help text-xs" title="Wed, 6 November 2024 16:41" datetime="2024-11-06T16:41:19+00:00">50m ago</time>
                                </div>
                            </div>
                        </li>
                        <li class="p-6 xl:p-8 cursor-pointer hover:bg-gray-800/20">
                            <div class="flex justify-between">
                                <a href="https://pinkary.com/@developermithu" class="group/profile flex items-center gap-3">
                                    <figure class="rounded-full size-10 flex-shrink-0 bg-gray-800 transition-opacity group-hover/profile:opacity-90">
                                        <img src="https://pinkary.com/storage/avatars/0c64f67f3f182570b8cf1028e5a72fa0ac83b2c295c6bf06e8ea930a89026f15.png" alt="developermithu" class="rounded-full size-10">
                                    </figure>
                                    <div class="overflow-hidden text-sm">
                                        <div class="items flex">
                                            <p class="truncate font-medium text-gray-50">Punyapal Shah ☁️🦹</p>
                                        </div>

                                        <p class="truncate text-gray-500 transition-colors group-hover/profile:text-gray-400">@MrPunyapal</p>
                                    </div>
                                </a>
                            </div>

                            <div class="text-sm mt-3 break-words text-gray-200 overflow-hidden">
                                <p>⚡ Boost Filament table performance! Load large datasets asynchronously with deferLoading() to keep things fast and smooth. 🌐✨<br><br>
                                    <a class="text-pink-500 hover:underline cursor-pointer mr-1" href="/hashtag/Laravel">#Laravel</a>
                                    <a class="text-pink-500 hover:underline cursor-pointer mr-1" href="/hashtag/Laravel">#Filament</a>
                                    <a class="text-pink-500 hover:underline cursor-pointer mr-1" href="/hashtag/Laravel">#PHP</a>
                                </p>

                                <br>

                                <img class="object-contain mx-auto rounded-lg cursor-pointer" src="https://pinkary.com/storage/images/2024-11-06/xOYu0HM9By1bPbUSYq2WpwlqCckDzR0iRoyKW9Hi.png" alt="image" data-navigate-ignore="true">
                            </div>

                            <div class="mt-3 flex items-center justify-between text-sm text-gray-500">
                                <div class="flex items-center gap-2">
                                    <a href="https://pinkary.com/@developermithu/questions/9d6ceade-3e53-4211-8063-a923d1bae5c1" class="flex items-center transition-colors group-hover:text-pink-500 hover:text-gray-400 focus:outline-none cursor-pointer">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M8 13.5H16M8 8.5H12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.09881 19C4.7987 18.8721 3.82475 18.4816 3.17157 17.8284C2 16.6569 2 14.7712 2 11V10.5C2 6.72876 2 4.84315 3.17157 3.67157C4.34315 2.5 6.22876 2.5 10 2.5H14C17.7712 2.5 19.6569 2.5 20.8284 3.67157C22 4.84315 22 6.72876 22 10.5V11C22 14.7712 22 16.6569 20.8284 17.8284C19.6569 19 17.7712 19 14 19C13.4395 19.0125 12.9931 19.0551 12.5546 19.155C11.3562 19.4309 10.2465 20.0441 9.14987 20.5789C7.58729 21.3408 6.806 21.7218 6.31569 21.3651C5.37769 20.6665 6.29454 18.5019 6.5 17.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg>

                                        <span class="mx-1 text-xs">2</span>
                                    </a>

                                    <span>•</span>

                                    <button class="flex items-center transition-colors hover:text-gray-400 focus:outline-none">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M19.4626 3.99415C16.7809 2.34923 14.4404 3.01211 13.0344 4.06801C12.4578 4.50096 12.1696 4.71743 12 4.71743C11.8304 4.71743 11.5422 4.50096 10.9656 4.06801C9.55962 3.01211 7.21909 2.34923 4.53744 3.99415C1.01807 6.15294 0.221721 13.2749 8.33953 19.2834C9.88572 20.4278 10.6588 21 12 21C13.3412 21 14.1143 20.4278 15.6605 19.2834C23.7783 13.2749 22.9819 6.15294 19.4626 3.99415Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg>

                                        <span class="mx-1 text-xs">21</span>
                                    </button>

                                    <span>•</span>

                                    <div class="inline-flex cursor-help items-center">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M3.5 9.5V18.5C3.5 18.9659 3.5 19.1989 3.57612 19.3827C3.67761 19.6277 3.87229 19.8224 4.11732 19.9239C4.30109 20 4.53406 20 5 20C5.46594 20 5.69891 20 5.88268 19.9239C6.12771 19.8224 6.32239 19.6277 6.42388 19.3827C6.5 19.1989 6.5 18.9659 6.5 18.5V9.5C6.5 9.03406 6.5 8.80109 6.42388 8.61732C6.32239 8.37229 6.12771 8.17761 5.88268 8.07612C5.69891 8 5.46594 8 5 8C4.53406 8 4.30109 8 4.11732 8.07612C3.87229 8.17761 3.67761 8.37229 3.57612 8.61732C3.5 8.80109 3.5 9.03406 3.5 9.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"></path><path d="M10.5 5.5V18.4995C10.5 18.9654 10.5 19.1984 10.5761 19.3822C10.6776 19.6272 10.8723 19.8219 11.1173 19.9234C11.3011 19.9995 11.5341 19.9995 12 19.9995C12.4659 19.9995 12.6989 19.9995 12.8827 19.9234C13.1277 19.8219 13.3224 19.6272 13.4239 19.3822C13.5 19.1984 13.5 18.9654 13.5 18.4995V5.5C13.5 5.03406 13.5 4.80109 13.4239 4.61732C13.3224 4.37229 13.1277 4.17761 12.8827 4.07612C12.6989 4 12.4659 4 12 4C11.5341 4 11.3011 4 11.1173 4.07612C10.8723 4.17761 10.6776 4.37229 10.5761 4.61732C10.5 4.80109 10.5 5.03406 10.5 5.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"></path><path d="M17.5 12.5V18.5C17.5 18.9659 17.5 19.1989 17.5761 19.3827C17.6776 19.6277 17.8723 19.8224 18.1173 19.9239C18.3011 20 18.5341 20 19 20C19.4659 20 19.6989 20 19.8827 19.9239C20.1277 19.8224 20.3224 19.6277 20.4239 19.3827C20.5 19.1989 20.5 18.9659 20.5 18.5V12.5C20.5 12.0341 20.5 11.8011 20.4239 11.6173C20.3224 11.3723 20.1277 11.1776 19.8827 11.0761C19.6989 11 19.4659 11 19 11C18.5341 11 18.3011 11 18.1173 11.0761C17.8723 11.1776 17.6776 11.3723 17.5761 11.6173C17.5 11.8011 17.5 12.0341 17.5 12.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"></path></svg>

                                        <span class="mx-1 text-xs">12</span>
                                    </div>

                                    <span>•</span>

                                    <button class="flex items-center transition-colors duration-150 ease-in-out focus:outline-none text-gray-500 hover:text-gray-400">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M4 17.9808V9.70753C4 6.07416 4 4.25748 5.17157 3.12874C6.34315 2 8.22876 2 12 2C15.7712 2 17.6569 2 18.8284 3.12874C20 4.25748 20 6.07416 20 9.70753V17.9808C20 20.2867 20 21.4396 19.2272 21.8523C17.7305 22.6514 14.9232 19.9852 13.59 19.1824C12.8168 18.7168 12.4302 18.484 12 18.484C11.5698 18.484 11.1832 18.7168 10.41 19.1824C9.0768 19.9852 6.26947 22.6514 4.77285 21.8523C4 21.4396 4 20.2867 4 17.9808Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>

                                        <span class="mx-1 text-xs">4</span>
                                    </button>

                                    <span>•</span>

                                    <button class="flex items-center transition-colors duration-150 ease-in-out focus:outline-none text-gray-500 hover:text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-4" fill="none"><path d="M21.0477 3.05293C18.8697 0.707363 2.48648 6.4532 2.50001 8.551C2.51535 10.9299 8.89809 11.6617 10.6672 12.1581C11.7311 12.4565 12.016 12.7625 12.2613 13.8781C13.3723 18.9305 13.9301 21.4435 15.2014 21.4996C17.2278 21.5892 23.1733 5.342 21.0477 3.05293Z" stroke="currentColor" stroke-width="1.5"></path><path d="M11.5 12.5L15 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>

                                        <span class="mx-1 text-xs">1</span>
                                    </button>
                                </div>

                                <div class="flex items-center text-gray-500">
                                    <time class="cursor-help text-xs" title="Wed, 6 November 2024 16:41" datetime="2024-11-06T16:41:19+00:00">50m ago</time>
                                </div>
                            </div>
                        </li>
                        <li class="p-6 xl:p-8 cursor-pointer hover:bg-gray-800/20">
                            <div class="flex justify-between">
                                <a href="https://pinkary.com/@developermithu" class="group/profile flex items-center gap-3">
                                    <figure class="rounded-full size-10 flex-shrink-0 bg-gray-800 transition-opacity group-hover/profile:opacity-90">
                                        <img src="https://pinkary.com/storage/avatars/3d344ad52744ea44975f46f815a6299c7fc2c3e007831d4a5217069b8f04f13a.png" alt="developermithu" class="rounded-full size-10">
                                    </figure>
                                    <div class="overflow-hidden text-sm">
                                        <div class="items flex">
                                            <p class="truncate font-medium text-gray-50">Mithu Das</p>
                                        </div>

                                        <p class="truncate text-gray-500 transition-colors group-hover/profile:text-gray-400">@developermithu</p>
                                    </div>
                                </a>
                            </div>

                            <div class="text-sm mt-3 break-words text-gray-200 overflow-hidden">
                                <p>How do you deploy <a class="text-pink-500 hover:underline cursor-pointer" href="/hashtag/Laravel">#Laravel</a> applications?<br><br>A) Laravel Forge<br>B) Self-hosted solutions</p>
                            </div>

                            <div class="mt-3 flex items-center justify-between text-sm text-gray-500">
                                <div class="flex items-center gap-2">
                                    <a href="https://pinkary.com/@developermithu/questions/9d6ceade-3e53-4211-8063-a923d1bae5c1" class="flex items-center transition-colors group-hover:text-pink-500 hover:text-gray-400 focus:outline-none cursor-pointer">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M8 13.5H16M8 8.5H12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.09881 19C4.7987 18.8721 3.82475 18.4816 3.17157 17.8284C2 16.6569 2 14.7712 2 11V10.5C2 6.72876 2 4.84315 3.17157 3.67157C4.34315 2.5 6.22876 2.5 10 2.5H14C17.7712 2.5 19.6569 2.5 20.8284 3.67157C22 4.84315 22 6.72876 22 10.5V11C22 14.7712 22 16.6569 20.8284 17.8284C19.6569 19 17.7712 19 14 19C13.4395 19.0125 12.9931 19.0551 12.5546 19.155C11.3562 19.4309 10.2465 20.0441 9.14987 20.5789C7.58729 21.3408 6.806 21.7218 6.31569 21.3651C5.37769 20.6665 6.29454 18.5019 6.5 17.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg>

                                        <span class="mx-1 text-xs">2</span>
                                    </a>

                                    <span>•</span>

                                    <button class="flex items-center transition-colors hover:text-gray-400 focus:outline-none">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M19.4626 3.99415C16.7809 2.34923 14.4404 3.01211 13.0344 4.06801C12.4578 4.50096 12.1696 4.71743 12 4.71743C11.8304 4.71743 11.5422 4.50096 10.9656 4.06801C9.55962 3.01211 7.21909 2.34923 4.53744 3.99415C1.01807 6.15294 0.221721 13.2749 8.33953 19.2834C9.88572 20.4278 10.6588 21 12 21C13.3412 21 14.1143 20.4278 15.6605 19.2834C23.7783 13.2749 22.9819 6.15294 19.4626 3.99415Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg>

                                        <span class="mx-1 text-xs">21</span>
                                    </button>

                                    <span>•</span>

                                    <div class="inline-flex cursor-help items-center">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M3.5 9.5V18.5C3.5 18.9659 3.5 19.1989 3.57612 19.3827C3.67761 19.6277 3.87229 19.8224 4.11732 19.9239C4.30109 20 4.53406 20 5 20C5.46594 20 5.69891 20 5.88268 19.9239C6.12771 19.8224 6.32239 19.6277 6.42388 19.3827C6.5 19.1989 6.5 18.9659 6.5 18.5V9.5C6.5 9.03406 6.5 8.80109 6.42388 8.61732C6.32239 8.37229 6.12771 8.17761 5.88268 8.07612C5.69891 8 5.46594 8 5 8C4.53406 8 4.30109 8 4.11732 8.07612C3.87229 8.17761 3.67761 8.37229 3.57612 8.61732C3.5 8.80109 3.5 9.03406 3.5 9.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"></path><path d="M10.5 5.5V18.4995C10.5 18.9654 10.5 19.1984 10.5761 19.3822C10.6776 19.6272 10.8723 19.8219 11.1173 19.9234C11.3011 19.9995 11.5341 19.9995 12 19.9995C12.4659 19.9995 12.6989 19.9995 12.8827 19.9234C13.1277 19.8219 13.3224 19.6272 13.4239 19.3822C13.5 19.1984 13.5 18.9654 13.5 18.4995V5.5C13.5 5.03406 13.5 4.80109 13.4239 4.61732C13.3224 4.37229 13.1277 4.17761 12.8827 4.07612C12.6989 4 12.4659 4 12 4C11.5341 4 11.3011 4 11.1173 4.07612C10.8723 4.17761 10.6776 4.37229 10.5761 4.61732C10.5 4.80109 10.5 5.03406 10.5 5.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"></path><path d="M17.5 12.5V18.5C17.5 18.9659 17.5 19.1989 17.5761 19.3827C17.6776 19.6277 17.8723 19.8224 18.1173 19.9239C18.3011 20 18.5341 20 19 20C19.4659 20 19.6989 20 19.8827 19.9239C20.1277 19.8224 20.3224 19.6277 20.4239 19.3827C20.5 19.1989 20.5 18.9659 20.5 18.5V12.5C20.5 12.0341 20.5 11.8011 20.4239 11.6173C20.3224 11.3723 20.1277 11.1776 19.8827 11.0761C19.6989 11 19.4659 11 19 11C18.5341 11 18.3011 11 18.1173 11.0761C17.8723 11.1776 17.6776 11.3723 17.5761 11.6173C17.5 11.8011 17.5 12.0341 17.5 12.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"></path></svg>

                                        <span class="mx-1 text-xs">12</span>
                                    </div>

                                    <span>•</span>

                                    <button class="flex items-center transition-colors duration-150 ease-in-out focus:outline-none text-gray-500 hover:text-gray-400">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M4 17.9808V9.70753C4 6.07416 4 4.25748 5.17157 3.12874C6.34315 2 8.22876 2 12 2C15.7712 2 17.6569 2 18.8284 3.12874C20 4.25748 20 6.07416 20 9.70753V17.9808C20 20.2867 20 21.4396 19.2272 21.8523C17.7305 22.6514 14.9232 19.9852 13.59 19.1824C12.8168 18.7168 12.4302 18.484 12 18.484C11.5698 18.484 11.1832 18.7168 10.41 19.1824C9.0768 19.9852 6.26947 22.6514 4.77285 21.8523C4 21.4396 4 20.2867 4 17.9808Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>

                                        <span class="mx-1 text-xs">4</span>
                                    </button>

                                    <span>•</span>

                                    <button class="flex items-center transition-colors duration-150 ease-in-out focus:outline-none text-gray-500 hover:text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-4" fill="none"><path d="M21.0477 3.05293C18.8697 0.707363 2.48648 6.4532 2.50001 8.551C2.51535 10.9299 8.89809 11.6617 10.6672 12.1581C11.7311 12.4565 12.016 12.7625 12.2613 13.8781C13.3723 18.9305 13.9301 21.4435 15.2014 21.4996C17.2278 21.5892 23.1733 5.342 21.0477 3.05293Z" stroke="currentColor" stroke-width="1.5"></path><path d="M11.5 12.5L15 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>

                                        <span class="mx-1 text-xs">1</span>
                                    </button>
                                </div>

                                <div class="flex items-center text-gray-500">
                                    <time class="cursor-help text-xs" title="Wed, 6 November 2024 16:41" datetime="2024-11-06T16:41:19+00:00">50m ago</time>
                                </div>
                            </div>
                        </li>
                        <li class="p-6 xl:p-8 cursor-pointer hover:bg-gray-800/20">
                            <div class="flex justify-between">
                                <a href="https://pinkary.com/@developermithu" class="group/profile flex items-center gap-3">
                                    <figure class="rounded-full size-10 flex-shrink-0 bg-gray-800 transition-opacity group-hover/profile:opacity-90">
                                        <img src="https://pinkary.com/storage/avatars/3d344ad52744ea44975f46f815a6299c7fc2c3e007831d4a5217069b8f04f13a.png" alt="developermithu" class="rounded-full size-10">
                                    </figure>
                                    <div class="overflow-hidden text-sm">
                                        <div class="items flex">
                                            <p class="truncate font-medium text-gray-50">Mithu Das</p>
                                        </div>

                                        <p class="truncate text-gray-500 transition-colors group-hover/profile:text-gray-400">@developermithu</p>
                                    </div>
                                </a>
                            </div>

                            <div class="text-sm mt-3 break-words text-gray-200 overflow-hidden">
                                <p>How do you deploy <a class="text-pink-500 hover:underline cursor-pointer" href="/hashtag/Laravel">#Laravel</a> applications?<br><br>A) Laravel Forge<br>B) Self-hosted solutions</p>
                            </div>

                            <div class="mt-3 flex items-center justify-between text-sm text-gray-500">
                                <div class="flex items-center gap-2">
                                    <a href="https://pinkary.com/@developermithu/questions/9d6ceade-3e53-4211-8063-a923d1bae5c1" class="flex items-center transition-colors group-hover:text-pink-500 hover:text-gray-400 focus:outline-none cursor-pointer">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M8 13.5H16M8 8.5H12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.09881 19C4.7987 18.8721 3.82475 18.4816 3.17157 17.8284C2 16.6569 2 14.7712 2 11V10.5C2 6.72876 2 4.84315 3.17157 3.67157C4.34315 2.5 6.22876 2.5 10 2.5H14C17.7712 2.5 19.6569 2.5 20.8284 3.67157C22 4.84315 22 6.72876 22 10.5V11C22 14.7712 22 16.6569 20.8284 17.8284C19.6569 19 17.7712 19 14 19C13.4395 19.0125 12.9931 19.0551 12.5546 19.155C11.3562 19.4309 10.2465 20.0441 9.14987 20.5789C7.58729 21.3408 6.806 21.7218 6.31569 21.3651C5.37769 20.6665 6.29454 18.5019 6.5 17.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg>

                                        <span class="mx-1 text-xs">2</span>
                                    </a>

                                    <span>•</span>

                                    <button class="flex items-center transition-colors hover:text-gray-400 focus:outline-none">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M19.4626 3.99415C16.7809 2.34923 14.4404 3.01211 13.0344 4.06801C12.4578 4.50096 12.1696 4.71743 12 4.71743C11.8304 4.71743 11.5422 4.50096 10.9656 4.06801C9.55962 3.01211 7.21909 2.34923 4.53744 3.99415C1.01807 6.15294 0.221721 13.2749 8.33953 19.2834C9.88572 20.4278 10.6588 21 12 21C13.3412 21 14.1143 20.4278 15.6605 19.2834C23.7783 13.2749 22.9819 6.15294 19.4626 3.99415Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg>

                                        <span class="mx-1 text-xs">21</span>
                                    </button>

                                    <span>•</span>

                                    <div class="inline-flex cursor-help items-center">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M3.5 9.5V18.5C3.5 18.9659 3.5 19.1989 3.57612 19.3827C3.67761 19.6277 3.87229 19.8224 4.11732 19.9239C4.30109 20 4.53406 20 5 20C5.46594 20 5.69891 20 5.88268 19.9239C6.12771 19.8224 6.32239 19.6277 6.42388 19.3827C6.5 19.1989 6.5 18.9659 6.5 18.5V9.5C6.5 9.03406 6.5 8.80109 6.42388 8.61732C6.32239 8.37229 6.12771 8.17761 5.88268 8.07612C5.69891 8 5.46594 8 5 8C4.53406 8 4.30109 8 4.11732 8.07612C3.87229 8.17761 3.67761 8.37229 3.57612 8.61732C3.5 8.80109 3.5 9.03406 3.5 9.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"></path><path d="M10.5 5.5V18.4995C10.5 18.9654 10.5 19.1984 10.5761 19.3822C10.6776 19.6272 10.8723 19.8219 11.1173 19.9234C11.3011 19.9995 11.5341 19.9995 12 19.9995C12.4659 19.9995 12.6989 19.9995 12.8827 19.9234C13.1277 19.8219 13.3224 19.6272 13.4239 19.3822C13.5 19.1984 13.5 18.9654 13.5 18.4995V5.5C13.5 5.03406 13.5 4.80109 13.4239 4.61732C13.3224 4.37229 13.1277 4.17761 12.8827 4.07612C12.6989 4 12.4659 4 12 4C11.5341 4 11.3011 4 11.1173 4.07612C10.8723 4.17761 10.6776 4.37229 10.5761 4.61732C10.5 4.80109 10.5 5.03406 10.5 5.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"></path><path d="M17.5 12.5V18.5C17.5 18.9659 17.5 19.1989 17.5761 19.3827C17.6776 19.6277 17.8723 19.8224 18.1173 19.9239C18.3011 20 18.5341 20 19 20C19.4659 20 19.6989 20 19.8827 19.9239C20.1277 19.8224 20.3224 19.6277 20.4239 19.3827C20.5 19.1989 20.5 18.9659 20.5 18.5V12.5C20.5 12.0341 20.5 11.8011 20.4239 11.6173C20.3224 11.3723 20.1277 11.1776 19.8827 11.0761C19.6989 11 19.4659 11 19 11C18.5341 11 18.3011 11 18.1173 11.0761C17.8723 11.1776 17.6776 11.3723 17.5761 11.6173C17.5 11.8011 17.5 12.0341 17.5 12.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"></path></svg>

                                        <span class="mx-1 text-xs">12</span>
                                    </div>

                                    <span>•</span>

                                    <button class="flex items-center transition-colors duration-150 ease-in-out focus:outline-none text-gray-500 hover:text-gray-400">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M4 17.9808V9.70753C4 6.07416 4 4.25748 5.17157 3.12874C6.34315 2 8.22876 2 12 2C15.7712 2 17.6569 2 18.8284 3.12874C20 4.25748 20 6.07416 20 9.70753V17.9808C20 20.2867 20 21.4396 19.2272 21.8523C17.7305 22.6514 14.9232 19.9852 13.59 19.1824C12.8168 18.7168 12.4302 18.484 12 18.484C11.5698 18.484 11.1832 18.7168 10.41 19.1824C9.0768 19.9852 6.26947 22.6514 4.77285 21.8523C4 21.4396 4 20.2867 4 17.9808Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>

                                        <span class="mx-1 text-xs">4</span>
                                    </button>

                                    <span>•</span>

                                    <button class="flex items-center transition-colors duration-150 ease-in-out focus:outline-none text-gray-500 hover:text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-4" fill="none"><path d="M21.0477 3.05293C18.8697 0.707363 2.48648 6.4532 2.50001 8.551C2.51535 10.9299 8.89809 11.6617 10.6672 12.1581C11.7311 12.4565 12.016 12.7625 12.2613 13.8781C13.3723 18.9305 13.9301 21.4435 15.2014 21.4996C17.2278 21.5892 23.1733 5.342 21.0477 3.05293Z" stroke="currentColor" stroke-width="1.5"></path><path d="M11.5 12.5L15 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>

                                        <span class="mx-1 text-xs">1</span>
                                    </button>
                                </div>

                                <div class="flex items-center text-gray-500">
                                    <time class="cursor-help text-xs" title="Wed, 6 November 2024 16:41" datetime="2024-11-06T16:41:19+00:00">50m ago</time>
                                </div>
                            </div>
                        </li>
                        <li class="p-6 xl:p-8 cursor-pointer hover:bg-gray-800/20">
                            <div class="flex justify-between">
                                <a href="https://pinkary.com/@developermithu" class="group/profile flex items-center gap-3">
                                    <figure class="rounded-full size-10 flex-shrink-0 bg-gray-800 transition-opacity group-hover/profile:opacity-90">
                                        <img src="https://pinkary.com/storage/avatars/3d344ad52744ea44975f46f815a6299c7fc2c3e007831d4a5217069b8f04f13a.png" alt="developermithu" class="rounded-full size-10">
                                    </figure>
                                    <div class="overflow-hidden text-sm">
                                        <div class="items flex">
                                            <p class="truncate font-medium text-gray-50">Mithu Das</p>
                                        </div>

                                        <p class="truncate text-gray-500 transition-colors group-hover/profile:text-gray-400">@developermithu</p>
                                    </div>
                                </a>
                            </div>

                            <div class="text-sm mt-3 break-words text-gray-200 overflow-hidden">
                                <p>How do you deploy <a class="text-pink-500 hover:underline cursor-pointer" href="/hashtag/Laravel">#Laravel</a> applications?<br><br>A) Laravel Forge<br>B) Self-hosted solutions</p>
                            </div>

                            <div class="mt-3 flex items-center justify-between text-sm text-gray-500">
                                <div class="flex items-center gap-2">
                                    <a href="https://pinkary.com/@developermithu/questions/9d6ceade-3e53-4211-8063-a923d1bae5c1" class="flex items-center transition-colors group-hover:text-pink-500 hover:text-gray-400 focus:outline-none cursor-pointer">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M8 13.5H16M8 8.5H12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.09881 19C4.7987 18.8721 3.82475 18.4816 3.17157 17.8284C2 16.6569 2 14.7712 2 11V10.5C2 6.72876 2 4.84315 3.17157 3.67157C4.34315 2.5 6.22876 2.5 10 2.5H14C17.7712 2.5 19.6569 2.5 20.8284 3.67157C22 4.84315 22 6.72876 22 10.5V11C22 14.7712 22 16.6569 20.8284 17.8284C19.6569 19 17.7712 19 14 19C13.4395 19.0125 12.9931 19.0551 12.5546 19.155C11.3562 19.4309 10.2465 20.0441 9.14987 20.5789C7.58729 21.3408 6.806 21.7218 6.31569 21.3651C5.37769 20.6665 6.29454 18.5019 6.5 17.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg>

                                        <span class="mx-1 text-xs">2</span>
                                    </a>

                                    <span>•</span>

                                    <button class="flex items-center transition-colors hover:text-gray-400 focus:outline-none">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M19.4626 3.99415C16.7809 2.34923 14.4404 3.01211 13.0344 4.06801C12.4578 4.50096 12.1696 4.71743 12 4.71743C11.8304 4.71743 11.5422 4.50096 10.9656 4.06801C9.55962 3.01211 7.21909 2.34923 4.53744 3.99415C1.01807 6.15294 0.221721 13.2749 8.33953 19.2834C9.88572 20.4278 10.6588 21 12 21C13.3412 21 14.1143 20.4278 15.6605 19.2834C23.7783 13.2749 22.9819 6.15294 19.4626 3.99415Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg>

                                        <span class="mx-1 text-xs">21</span>
                                    </button>

                                    <span>•</span>

                                    <div class="inline-flex cursor-help items-center">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M3.5 9.5V18.5C3.5 18.9659 3.5 19.1989 3.57612 19.3827C3.67761 19.6277 3.87229 19.8224 4.11732 19.9239C4.30109 20 4.53406 20 5 20C5.46594 20 5.69891 20 5.88268 19.9239C6.12771 19.8224 6.32239 19.6277 6.42388 19.3827C6.5 19.1989 6.5 18.9659 6.5 18.5V9.5C6.5 9.03406 6.5 8.80109 6.42388 8.61732C6.32239 8.37229 6.12771 8.17761 5.88268 8.07612C5.69891 8 5.46594 8 5 8C4.53406 8 4.30109 8 4.11732 8.07612C3.87229 8.17761 3.67761 8.37229 3.57612 8.61732C3.5 8.80109 3.5 9.03406 3.5 9.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"></path><path d="M10.5 5.5V18.4995C10.5 18.9654 10.5 19.1984 10.5761 19.3822C10.6776 19.6272 10.8723 19.8219 11.1173 19.9234C11.3011 19.9995 11.5341 19.9995 12 19.9995C12.4659 19.9995 12.6989 19.9995 12.8827 19.9234C13.1277 19.8219 13.3224 19.6272 13.4239 19.3822C13.5 19.1984 13.5 18.9654 13.5 18.4995V5.5C13.5 5.03406 13.5 4.80109 13.4239 4.61732C13.3224 4.37229 13.1277 4.17761 12.8827 4.07612C12.6989 4 12.4659 4 12 4C11.5341 4 11.3011 4 11.1173 4.07612C10.8723 4.17761 10.6776 4.37229 10.5761 4.61732C10.5 4.80109 10.5 5.03406 10.5 5.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"></path><path d="M17.5 12.5V18.5C17.5 18.9659 17.5 19.1989 17.5761 19.3827C17.6776 19.6277 17.8723 19.8224 18.1173 19.9239C18.3011 20 18.5341 20 19 20C19.4659 20 19.6989 20 19.8827 19.9239C20.1277 19.8224 20.3224 19.6277 20.4239 19.3827C20.5 19.1989 20.5 18.9659 20.5 18.5V12.5C20.5 12.0341 20.5 11.8011 20.4239 11.6173C20.3224 11.3723 20.1277 11.1776 19.8827 11.0761C19.6989 11 19.4659 11 19 11C18.5341 11 18.3011 11 18.1173 11.0761C17.8723 11.1776 17.6776 11.3723 17.5761 11.6173C17.5 11.8011 17.5 12.0341 17.5 12.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"></path></svg>

                                        <span class="mx-1 text-xs">12</span>
                                    </div>

                                    <span>•</span>

                                    <button class="flex items-center transition-colors duration-150 ease-in-out focus:outline-none text-gray-500 hover:text-gray-400">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M4 17.9808V9.70753C4 6.07416 4 4.25748 5.17157 3.12874C6.34315 2 8.22876 2 12 2C15.7712 2 17.6569 2 18.8284 3.12874C20 4.25748 20 6.07416 20 9.70753V17.9808C20 20.2867 20 21.4396 19.2272 21.8523C17.7305 22.6514 14.9232 19.9852 13.59 19.1824C12.8168 18.7168 12.4302 18.484 12 18.484C11.5698 18.484 11.1832 18.7168 10.41 19.1824C9.0768 19.9852 6.26947 22.6514 4.77285 21.8523C4 21.4396 4 20.2867 4 17.9808Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>

                                        <span class="mx-1 text-xs">4</span>
                                    </button>

                                    <span>•</span>

                                    <button class="flex items-center transition-colors duration-150 ease-in-out focus:outline-none text-gray-500 hover:text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-4" fill="none"><path d="M21.0477 3.05293C18.8697 0.707363 2.48648 6.4532 2.50001 8.551C2.51535 10.9299 8.89809 11.6617 10.6672 12.1581C11.7311 12.4565 12.016 12.7625 12.2613 13.8781C13.3723 18.9305 13.9301 21.4435 15.2014 21.4996C17.2278 21.5892 23.1733 5.342 21.0477 3.05293Z" stroke="currentColor" stroke-width="1.5"></path><path d="M11.5 12.5L15 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>

                                        <span class="mx-1 text-xs">1</span>
                                    </button>
                                </div>

                                <div class="flex items-center text-gray-500">
                                    <time class="cursor-help text-xs" title="Wed, 6 November 2024 16:41" datetime="2024-11-06T16:41:19+00:00">50m ago</time>
                                </div>
                            </div>
                        </li>
                        <li class="p-6 xl:p-8 cursor-pointer hover:bg-gray-800/20">
                            <div class="flex justify-between">
                                <a href="https://pinkary.com/@developermithu" class="group/profile flex items-center gap-3">
                                    <figure class="rounded-full size-10 flex-shrink-0 bg-gray-800 transition-opacity group-hover/profile:opacity-90">
                                        <img src="https://pinkary.com/storage/avatars/3d344ad52744ea44975f46f815a6299c7fc2c3e007831d4a5217069b8f04f13a.png" alt="developermithu" class="rounded-full size-10">
                                    </figure>
                                    <div class="overflow-hidden text-sm">
                                        <div class="items flex">
                                            <p class="truncate font-medium text-gray-50">Mithu Das</p>
                                        </div>

                                        <p class="truncate text-gray-500 transition-colors group-hover/profile:text-gray-400">@developermithu</p>
                                    </div>
                                </a>
                            </div>

                            <div class="text-sm mt-3 break-words text-gray-200 overflow-hidden">
                                <p>How do you deploy <a class="text-pink-500 hover:underline cursor-pointer" href="/hashtag/Laravel">#Laravel</a> applications?<br><br>A) Laravel Forge<br>B) Self-hosted solutions</p>
                            </div>

                            <div class="mt-3 flex items-center justify-between text-sm text-gray-500">
                                <div class="flex items-center gap-2">
                                    <a href="https://pinkary.com/@developermithu/questions/9d6ceade-3e53-4211-8063-a923d1bae5c1" class="flex items-center transition-colors group-hover:text-pink-500 hover:text-gray-400 focus:outline-none cursor-pointer">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M8 13.5H16M8 8.5H12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.09881 19C4.7987 18.8721 3.82475 18.4816 3.17157 17.8284C2 16.6569 2 14.7712 2 11V10.5C2 6.72876 2 4.84315 3.17157 3.67157C4.34315 2.5 6.22876 2.5 10 2.5H14C17.7712 2.5 19.6569 2.5 20.8284 3.67157C22 4.84315 22 6.72876 22 10.5V11C22 14.7712 22 16.6569 20.8284 17.8284C19.6569 19 17.7712 19 14 19C13.4395 19.0125 12.9931 19.0551 12.5546 19.155C11.3562 19.4309 10.2465 20.0441 9.14987 20.5789C7.58729 21.3408 6.806 21.7218 6.31569 21.3651C5.37769 20.6665 6.29454 18.5019 6.5 17.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg>

                                        <span class="mx-1 text-xs">2</span>
                                    </a>

                                    <span>•</span>

                                    <button class="flex items-center transition-colors hover:text-gray-400 focus:outline-none">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M19.4626 3.99415C16.7809 2.34923 14.4404 3.01211 13.0344 4.06801C12.4578 4.50096 12.1696 4.71743 12 4.71743C11.8304 4.71743 11.5422 4.50096 10.9656 4.06801C9.55962 3.01211 7.21909 2.34923 4.53744 3.99415C1.01807 6.15294 0.221721 13.2749 8.33953 19.2834C9.88572 20.4278 10.6588 21 12 21C13.3412 21 14.1143 20.4278 15.6605 19.2834C23.7783 13.2749 22.9819 6.15294 19.4626 3.99415Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg>

                                        <span class="mx-1 text-xs">21</span>
                                    </button>

                                    <span>•</span>

                                    <div class="inline-flex cursor-help items-center">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M3.5 9.5V18.5C3.5 18.9659 3.5 19.1989 3.57612 19.3827C3.67761 19.6277 3.87229 19.8224 4.11732 19.9239C4.30109 20 4.53406 20 5 20C5.46594 20 5.69891 20 5.88268 19.9239C6.12771 19.8224 6.32239 19.6277 6.42388 19.3827C6.5 19.1989 6.5 18.9659 6.5 18.5V9.5C6.5 9.03406 6.5 8.80109 6.42388 8.61732C6.32239 8.37229 6.12771 8.17761 5.88268 8.07612C5.69891 8 5.46594 8 5 8C4.53406 8 4.30109 8 4.11732 8.07612C3.87229 8.17761 3.67761 8.37229 3.57612 8.61732C3.5 8.80109 3.5 9.03406 3.5 9.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"></path><path d="M10.5 5.5V18.4995C10.5 18.9654 10.5 19.1984 10.5761 19.3822C10.6776 19.6272 10.8723 19.8219 11.1173 19.9234C11.3011 19.9995 11.5341 19.9995 12 19.9995C12.4659 19.9995 12.6989 19.9995 12.8827 19.9234C13.1277 19.8219 13.3224 19.6272 13.4239 19.3822C13.5 19.1984 13.5 18.9654 13.5 18.4995V5.5C13.5 5.03406 13.5 4.80109 13.4239 4.61732C13.3224 4.37229 13.1277 4.17761 12.8827 4.07612C12.6989 4 12.4659 4 12 4C11.5341 4 11.3011 4 11.1173 4.07612C10.8723 4.17761 10.6776 4.37229 10.5761 4.61732C10.5 4.80109 10.5 5.03406 10.5 5.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"></path><path d="M17.5 12.5V18.5C17.5 18.9659 17.5 19.1989 17.5761 19.3827C17.6776 19.6277 17.8723 19.8224 18.1173 19.9239C18.3011 20 18.5341 20 19 20C19.4659 20 19.6989 20 19.8827 19.9239C20.1277 19.8224 20.3224 19.6277 20.4239 19.3827C20.5 19.1989 20.5 18.9659 20.5 18.5V12.5C20.5 12.0341 20.5 11.8011 20.4239 11.6173C20.3224 11.3723 20.1277 11.1776 19.8827 11.0761C19.6989 11 19.4659 11 19 11C18.5341 11 18.3011 11 18.1173 11.0761C17.8723 11.1776 17.6776 11.3723 17.5761 11.6173C17.5 11.8011 17.5 12.0341 17.5 12.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"></path></svg>

                                        <span class="mx-1 text-xs">12</span>
                                    </div>

                                    <span>•</span>

                                    <button class="flex items-center transition-colors duration-150 ease-in-out focus:outline-none text-gray-500 hover:text-gray-400">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M4 17.9808V9.70753C4 6.07416 4 4.25748 5.17157 3.12874C6.34315 2 8.22876 2 12 2C15.7712 2 17.6569 2 18.8284 3.12874C20 4.25748 20 6.07416 20 9.70753V17.9808C20 20.2867 20 21.4396 19.2272 21.8523C17.7305 22.6514 14.9232 19.9852 13.59 19.1824C12.8168 18.7168 12.4302 18.484 12 18.484C11.5698 18.484 11.1832 18.7168 10.41 19.1824C9.0768 19.9852 6.26947 22.6514 4.77285 21.8523C4 21.4396 4 20.2867 4 17.9808Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>

                                        <span class="mx-1 text-xs">4</span>
                                    </button>

                                    <span>•</span>

                                    <button class="flex items-center transition-colors duration-150 ease-in-out focus:outline-none text-gray-500 hover:text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-4" fill="none"><path d="M21.0477 3.05293C18.8697 0.707363 2.48648 6.4532 2.50001 8.551C2.51535 10.9299 8.89809 11.6617 10.6672 12.1581C11.7311 12.4565 12.016 12.7625 12.2613 13.8781C13.3723 18.9305 13.9301 21.4435 15.2014 21.4996C17.2278 21.5892 23.1733 5.342 21.0477 3.05293Z" stroke="currentColor" stroke-width="1.5"></path><path d="M11.5 12.5L15 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>

                                        <span class="mx-1 text-xs">1</span>
                                    </button>
                                </div>

                                <div class="flex items-center text-gray-500">
                                    <time class="cursor-help text-xs" title="Wed, 6 November 2024 16:41" datetime="2024-11-06T16:41:19+00:00">50m ago</time>
                                </div>
                            </div>
                        </li>
                        <li class="p-6 xl:p-8 cursor-pointer hover:bg-gray-800/20">
                            <div class="flex justify-between">
                                <a href="https://pinkary.com/@developermithu" class="group/profile flex items-center gap-3">
                                    <figure class="rounded-full size-10 flex-shrink-0 bg-gray-800 transition-opacity group-hover/profile:opacity-90">
                                        <img src="https://pinkary.com/storage/avatars/3d344ad52744ea44975f46f815a6299c7fc2c3e007831d4a5217069b8f04f13a.png" alt="developermithu" class="rounded-full size-10">
                                    </figure>
                                    <div class="overflow-hidden text-sm">
                                        <div class="items flex">
                                            <p class="truncate font-medium text-gray-50">Mithu Das</p>
                                        </div>

                                        <p class="truncate text-gray-500 transition-colors group-hover/profile:text-gray-400">@developermithu</p>
                                    </div>
                                </a>
                            </div>

                            <div class="text-sm mt-3 break-words text-gray-200 overflow-hidden">
                                <p>How do you deploy <a class="text-pink-500 hover:underline cursor-pointer" href="/hashtag/Laravel">#Laravel</a> applications?<br><br>A) Laravel Forge<br>B) Self-hosted solutions</p>
                            </div>

                            <div class="mt-3 flex items-center justify-between text-sm text-gray-500">
                                <div class="flex items-center gap-2">
                                    <a href="https://pinkary.com/@developermithu/questions/9d6ceade-3e53-4211-8063-a923d1bae5c1" class="flex items-center transition-colors group-hover:text-pink-500 hover:text-gray-400 focus:outline-none cursor-pointer">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M8 13.5H16M8 8.5H12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.09881 19C4.7987 18.8721 3.82475 18.4816 3.17157 17.8284C2 16.6569 2 14.7712 2 11V10.5C2 6.72876 2 4.84315 3.17157 3.67157C4.34315 2.5 6.22876 2.5 10 2.5H14C17.7712 2.5 19.6569 2.5 20.8284 3.67157C22 4.84315 22 6.72876 22 10.5V11C22 14.7712 22 16.6569 20.8284 17.8284C19.6569 19 17.7712 19 14 19C13.4395 19.0125 12.9931 19.0551 12.5546 19.155C11.3562 19.4309 10.2465 20.0441 9.14987 20.5789C7.58729 21.3408 6.806 21.7218 6.31569 21.3651C5.37769 20.6665 6.29454 18.5019 6.5 17.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg>

                                        <span class="mx-1 text-xs">2</span>
                                    </a>

                                    <span>•</span>

                                    <button class="flex items-center transition-colors hover:text-gray-400 focus:outline-none">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M19.4626 3.99415C16.7809 2.34923 14.4404 3.01211 13.0344 4.06801C12.4578 4.50096 12.1696 4.71743 12 4.71743C11.8304 4.71743 11.5422 4.50096 10.9656 4.06801C9.55962 3.01211 7.21909 2.34923 4.53744 3.99415C1.01807 6.15294 0.221721 13.2749 8.33953 19.2834C9.88572 20.4278 10.6588 21 12 21C13.3412 21 14.1143 20.4278 15.6605 19.2834C23.7783 13.2749 22.9819 6.15294 19.4626 3.99415Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg>

                                        <span class="mx-1 text-xs">21</span>
                                    </button>

                                    <span>•</span>

                                    <div class="inline-flex cursor-help items-center">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M3.5 9.5V18.5C3.5 18.9659 3.5 19.1989 3.57612 19.3827C3.67761 19.6277 3.87229 19.8224 4.11732 19.9239C4.30109 20 4.53406 20 5 20C5.46594 20 5.69891 20 5.88268 19.9239C6.12771 19.8224 6.32239 19.6277 6.42388 19.3827C6.5 19.1989 6.5 18.9659 6.5 18.5V9.5C6.5 9.03406 6.5 8.80109 6.42388 8.61732C6.32239 8.37229 6.12771 8.17761 5.88268 8.07612C5.69891 8 5.46594 8 5 8C4.53406 8 4.30109 8 4.11732 8.07612C3.87229 8.17761 3.67761 8.37229 3.57612 8.61732C3.5 8.80109 3.5 9.03406 3.5 9.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"></path><path d="M10.5 5.5V18.4995C10.5 18.9654 10.5 19.1984 10.5761 19.3822C10.6776 19.6272 10.8723 19.8219 11.1173 19.9234C11.3011 19.9995 11.5341 19.9995 12 19.9995C12.4659 19.9995 12.6989 19.9995 12.8827 19.9234C13.1277 19.8219 13.3224 19.6272 13.4239 19.3822C13.5 19.1984 13.5 18.9654 13.5 18.4995V5.5C13.5 5.03406 13.5 4.80109 13.4239 4.61732C13.3224 4.37229 13.1277 4.17761 12.8827 4.07612C12.6989 4 12.4659 4 12 4C11.5341 4 11.3011 4 11.1173 4.07612C10.8723 4.17761 10.6776 4.37229 10.5761 4.61732C10.5 4.80109 10.5 5.03406 10.5 5.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"></path><path d="M17.5 12.5V18.5C17.5 18.9659 17.5 19.1989 17.5761 19.3827C17.6776 19.6277 17.8723 19.8224 18.1173 19.9239C18.3011 20 18.5341 20 19 20C19.4659 20 19.6989 20 19.8827 19.9239C20.1277 19.8224 20.3224 19.6277 20.4239 19.3827C20.5 19.1989 20.5 18.9659 20.5 18.5V12.5C20.5 12.0341 20.5 11.8011 20.4239 11.6173C20.3224 11.3723 20.1277 11.1776 19.8827 11.0761C19.6989 11 19.4659 11 19 11C18.5341 11 18.3011 11 18.1173 11.0761C17.8723 11.1776 17.6776 11.3723 17.5761 11.6173C17.5 11.8011 17.5 12.0341 17.5 12.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"></path></svg>

                                        <span class="mx-1 text-xs">12</span>
                                    </div>

                                    <span>•</span>

                                    <button class="flex items-center transition-colors duration-150 ease-in-out focus:outline-none text-gray-500 hover:text-gray-400">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M4 17.9808V9.70753C4 6.07416 4 4.25748 5.17157 3.12874C6.34315 2 8.22876 2 12 2C15.7712 2 17.6569 2 18.8284 3.12874C20 4.25748 20 6.07416 20 9.70753V17.9808C20 20.2867 20 21.4396 19.2272 21.8523C17.7305 22.6514 14.9232 19.9852 13.59 19.1824C12.8168 18.7168 12.4302 18.484 12 18.484C11.5698 18.484 11.1832 18.7168 10.41 19.1824C9.0768 19.9852 6.26947 22.6514 4.77285 21.8523C4 21.4396 4 20.2867 4 17.9808Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>

                                        <span class="mx-1 text-xs">4</span>
                                    </button>

                                    <span>•</span>

                                    <button class="flex items-center transition-colors duration-150 ease-in-out focus:outline-none text-gray-500 hover:text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-4" fill="none"><path d="M21.0477 3.05293C18.8697 0.707363 2.48648 6.4532 2.50001 8.551C2.51535 10.9299 8.89809 11.6617 10.6672 12.1581C11.7311 12.4565 12.016 12.7625 12.2613 13.8781C13.3723 18.9305 13.9301 21.4435 15.2014 21.4996C17.2278 21.5892 23.1733 5.342 21.0477 3.05293Z" stroke="currentColor" stroke-width="1.5"></path><path d="M11.5 12.5L15 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>

                                        <span class="mx-1 text-xs">1</span>
                                    </button>
                                </div>

                                <div class="flex items-center text-gray-500">
                                    <time class="cursor-help text-xs" title="Wed, 6 November 2024 16:41" datetime="2024-11-06T16:41:19+00:00">50m ago</time>
                                </div>
                            </div>
                        </li>
                    </ul>
                </main>

                <aside class="bg-black/10 xl:flex-1 border-t xl:border-t-0 xl:border-x border-white/5">
                    <div class="xl:sticky xl:bottom-0 xl:right-0 xl:top-16">
                        <div class="flex flex-col text-sm text-gray-400 border-b border-white/5 p-6 xl:p-8">
                            <span class="text-[10px] mb-1">PUB</span>
                            <img src="https://forge.laravel.com/social-share.png" class="rounded-md">
                            <p class="mt-2">Server management doesn't have to be a nightmare.</p>
                        </div>

                        <div class="flex items-center justify-between border-b border-white/5 p-6 xl:p-8">
                            <h2 class="text-sm font-medium text-white">Recent <span class="opacity-50">signups</span></h2>
                            <a href="" class="text-xs font-medium text-pink-500">View all</a>
                        </div>

                        <ul role="list" class="divide-y divide-white/5">
                            <li class="px-6 py-4 hover:bg-gray-800/20 cursor-pointer">
                                <div class="flex items-center gap-x-3">
                                    <img src="https://pinkary.com/storage/avatars/120f8d175fd0146ca0541625b8bd6c742e838632951a7e58dc7fbdc8c2170c4f.png" alt="" class="h-6 w-6 flex-none rounded-full bg-gray-800">
                                    <h3 class="flex-auto truncate text-sm text-white">Nuno Maduro</h3>
                                    <time datetime="2023-01-23T11:00" class="flex-none text-xs text-gray-600">1h</time>
                                </div>
                            </li>
                            <li class="px-6 py-4 hover:bg-gray-800/20 cursor-pointer">
                                <div class="flex items-center gap-x-3">
                                    <img src="https://pinkary.com/storage/avatars/d6518d7d630379e204df6891b25617f1495cfecf11557526d3ee054d5db33704.png" alt="" class="h-6 w-6 flex-none rounded-full bg-gray-800">
                                    <h3 class="flex-auto truncate text-sm text-white">Cam Kemshal-Bell</h3>
                                    <time datetime="2023-01-23T11:00" class="flex-none text-xs text-gray-600">2h</time>
                                </div>
                            </li>
                            <li class="px-6 py-4 hover:bg-gray-800/20 cursor-pointer">
                                <div class="flex items-center gap-x-3">
                                    <img src="https://pinkary.com/storage/avatars/0c64f67f3f182570b8cf1028e5a72fa0ac83b2c295c6bf06e8ea930a89026f15.png" alt="" class="h-6 w-6 flex-none rounded-full bg-gray-800">
                                    <h3 class="flex-auto truncate text-sm text-white">Punyapal Shah</h3>
                                    <time datetime="2023-01-23T11:00" class="flex-none text-xs text-gray-600">2h</time>
                                </div>
                            </li>
                            <li class="px-6 py-4 hover:bg-gray-800/20 cursor-pointer">
                                <div class="flex items-center gap-x-3">
                                    <img src="https://pinkary.com/storage/avatars/54b5512e2676bf1900aafe92a707d8941773128bbec6a05ac6e2d3adf3160bf1.png" alt="" class="h-6 w-6 flex-none rounded-full bg-gray-800">
                                    <h3 class="flex-auto truncate text-sm text-white">Joel Clermont</h3>
                                    <time datetime="2023-01-23T11:00" class="flex-none text-xs text-gray-600">3h</time>
                                </div>
                            </li>
                            <li class="px-6 py-4 hover:bg-gray-800/20 cursor-pointer">
                                <div class="flex items-center gap-x-3">
                                    <img src="https://pinkary.com/storage/avatars/bec85860bae95878772479fb9febce97b67444f7aa3dcbf0129958d43886f5ac.png" alt="" class="h-6 w-6 flex-none rounded-full bg-gray-800">
                                    <h3 class="flex-auto truncate text-sm text-white">Nuno Guerra</h3>
                                    <time datetime="2023-01-23T11:00" class="flex-none text-xs text-gray-600">4h</time>
                                </div>
                            </li>
                        </ul>

                        <div class="relative flex flex-col p-6 xl:py-8 border-t border-white/5">
                            <span class="text-sm text-gray-200 font-medium">Pinkary</span>
                            <span class="text-xs text-gray-500">One link. All your socials.</span>

                            <ul class="flex flex-wrap gap-x-4 mt-3 text-xs">
                                <li class="flex"><a class="text-gray-500 hover:text-gray-200 transition" href="/about">About</a></li>
                                <li class="flex"><a class="text-gray-500 hover:text-gray-200 transition" href="/advertise">Advertise</a></li>
                                <li class="flex"><a class="text-gray-500 hover:text-gray-200 transition" href="/legal/terms-and-conditions">Terms</a></li>
                                <li class="flex"><a class="text-gray-500 hover:text-gray-200 transition" href="/legal/privacy-policy">Privacy</a></li>
                            </ul>

                            <div class="flex gap-3 mt-4">
                                <a href="" target="_blank" rel="noreferrer noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-5 text-gray-500 hover:text-gray-200 transition" fill="none"><path d="M7 17L11.1935 12.8065M17 7L12.8065 11.1935M12.8065 11.1935L9.77778 7H7L11.1935 12.8065M12.8065 11.1935L17 17H14.2222L11.1935 12.8065" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="1.5"></path></svg>
                                </a>

                                <a href="" target="_blank" rel="noreferrer noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-5 text-gray-500 hover:text-gray-200 transition" fill="none"><path d="M6.51734 17.1132C6.91177 17.6905 8.10883 18.9228 9.74168 19.2333M9.86428 22C8.83582 21.8306 2 19.6057 2 12.0926C2 5.06329 8.0019 2 12.0008 2C15.9996 2 22 5.06329 22 12.0926C22 19.6057 15.1642 21.8306 14.1357 22C14.1357 22 13.9267 18.5826 14.0487 17.9969C14.1706 17.4113 13.7552 16.4688 13.7552 16.4688C14.7262 16.1055 16.2043 15.5847 16.7001 14.1874C17.0848 13.1032 17.3268 11.5288 16.2508 10.0489C16.2508 10.0489 16.5318 7.65809 15.9996 7.56548C15.4675 7.47287 13.8998 8.51192 13.8998 8.51192C13.4432 8.38248 12.4243 8.13476 12.0018 8.17939C11.5792 8.13476 10.5568 8.38248 10.1002 8.51192C10.1002 8.51192 8.53249 7.47287 8.00036 7.56548C7.46823 7.65809 7.74917 10.0489 7.74917 10.0489C6.67316 11.5288 6.91516 13.1032 7.2999 14.1874C7.79575 15.5847 9.27384 16.1055 10.2448 16.4688C10.2448 16.4688 9.82944 17.4113 9.95135 17.9969C10.0733 18.5826 9.86428 22 9.86428 22Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</x-main-layout>
