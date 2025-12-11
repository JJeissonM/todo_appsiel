<?php

namespace App\Http\Controllers\Nomina;

use Illuminate\Http\Request;

use App\Http\Controllers\Core\TransaccionController;


use App\Nomina\NomContrato;

use App\Nomina\RegistroTurno;
use App\Nomina\TipoTurno;
use Illuminate\Support\Facades\Input;

class GestionTurnosController extends TransaccionController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $miga_pan = [
                ['url'=>'NO','etiqueta'=>'Nómina']
            ];

        return view( 'nomina.index', compact( 'miga_pan' ) );
    }
    
    /**
     * 
     */
    public function create()
    {
        $empleados = NomContrato::where([
                                            ['estado','=','Activo'],
                                            ['clase_contrato','=','por_turnos']
                                        ])
                                ->get();

        $turnos_activos = TipoTurno::where('estado', 'Activo')
                            ->with('cargos:id')
                            ->orderBy('descripcion')
                            ->get();

        $opciones_generales = ['' => ''];
        $turnos_por_cargo = [];
        foreach ($turnos_activos as $turno)
        {
            $label = $turno->descripcion . ' ($' . number_format($turno->valor, 0, ',', '.') . ')';

            if ($turno->cargos->isEmpty()) {
                $opciones_generales[$turno->id] = $label;
            }

            foreach ($turno->cargos as $cargo) {
                $turnos_por_cargo[$cargo->id][$turno->id] = $label;
            }
        }

        $fecha = date('Y-m-d');
        if ( Input::get('fecha') != null )
        {
            $fecha = Input::get('fecha');
        }

        $turnos_ingresados = RegistroTurno::where('fecha', '=', $fecha)->get();

        $action = 'create';
        if ( $turnos_ingresados->count() > 0) {
            $action = 'edit';
        }

        foreach ($empleados as $empleado)
        {
            $turno_ingresado = $turnos_ingresados->where('contrato_id', $empleado->id)->first();
        
            if ( $action == 'create')
            {
                $empleado->tipo_turno_id = $empleado->turno_default_id;
                
                $turno_default = $empleado->turno_default;
                if ($turno_default != null) {
                    $empleado->checkin_time_1 = $turno_default->checkin_time_1;
                    $empleado->checkout_time_1 = $turno_default->checkout_time_1;
                    $empleado->checkin_time_2 = $turno_default->checkin_time_2;
                    $empleado->checkout_time_2 = $turno_default->checkout_time_2;
                }
            }
            
            if ( $turno_ingresado != null) {
                $empleado->tipo_turno_id = $turno_ingresado->tipo_turno_id;
                $empleado->checkin_time_1 = $turno_ingresado->checkin_time_1;
                $empleado->checkout_time_1 = $turno_ingresado->checkout_time_1;
                $empleado->checkin_time_2 = $turno_ingresado->checkin_time_2;
                $empleado->checkout_time_2 = $turno_ingresado->checkout_time_2;
            }

            $empleado->anotacion = $turnos_ingresados->where('contrato_id', $empleado->id)->first()->anotacion ?? null;
            $empleado->estado_turno = $turnos_ingresados->where('contrato_id', $empleado->id)->first()->estado ?? null;

            $empleado->tipos_turno_options = ['' => ''] + ($turnos_por_cargo[$empleado->cargo_id] ?? $opciones_generales);
        }

        $miga_pan = [
                        ['url'=> 'nomina?id=' . Input::get('id') ,'etiqueta'=>'Nómina'],
                        ['url'=> 'web?id=' . Input::get('id') . '&id_modelo=337', 'etiqueta'=>'Registros de Turnos'],
                        ['url'=> 'NO','etiqueta'=>'Registrar datos']
                    ];
         
        $tipos_turnos = $opciones_generales;
        return view( 'nomina.turnos.create_registros', compact('miga_pan', 'empleados', 'tipos_turnos', 'fecha', 'action') );
    }

    /**
     * 
     */
    public function store(Request $request)
    {
        $datos = [];

        $cantidad_empleados = count( $request->contrato_id );

        $datos['fecha'] = $request->fecha;
        $datos['estado'] = 'Pendiente';

        // Guardar los valores para cada persona      
        for( $i=0; $i < $cantidad_empleados; $i++)
        {
            $datos['contrato_id'] = (int)$request->input('contrato_id.' . $i);
            $datos['tipo_turno_id'] = (int)$request->input('tipo_turno_id.' . $i);

            $datos['valor'] = 0;
            $tipo_turno = TipoTurno::find( $datos['tipo_turno_id'] );
            if ( $tipo_turno != null ) {
                $datos['valor'] = $tipo_turno->valor;
            }            
            
            $datos['anotacion'] = $request->input('anotacion.' . $i);

            $registro_turno = RegistroTurno::where([
                                                    ['contrato_id', '=', $datos['contrato_id']],
                                                    ['fecha', '=', $datos['fecha']]
                                                ])->first();

            if ( $registro_turno == null ) {

                if ( $datos['tipo_turno_id'] != 0)
                {
                    RegistroTurno::create( $datos );
                }
                
                $label = 'Registrados';
            }else{

                if ( $registro_turno->estado == 'Liquidado') {
                    continue;
                }

                if ( $datos['tipo_turno_id'] != 0)
                {
                    $registro_turno->tipo_turno_id = $datos['tipo_turno_id'];
                    $registro_turno->valor = $datos['valor'];
                    $registro_turno->anotacion = $datos['anotacion'];
                    $registro_turno->save();
                }else{
                    $registro_turno->delete();
                }
                
                $label = 'Actualizados';
            }
        }

        return redirect( 'web?id=' . $request->app_id . '&id_modelo=' . $request->modelo_id )->with( 'flash_message','Turnos ' . $label . ' correctamente. Fecha: ' . $request->fecha );
    }
    
}
