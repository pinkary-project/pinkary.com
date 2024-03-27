<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 border rounded-lg font-semibold text-xs tracking-widest hover:bg-gray-900 focus:outline-none focus:ring-0 focus:ring-offset-0 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
