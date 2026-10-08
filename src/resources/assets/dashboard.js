(function () {
    'use strict';
    document.querySelectorAll('.logger-dashboard').forEach(function (dashboard) {
        var system = window.matchMedia('(prefers-color-scheme: dark)');
        var choices = dashboard.querySelectorAll('[data-theme-choice]');
        if (dashboard.dataset.theme === 'system') {
            try {
                var saved = localStorage.getItem('laravel-logger-theme');
                if (['light', 'dark', 'system'].indexOf(saved) !== -1) dashboard.dataset.theme = saved;
            } catch (error) {}
        }
        function updateTheme() {
            var theme = dashboard.dataset.theme;
            var resolved = theme === 'system' ? (system.matches ? 'dark' : 'light') : theme;
            dashboard.dataset.colorScheme = resolved;
            dashboard.dataset.bsTheme = resolved;
            choices.forEach(function (button) {
                button.setAttribute('aria-pressed', button.dataset.themeChoice === theme ? 'true' : 'false');
            });
        }
        choices.forEach(function (button) {
            button.addEventListener('click', function () {
                dashboard.dataset.theme = button.dataset.themeChoice;
                try { localStorage.setItem('laravel-logger-theme', dashboard.dataset.theme); } catch (error) {}
                updateTheme();
            });
        });
        if (system.addEventListener) system.addEventListener('change', updateTheme);
        else system.addListener(updateTheme);
        updateTheme();
        dashboard.querySelectorAll('form[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!window.confirm(form.dataset.confirm)) event.preventDefault();
            });
        });
        dashboard.querySelectorAll('select[name="period"]').forEach(function (select) {
            select.addEventListener('change', function () {
                if (select.value) select.form.querySelectorAll('input[type="date"]').forEach(function (input) { input.value = ''; });
            });
        });
    });
}());
