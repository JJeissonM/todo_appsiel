<?php

namespace App\Matriculas\Services;

use App\Matriculas\Responsableestudiante;
use App\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class TutorAcademicoService
{
    public const SESSION_KEY = 'academico_estudiante_tutor_estudiante_id';

    public function obtenerEstudiantesParaTutor($user)
    {
        if (is_null($user)) {
            return collect();
        }

        $tercero = $user->tercero;
        if (is_null($tercero)) {
            return collect();
        }

        $responsables = Responsableestudiante::with('estudiante.tercero')
            ->where('tercero_id', $tercero->id)
            ->where('tiporesponsable_id', 3)
            ->get();

        $estudiantes = collect();
        foreach ($responsables as $responsable) {
            if (!is_null($responsable->estudiante)) {
                $estudiantes->push($responsable->estudiante);
            }
        }

        return $estudiantes->unique('id')->sortBy(function ($estudiante) {
            return $estudiante->nombre_completo ?? $estudiante->descripcion;
        })->values();
    }

    public function resolverEstudianteParaTutor($user, $estudianteId = null)
    {
        $estudiantes = $this->obtenerEstudiantesParaTutor($user);
        if ($estudiantes->isEmpty()) {
            session()->forget(self::SESSION_KEY);
            return null;
        }

        $estudianteId = $estudianteId ?: session(self::SESSION_KEY);

        $seleccionado = $estudiantes->filter(function ($item) use ($estudianteId) {
            return $item->id == (int)$estudianteId;
        })->first();
        if (is_null($seleccionado)) {
            $seleccionado = $estudiantes->first();
        }

        if (!is_null($seleccionado)) {
            session([self::SESSION_KEY => $seleccionado->id]);
        }

        return $seleccionado;
    }

    public function crearUsuarioDesdeResponsable(Responsableestudiante $responsable)
    {
        if (is_null($responsable)) {
            return [
                'success' => false,
                'message' => 'Responsable no encontrado.',
            ];
        }

        if ($responsable->tiporesponsable_id != 3) {
            return [
                'success' => false,
                'message' => 'Solo los responsables financieros pueden ser tutores.',
            ];
        }

        $tercero = $responsable->tercero;
        if (is_null($tercero)) {
            return [
                'success' => false,
                'message' => 'No existe información del tercero asociado.',
            ];
        }

        if (empty($tercero->email)) {
            return [
                'success' => false,
                'message' => 'El responsable financiero requiere un correo electrónico para crear el usuario.',
            ];
        }

        $role = Role::firstOrCreate(['name' => 'Tutor de estudiante']);

        $usuario = User::where('email', $tercero->email)->first();
        $password = null;

        if ($usuario) {
            if (!$usuario->hasRole($role)) {
                $usuario->assignRole($role);
            }
            $mensaje = 'El usuario ya existía y ahora tiene el rol Tutor de estudiante.';
        } else {
            $nombre = $tercero->descripcion ?: trim($tercero->nombre1 . ' ' . $tercero->otros_nombres . ' ' . $tercero->apellido1 . ' ' . $tercero->apellido2);
            $nombre = $nombre ?: $tercero->numero_identificacion;
            $password = Str::random(10);
            $usuario = User::crear_y_asignar_role($nombre, $tercero->email, $role->id, $password);

            if (is_null($usuario)) {
                return [
                    'success' => false,
                    'message' => 'Ya existe un usuario con el correo señalado.',
                ];
            }

            $mensaje = 'Usuario Tutor de estudiante creado correctamente.';
        }

        $tercero->user_id = $usuario->id;
        $tercero->save();

        return [
            'success' => true,
            'message' => $mensaje,
            'user' => $usuario,
            'password' => $password,
        ];
    }
}


