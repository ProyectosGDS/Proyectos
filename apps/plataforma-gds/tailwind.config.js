/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    fontFamily: {
      sans : ['Jost']
    },
    extend: {
      colors: {
        'color-1' : '#273385',
        'color-2' : '#A78BFA',
        'color-3' : '#E5C9D7',
        'color-4' : '#83A6CE',
        'color-5' : '#26415E',
        'color-6' : '#0B1B32',
        'color-7' : '#97C21E'
      }
    },
  },
  plugins: [
    require('tailwindcss-animated')
  ],
}

