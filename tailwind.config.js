import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                brand: {
                    gold: '#D9A72F',
                    dark: '#07112F',
                    navy: '#0D1C4C',
                    blue: '#17245D',
                    gray: '#F5F7FB',
                    soft: '#F5F7FB',
                    light: '#FFFFFF',
                },
            },
            fontFamily: {
                serif: ['"Cormorant Garamond"', ...defaultTheme.fontFamily.serif],
                sans: ['"Montserrat"', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms, typography],
};
