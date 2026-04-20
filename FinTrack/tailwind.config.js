import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ["Inter", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                ctp: {
                    rosewater: "#f5e0dc",
                    flamingo: "#f2cdcd",
                    pink: "#f5c2e7",
                    mauve: "#cba6f7",
                    red: "#f38ba8",
                    maroon: "#eba0ac",
                    peach: "#fab387",
                    yellow: "#f9e2af",
                    green: "#a6e3a1",
                    teal: "#94e2d5",
                    sky: "#89dceb",
                    sapphire: "#74c7ec",
                    blue: "#89b4fa",
                    lavender: "#b4befe",
                    text: "#cdd6f4",
                    subtext1: "#bac2de",
                    subtext0: "#a6adc8",
                    overlay2: "#9399b2",
                    overlay1: "#7f849c",
                    overlay0: "#6c7086",
                    surface2: "#585b70",
                    surface1: "#45475a",
                    surface0: "#313244",
                    base: "#1e1e2e",
                    mantle: "#181825",
                    crust: "#11111b",
                },
                byzantine: {
                    DEFAULT: "#cba6f7",
                    hover: "#b4befe",
                    light: "#f5c2e7",
                },
                platinum: "#cdd6f4",
                night: "#1e1e2e",
                raisin: "#181825",
                raisin2: "#313244",
                raisin3: "#45475a",
                success: "#a6e3a1",
                danger: "#f38ba8",
                warning: "#f9e2af",
                info: "#89dceb",
            },
            animation: {
                fadeIn: "fadeIn 1s ease-out forwards",
            },
            keyframes: {
                fadeIn: {
                    "0%": { opacity: 0, transform: "translateY(10px)" },
                    "100%": { opacity: 1, transform: "translateY(0)" },
                },
            },
        },
    },
    plugins: [forms({ strategy: "class" })],
};
