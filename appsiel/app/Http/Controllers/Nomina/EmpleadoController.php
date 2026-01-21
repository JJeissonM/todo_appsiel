<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use App\Nomina\NomContrato;
use App\Nomina\NomDocEncabezado;
use App\Sistema\Aplicacion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class EmpleadoController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Empleado']);
    }

    /**
     * Retorna el contrato asociado al usuario autenticado (el más reciente).
     */
    protected function getContratoPorUsuario()
    {
        $user = Auth::user();

        if ( is_null($user) || is_null($user->tercero) )
        {
            return null;
        }

        return NomContrato::where('core_tercero_id', $user->tercero->id)
            ->orderByRaw("FIELD(estado, 'Activo', 'Retirado') DESC")
            ->orderBy('fecha_ingreso', 'DESC')
            ->first();
    }

    public function index()
    {
        $app = Aplicacion::find(Input::get('id'));
        $miga_pan = [
            [
                'url' => $app ? url($app->app . '?id=' . Input::get('id')) : url('inicio'),
                'etiqueta' => $app ? $app->descripcion : 'Nomina'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Mi nomina'
            ]
        ];

        $contrato = $this->getContratoPorUsuario();
        $documentos = collect([]);

        if ( !is_null($contrato) )
        {
            $documentos = NomDocEncabezado::whereHas('empleados', function ($query) use ($contrato) {
                    $query->where('nom_contrato_id', $contrato->id);
                })
                ->orderBy('fecha', 'DESC')
                ->get();
        }

        return view('nomina.empleado.index', compact('miga_pan', 'contrato', 'documentos'));
    }

    public function desprendible($nom_doc_encabezado_id)
    {
        $contrato = $this->getContratoPorUsuario();

        if ( is_null($contrato) )
        {
            abort(404, 'No se encontró un contrato asociado al usuario.');
        }

        $documento = NomDocEncabezado::findOrFail($nom_doc_encabezado_id);

        if ( !$documento->empleados()->where('nom_contrato_id', $contrato->id)->exists() )
        {
            abort(403);
        }

        $vista = View::make('nomina.reportes.tabla_desprendibles_pagos', [
            'documento' => $documento,
            'empleado' => $contrato
        ])->render();

        return response()->json(['html' => $vista]);
    }
}
