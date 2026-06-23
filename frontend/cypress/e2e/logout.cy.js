/* global cy */

describe('Cierre de sesion E2E', () => {
  it('cierra sesion desde la UI y vuelve al login', () => {
    cy.loginAsE2EUser();

    cy.logout();

    cy.getByTestId('login-email').should('be.visible');
    cy.getByTestId('login-password').should('be.visible');
    cy.getByTestId('main-sidebar').should('not.exist');
  });

  it('no mantiene acceso al area autenticada despues del logout', () => {
    cy.loginAsE2EUser();
    cy.logout();

    cy.visitApp();
    cy.getByTestId('login-screen', { timeout: 15000 }).should('be.visible');
    cy.getByTestId('workspace-main').should('not.exist');
  });
});
