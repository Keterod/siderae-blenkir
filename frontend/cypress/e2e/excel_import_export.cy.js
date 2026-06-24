/**
 * excel_import_export.cy.js
 * Valida: login mockeado → módulo Excel por aula → botón Descargar Excel visible
 * El módulo ExcelPorAulaPanel usa: getCurricularPeriodos, getAnioEscolarActivo, descargarExcelAula
 */
describe('Flujo de importación/exportación de Excel', () => {
  beforeEach(() => {
    cy.intercept('GET', '/sanctum/csrf-cookie', { statusCode: 204 });

    cy.intercept('GET', '/api/me', {
      statusCode: 200,
      body: {
        usuario: { id: 1, name: 'Docente Test', email: 'docente@test.com' },
        roles: ['docente'],
        permisos: ['descargar_excel_aula', 'registrar_notas_semanales'],
      },
    }).as('getMe');

    cy.intercept('GET', '/api/curricular/anios-escolares/activo', {
      statusCode: 200,
      body: { anio: 2026, activo: true },
    }).as('getAnioEscolar');

    cy.intercept('GET', '/api/curricular/periodos*', {
      statusCode: 200,
      body: [
        { id: 1, bimestre: 1, nombre: 'Bimestre I', es_vigente: true, anio_escolar: 2026 },
        { id: 2, bimestre: 2, nombre: 'Bimestre II', es_vigente: false, anio_escolar: 2026 },
      ],
    }).as('getPeriodos');

    cy.intercept('GET', '/api/curricular/excel-aula*', {
      statusCode: 200,
      headers: {
        'content-disposition': 'attachment; filename="notas_aula.xlsx"',
        'content-type': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      },
      body: 'EXCEL_DUMMY',
    }).as('descargarExcel');

    cy.visit('/');
    cy.wait('@getMe');
    cy.get('[data-testid="workspace-main"]', { timeout: 15000 }).should('be.visible');
  });

  it('permite navegar al módulo Excel por aula y ver el botón de descarga', () => {
    cy.get('[data-testid="nav-curricular_excel_aula"]').should('be.visible').click();

    // El botón de descarga de Excel debe ser visible
    cy.get('[data-testid="excel-aula-descargar"]', { timeout: 10000 }).should('be.visible');

    // Verificar que hay selects de filtro (nivel, grado, sección, período)
    cy.get('select').should('have.length.at.least', 1);
    cy.screenshot('evidencia_excel_import_export_pass');
  });
});
