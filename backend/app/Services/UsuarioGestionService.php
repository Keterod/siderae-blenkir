<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UsuarioGestionService
{
    public const ROL_ADMINISTRADOR = 'administrador';

    /** @var list<string> */
    public const ROLES_PERMITIDOS = [
        'administrador',
        'docente',
        'coordinador_academico',
        'psicologo_tutor',
        'directivo',
    ];

    public function crear(array $datos): User
    {
        $rol = $this->validarRol($datos['rol']);

        $usuario = User::query()->create([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'password' => $datos['password'],
            'activo' => $datos['activo'] ?? true,
        ]);

        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    public function actualizar(User $usuario, array $datos, ?Authenticatable $actor): User
    {
        if (array_key_exists('name', $datos)) {
            $usuario->name = $datos['name'];
        }

        if (array_key_exists('email', $datos)) {
            $usuario->email = $datos['email'];
        }

        if (array_key_exists('rol', $datos)) {
            $nuevoRol = $this->validarRol($datos['rol']);
            $this->asegurarPuedeCambiarRolAdministrador($usuario, $nuevoRol, $actor);
            $usuario->syncRoles([$nuevoRol]);
        }

        $usuario->save();

        return $usuario->fresh();
    }

    public function activar(User $usuario): User
    {
        $usuario->activo = true;
        $usuario->save();

        return $usuario->fresh();
    }

    public function desactivar(User $usuario, ?Authenticatable $actor): User
    {
        $this->asegurarPuedeDesactivar($usuario, $actor);

        $usuario->activo = false;
        $usuario->save();

        return $usuario->fresh();
    }

    public function restablecerContrasena(User $usuario, string $password): User
    {
        $usuario->password = $password;
        $usuario->save();

        return $usuario->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function serializar(User $usuario): array
    {
        return [
            'id' => $usuario->id,
            'name' => $usuario->name,
            'email' => $usuario->email,
            'activo' => (bool) $usuario->activo,
            'rol' => $usuario->getRoleNames()->first(),
            'created_at' => $usuario->created_at,
            'updated_at' => $usuario->updated_at,
        ];
    }

    public function validarRol(string $rol): string
    {
        if (! in_array($rol, self::ROLES_PERMITIDOS, true)) {
            throw ValidationException::withMessages([
                'rol' => ['El rol indicado no es válido.'],
            ]);
        }

        Role::findByName($rol, 'web');

        return $rol;
    }

    public function asegurarPuedeDesactivar(User $usuario, ?Authenticatable $actor): void
    {
        if ($actor instanceof User && (int) $actor->id === (int) $usuario->id) {
            throw ValidationException::withMessages([
                'usuario' => ['No puede desactivar su propia cuenta.'],
            ]);
        }

        if ($usuario->hasRole(self::ROL_ADMINISTRADOR) && $usuario->activo) {
            $activos = $this->contarAdministradoresActivos();

            if ($activos <= 1) {
                throw ValidationException::withMessages([
                    'usuario' => ['No puede desactivar al último administrador activo del sistema.'],
                ]);
            }
        }
    }

    public function asegurarPuedeCambiarRolAdministrador(User $usuario, string $nuevoRol, ?Authenticatable $actor): void
    {
        if (! $usuario->hasRole(self::ROL_ADMINISTRADOR) || ! $usuario->activo) {
            return;
        }

        if ($nuevoRol === self::ROL_ADMINISTRADOR) {
            return;
        }

        if ($this->contarAdministradoresActivos() <= 1) {
            throw ValidationException::withMessages([
                'rol' => ['No puede cambiar el rol del último administrador activo del sistema.'],
            ]);
        }

        if ($actor instanceof User && (int) $actor->id === (int) $usuario->id) {
            throw ValidationException::withMessages([
                'rol' => ['No puede quitarse el rol de administrador a sí mismo.'],
            ]);
        }
    }

    public function contarAdministradoresActivos(): int
    {
        return User::role(self::ROL_ADMINISTRADOR)
            ->where('activo', true)
            ->count();
    }
}
