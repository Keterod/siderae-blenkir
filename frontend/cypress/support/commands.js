/* global cy, Cypress */

function requiredEnv(name) {
  const value = Cypress.env(name);
  if (!value) {
    throw new Error(`Debe definir CYPRESS_${name} para ejecutar el smoke RF-04.`);
  }
  return value;
}

Cypress.Commands.add('loginAsE2EUser', () => {
  const email = requiredEnv('E2E_EMAIL');
  const password = requiredEnv('E2E_PASSWORD');

  cy.visit('/');
  cy.get('[data-testid="login-screen"]').should('be.visible');
  cy.get('[data-testid="login-email"]').clear().type(email);
  cy.get('[data-testid="login-password"]').clear().type(password, { log: false });
  cy.get('[data-testid="login-submit"]').click();

  cy.get('[data-testid="workspace-main"]', { timeout: 15000 }).should('be.visible');
  cy.get('[data-testid="login-screen"]').should('not.exist');
});

Cypress.Commands.add('openRf04StudentProfile', () => {
  const studentText = Cypress.env('E2E_STUDENT_TEXT');

  cy.get('[data-testid="nav-estudiantes"]').should('be.visible').click();
  cy.get('[data-testid="estudiantes-panel"]', { timeout: 15000 }).should('be.visible');

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

  cy.get('[data-testid="perfil-reportes-conductuales"]', { timeout: 15000 })
    .should('be.visible')
    .and('contain', 'Reportes conductuales');
});
