<?php

namespace App\Http\Controllers\Api\Curricular;

use App\DTO\Curricular\AulaEvaluacionContext;
use App\Enums\Curricular\EvalBimComponenteTipo;
use App\Http\Controllers\Controller;
use App\Http\Requests\Curricular\BulkEvaluacionBimestralRequest;
use App\Http\Requests\Curricular\EvaluacionBimestralConfigQueryRequest;
use App\Http\Requests\Curricular\StoreEvalBimComponenteRequest;
use App\Http\Requests\Curricular\StoreEvalBimEtaRequest;
use App\Http\Requests\Curricular\UpdateEvalBimComponenteRequest;
use App\Http\Requests\Curricular\UpdateEvalBimEtaRequest;
use App\Models\Curricular\DocenteCursoAula;
use App\Models\Curricular\EvalBimComponente;
use App\Models\Curricular\EvalBimEtaItem;
use App\Models\Curricular\EvalBimResultado;
use App\Models\Curricular\PeriodoAcademico;
use App\Models\Estudiante;
use App\Services\Curricular\CatalogoNivelGrado;
use App\Services\Curricular\CurricularNotasAuthService;
use App\Services\Curricular\EvaluacionBimestral\EvaluacionBimestralBulkService;
use App\Services\Curricular\EvaluacionBimestral\EvaluacionBimestralConfiguracionService;
use App\Services\Curricular\EvaluacionBimestral\EvaluacionBimestralFormularioService;
use App\Services\Curricular\EvaluacionBimestral\EvaluacionComponentesResolver;
use App\Services\Curricular\EvaluacionBimestral\EvalBimResultadoPersistService;
use App\Services\Curricular\EvaluacionBimestral\EscalaLogroService;
use App\Services\Curricular\EvaluacionBimestral\PesosComponentesService;
use App\Services\Curricular\EvaluacionBimestral\PesosEtaInternosService;
use App\Services\Curricular\EquivalenciaGradoService;
use App\Services\Curricular\EstudianteAsignacionDocenteValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EvaluacionBimestralController extends Controller
{
    public function __construct(
        private readonly EvaluacionComponentesResolver $componentesResolver = new EvaluacionComponentesResolver,
        private readonly EvaluacionBimestralConfiguracionService $configuracionService = new EvaluacionBimestralConfiguracionService,
        private readonly EvaluacionBimestralFormularioService $formularioService = new EvaluacionBimestralFormularioService,
        private readonly EvaluacionBimestralBulkService $bulkService = new EvaluacionBimestralBulkService,
        private readonly EvalBimResultadoPersistService $resultadoPersistService = new EvalBimResultadoPersistService,
        private readonly PesosComponentesService $pesosComponentesService = new PesosComponentesService,
        private readonly PesosEtaInternosService $pesosEtaInternosService = new PesosEtaInternosService,
        private readonly EscalaLogroService $escalaLogroService = new EscalaLogroService,
        private readonly EstudianteAsignacionDocenteValidator $estudianteValidator = new EstudianteAsignacionDocenteValidator,
        private readonly EquivalenciaGradoService $equivalenciaGradoService = new EquivalenciaGradoService,
        private readonly CurricularNotasAuthService $notasAuth = new CurricularNotasAuthService,
    ) {}

    public function config(EvaluacionBimestralConfigQueryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $config = $this->componentesResolver->resolver(
            (int) $data['malla_curso_id'],
            (int) $data['periodo_academico_id'],
        );

        return response()->json([
            'malla_curso_id' => (int) $data['malla_curso_id'],
            'periodo_academico_id' => (int) $data['periodo_academico_id'],
            'componentes' => $config['componentes']->map(fn (EvalBimComponente $c) => [
                'id' => $c->id,
                'tipo' => $c->tipo->value,
                'codigo' => $c->codigo,
                'nombre' => $c->nombre,
                'peso' => (float) $c->peso,
                'orden' => (int) $c->orden,
                'activo' => (bool) $c->activo,
            ])->values(),
            'etas' => $config['eta_items']->map(fn (EvalBimEtaItem $e) => [
                'id' => $e->id,
                'eval_bim_componente_id' => $e->eval_bim_componente_id,
                'nombre' => $e->nombre,
                'peso_interno' => (float) $e->peso_interno,
                'orden' => (int) $e->orden,
                'activo' => (bool) $e->activo,
            ])->values(),
            'escala_logro' => $this->escalaLogroService->listarEscalaActiva(),
        ]);
    }

    public function storeComponente(StoreEvalBimComponenteRequest $request): JsonResponse
    {
        $data = $request->validated();
        $nombre = trim($data['nombre']);

        $duplicado = EvalBimComponente::query()
            ->where('malla_curso_id', $data['malla_curso_id'])
            ->where('periodo_academico_id', $data['periodo_academico_id'])
            ->where('tipo', EvalBimComponenteTipo::Personalizado)
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])
            ->exists();

        if ($duplicado) {
            throw ValidationException::withMessages([
                'nombre' => ['Ya existe un componente personalizado con ese nombre en este curso y bimestre.'],
            ]);
        }

        $componente = $this->configuracionService->crearComponentePersonalizado(
            (int) $data['malla_curso_id'],
            (int) $data['periodo_academico_id'],
            $nombre,
            $this->pesosComponentesService,
        );

        return response()->json($componente->fresh(), 201);
    }

    public function updateComponente(UpdateEvalBimComponenteRequest $request, EvalBimComponente $componente): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['nombre']) && $componente->tipo !== EvalBimComponenteTipo::Personalizado) {
            throw ValidationException::withMessages([
                'nombre' => ['Solo los componentes personalizados permiten cambiar el nombre.'],
            ]);
        }

        if (isset($data['nombre']) && $componente->tipo === EvalBimComponenteTipo::Personalizado) {
            $nombre = trim($data['nombre']);
            $duplicado = EvalBimComponente::query()
                ->where('malla_curso_id', $componente->malla_curso_id)
                ->where('periodo_academico_id', $componente->periodo_academico_id)
                ->where('tipo', EvalBimComponenteTipo::Personalizado)
                ->where('id', '!=', $componente->id)
                ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])
                ->exists();
            if ($duplicado) {
                throw ValidationException::withMessages([
                    'nombre' => ['Ya existe un componente personalizado con ese nombre en este curso y bimestre.'],
                ]);
            }
        }

        $activoAnterior = (bool) $componente->activo;
        $componente->fill($data);
        $componente->save();

        if (array_key_exists('activo', $data) && (bool) $data['activo'] !== $activoAnterior) {
            if (! $data['activo']) {
                $this->pesosComponentesService->redistribuirTrasDesactivar(
                    $componente->malla_curso_id,
                    $componente->periodo_academico_id,
                );
            } else {
                $activos = EvalBimComponente::query()
                    ->where('malla_curso_id', $componente->malla_curso_id)
                    ->where('periodo_academico_id', $componente->periodo_academico_id)
                    ->where('activo', true)
                    ->get();
                $this->pesosComponentesService->redistribuirEntreActivos($activos);
            }
        } elseif (array_key_exists('peso', $data)) {
            $pesos = EvalBimComponente::query()
                ->where('malla_curso_id', $componente->malla_curso_id)
                ->where('periodo_academico_id', $componente->periodo_academico_id)
                ->where('activo', true)
                ->pluck('peso', 'id')
                ->map(fn ($p) => (float) $p)
                ->all();
            $this->pesosComponentesService->validarPesosManuales($pesos);
        }

        return response()->json($componente->fresh());
    }

    public function storeEta(StoreEvalBimEtaRequest $request): JsonResponse
    {
        $data = $request->validated();
        $config = $this->componentesResolver->resolver(
            (int) $data['malla_curso_id'],
            (int) $data['periodo_academico_id'],
        );
        $componenteEta = $config['componente_promedio_eta'];
        if ($componenteEta === null) {
            throw ValidationException::withMessages([
                'malla_curso_id' => ['No existe el bloque Promedio ETA para este curso y bimestre.'],
            ]);
        }

        $maxOrden = (int) EvalBimEtaItem::query()
            ->where('eval_bim_componente_id', $componenteEta->id)
            ->max('orden');

        $eta = EvalBimEtaItem::query()->create([
            'eval_bim_componente_id' => $componenteEta->id,
            'nombre' => trim($data['nombre']),
            'peso_interno' => 0,
            'orden' => $maxOrden + 1,
            'activo' => true,
        ]);

        $this->pesosEtaInternosService->redistribuirTrasAgregar($eta, $componenteEta->id);

        return response()->json($eta->fresh(), 201);
    }

    public function updateEta(UpdateEvalBimEtaRequest $request, EvalBimEtaItem $eta): JsonResponse
    {
        $data = $request->validated();
        $activoAnterior = (bool) $eta->activo;
        $eta->fill($data);
        $eta->save();

        if (array_key_exists('activo', $data) && (bool) $data['activo'] !== $activoAnterior) {
            if ($data['activo']) {
                $activos = EvalBimEtaItem::query()
                    ->where('eval_bim_componente_id', $eta->eval_bim_componente_id)
                    ->where('activo', true)
                    ->get();
                $this->pesosEtaInternosService->redistribuirEntreActivos($activos);
            } else {
                $this->pesosEtaInternosService->redistribuirTrasDesactivar($eta->eval_bim_componente_id);
            }
        } elseif (array_key_exists('peso_interno', $data)) {
            $pesos = EvalBimEtaItem::query()
                ->where('eval_bim_componente_id', $eta->eval_bim_componente_id)
                ->where('activo', true)
                ->pluck('peso_interno', 'id')
                ->map(fn ($p) => (float) $p)
                ->all();
            $this->pesosEtaInternosService->validarPesosManuales($pesos);
        }

        return response()->json($eta->fresh());
    }

    public function formulario(Request $request): JsonResponse
    {
        $consultaGlobal = $request->boolean('consulta_global');

        if ($consultaGlobal) {
            if (! $request->user()?->can('ver_notas_academicas')) {
                return response()->json(['message' => 'Permiso denegado.'], 403);
            }

            $data = Validator::make($request->query(), [
                'anio_escolar' => ['required', 'string'],
                'nivel' => ['required', 'string', 'in:'.implode(',', CatalogoNivelGrado::nivelesCurriculares())],
                'sede' => ['required', 'string'],
                'grado' => ['required', 'string'],
                'seccion' => ['required', 'string'],
                'malla_curso_id' => ['required', 'integer', 'exists:malla_cursos,id'],
                'periodo_academico_id' => ['required', 'integer', 'exists:periodos_academicos,id'],
            ])->validate();

            $resultado = $this->formularioService->construirConsulta($data);
            $asignacionActiva = $this->notasAuth->resolverAsignacionActiva($data);

            if ($asignacionActiva !== null && $this->notasAuth->puedeRegistrarEnAsignacion($request->user(), $asignacionActiva)) {
                $resultado['readonly'] = false;
                $resultado['contexto']['asignacion_docente_id'] = $asignacionActiva->id;
            }

            return response()->json($resultado);
        }

        if (! $request->user()?->can('registrar_notas_semanales') && ! $request->user()?->can('ver_notas_academicas')) {
            return response()->json(['message' => 'Permiso denegado.'], 403);
        }

        $data = Validator::make($request->query(), [
            'asignacion_docente_id' => ['required', 'integer', 'exists:docente_curso_aulas,id'],
            'periodo_academico_id' => ['required', 'integer', 'exists:periodos_academicos,id'],
        ])->validate();

        $asignacion = DocenteCursoAula::query()->findOrFail($data['asignacion_docente_id']);

        if (! $this->notasAuth->puedeVerAsignacion($request->user(), $asignacion)) {
            return response()->json(['message' => 'No autorizado para esta asignación.'], 403);
        }

        $resultado = $this->formularioService->construirDocente(
            $asignacion,
            (int) $data['periodo_academico_id'],
        );

        $resultado['readonly'] = ! $this->notasAuth->puedeRegistrarEnAsignacion($request->user(), $asignacion);

        return response()->json($resultado);
    }

    public function bulk(BulkEvaluacionBimestralRequest $request): JsonResponse
    {
        $data = $request->validated();
        $asignacion = DocenteCursoAula::query()->findOrFail($data['asignacion_docente_id']);

        if (! $this->notasAuth->puedeRegistrarEnAsignacion($request->user(), $asignacion)) {
            return response()->json(['message' => 'Solo puede registrar evaluación bimestral en sus asignaciones activas.'], 403);
        }

        $resultado = $this->bulkService->registrar(
            $request->user(),
            $asignacion,
            (int) $data['periodo_academico_id'],
            $data['registros_por_estudiante'],
        );

        return response()->json([
            'resultados' => collect($resultado['resultados'])->map(fn (EvalBimResultado $r) => [
                'estudiante_id' => $r->estudiante_id,
                'promedio_criterios' => $r->promedio_criterios !== null ? (float) $r->promedio_criterios : null,
                'oral' => $r->oral !== null ? (float) $r->oral : null,
                'promedio_eta' => $r->promedio_eta !== null ? (float) $r->promedio_eta : null,
                'examen_bimestral' => $r->examen_bimestral !== null ? (float) $r->examen_bimestral : null,
                'nivel_logro_numerico' => $r->nivel_logro_numerico !== null ? (float) $r->nivel_logro_numerico : null,
                'nivel_logro_literal' => $r->nivel_logro_literal,
                'conclusion_descriptiva' => $r->conclusion_descriptiva,
                'estado_calculo' => $r->estado_calculo->value,
                'detalle_json' => $r->detalle_json,
                'calculado_en' => $r->calculado_en?->toIso8601String(),
            ])->values(),
            'advertencias' => $resultado['advertencias'],
        ], 201);
    }

    public function resultados(Request $request): JsonResponse
    {
        if (! $request->user()?->can('ver_notas_academicas')) {
            return response()->json(['message' => 'Permiso denegado.'], 403);
        }

        $data = Validator::make($request->query(), [
            'malla_curso_id' => ['required', 'integer', 'exists:malla_cursos,id'],
            'periodo_academico_id' => ['required', 'integer', 'exists:periodos_academicos,id'],
            'sede' => ['required', 'string'],
            'grado' => ['required', 'string'],
            'seccion' => ['required', 'string'],
            'recalcular' => ['nullable', 'boolean'],
        ])->validate();

        $periodo = PeriodoAcademico::query()->findOrFail($data['periodo_academico_id']);

        $estudiantes = Estudiante::query()
            ->where('anio_escolar', $periodo->anio_escolar)
            ->where('sede', $data['sede'])
            ->where('seccion', $data['seccion'])
            ->where('activo', true)
            ->get()
            ->filter(function (Estudiante $e) use ($data) {
                $equiv = $this->equivalenciaGradoService->aCurricular($e->nivel, $e->grado);

                return $equiv === $data['grado'];
            })
            ->values();

        $aula = new AulaEvaluacionContext(
            mallaCursoId: (int) $data['malla_curso_id'],
            periodoAcademicoId: (int) $data['periodo_academico_id'],
            sede: $data['sede'],
            grado: $data['grado'],
            seccion: $data['seccion'],
            estudianteIds: $estudiantes->pluck('id')->all(),
        );

        if ($request->boolean('recalcular')) {
            $this->resultadoPersistService->recalcularAula($aula);
        }

        $resultados = EvalBimResultado::query()
            ->where('malla_curso_id', $aula->mallaCursoId)
            ->where('periodo_academico_id', $aula->periodoAcademicoId)
            ->where('sede', $aula->sede)
            ->where('grado', $aula->grado)
            ->where('seccion', $aula->seccion)
            ->whereIn('estudiante_id', $aula->estudianteIds)
            ->get();

        return response()->json([
            'resultados' => $resultados->map(fn (EvalBimResultado $r) => [
                'estudiante_id' => $r->estudiante_id,
                'promedio_criterios' => $r->promedio_criterios !== null ? (float) $r->promedio_criterios : null,
                'oral' => $r->oral !== null ? (float) $r->oral : null,
                'promedio_eta' => $r->promedio_eta !== null ? (float) $r->promedio_eta : null,
                'examen_bimestral' => $r->examen_bimestral !== null ? (float) $r->examen_bimestral : null,
                'nivel_logro_numerico' => $r->nivel_logro_numerico !== null ? (float) $r->nivel_logro_numerico : null,
                'nivel_logro_literal' => $r->nivel_logro_literal,
                'conclusion_descriptiva' => $r->conclusion_descriptiva,
                'estado_calculo' => $r->estado_calculo->value,
                'detalle_json' => $r->detalle_json,
                'calculado_en' => $r->calculado_en?->toIso8601String(),
            ])->values(),
        ]);
    }
}
