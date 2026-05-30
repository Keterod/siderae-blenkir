<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RestablecerContrasenaUsuarioRequest;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Models\User;
use App\Services\UsuarioGestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    public function __construct(
        private readonly UsuarioGestionService $usuarios,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = User::query()->orderBy('name');

        $search = trim((string) ($request->query('q') ?? $request->query('search') ?? ''));
        if ($search !== '') {
            $query->where(function ($sub) use ($search): void {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('rol')) {
            $rol = (string) $request->query('rol');
            if (Role::where('name', $rol)->where('guard_name', 'web')->exists()) {
                $query->role($rol);
            }
        }

        if ($request->filled('activo')) {
            $filtroActivo = filter_var($request->query('activo'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($filtroActivo !== null) {
                $query->where('activo', $filtroActivo);
            }
        } elseif (! $request->boolean('incluir_inactivos')) {
            $query->where('activo', true);
        }

        $perPage = min(100, max(1, (int) $request->query('per_page', 25)));
        $page = max(1, (int) $request->query('page', 1));

        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginated->items())
            ->map(fn (User $usuario): array => $this->usuarios->serializar($usuario))
            ->values()
            ->all();

        return response()->json([
            'data' => $items,
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total(),
            'last_page' => $paginated->lastPage(),
        ]);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($this->usuarios->serializar($user));
    }

    public function store(StoreUsuarioRequest $request): JsonResponse
    {
        $usuario = $this->usuarios->crear($request->validated());

        activity()
            ->causedBy($request->user())
            ->performedOn($usuario)
            ->withProperties([
                'accion' => 'usuario.creado',
                'usuario_id' => $usuario->id,
                'email' => $usuario->email,
                'rol' => $usuario->getRoleNames()->first(),
            ])
            ->log('usuario.creado');

        return response()->json($this->usuarios->serializar($usuario), 201);
    }

    public function update(UpdateUsuarioRequest $request, User $user): JsonResponse
    {
        $usuario = $this->usuarios->actualizar($user, $request->validated(), $request->user());

        activity()
            ->causedBy($request->user())
            ->performedOn($usuario)
            ->withProperties([
                'accion' => 'usuario.actualizado',
                'usuario_id' => $usuario->id,
                'rol' => $usuario->getRoleNames()->first(),
            ])
            ->log('usuario.actualizado');

        return response()->json($this->usuarios->serializar($usuario));
    }

    public function activar(Request $request, User $user): JsonResponse
    {
        $usuario = $this->usuarios->activar($user);

        activity()
            ->causedBy($request->user())
            ->performedOn($usuario)
            ->withProperties([
                'accion' => 'usuario.activado',
                'usuario_id' => $usuario->id,
            ])
            ->log('usuario.activado');

        return response()->json($this->usuarios->serializar($usuario));
    }

    public function desactivar(Request $request, User $user): JsonResponse
    {
        $usuario = $this->usuarios->desactivar($user, $request->user());

        activity()
            ->causedBy($request->user())
            ->performedOn($usuario)
            ->withProperties([
                'accion' => 'usuario.desactivado',
                'usuario_id' => $usuario->id,
            ])
            ->log('usuario.desactivado');

        return response()->json($this->usuarios->serializar($usuario));
    }

    public function restablecerContrasena(
        RestablecerContrasenaUsuarioRequest $request,
        User $user,
    ): JsonResponse {
        $usuario = $this->usuarios->restablecerContrasena($user, $request->validated('password'));

        activity()
            ->causedBy($request->user())
            ->performedOn($usuario)
            ->withProperties([
                'accion' => 'usuario.contrasena_restablecida',
                'usuario_id' => $usuario->id,
            ])
            ->log('usuario.contrasena_restablecida');

        return response()->json([
            'message' => 'Contraseña restablecida correctamente.',
            'usuario' => $this->usuarios->serializar($usuario),
        ]);
    }
}
