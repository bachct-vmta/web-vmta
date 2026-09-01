import type { Config } from 'tailwindcss';

const config: Config = {
  content: [
    './src/pages/**/*.{js,ts,jsx,tsx,mdx}',
    './src/components/**/*.{js,ts,jsx,tsx,mdx}',
    './src/app/**/*.{js,ts,jsx,tsx,mdx}',
  ],
  theme: {
    extend: {
      colors: {
        'vmta-teal': '#0b7f7c',
        'vmta-red': '#d31e45',
        'vmta-cream': '#f8f5ef',
      },
      fontFamily: {
        sans: ['var(--font-be-vietnam)', 'Be Vietnam Pro', 'Inter', 'system-ui', 'sans-serif'],
        'utm-helve': ['var(--font-be-vietnam)', 'Be Vietnam Pro', 'Inter', 'system-ui', 'sans-serif'],
        'sharp-bo': ['var(--font-be-vietnam)', 'Be Vietnam Pro', 'Inter', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
};
export default config;
