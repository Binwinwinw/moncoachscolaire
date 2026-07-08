/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./src/**/*.{html,js,php}",
        "./public/**/*.{html,js,php}",
        "./index.php",
    ],
    theme: {
        extend: {
            colors: {
                landing: {
                    1: "rgb(255 255 255 / 70%)",
                },
            },
            backgroundImage: {
                "landing-2":
                    "linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%)",
                "landing-3":
                    "linear-gradient(135deg, #fef3c7 0%, #fde68a 100%)",
                "landing-4":
                    "linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%)",
            },
            backdropBlur: {
                xs: "2px",
            },
        },
    },
    plugins: [],
};
