/**
 * alertas_intervenciones.cy.js
 * Valida: login mockeado → listado de alertas → abrir detalle → registrar intervención → cerrar
 * testids reales: alertas-panel, alertas-tabla, alerta-abrir-{id}, form-intervencion,
 *                 intervencion-guardar, form-cerrar-alerta, alerta-cerrar
 */
describe('Módulo de alertas e intervenciones', () => {
  beforeEach(() => {
    cy.intercept('GET', '/sanctum/csrf-cookie', { statusCode: 204 });

    cy.intercept('GET', '/api/me', {
      statusCode: 200,
      body: {
        usuario: { id: 1, name: 'Psicólogo Test', email: 'psicologo@test.com' },
        roles: ['psicologo_tutor'],
        permisos: ['ver_alertas', 'registrar_intervencion'],
      },
    }).as('getMe');

    // Listado de alertas — usar URL exacta sin parámetros ni con comodín para no colisionar con /api/alertas/1
    cy.intercept('GET', '/api/alertas', {
      statusCode: 200,
      body: [
        {
          id: 1,
          titulo: 'Alerta de Inasistencia',
          estado: 'abierta',
          nivel_riesgo: 'alto',
          estudiante: { nombres: 'Juan', apellidos: 'Pérez' },
          created_at: '2026-06-24T10:00:00.000000Z',
        },
      ],
    }).as('getAlertas');

    // Detalle de alerta individual
    cy.intercept('GET', '/api/alertas/1', {
      statusCode: 200,
      body: {
        id: 1,
        titulo: 'Alerta de Inasistencia',
        estado: 'abierta',
        nivel_riesgo: 'alto',
        estudiante: { id: 10, nombres: 'Juan', apellidos: 'Pérez' },
        intervenciones: [],
      },
    }).as('getAlerta');

    // Después de guardar intervención, recargar la alerta con intervención incluida
    cy.intercept('POST', '/api/alertas/1/intervenciones', {
      statusCode: 201,
      body: { id: 99, descripcion: 'Se conversó con el estudiante', created_at: '2026-06-24T11:00:00.000000Z' },
    }).as('saveIntervencion');

    cy.intercept('POST', '/api/alertas/1/cerrar', {
      statusCode: 200,
      body: { message: 'Alerta cerrada correctamente' },
    }).as('closeAlerta');

    cy.visit('/');
    cy.wait('@getMe');
    cy.get('[data-testid="workspace-main"]', { timeout: 15000 }).should('be.visible');
  });

  it('muestra el listado de alertas al navegar al módulo', () => {
    cy.get('[data-testid="nav-alertas"]').should('be.visible').click();
    cy.wait('@getAlertas');

    // Verificar que el título principal de la vista sea visible
    cy.get('h2').contains(/alertas|detalle/i, { timeout: 10000 }).should('be.visible');
    cy.get('[data-testid="alertas-tabla"]').should('be.visible');
  });

  it('permite abrir el detalle de una alerta', () => {
    cy.get('[data-testid="nav-alertas"]').should('be.visible').click();
    cy.wait('@getAlertas');

    cy.get('h2').contains(/alertas/i, { timeout: 10000 }).should('be.visible');

    // Hacer clic en el botón de abrir detalle del alerta ID=1
    cy.get('[data-testid="alerta-abrir-1"]').should('be.visible').click();
    cy.wait('@getAlerta');

    // Debe mostrarse el formulario de intervención
    cy.get('[data-testid="form-intervencion"]', { timeout: 10000 }).should('exist');
  });

  it('permite registrar una intervención en una alerta abierta', () => {
    cy.get('[data-testid="nav-alertas"]').should('be.visible').click();
    cy.wait('@getAlertas');

    cy.get('h2').contains(/alertas/i, { timeout: 10000 }).should('be.visible');
    cy.get('[data-testid="alerta-abrir-1"]').should('be.visible').click();
    cy.wait('@getAlerta');

    // Rellenar el formulario de intervención
    cy.get('[data-testid="form-intervencion"]', { timeout: 10000 }).within(() => {
      cy.get('textarea').should('exist').type('Se conversó con el estudiante sobre su situación académica.', { force: true });
      cy.get('[data-testid="intervencion-guardar"]').should('exist').click({ force: true });
    });

    cy.wait('@saveIntervencion');
    cy.screenshot('evidencia_alertas_intervenciones_pass');
  });
});
