<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 border text-pink-500 border-pink-500 rounded-sm font-semibold text-xs tracking-widest hover:bg-gray-800 focus:outline-none focus:ring-0 focus:ring-offset-0 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
