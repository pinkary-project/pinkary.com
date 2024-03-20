<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 border text-gray-500 border-gray-500 rounded-sm font-semibold text-xs tracking-widest hover:bg-gray-800 focus:outline-none focus:ring-0 focus:ring-offset-0 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
