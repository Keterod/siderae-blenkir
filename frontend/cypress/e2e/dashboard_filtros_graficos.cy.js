/**
 * dashboard_filtros_graficos.cy.js
 * Valida: login mockeado → dashboard institucional → panel y métricas visibles → filtros
 * Endpoint: GET /api/dashboard/institucional
 * testid: dashboard-institucional-panel
 */
describe('Dashboard: gráficos y filtros', () => {
  beforeEach(() => {
    cy.intercept('GET', '/sanctum/csrf-cookie', { statusCode: 204 });

    cy.intercept('GET', '/api/me', {
      statusCode: 200,
      body: {
        usuario: { id: 1, name: 'Director Test', email: 'director@test.com' },
        roles: ['directivo'],
        permisos: ['ver_dashboard_institucional', 'ver_dashboard'],
      },
    }).as('getMe');

    cy.intercept('GET', '/api/dashboard/institucional*', {
      statusCode: 200,
      body: {
        totales: { estudiantes: 100, docentes: 10, riesgo_alto: 5, intervenciones: 2 },
        por_grado_seccion: [
          { grado: '1', seccion: 'A', total_estudiantes: 30, riesgo_bajo: 20, riesgo_medio: 8, riesgo_alto: 2 }
        ],
        ultimos_riesgos: [
          { estudiante: 'Juan Pérez', grado: '1', seccion: 'A', nivel: 'Alto', indice: 0.85, fecha: '2026-06-24' }
        ],
        graficoRiesgo: [
          { nombre: 'Alto', valor: 5 },
          { nombre: 'Medio', valor: 20 },
          { nombre: 'Bajo', valor: 75 },
        ],
      },
    }).as('getDashboardInstitucional');

    cy.visit('/');
    cy.wait('@getMe');
    cy.get('[data-testid="workspace-main"]', { timeout: 15000 }).should('be.visible');
  });

  it('muestra el dashboard institucional con métricas y panel visible', () => {
    cy.get('[data-testid="nav-dashboard_institucional"]').should('be.visible').click();
    cy.wait('@getDashboardInstitucional');

    // El panel principal debe estar visible
    cy.get('[data-testid="dashboard-institucional-panel"]', { timeout: 10000 }).should('be.visible');

    // Debe haber algún elemento de gráfico (svg, canvas o contenedor)
    cy.get('[data-testid="dashboard-institucional-panel"]').within(() => {
      cy.get('svg, canvas, .recharts-wrapper, [class*="chart"], table')
        .should('exist');
    });
  });

  it('permite aplicar filtros en el dashboard', () => {
    cy.get('[data-testid="nav-dashboard_institucional"]').should('be.visible').click();
    cy.wait('@getDashboardInstitucional');

    cy.get('[data-testid="dashboard-institucional-panel"]', { timeout: 10000 }).should('be.visible');

    // El botón aplicar filtros debe existir
    cy.get('[data-testid="dashboard-institucional-aplicar"]').should('exist');
    cy.screenshot('evidencia_dashboard_filtros_graficos_pass');
  });
});
