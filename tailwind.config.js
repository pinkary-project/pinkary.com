import defaultTheme from "tailwindcss/defaultTheme";

/** @type {import('tailwindcss').Config} */
export default {
    plugins: [require('@tailwindcss/typography'), require('@tailwindcss/forms')],

    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./app/**/*.blade.php",
        "./resources/js/**/*.js",
    ],

    darkMode: 'selector',

    theme: {
        extend: {
            fontFamily: {
                mona: ["Mona Sans", ...defaultTheme.fontFamily.sans],
            },
            screens: {
                xsm: '467px',
            },
            maxWidth: {
               'md': '30rem',
            },
        },
    },

    safelist: [
        'from-blue-500',
        'to-purple-600',
        'to-teal-700',
        'from-red-500',
        'to-orange-600',
        'from-purple-500',
        'to-pink-500',
        'from-indigo-500',
        'to-lime-700',
        'from-yellow-600',
        'to-blue-600',

        'border-blue-500',
        'border-purple-600',
        'border-teal-700',
        'border-red-500',
        'border-orange-600',
        'border-purple-500',
        'border-pink-500',
        'border-indigo-500',
        'border-lime-700',
        'border-yellow-600',
        'border-blue-600',

        'ring-blue-500',
        'ring-purple-600',
        'ring-teal-700',
        'ring-red-500',
        'ring-orange-600',
        'ring-purple-500',
        'ring-pink-500',
        'ring-indigo-500',
        'ring-lime-700',
        'ring-yellow-600',
        'ring-blue-600',

        'bg-blue-500',
        'bg-purple-600',
        'bg-teal-700',
        'bg-red-500',
        'bg-orange-600',
        'bg-purple-500',
        'bg-pink-500',
        'bg-indigo-500',
        'bg-lime-700',
        'bg-yellow-600',
        'bg-blue-600',

        'text-blue-500',
        'text-purple-600',
        'text-teal-700',
        'text-red-500',
        'text-orange-600',
        'text-purple-500',
        'text-pink-500',
        'text-indigo-500',
        'text-lime-700',
        'text-yellow-600',
        'text-blue-600',
    ]
};
