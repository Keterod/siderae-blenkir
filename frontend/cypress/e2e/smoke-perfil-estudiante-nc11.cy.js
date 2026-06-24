/* global cy */

describe('Smoke V1 - Perfil de estudiante y bloque de riesgo (NC-11)', () => {
  beforeEach(() => {
    cy.loginAsE2EUser();
  });

  it('abre el perfil del primer estudiante y muestra el bloque de riesgo', () => {
    cy.openFirstStudentProfile();

    cy.getByTestId('perfil-riesgo')
      .should('be.visible')
      .and('contain', 'Riesgo académico');

    cy.getByTestId('perfil-riesgo-procesar')
      .should('be.visible')
      .and('contain', /Procesar riesgo|Actualizar riesgo/);

    cy.assertSedeUnicaChilca();
  });

  it('no expone selector de sede en el perfil del estudiante', () => {
    cy.openFirstStudentProfile();

    cy.get('body').should('not.contain', 'Auquimarca');
    cy.get('body').should('not.contain', 'Selector de sede');
  });
});
