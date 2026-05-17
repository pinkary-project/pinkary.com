<button {{ $attributes->merge(['class' => 'block w-full rounded-xl px-4 py-2.5 text-start text-sm leading-5 text-slate-600 transition duration-150 ease-in-out hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-[#11192b] dark:hover:text-white']) }}>
    {{ $slot }}
</button>
