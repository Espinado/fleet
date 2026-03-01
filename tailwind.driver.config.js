import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/views/driver-app/**/*.blade.php',
    './resources/views/livewire/driver-app/**/*.blade.php',
    './resources/views/layouts/driver*.blade.php',
    './storage/framework/views/*.php',
  ],
  theme: {
    // ✅ сдвигаем брейкпоинты вверх, чтобы “большой телефон” не становился desktop
    screens: {
      sm: '820px',
      md: '1024px',
      lg: '1280px',
      xl: '1536px',
      '2xl': '1920px',
    },
    extend: {
      fontFamily: {
        sans: ['Figtree', ...defaultTheme.fontFamily.sans],
      },
    },
  },
  plugins: [forms],
};
