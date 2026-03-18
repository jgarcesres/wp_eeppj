const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
  testDir: '.',
  testMatch: ['visual.spec.js', 'e2e.spec.js'],
  timeout: 30000,
  retries: 0,
  use: {
    baseURL: 'http://localhost:8888',
    viewport: { width: 1280, height: 720 },
    screenshot: 'off',
  },
  projects: [
    {
      name: 'chromium',
      use: { browserName: 'chromium' },
    },
  ],
});
