/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                'meetrix-orange': '#ff4d00', // Signal Orange
                'meetrix-green': '#adff2f',  // Acid Green
                'zinc': {
                    950: '#09090b',
                }
            },
            fontFamily: {
                'sans': ['Inter', 'sans-serif'],
                'outfit': ['Outfit', 'sans-serif'],
            },
            borderRadius: {
                '4xl': '2rem',
                '5xl': '3rem',
            },
            boxShadow: {
                'premium': '0 20px 50px -12px rgba(0, 0, 0, 0.5)',
                '3d': '0 8px 0 rgb(0,0,0), 0 15px 20px rgba(0,0,0,.2)',
                '3d-hover': '0 2px 0 rgb(0,0,0), 0 5px 10px rgba(0,0,0,.2)',
            }
        },
    },
    plugins: [],
}
