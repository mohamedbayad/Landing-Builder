import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class', // Enable class-based dark mode
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './public/storage/templates/**/**/*.html',
    ],

    theme: {
        extend: {
            
            colors: {
                brand: {
                    orange: '#FF7A00',
                    dark: '#0B1120',
                    light: '#FFF5EB',
                    surface: '#1E293B'
                }
            },
            backgroundImage: {
                'hero-pattern': "linear-gradient(to bottom, rgba(0,0,0,0.8), rgba(11,17,32,1)), url('https://images.unsplash.com/photo-1552662977-eb44919c5305?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80')",
            },
            animation: {
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                'float': 'float 6s ease-in-out infinite',
            },
            keyframes: {
                float: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-10px)' },
                }
            }
        },
    },

    plugins: [forms],
};
