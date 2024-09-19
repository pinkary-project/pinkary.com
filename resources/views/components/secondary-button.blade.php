<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 border text-slate-500 border-slate-500 rounded-lg font-semibold text-xs tracking-widest dark:hover:bg-slate-800/40 hover:bg-slate-300/40 focus:outline-none focus:ring-0 focus:ring-offset-0 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
