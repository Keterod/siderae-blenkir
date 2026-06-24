/**
 * asistencia_registro.cy.js
 * Valida: login mockeado → módulo asistencia curricular → panel visible → botón cargar asistencia
 */
describe('Registro de asistencia', () => {
  beforeEach(() => {
    cy.intercept('GET', '/sanctum/csrf-cookie', { statusCode: 204 });

    cy.intercept('GET', '/api/me', {
      statusCode: 200,
      body: {
        usuario: { id: 1, name: 'Docente Test', email: 'docente@test.com' },
        roles: ['docente'],
        permisos: ['registrar_asistencia_curricular', 'ver_asistencia_curricular'],
      },
    }).as('getMe');

    cy.intercept('GET', '/api/curricular/anios-escolares/activo', {
      statusCode: 200,
      body: { anio: 2026, activo: true },
    }).as('getAnioEscolar');

    // Asignaciones del docente (mismo endpoint que notas)
    cy.intercept('GET', '/api/curricular/docente/aulas-cursos*', {
      statusCode: 200,
      body: [
        {
          id: 1,
          grado: '1',
          seccion: 'A',
          nivel: 'Secundaria',
          sede: 'chilca',
          anio_escolar: '2026',
        },
      ],
    }).as('getAulasDocente');

    // Mock formulario asistencia
    cy.intercept('GET', '/api/curricular/asistencias-diarias/formulario*', {
      statusCode: 200,
      body: {
        fecha: '2026-06-24',
        estudiantes: [
          { id: 10, nombres: 'Juan', apellidos: 'Pérez', codigo: 'EST001' },
          { id: 11, nombres: 'María', apellidos: 'García', codigo: 'EST002' },
        ],
        estados: ['presente', 'falta', 'tardanza', 'permiso'],
        asistencias: [],
      },
    }).as('getFormularioAsistencia');

    cy.intercept('POST', '/api/curricular/asistencias-diarias/bulk', {
      statusCode: 200,
      body: { message: 'Asistencia guardada correctamente' },
    }).as('saveAsistencia');

    cy.visit('/');
    cy.wait('@getMe');
    cy.get('[data-testid="workspace-main"]', { timeout: 15000 }).should('be.visible');
  });

  it('permite navegar al módulo de asistencia y ver el panel', () => {
    cy.get('[data-testid="nav-curricular_asistencia"]').should('be.visible').click();

    // El panel principal de asistencia debe aparecer
    cy.get('[data-testid="asistencia-curricular-panel"]', { timeout: 10000 }).should('be.visible');

    // El botón de cargar asistencia debe estar disponible
    cy.get('[data-testid="asistencia-cargar"]').should('exist');
    cy.screenshot('evidencia_asistencia_registro_pass');
  });
});
