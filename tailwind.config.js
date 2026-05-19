// import preset from "./vendor/filament/support/tailwind.config.preset";

/** @type {import('tailwindcss').Config} */

export default {
    // presets: [preset],
    corePlugins: {
        visibility: false
    },
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    // 50: '#EEEEFB',
                    // 100: '#DCDCF7',
                    // 200: '#B9B8EF',
                    // 300: '#9795E6',
                    // 400: '#7471DE',
                    // 500: '#524ED5',
                    // 600: '#413CAD',
                    // 700: '#342F8A',
                    // 800: '#2D2B8D', // base primary
                    // 900: '#1E1C5A',
                    //
                     50: '#f9f6fe',
                     100: '#f1eafd',
                     200: '#e6d9fb',
                     300: '#d2bcf6',
                     400: '#b690f0',
                     500: '#9b66e6',
                     600: '#8446d7',
                     700: '#7034bc',
                     800: '#572b8d', // base primary
                     900: '#4f277c',
                     950: '#33115a',
                },
                secondary: {
                    // '50': '#f4faeb',
                    // '100': '#e8f3d4',
                    // '200': '#d2e8ae',
                    // '300': '#b4d77f',
                    // '400': '#97c556',
                    // '500': '#79aa38',
                    // '600': '#618d2b',
                    // '700': '#486823',
                    // '800': '#3b5321',
                    // '900': '#344720',
                    // '950': '#19260d',
                    50: '#f5f4f0',
                    100: '#eaeadd',
                    200: '#d7d7bf',
                    300: '#bcbd99',
                    400: '#9c9e6e',
                    500: '#85885a',
                    600: '#686b45',
                    700: '#505338',
                    800: '#424430',
                    900: '#3a3c2b',
                    950: '#1e1f14',
                },
                background: {
                    white: '#FFFFFF',
                    wheat: '#F9F6F0',
                    light: '#F5F1E8',
                    subtle: '#EDE7DB',
                },
                success: '#10B981',
                error: '#EF4444',
                warning: '#F59E0B',
                info: '#3B82F6',
            },
        },
    },
    plugins: [
        require("@tailwindcss/forms"),
        require("@tailwindcss/typography"),
    ],
};
