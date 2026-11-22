import { defineConfig } from '@playwright/test'

export default defineConfig({
  testDir: './tests/browser',
  use: {
    baseURL: 'http://127.0.0.1:4173',
    launchOptions: process.env.CHROMIUM_PATH ? { executablePath: process.env.CHROMIUM_PATH } : {},
  },
  webServer: [
    { command: 'node tests/serve.mjs', port: 4173, reuseExistingServer: false },
    {
      command: 'npm run dev -- --host 127.0.0.1 --port 4174 --strictPort',
      port: 4174,
      reuseExistingServer: false,
      env: { VITE_API_BASE_URL: '/api', VITE_BASE_PATH: '/' },
    },
  ],
})
