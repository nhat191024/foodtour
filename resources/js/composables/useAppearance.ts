import { ref } from 'vue';

type Appearance = 'light' | 'dark' | 'system';

export function updateTheme() {
    if (typeof window === 'undefined') {
        return;
    }
    // dark mode is not finished, will fix later
    document.documentElement.classList.remove('dark');
}

export function initializeTheme() {
    if (typeof window === 'undefined') {
        return;
    }
    updateTheme();
}

export function useAppearance() {
    const appearance = ref<Appearance>('light');

    function updateAppearance() {}

    return {
        appearance,
        updateAppearance,
    };
}
