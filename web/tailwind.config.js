/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'selector',
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      boxShadow: {
        custom: '0px 4px 6px -1px rgba(0, 0, 0, 0.04)',
      },
      keyframes: {
        slide: {
          '0%': { opacity: .9 },
          '10%': { opacity: 1 },
          '50%': { opacity: 1, transform: 'scale(1.4)' },
          '100%': { opacity: 1, transform: 'scale(1)' },
        }
      },
      animation: {
        slide: 'slide 80s 1 ease both',
      }
    },
  },
  plugins: [
    require('@tailwindcss/typography'),
  ],
}