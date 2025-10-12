import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                primary: { DEFAULT: '#3B82F6', 500: '#3B82F6', 600: '#2563EB' },
                secondary: { DEFAULT: '#9333EA', 500: '#9333EA', 600: '#7E22CE' },
                accent: { DEFAULT: '#F59E0B', 500: '#F59E0B', 600: '#D97706' },
                success: { DEFAULT: '#10B981', 500: '#10B981', 600: '#059669' },
                danger: { DEFAULT: '#EF4444', 500: '#EF4444', 600: '#DC2626' },
                gray: { 100: '#F3F4F6', 900: '#111827' },
            },
            spacing: { 1: '4px', 2: '8px', 3: '12px', 4: '16px' },
            fontSize: {
                base: ['1rem', { lineHeight: '1.75rem' }],
                sm: ['0.875rem', { lineHeight: '1.5rem' }],
                lg: ['1.125rem', { lineHeight: '1.75rem' }],
                xl: ['1.25rem', { lineHeight: '1.75rem' }],
                '2xl': ['1.5rem', { lineHeight: '2rem' }],
                '3xl': ['1.875rem', { lineHeight: '2.25rem' }],
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};

