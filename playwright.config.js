const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
    testDir: './tests/Browser',
    use: { baseURL: 'http://127.0.0.1:8765', browserName: 'chromium' },
    webServer: {
        command: 'php -S 127.0.0.1:8765 tests/Browser/server.php',
        url: 'http://127.0.0.1:8765/activity',
        reuseExistingServer: !process.env.CI,
    },
    workers: 1,
});
