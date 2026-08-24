import { computed, readonly, ref } from 'vue';

export type ThemePreference = 'light' | 'dark' | 'system';
export type ResolvedTheme = Exclude<ThemePreference, 'system'>;

const STORAGE_KEY = 'thuongoc-theme';
const DARK_THEME_COLOR = '#211b1a';
const LIGHT_THEME_COLOR = '#ffffff';
const preferences: ThemePreference[] = ['light', 'dark', 'system'];

const preference = ref<ThemePreference>('system');
const resolvedTheme = ref<ResolvedTheme>('light');
let mediaQuery: MediaQueryList | null = null;
let initialized = false;

function isThemePreference(value: string | null): value is ThemePreference {
    return value !== null && preferences.includes(value as ThemePreference);
}

function getSystemTheme(): ResolvedTheme {
    return mediaQuery?.matches ? 'dark' : 'light';
}

function updateThemeColor(theme: ResolvedTheme): void {
    document.querySelectorAll<HTMLMetaElement>('meta[name="theme-color"]').forEach((meta) => {
        meta.content = theme === 'dark' ? DARK_THEME_COLOR : LIGHT_THEME_COLOR;
    });
}

function applyTheme(): void {
    const nextTheme = preference.value === 'system' ? getSystemTheme() : preference.value;

    resolvedTheme.value = nextTheme;
    document.documentElement.dataset.theme = nextTheme;
    document.documentElement.style.colorScheme = nextTheme;
    updateThemeColor(nextTheme);
}

function handleSystemThemeChange(): void {
    if (preference.value === 'system') {
        applyTheme();
    }
}

export function initializeTheme(): void {
    if (initialized || typeof window === 'undefined') {
        return;
    }

    initialized = true;
    mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    const savedPreference = window.localStorage.getItem(STORAGE_KEY);
    preference.value = isThemePreference(savedPreference) ? savedPreference : 'system';
    mediaQuery.addEventListener('change', handleSystemThemeChange);
    applyTheme();
}

export function useTheme() {
    initializeTheme();

    const isDark = computed(() => resolvedTheme.value === 'dark');

    function setPreference(nextPreference: ThemePreference): void {
        preference.value = nextPreference;
        window.localStorage.setItem(STORAGE_KEY, nextPreference);
        applyTheme();
    }

    return {
        isDark,
        preference: readonly(preference),
        resolvedTheme: readonly(resolvedTheme),
        setPreference,
    };
}
