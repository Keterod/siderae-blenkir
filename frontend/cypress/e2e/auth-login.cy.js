/* global cy */

const E2E_STATE_TIMEOUT = 30000;

describe('Autenticacion E2E', () => {
  it('muestra la pantalla de login', () => {
    cy.ensureLoggedOut();

    cy.getByTestId('login-screen', { timeout: E2E_STATE_TIMEOUT }).should('be.visible');
    cy.getByTestId('login-email').should('be.visible');
    cy.getByTestId('login-password').should('be.visible');
    cy.getByTestId('login-submit').should('be.visible');
  });

  it('rechaza credenciales invalidas', () => {
    cy.ensureLoggedOut();

    cy.getByTestId('login-email').clear().type(`usuario-invalido-${Date.now()}@example.test`);
    cy.getByTestId('login-password').clear().type('password-invalido-e2e', { log: false });
    cy.getByTestId('login-submit').click();

    cy.getByTestId('login-screen', { timeout: E2E_STATE_TIMEOUT }).should('be.visible');
    cy.getByTestId('workspace-main').should('not.exist');
    cy.contains(/Credenciales invalidas|Credenciales inválidas|No se pudo iniciar sesión/i, { timeout: E2E_STATE_TIMEOUT })
      .should('be.visible');
  });

  it('inicia sesion con usuario E2E y muestra layout autenticado', () => {
    cy.loginAsE2EUser();

    cy.getByTestId('workspace-main').should('be.visible');
    cy.getMainNavigation().should('be.visible');
    cy.getByTestId('header-logout').should('be.visible');
  });

  it('no expone selector de sede visible tras login', () => {
    cy.loginAsE2EUser();

    cy.getMainNavigation().should('not.contain', 'Auquimarca');
    cy.get('body').should('not.contain', 'Selector de sede');
  });
});
