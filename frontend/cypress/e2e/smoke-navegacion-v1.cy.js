/* global cy */

describe('Smoke V1 - Navegacion por modulos aprobados', () => {
  beforeEach(() => {
    cy.loginAsE2EUser();
  });

  it('muestra el Dashboard institucional sin selector de sede', () => {
    cy.openModule('dashboard_institucional', 'dashboard-institucional-panel');
    cy.getByTestId('dashboard-institucional-panel')
      .should('be.visible')
      .and('contain', 'Dashboard institucional');
    cy.assertSedeUnicaChilca();
  });

  it('muestra Reportes de riesgo academico sin selector de sede', () => {
    cy.openModule('reportes_riesgo_academico', 'reportes-riesgo-panel');
    cy.getByTestId('reportes-riesgo-panel')
      .should('be.visible')
      .and('contain', 'Reportes de riesgo académico');
    cy.assertSedeUnicaChilca();
  });

  it('muestra Seguimiento psicologo/tutor sin selector de sede', () => {
    cy.openModule('psicologo_tutor_seguimiento', 'psicologo-tutor-panel');
    cy.getByTestId('psicologo-tutor-panel')
      .should('be.visible')
      .and('contain', 'Seguimiento psicólogo/tutor');
    cy.assertSedeUnicaChilca();
  });

  it('muestra el modulo Estudiantes sin selector de sede', () => {
    cy.openModule('estudiantes', 'estudiantes-panel');
    cy.getByTestId('estudiantes-panel')
      .should('be.visible')
      .and('contain', 'Estudiantes');
    cy.assertSedeUnicaChilca();
  });
});
