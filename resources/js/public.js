import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        const icon = button.querySelector('[data-theme-icon]');
        const applyTheme = (dark, animate = false) => {
            document.documentElement.classList.toggle('dark', dark);
            button.setAttribute('aria-pressed', String(dark));
            button.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');

            if (!icon) return;

            const moon = icon.querySelector('[data-theme-moon]');
            const sun = icon.querySelector('[data-theme-sun]');
            moon?.classList.toggle('hidden', dark);
            sun?.classList.toggle('hidden', !dark);

            if (animate) {
                icon.classList.remove('rotate-180');
                requestAnimationFrame(() => icon.classList.add('rotate-180'));
            }
        };

        applyTheme(document.documentElement.classList.contains('dark'));

        button.addEventListener('click', () => {
            const dark = !document.documentElement.classList.contains('dark');

            applyTheme(dark, true);
            window.localStorage.setItem('theme', dark ? 'dark' : 'light');
        });
    });
});
