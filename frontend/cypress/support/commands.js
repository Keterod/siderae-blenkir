/* global cy, Cypress, expect */

const MAIN_NAV_SELECTOR = '[data-testid="main-sidebar"], aside[aria-label="Navegación principal"]';
const E2E_STATE_TIMEOUT = 30000;
const DEFAULT_E2E_EMAIL = 'admin@siderae.test';
const DEFAULT_E2E_PASSWORD = 'password';

function envOrDefault(name, defaultValue) {
  const value = Cypress.env(name);
  if (value === undefined || value === null || value === '') {
    return defaultValue;
  }
  return value;
}

Cypress.Commands.add('getByTestId', (testId, options = {}) => {
  return cy.get(`[data-testid="${testId}"]`, options);
});

Cypress.Commands.add('getMainNavigation', (options = {}) => {
  return cy.get(MAIN_NAV_SELECTOR, options);
});

Cypress.Commands.add('visitApp', (options = {}) => {
  if (options.clearSession) {
    cy.clearAllCookies();
    cy.clearAllLocalStorage();
    cy.clearAllSessionStorage();
  }
  cy.visit('/');
});

Cypress.Commands.add('requireE2ECredentials', () => {
  return cy.then(() => ({
    email: envOrDefault('E2E_EMAIL', DEFAULT_E2E_EMAIL),
    password: envOrDefault('E2E_PASSWORD', DEFAULT_E2E_PASSWORD),
  }));
});

Cypress.Commands.add('assertAuthenticated', () => {
  cy.getByTestId('workspace-main', { timeout: E2E_STATE_TIMEOUT }).should('be.visible');
  cy.get(MAIN_NAV_SELECTOR, { timeout: E2E_STATE_TIMEOUT }).should('be.visible');
  cy.getByTestId('header-logout').should('be.visible');
  cy.getByTestId('login-screen').should('not.exist');
});

Cypress.Commands.add('ensureLoggedOut', () => {
  cy.visitApp({ clearSession: true });
  cy.get('body', { timeout: E2E_STATE_TIMEOUT }).should(($body) => {
    expect(
      $body.find('[data-testid="login-screen"], [data-testid="workspace-main"]').length,
      'login o layout autenticado visible',
    ).to.be.greaterThan(0);
  });
  cy.get('body').then(($body) => {
    if ($body.find('[data-testid="workspace-main"]').length > 0) {
      cy.getByTestId('header-logout').click();
    }
  });
  cy.getByTestId('login-screen', { timeout: E2E_STATE_TIMEOUT }).should('be.visible');
  cy.getByTestId('workspace-main').should('not.exist');
});

Cypress.Commands.add('loginAsE2EUser', () => {
  cy.requireE2ECredentials().then(({ email, password }) => {
    cy.ensureLoggedOut();
    cy.getByTestId('login-screen', { timeout: E2E_STATE_TIMEOUT }).should('be.visible');
    cy.getByTestId('login-email').clear().type(email);
    cy.getByTestId('login-password').clear().type(password, { log: false });
    cy.getByTestId('login-submit').click();
    // Esperar a que el flujo de login complete: desaparece el login y aparece el workspace.
    cy.get('body', { timeout: E2E_STATE_TIMEOUT }).should(($body) => {
      const hasWorkspace = $body.find('[data-testid="workspace-main"]').length > 0;
      const hasLogin = $body.find('[data-testid="login-screen"]').length > 0;
      if (!hasWorkspace && !hasLogin) {
        throw new Error('No se ve ni login ni workspace durante el ingreso.');
      }
      expect(
        hasWorkspace,
        'workspace visible tras login (login aún visible indica que el flujo no terminó)',
      ).to.be.true;
    });
    cy.assertAuthenticated();
  });
});

Cypress.Commands.add('logout', () => {
  cy.getByTestId('header-logout', { timeout: E2E_STATE_TIMEOUT }).should('be.visible').click();
  cy.getByTestId('login-screen', { timeout: E2E_STATE_TIMEOUT }).should('be.visible');
  cy.getByTestId('workspace-main').should('not.exist');
});

Cypress.Commands.add('openModule', (moduleKey, expectedTestId = null) => {
  cy.getByTestId(`nav-${moduleKey}`, { timeout: E2E_STATE_TIMEOUT }).should('be.visible').click();
  if (expectedTestId) {
    cy.getByTestId(expectedTestId, { timeout: E2E_STATE_TIMEOUT }).should('be.visible');
  }
});

Cypress.Commands.add('openRf04StudentProfile', () => {
  const studentText = Cypress.env('E2E_STUDENT_TEXT');

  cy.openModule('estudiantes', 'estudiantes-panel');

  if (studentText) {
    cy.contains('label', 'Buscar (código, nombres o apellidos)')
      .parent()
      .find('input')
      .clear()
      .type(studentText);
    cy.contains('button', 'Aplicar filtros').click();
  }

  cy.get('[data-testid="estudiantes-tabla"]', { timeout: 15000 }).should('be.visible');
  cy.get('[data-testid^="estudiante-fila-"]').then(($rows) => {
    if (!$rows.length) {
      throw new Error(
        studentText
          ? `No se encontró estudiante Chilca para CYPRESS_E2E_STUDENT_TEXT="${studentText}".`
          : 'No se encontró ningún estudiante Chilca visible para ejecutar RF-04.',
      );
    }
  });
  cy.get('[data-testid^="estudiante-fila-"]').first().within(() => {
    cy.contains('button', 'Ver perfil').click();
  });

  cy.getByTestId('perfil-reportes-conductuales', { timeout: E2E_STATE_TIMEOUT })
    .should('be.visible')
    .and('contain', 'Reportes conductuales');
});

Cypress.Commands.add('openFirstStudentProfile', () => {
  cy.openModule('estudiantes', 'estudiantes-panel');

  cy.get('[data-testid="estudiantes-tabla"]', { timeout: 15000 }).should('be.visible');
  cy.get('[data-testid^="estudiante-perfil-"]').then(($buttons) => {
    if (!$buttons.length) {
      throw new Error('No se encontró ningún estudiante Chilca visible para abrir perfil.');
    }
  });
  cy.get('[data-testid^="estudiante-perfil-"]').first().click();

  cy.getByTestId('perfil-datos-academicos', { timeout: E2E_STATE_TIMEOUT }).should('be.visible');
});

Cypress.Commands.add('assertSedeUnicaChilca', () => {
  cy.get('body').should('not.contain', 'Auquimarca');
  cy.get('body').should('not.contain', 'Selector de sede');
});
