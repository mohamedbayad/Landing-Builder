import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import tailwindCardsPlugin from './tailwind-cards-plugin';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
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
                    orange: '#F97316',
                    'orange-50': '#FFF7ED',
                    'orange-100': '#FFEDD5',
                    'orange-200': '#FED7AA',
                    'orange-300': '#FDBA74',
                    'orange-400': '#FB923C',
                    'orange-500': '#F97316',
                    'orange-600': '#EA6500',
                    'orange-700': '#C2510A',
                    dark: '#0D1117',
                    'dark-50': '#161B22',
                    'dark-100': '#13191F',
                    'dark-200': '#0D1117',
                    light: '#FFF7ED',
                    surface: '#1C2432',
                },
            },

            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },

            boxShadow: {
                'sidebar': '1px 0 0 0 rgba(255,255,255,0.05)',
                'card': '0 1px 2px rgba(0,0,0,0.05)',
                'card-hover': '0 4px 16px rgba(0,0,0,0.08), 0 1px 3px rgba(0,0,0,0.05)',
                'stat': '0 2px 8px rgba(0,0,0,0.06)',
                'input': '0 1px 2px rgba(0,0,0,0.04)',
                'dropdown': '0 8px 24px rgba(0,0,0,0.12), 0 2px 6px rgba(0,0,0,0.08)',
            },

            borderRadius: {
                'xl': '0.75rem',
                '2xl': '1rem',
            },

            backgroundImage: {
                'hero-pattern': "linear-gradient(to bottom, rgba(0,0,0,0.8), rgba(13,17,23,1)), url('https://images.unsplash.com/photo-1552662977-eb44919c5305?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80')",
                'auth-gradient': 'linear-gradient(135deg, #0D1117 0%, #1C2432 50%, #0D1117 100%)',
            },

            animation: {
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                'fade-in': 'fadeIn 0.15s ease-out',
            },

            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0', transform: 'translateY(4px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
            },
        },
    },

    plugins: [forms, tailwindCardsPlugin],
};
