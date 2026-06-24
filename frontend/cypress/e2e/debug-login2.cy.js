/* global cy */

describe('Debug login minimal', () => {
  it('logs in via command and shows workspace', () => {
    cy.loginAsE2EUser();
    cy.getByTestId('workspace-main').should('be.visible');
  });
});
