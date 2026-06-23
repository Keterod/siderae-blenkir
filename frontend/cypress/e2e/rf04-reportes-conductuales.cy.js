/* global cy, Cypress */

const uniqueText = `Reporte E2E RF04 ${Date.now()}`;

function openReportForm() {
  cy.getByTestId('reporte-conductual-nuevo').click();
  cy.getByTestId('reporte-conductual-formulario').should('be.visible');
}

describe('RF-04 Reportes conductuales', () => {
  beforeEach(() => {
    cy.loginAsE2EUser();
    cy.openRf04StudentProfile();
  });

  it('Caso 1 - muestra el bloque RF-04 en perfil de estudiante Chilca', () => {
    cy.getByTestId('perfil-reportes-conductuales')
      .should('be.visible')
      .and('contain', 'Reportes conductuales');
    cy.getByTestId('perfil-reportes-conductuales').find('select').should('not.exist');
    cy.getByTestId('perfil-reportes-conductuales').should('not.contain', 'Auquimarca');
  });

  it('Caso 2 - registra un reporte conductual y lo muestra en el listado', () => {
    openReportForm();

    cy.get('#rc-fecha').should('have.value').and('match', /^\d{4}-\d{2}-\d{2}$/);
    cy.get('#rc-tipo').clear().type('Incidencia E2E');
    cy.get('#rc-gravedad').select('leve');
    cy.get('#rc-descripcion').clear().type(uniqueText);
    cy.get('#rc-accion').clear().type('Accion inmediata E2E');
    cy.getByTestId('reporte-conductual-guardar').click();

    cy.getByTestId('reporte-conductual-formulario').should('not.exist');
    cy.contains('[data-testid^="reporte-conductual-"]', uniqueText, { timeout: 15000 })
      .should('be.visible')
      .and('contain', 'Incidencia E2E')
      .and('contain', 'leve');
  });

  it('Caso 3 - bloquea el guardado cuando falta la descripcion obligatoria', () => {
    openReportForm();

    cy.get('#rc-tipo').clear().type('Incidencia E2E incompleta');
    cy.get('#rc-gravedad').select('leve');
    cy.get('#rc-descripcion').clear();
    cy.getByTestId('reporte-conductual-guardar').click();

    cy.get('#rc-descripcion:invalid').should('exist');
    cy.getByTestId('reporte-conductual-formulario').should('be.visible');
    cy.contains('[data-testid^="reporte-conductual-"]', 'Incidencia E2E incompleta').should('not.exist');
  });

  it('Caso 4 - anula el reporte creado y lo retira del listado activo', () => {
    cy.contains('[data-testid^="reporte-conductual-"]', uniqueText, { timeout: 15000 })
      .should('be.visible')
      .within(() => {
        cy.on('window:confirm', () => true);
        cy.contains('button', 'Anular').click();
      });

    cy.contains('[data-testid^="reporte-conductual-"]', uniqueText, { timeout: 15000 }).should('not.exist');
  });

  it('Caso 5 - no expone selector ni opcion operativa multi-sede en RF-04', () => {
    cy.getByTestId('perfil-reportes-conductuales').within(() => {
      cy.contains('Sede').should('not.exist');
      cy.get('select').should('not.exist');
      cy.contains('Auquimarca').should('not.exist');
    });

    cy.get('body').should('not.contain', 'Selector de sede');
  });
});
