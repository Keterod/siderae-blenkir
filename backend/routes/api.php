<?php

use App\Http\Controllers\Api\AlertaCierreController;
use App\Http\Controllers\Api\AlertaController;
use App\Http\Controllers\Api\AsistenciaBatchController;
use App\Http\Controllers\Api\AsistenciaController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EstudianteController;
use App\Http\Controllers\Api\MateriaController;
use App\Http\Controllers\Api\IntervencionController;
use App\Http\Controllers\Api\NotaBatchController;
use App\Http\Controllers\Api\NotaController;
use App\Http\Controllers\Api\ProcesarRiesgoController;
use App\Http\Controllers\Api\Curricular\AsignacionDocenteController;
use App\Http\Controllers\Api\Curricular\AnioEscolarController;
use App\Http\Controllers\Api\Curricular\AsistenciaDiariaController;
use App\Http\Controllers\Api\Curricular\CatalogoCurricularController;
use App\Http\Controllers\Api\Curricular\CompetenciaCapacidadController;
use App\Http\Controllers\Api\Curricular\ComponenteCalificacionController;
use App\Http\Controllers\Api\Curricular\ConfiguracionPesoEvaluacionController;
use App\Http\Controllers\Api\Curricular\DocenteAulaCurricularController;
use App\Http\Controllers\Api\Curricular\EvaluacionBimestralController;
use App\Http\Controllers\Api\Curricular\MallaCurricularController;
use App\Http\Controllers\Api\Curricular\NotaSemanalController;
use App\Http\Controllers\Api\Curricular\PeriodoAcademicoAdminController;
use App\Http\Controllers\Api\Curricular\ResumenAcademicoController;
use App\Http\Controllers\Api\Curricular\SeccionAulaController;
use App\Http\Controllers\Api\Curricular\TemaSemanalController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\VariableSocioeconomicaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'SIDERAE Backend',
    ]);
});

Route::middleware(['auth:sanctum'])->get('/me', function (Request $request) {
    $user = $request->user();

    return response()->json([
        'usuario' => $user,
        'roles' => $user->getRoleNames()->values(),
        'permisos' => $user->getAllPermissions()->pluck('name')->values(),
    ]);
});

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum', 'permission:gestionar_usuarios'])
    ->group(function (): void {
        Route::get('/usuarios', [UsuarioController::class, 'index']);
        Route::get('/usuarios/{user}', [UsuarioController::class, 'show']);
        Route::post('/usuarios', [UsuarioController::class, 'store']);
        Route::patch('/usuarios/{user}', [UsuarioController::class, 'update']);
        Route::patch('/usuarios/{user}/activar', [UsuarioController::class, 'activar']);
        Route::patch('/usuarios/{user}/desactivar', [UsuarioController::class, 'desactivar']);
        Route::post('/usuarios/{user}/restablecer-contrasena', [UsuarioController::class, 'restablecerContrasena']);
    });

Route::middleware(['auth:sanctum', 'permission:ver_dashboard'])
    ->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/export', [DashboardController::class, 'export']);
    });

Route::middleware(['auth:sanctum', 'permission:gestionar_materias|registrar_datos_academicos'])
    ->group(function (): void {
        Route::get('/materias', [MateriaController::class, 'index']);
        Route::get('/materias/{materia}', [MateriaController::class, 'show']);
    });

Route::middleware(['auth:sanctum', 'permission:gestionar_materias'])
    ->group(function (): void {
        Route::post('/materias', [MateriaController::class, 'store']);
        Route::match(['put', 'patch'], '/materias/{materia}', [MateriaController::class, 'update']);
        Route::patch('/materias/{materia}/desactivar', [MateriaController::class, 'desactivar']);
        Route::patch('/materias/{materia}/activar', [MateriaController::class, 'activar']);
    });

Route::middleware(['auth:sanctum', 'permission:gestionar_estudiantes|registrar_datos_academicos'])
    ->group(function (): void {
        Route::get('/estudiantes', [EstudianteController::class, 'index']);
        Route::get('/estudiantes/{estudiante}', [EstudianteController::class, 'show']);
    });

Route::middleware(['auth:sanctum', 'permission:gestionar_estudiantes'])
    ->group(function (): void {
        Route::post('/estudiantes', [EstudianteController::class, 'store']);
        Route::match(['put', 'patch'], '/estudiantes/{estudiante}', [EstudianteController::class, 'update']);
    });

Route::middleware(['auth:sanctum', 'permission:registrar_datos_academicos'])
    ->group(function (): void {
        Route::post('/notas/lote', [NotaBatchController::class, 'store']);
        Route::post('/asistencias/lote', [AsistenciaBatchController::class, 'store']);

        Route::get('estudiantes/{estudiante}/notas', [NotaController::class, 'index']);
        Route::post('estudiantes/{estudiante}/notas', [NotaController::class, 'store']);

        Route::get('estudiantes/{estudiante}/asistencias', [AsistenciaController::class, 'index']);
        Route::post('estudiantes/{estudiante}/asistencias', [AsistenciaController::class, 'store']);

        Route::get('estudiantes/{estudiante}/variables-socioeconomicas', [VariableSocioeconomicaController::class, 'index']);
        Route::post('estudiantes/{estudiante}/variables-socioeconomicas', [VariableSocioeconomicaController::class, 'store']);
    });

Route::middleware(['auth:sanctum', 'permission:procesar_riesgo'])
    ->post('estudiantes/{estudiante}/procesar-riesgo', [ProcesarRiesgoController::class, 'store']);

Route::middleware(['auth:sanctum', 'permission:ver_alertas'])
    ->group(function (): void {
        Route::get('alertas', [AlertaController::class, 'index']);
        Route::get('alertas/{alerta}', [AlertaController::class, 'show']);
    });

Route::middleware(['auth:sanctum', 'permission:registrar_intervencion'])
    ->group(function (): void {
        Route::post('alertas/{alerta}/intervenciones', [IntervencionController::class, 'store']);
        Route::post('alertas/{alerta}/cerrar', [AlertaCierreController::class, 'store']);
    });

Route::middleware(['auth:sanctum'])->prefix('curricular')->group(function (): void {
    Route::get('/catalogo/niveles-grados', [CatalogoCurricularController::class, 'nivelesGrados']);

    Route::middleware(['permission:ver_malla_curricular|registrar_notas_semanales|ver_notas_academicas|registrar_asistencia_curricular|ver_asistencia_curricular|gestionar_calendario_academico'])->group(function (): void {
        Route::get('/anios-escolares/activo', [AnioEscolarController::class, 'activo']);
    });

    Route::middleware(['permission:gestionar_calendario_academico'])->group(function (): void {
        Route::get('/anios-escolares', [AnioEscolarController::class, 'index']);
        Route::post('/anios-escolares', [AnioEscolarController::class, 'store']);
        Route::get('/anios-escolares/{anioEscolar}', [AnioEscolarController::class, 'show']);
        Route::patch('/anios-escolares/{anioEscolar}', [AnioEscolarController::class, 'update']);
        Route::post('/anios-escolares/{anioEscolar}/activar', [AnioEscolarController::class, 'activar']);
        Route::post('/anios-escolares/{anioEscolar}/cerrar', [AnioEscolarController::class, 'cerrar']);
        Route::post('/anios-escolares/{anioEscolar}/generar-bimestres', [AnioEscolarController::class, 'generarBimestres']);

        Route::patch('/periodos-academicos/{periodoAcademico}', [PeriodoAcademicoAdminController::class, 'update']);
        Route::post('/periodos-academicos/{periodoAcademico}/marcar-vigente', [PeriodoAcademicoAdminController::class, 'marcarVigente']);
        Route::post('/periodos-academicos/{periodoAcademico}/cerrar', [PeriodoAcademicoAdminController::class, 'cerrar']);
        Route::post('/periodos-academicos/{periodoAcademico}/generar-semanas', [PeriodoAcademicoAdminController::class, 'generarSemanas']);
    });

    Route::middleware(['permission:ver_malla_curricular'])->group(function (): void {
        Route::get('/areas', [CatalogoCurricularController::class, 'areas']);
        Route::get('/areas/{area}/competencias', [CatalogoCurricularController::class, 'competenciasPorArea']);
        Route::get('/competencias/{competencia}/capacidades', [CatalogoCurricularController::class, 'capacidadesPorCompetencia']);
        Route::get('/periodos', [CatalogoCurricularController::class, 'periodos']);
        Route::get('/periodos/{periodo}/semanas', [CatalogoCurricularController::class, 'semanasPorPeriodo']);
        Route::get('/mallas/grado', [MallaCurricularController::class, 'grado']);
        Route::get('/mallas', [MallaCurricularController::class, 'index']);
        Route::get('/mallas/{malla}', [MallaCurricularController::class, 'show']);
        Route::get('/temas', [TemaSemanalController::class, 'index']);
        Route::get('/temas/{temaSemanal}', [TemaSemanalController::class, 'show']);
    });

    Route::middleware(['permission:gestionar_malla_curricular'])->group(function (): void {
        Route::post('/mallas/cargar-plantilla', [MallaCurricularController::class, 'cargarPlantilla']);
        Route::post('/mallas/{malla}/cursos', [MallaCurricularController::class, 'agregarCurso']);
        Route::patch('/mallas/{malla}/cursos/{mallaCurso}', [MallaCurricularController::class, 'actualizarCurso']);
        Route::patch('/mallas/{malla}/cursos/{mallaCurso}/desactivar', [MallaCurricularController::class, 'desactivarCurso']);
        Route::patch('/mallas/{malla}/cursos/{mallaCurso}/reactivar', [MallaCurricularController::class, 'reactivarCurso']);
    });

    Route::middleware(['permission:gestionar_temas_semanales'])->group(function (): void {
        Route::post('/temas', [TemaSemanalController::class, 'store']);
        Route::patch('/temas/{temaSemanal}', [TemaSemanalController::class, 'update']);
        Route::patch('/temas/{temaSemanal}/desactivar', [TemaSemanalController::class, 'desactivar']);
    });

    Route::middleware(['permission:gestionar_competencias_capacidades'])->group(function (): void {
        Route::post('/areas/{area}/competencias', [CompetenciaCapacidadController::class, 'storeCompetencia']);
        Route::patch('/competencias/{competencia}', [CompetenciaCapacidadController::class, 'updateCompetencia']);
        Route::patch('/competencias/{competencia}/desactivar', [CompetenciaCapacidadController::class, 'desactivarCompetencia']);
        Route::patch('/competencias/{competencia}/reactivar', [CompetenciaCapacidadController::class, 'reactivarCompetencia']);
        Route::post('/competencias/{competencia}/capacidades', [CompetenciaCapacidadController::class, 'storeCapacidad']);
        Route::patch('/capacidades/{capacidad}', [CompetenciaCapacidadController::class, 'updateCapacidad']);
        Route::patch('/capacidades/{capacidad}/desactivar', [CompetenciaCapacidadController::class, 'desactivarCapacidad']);
        Route::patch('/capacidades/{capacidad}/reactivar', [CompetenciaCapacidadController::class, 'reactivarCapacidad']);
    });

    Route::middleware(['permission:configurar_pesos_evaluacion'])->group(function (): void {
        Route::get('/pesos/resolver', [ConfiguracionPesoEvaluacionController::class, 'resolver']);
        Route::get('/pesos', [ConfiguracionPesoEvaluacionController::class, 'index']);
        Route::post('/pesos', [ConfiguracionPesoEvaluacionController::class, 'store']);
        Route::patch('/pesos/{configuracionPesoEvaluacion}', [ConfiguracionPesoEvaluacionController::class, 'update']);
        Route::patch('/pesos/{configuracionPesoEvaluacion}/desactivar', [ConfiguracionPesoEvaluacionController::class, 'desactivar']);
    });

    Route::middleware(['permission:gestionar_componentes_calificacion'])->group(function (): void {
        Route::get('/componentes-calificacion/validar-suma', [ComponenteCalificacionController::class, 'validarSuma']);
        Route::get('/componentes-calificacion/por-nivel/{nivel}', [ComponenteCalificacionController::class, 'porNivel']);
        Route::post('/componentes-calificacion/asegurar-defaults', [ComponenteCalificacionController::class, 'asegurarDefaults']);
        Route::post('/componentes-calificacion/reordenar', [ComponenteCalificacionController::class, 'reordenar']);
        Route::get('/componentes-calificacion', [ComponenteCalificacionController::class, 'index']);
        Route::post('/componentes-calificacion', [ComponenteCalificacionController::class, 'store']);
        Route::patch('/componentes-calificacion/{componenteCalificacionNivel}', [ComponenteCalificacionController::class, 'update']);
        Route::patch('/componentes-calificacion/{componenteCalificacionNivel}/desactivar', [ComponenteCalificacionController::class, 'desactivar']);
        Route::patch('/componentes-calificacion/{componenteCalificacionNivel}/reactivar', [ComponenteCalificacionController::class, 'reactivar']);
    });

    Route::middleware([
        'permission:gestionar_secciones_aulas|gestionar_estudiantes|gestionar_asignaciones_docente|registrar_notas_semanales|ver_notas_academicas|registrar_asistencia_curricular|ver_asistencia_curricular',
    ])->group(function (): void {
        Route::get('/secciones-aulas', [SeccionAulaController::class, 'index']);
    });

    Route::middleware(['permission:gestionar_secciones_aulas'])->group(function (): void {
        Route::post('/secciones-aulas', [SeccionAulaController::class, 'store']);
        Route::patch('/secciones-aulas/{seccionAula}', [SeccionAulaController::class, 'update']);
        Route::patch('/secciones-aulas/{seccionAula}/desactivar', [SeccionAulaController::class, 'desactivar']);
        Route::patch('/secciones-aulas/{seccionAula}/reactivar', [SeccionAulaController::class, 'reactivar']);
    });

    Route::middleware(['permission:gestionar_asignaciones_docente'])->group(function (): void {
        Route::get('/docentes', [AsignacionDocenteController::class, 'docentes']);
        Route::get('/asignaciones-docente', [AsignacionDocenteController::class, 'index']);
        Route::get('/asignaciones-docente/docente/{docente}', [AsignacionDocenteController::class, 'porDocente']);
        Route::post('/asignaciones-docente', [AsignacionDocenteController::class, 'store']);
        Route::post('/asignaciones-docente/bulk', [AsignacionDocenteController::class, 'bulk']);
        Route::patch('/asignaciones-docente/{docenteCursoAula}/desactivar', [AsignacionDocenteController::class, 'desactivar']);
    });

    Route::middleware(['permission:registrar_notas_semanales'])->group(function (): void {
        Route::get('/docente/aulas-cursos', [DocenteAulaCurricularController::class, 'aulasCursos']);
    });

    Route::middleware(['permission:registrar_notas_semanales|ver_notas_academicas'])->group(function (): void {
        Route::get('/notas-semanales/formulario', [NotaSemanalController::class, 'formulario']);
        Route::get('/notas-semanales/plantilla-excel', [NotaSemanalController::class, 'plantillaExcel']);
        Route::get('/notas-semanales/contextos-aula', [DocenteAulaCurricularController::class, 'contextosAulaConsulta']);
    });

    Route::middleware(['permission:registrar_notas_semanales'])->group(function (): void {
        Route::post('/notas-semanales/bulk', [NotaSemanalController::class, 'bulk']);
        Route::post('/notas-semanales/importar-excel', [NotaSemanalController::class, 'importarExcel']);
    });

    Route::middleware(['permission:ver_notas_academicas'])->group(function (): void {
        Route::get('/estudiantes/{estudiante}/resumen-academico', [ResumenAcademicoController::class, 'show']);
    });

    Route::middleware(['permission:ver_notas_academicas|configurar_evaluacion_bimestral'])->group(function (): void {
        Route::get('/evaluacion-bimestral/config', [EvaluacionBimestralController::class, 'config']);
        Route::get('/evaluacion-bimestral/resultados', [EvaluacionBimestralController::class, 'resultados']);
    });

    Route::middleware(['permission:configurar_evaluacion_bimestral'])->group(function (): void {
        Route::post('/evaluacion-bimestral/componentes', [EvaluacionBimestralController::class, 'storeComponente']);
        Route::patch('/evaluacion-bimestral/componentes/{componente}', [EvaluacionBimestralController::class, 'updateComponente']);
        Route::post('/evaluacion-bimestral/etas', [EvaluacionBimestralController::class, 'storeEta']);
        Route::patch('/evaluacion-bimestral/etas/{eta}', [EvaluacionBimestralController::class, 'updateEta']);
    });

    Route::middleware(['permission:registrar_notas_semanales|ver_notas_academicas'])->group(function (): void {
        Route::get('/evaluacion-bimestral/formulario', [EvaluacionBimestralController::class, 'formulario']);
    });

    Route::middleware(['permission:registrar_notas_semanales'])->group(function (): void {
        Route::post('/evaluacion-bimestral/bulk', [EvaluacionBimestralController::class, 'bulk']);
    });

    Route::middleware(['permission:registrar_asistencia_curricular|ver_asistencia_curricular'])->group(function (): void {
        Route::get('/asistencias-diarias/formulario', [AsistenciaDiariaController::class, 'formulario']);
        Route::get('/asistencias-diarias/resumen', [AsistenciaDiariaController::class, 'resumen']);
    });

    Route::middleware(['permission:registrar_asistencia_curricular'])->group(function (): void {
        Route::post('/asistencias-diarias/bulk', [AsistenciaDiariaController::class, 'bulk']);
    });
});
