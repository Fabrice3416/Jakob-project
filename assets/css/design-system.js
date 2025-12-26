/**
 * JaKòb Design System - Unified Configuration
 * Version: 1.0.0
 *
 * Ce fichier contient toutes les configurations visuelles de l'application
 * pour assurer une cohérence totale à travers toutes les pages.
 */

const jakobDesignSystem = {
    // Configuration Tailwind CSS
    tailwindConfig: {
        darkMode: "class",
        theme: {
            extend: {
                // Palette de couleurs unifiée
                colors: {
                    // Couleur principale - Rouge haïtien
                    "primary": "#ea2a33",
                    "primary-dark": "#c91b24",
                    "primary-light": "#ff4444",

                    // Couleur d'accentuation - Beige/Doré
                    "accent": "#f7c59f",
                    "accent-dark": "#d9a884",

                    // Backgrounds
                    "background-light": "#f8f6f6",
                    "background-dark": "#211111",

                    // Surfaces (cartes, panels)
                    "surface-light": "#ffffff",
                    "surface-dark": "#2f1a1b",
                    "card-dark": "#382020",

                    // Textes
                    "text-muted": "#c9a092",
                    "text-muted-dark": "#8a6b61",
                },

                // Typographie
                fontFamily: {
                    "display": ["Plus Jakarta Sans", "sans-serif"],
                    "body": ["Plus Jakarta Sans", "sans-serif"],
                },

                // Tailles de police
                fontSize: {
                    'xs': '0.75rem',      // 12px
                    'sm': '0.875rem',     // 14px
                    'base': '1rem',       // 16px
                    'lg': '1.125rem',     // 18px
                    'xl': '1.25rem',      // 20px
                    '2xl': '1.5rem',      // 24px
                    '3xl': '1.875rem',    // 30px
                    '4xl': '2.25rem',     // 36px
                    '5xl': '3rem',        // 48px
                },

                // Border Radius
                borderRadius: {
                    "DEFAULT": "1rem",      // 16px
                    "sm": "0.5rem",         // 8px
                    "md": "0.75rem",        // 12px
                    "lg": "1.5rem",         // 24px
                    "xl": "2rem",           // 32px
                    "2xl": "2.5rem",        // 40px
                    "3xl": "3rem",          // 48px
                    "full": "9999px"
                },

                // Spacing (pour margins, padding)
                spacing: {
                    '18': '4.5rem',
                    '88': '22rem',
                    '128': '32rem',
                },

                // Shadows personnalisées
                boxShadow: {
                    'primary': '0 4px 14px rgba(234, 42, 51, 0.4)',
                    'primary-lg': '0 10px 40px rgba(234, 42, 51, 0.3)',
                    'card': '0 2px 8px rgba(0, 0, 0, 0.1)',
                    'card-hover': '0 8px 24px rgba(0, 0, 0, 0.15)',
                    'glow': '0 0 20px rgba(234, 42, 51, 0.5)',
                },

                // Backgrounds avec patterns
                backgroundImage: {
                    'taino-pattern': "url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ea2a33\" fill-opacity=\"0.05\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')",
                    'gradient-warm': 'linear-gradient(180deg, rgba(234,42,51,0.15) 0%, rgba(33,17,17,0) 100%)',
                    'gradient-radial': 'radial-gradient(circle at top right, var(--tw-gradient-stops))',
                },

                // Animations
                animation: {
                    'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    'float': 'float 3s ease-in-out infinite',
                },

                keyframes: {
                    float: {
                        '0%, 100%': { transform: 'translateY(0)' },
                        '50%': { transform: 'translateY(-10px)' },
                    }
                },
            },
        },
    },

    // Classes CSS communes réutilisables
    commonClasses: {
        // Boutons
        button: {
            primary: "bg-primary hover:bg-primary-dark text-white font-bold py-3 px-6 rounded-full shadow-primary transition-all active:scale-95",
            secondary: "bg-surface-dark hover:bg-white/5 text-white font-bold py-3 px-6 rounded-full border border-white/10 transition-all active:scale-95",
            ghost: "text-primary hover:bg-primary/10 font-bold py-2 px-4 rounded-full transition-all",
            icon: "flex items-center justify-center size-10 rounded-full bg-white/5 hover:bg-white/10 text-white transition-colors",
        },

        // Cards
        card: {
            default: "bg-white dark:bg-surface-dark rounded-2xl p-4 shadow-card hover:shadow-card-hover transition-all border border-gray-100 dark:border-white/5",
            elevated: "bg-white dark:bg-surface-dark rounded-2xl p-6 shadow-primary-lg border border-gray-100 dark:border-white/5",
            glass: "bg-white/10 dark:bg-white/5 backdrop-blur-xl rounded-2xl p-4 border border-white/10",
        },

        // Inputs
        input: {
            default: "bg-surface-light dark:bg-surface-dark border border-gray-200 dark:border-white/10 rounded-xl px-4 py-3 text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-white/40 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all",
            search: "bg-surface-light dark:bg-surface-dark border border-gray-200 dark:border-white/10 rounded-full px-6 py-3 text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-white/40 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all",
        },

        // Badges
        badge: {
            primary: "bg-primary/20 text-primary px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider",
            success: "bg-green-500/20 text-green-500 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider",
            warning: "bg-yellow-500/20 text-yellow-500 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider",
            info: "bg-blue-500/20 text-blue-500 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider",
        },

        // Navigation
        nav: {
            bottomFloating: "fixed bottom-6 left-1/2 -translate-x-1/2 w-[90%] max-w-[360px] bg-surface-dark/90 backdrop-blur-xl border border-white/10 rounded-full p-2 z-50 shadow-2xl",
            bottomFull: "fixed bottom-0 left-0 right-0 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-lg border-t border-gray-200 dark:border-white/5 pb-6 pt-3 z-50",
            topBar: "sticky top-0 z-20 bg-background-light/90 dark:bg-background-dark/90 backdrop-blur-md border-b border-gray-200 dark:border-white/5 transition-colors",
        },

        // Sections
        section: {
            default: "px-4 py-6",
            hero: "px-5 pt-2 pb-6",
            compact: "px-4 py-3",
        },

        // Containers
        container: {
            mobile: "relative w-full max-w-md mx-auto",
            page: "relative flex flex-col min-h-screen w-full max-w-md mx-auto bg-background-light dark:bg-background-dark",
        },
    },

    // Composants HTML réutilisables
    components: {
        // Bottom Navigation (Style recommandé)
        bottomNav: `
            <nav class="fixed bottom-6 left-1/2 -translate-x-1/2 w-[90%] max-w-[360px] bg-surface-dark/90 backdrop-blur-xl border border-white/10 rounded-full p-2 z-50 shadow-2xl flex justify-between items-center">
                <a class="flex flex-col items-center justify-center w-1/4 h-full gap-1 group" href="index.html">
                    <div class="p-2 rounded-full hover:bg-white/5 transition-colors">
                        <span class="material-symbols-outlined text-white/50 group-hover:text-white text-2xl">home</span>
                    </div>
                </a>
                <a class="flex flex-col items-center justify-center w-1/4 h-full gap-1 group" href="explore.html">
                    <div class="p-2 rounded-full hover:bg-white/5 transition-colors">
                        <span class="material-symbols-outlined text-white/50 group-hover:text-white text-2xl">search</span>
                    </div>
                </a>
                <a class="flex flex-col items-center justify-center w-1/4 h-full gap-1 group" href="wallet.html">
                    <div class="p-2 rounded-full hover:bg-white/5 transition-colors">
                        <span class="material-symbols-outlined text-white/50 group-hover:text-white text-2xl">account_balance_wallet</span>
                    </div>
                </a>
                <a class="flex flex-col items-center justify-center w-1/4 h-full gap-1 group" href="profile.html">
                    <div class="p-2 rounded-full hover:bg-white/5 transition-colors">
                        <span class="material-symbols-outlined text-white/50 group-hover:text-white text-2xl">person</span>
                    </div>
                </a>
            </nav>
        `,

        // FAB (Floating Action Button)
        fab: `
            <button class="fixed bottom-24 right-5 z-40 size-16 rounded-full bg-primary shadow-glow flex items-center justify-center text-white active:scale-90 transition-transform animate-pulse hover:animate-none">
                <span class="material-symbols-outlined text-3xl">volunteer_activism</span>
            </button>
        `,
    },

    // Guidelines UX
    guidelines: {
        touchTargets: {
            minimum: "44px", // WCAG AAA standard
            recommended: "48px",
        },
        contrast: {
            normalText: "4.5:1", // WCAG AA
            largeText: "3:1",    // WCAG AA
        },
        animations: {
            duration: {
                fast: "150ms",
                normal: "300ms",
                slow: "500ms",
            },
            easing: "cubic-bezier(0.4, 0, 0.2, 1)", // ease-in-out
        },
    },
};

// Export pour utilisation
if (typeof module !== 'undefined' && module.exports) {
    module.exports = jakobDesignSystem;
}
