import { defineConfig } from 'cypress';
import { createRequire } from 'module';

const require = createRequire(import.meta.url);

export default defineConfig({
  e2e: {
    baseUrl: process.env.CYPRESS_BASE_URL || 'http://localhost:5173',
    supportFile: 'cypress/support/e2e.js',
    specPattern: 'cypress/e2e/**/*.cy.js',
    video: false,
    screenshotOnRunFailure: false,
    chromeWebSecurity: false,
    setupNodeEvents(on, config) {
      require('@cypress/code-coverage/task')(on, config);
      on('task', {
        log(message) {
          console.log(message);
          return null;
        },
      });
      return config;
    },
  },
});
