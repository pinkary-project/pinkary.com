<x-app-layout>
    <div class="relative max-h-screen selection:bg-red-500 selection:text-white sm:flex sm:items-center sm:justify-center">
        <div class="m-auto w-full px-6 py-6 sm:w-3/4 xl:w-1/2">
            <div class="flex items-center bg-white from-slate-700/50 via-transparent px-6 py-4 shadow-2xl shadow-slate-500/20 focus:outline focus:outline-2 focus:outline-red-500">
                <div class="relative flex h-3 w-3">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex h-3 w-3 rounded-full bg-green-400"></span>
                </div>

                <div class="ml-6">
                    <h2 class="text-xl font-semibold text-slate-900">All Systems Operational</h2>

                    <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">All systems are operational. No outages to report.</p>
                </div>
            </div>
        </div>
    </div>
    <div class="relative m-auto overflow-x-auto rounded-lg p-6 shadow-md sm:w-3/4 sm:rounded-lg xl:w-1/2">
        <table class="w-full text-left text-sm text-slate-500 dark:text-slate-400">
            <thead class="bg-slate-50 text-xs uppercase text-slate-700 dark:bg-slate-700 dark:text-slate-400">
                <tr>
                    <th scope="col" class="px-6 py-3">Past Incidents</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</x-app-layout>
