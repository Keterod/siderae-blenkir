/**
 * notas_registro.cy.js
 * Valida: login mockeado → módulo notas semanales → carga de asignaciones del docente → panel visible
 */
describe('Registro de notas', () => {
  beforeEach(() => {
    // Interceptar llamadas de sesión/CSRF
    cy.intercept('GET', '/sanctum/csrf-cookie', { statusCode: 204 });

    // Mock de /api/me para simular usuario autenticado como docente
    cy.intercept('GET', '/api/me', {
      statusCode: 200,
      body: {
        usuario: { id: 1, name: 'Docente Test', email: 'docente@test.com' },
        roles: ['docente'],
        permisos: ['registrar_notas_semanales'],
      },
    }).as('getMe');

    // Interceptar llamadas secundarias que la app hace al cargar
    cy.intercept('GET', '/api/curricular/anios-escolares/activo', {
      statusCode: 200,
      body: { anio: 2026, activo: true },
    }).as('getAnioEscolar');

    cy.intercept('GET', '/api/curricular/periodos*', {
      statusCode: 200,
      body: [{ id: 1, bimestre: 1, nombre: 'Bimestre I', es_vigente: true, anio_escolar: 2026 }],
    }).as('getPeriodos');

    // Endpoint real de asignaciones del docente
    cy.intercept('GET', '/api/curricular/docente/aulas-cursos*', {
      statusCode: 200,
      body: [
        {
          id: 1,
          grado: '1',
          seccion: 'A',
          nivel: 'Secundaria',
          sede: 'chilca',
          anio_escolar: 2026,
          malla_curso: {
            curso_catalogo: { nombre: 'Matemática' },
            area: { id: 2, nombre: 'Matemática' },
            area_id: 2,
          },
        },
      ],
    }).as('getAulasDocente');

    // Mock del formulario de notas semanales
    cy.intercept('GET', '/api/curricular/notas-semanales*', {
      statusCode: 200,
      body: {
        asignacion: { id: 1 },
        criterios: [],
        estudiantes: [],
        notas_por_estudiante_criterio: {},
      },
    }).as('getFormularioNotas');

    cy.intercept('POST', '/api/curricular/notas-semanales/bulk', {
      statusCode: 200,
      body: { message: 'Notas guardadas correctamente' },
    }).as('saveNotas');

    // Visitar la app — el mock de /api/me hará que aparezca autenticado
    cy.visit('/');
    cy.wait('@getMe');
    cy.get('[data-testid="workspace-main"]', { timeout: 15000 }).should('be.visible');
  });

  it('permite navegar al módulo de notas y ver el panel', () => {
    // Click en el ítem de navegación del módulo de notas
    cy.get('[data-testid="nav-curricular_notas"]').should('be.visible').click();

    // Esperar carga de asignaciones del docente
    cy.wait('@getAulasDocente');

    // El panel de notas debe ser visible (el h1 es sr-only, así que verificamos exist o el header)
    cy.get('h1').contains(/notas/i).should('exist');
    cy.screenshot('evidencia_notas_registro_pass');
  });
});
