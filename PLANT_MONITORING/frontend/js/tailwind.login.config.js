        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'sage-bg': '#8f9f84',
                        'panel-light': '#ebdcca',
                        'panel-dark': '#cebc9f',
                        'widget-brown': '#b3956e',
                        'text-main': '#4a4132',
                        'text-muted': '#8c8069',
                        'accent-green': '#5d7f54',
                        'accent-orange': '#c87c46'
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.6s ease-out forwards'
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        }
                    }
                }
            }
        }
