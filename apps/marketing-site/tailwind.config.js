/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './components/**/*.{js,vue,ts}',
    './layouts/**/*.vue',
    './pages/**/*.vue',
    './plugins/**/*.{js,ts}',
    './app.vue',
    './error.vue',
  ],
  theme: {
    extend: {
      colors: {
        // Bonsai-inspired Professional Color Scheme
        primary: {
          DEFAULT: '#00C27A', // Bonsai Green
          50: '#E6FAF2',
          100: '#C2F4E0',
          200: '#85E9C0',
          300: '#47DEA0',
          400: '#0AD380',
          500: '#00C27A',
          600: '#009B61',
          700: '#007449',
          800: '#004D30',
          900: '#002618',
        },
        secondary: {
          DEFAULT: '#64748B', // Slate Gray
          50: '#F8FAFC',
          100: '#F1F5F9',
          200: '#E2E8F0',
          300: '#CBD5E1',
          400: '#94A3B8',
          500: '#64748B',
          600: '#475569',
          700: '#334155',
          800: '#1E293B',
          900: '#0F172A',
        },
        accent: {
          DEFAULT: '#F59E0B', // Amber for highlights
          50: '#FFFBEB',
          100: '#FEF3C7',
          200: '#FDE68A',
          300: '#FCD34D',
          400: '#FBBF24',
          500: '#F59E0B',
          600: '#D97706',
          700: '#B45309',
          800: '#92400E',
          900: '#78350F',
        },
        neutral: {
          DEFAULT: '#F9FAFB', // Clean White Background
          50: '#F9FAFB',
          100: '#F3F4F6',
          200: '#E5E7EB',
          300: '#D1D5DB',
          400: '#9CA3AF',
          500: '#6B7280',
          600: '#4B5563',
          700: '#374151',
          800: '#1F2937',
          900: '#111827',
        },
        text: {
          primary: '#1F2937', // Dark Charcoal
          secondary: '#6B7280', // Slate Gray
        },
        error: '#DC2626', // Red
        success: '#00C27A', // Bonsai Green
        warning: '#D97706', // Amber
      },
      fontFamily: {
        sans: ['Inter', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
      },
      fontSize: {
        'heading-1': ['32px', { lineHeight: '1.3', fontWeight: '600' }],
        'heading-2': ['24px', { lineHeight: '1.3', fontWeight: '600' }],
        'heading-3': ['20px', { lineHeight: '1.3', fontWeight: '500' }],
        body: ['16px', { lineHeight: '1.5', fontWeight: '400' }],
        small: ['14px', { lineHeight: '1.4', fontWeight: '400' }],
      },
      spacing: {
        18: '4.5rem', // 72px
        88: '22rem', // 352px
      },
    },
  },
  plugins: [],
};
