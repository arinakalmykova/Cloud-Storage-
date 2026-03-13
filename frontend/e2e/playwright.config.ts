import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './e2e/spec',
  fullyParallel: true,
  forbidOnly: false,
  retries: 0,
  workers: 1,
  reporter: 'html',
  
  use: {
    baseURL: 'http://localhost:80',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    // Добавляем игнорирование HTTPS ошибок для Firefox
    ignoreHTTPSErrors: true,
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    {
      name: 'firefox',
      use: { 
        ...devices['Desktop Firefox'],
        // Специально для Firefox
        launchOptions: {
          firefoxUserPrefs: {
            'security.enterprise_roots.enabled': true,
            'security.cert_pinning.enforcement_level': 0,
          },
        },
      },
    },
    {
      name: 'webkit',
      use: { ...devices['Desktop Safari'] },
    },
  ],
});