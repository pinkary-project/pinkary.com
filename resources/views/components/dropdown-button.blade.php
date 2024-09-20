<button {{ $attributes->merge(['class' => 'block w-full px-4 py-2 text-start text-sm leading-5 dark:text-slate-400 text-slate-600 dark:hover:bg-slate-800 hover:bg-slate-200 transition duration-150 ease-in-out']) }}>
    {{ $slot }}
</button>
