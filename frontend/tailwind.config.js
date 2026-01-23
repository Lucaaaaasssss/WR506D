/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./index.html",
        "./src/**/*.{vue,js,ts,jsx,tsx}",
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', 'system-ui', 'sans-serif'],
                logo: ['"Luckiest Guy"', 'cursive'],
            },
        },
    },
    plugins: [],
}
