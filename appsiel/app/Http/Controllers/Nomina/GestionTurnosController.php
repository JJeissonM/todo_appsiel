<?php

namespace App\Http\Controllers\Nomina;

use Illuminate\Http\Request;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;


// Modelos
use App\Core\TipoDocApp;
use App\Sistema\Modelo;
use App\Core\Empresa;

use App\Nomina\NomConcepto;
use App\Nomina\NomDocEncabezado;
use App\Nomina\NomDocRegistro;
use App\Nomina\NomContrato;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;
use App\Nomina\TipoTurno;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

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

    /*
        Formulario para registrar los valores a liquidar del concepto y el documento seleccionado
    */
    public function create()
    {
        $empleados = NomContrato::where([
                                            ['estado','=','Activo'],
                                            ['clase_contrato','=','por_turnos']
                                        ])
                                ->get();

        $fecha = date('Y-m-d');
        if ( Input::get('fecha') != null )
        {
            $fecha = Input::get('fecha');
        }

        $miga_pan = [
                        ['url'=> 'nomina?id=' . Input::get('id') ,'etiqueta'=>'Nómina'],
                        ['url'=> 'web?id=' . Input::get('id') ,'&id_modelo=337','etiqueta'=>'Registros de Turnos'],
                        ['url'=> 'NO','etiqueta'=>'Ingresar datos']
                    ];
         
        $tipos_turnos = TipoTurno::opciones_campo_select();
        return view( 'nomina.turnos.create_registros', compact('miga_pan', 'empleados', 'tipos_turnos', 'fecha') );
    }

    public static function get_array_tabla_registros( $nom_concepto_id, $nom_doc_encabezado_id, $ruta )
    {
        // Se obtienen las descripciones del concepto y documento de nómina
        $concepto = NomConcepto::find( $nom_concepto_id );
        $documento = NomDocEncabezado::find( $nom_doc_encabezado_id );

        // Se obtienen los Empleados del documento
        $empleados = $documento->empleados;
        
        // Verificar si ya se han ingresado registro para ese concepto y documento
        $cant_registros = NomDocRegistro::where([
                                                'nom_doc_encabezado_id'=>$nom_doc_encabezado_id,
                                                'nom_concepto_id'=>$nom_concepto_id
                                                ])
                                            ->count();

        // Si ya tienen al menos un empleado con concepto ingresado
        if( $cant_registros > 0 )
        {
            
            // Se crea un vector con los valores de los conceptos para modificarlas
            $vec_registros = array();
            $i=0;
            foreach($empleados as $empleado)
            {
                $vec_empleados[$i]['core_tercero_id'] = $empleado->tercero->id;
                $vec_empleados[$i]['nombre'] = $empleado->tercero->descripcion;
                
                // Se verifica si cada persona tiene valor ingresado
                $datos = NomDocRegistro::where(['nom_doc_encabezado_id'=>$nom_doc_encabezado_id,
                                                'nom_concepto_id'=>$nom_concepto_id,
                                                'core_tercero_id'=>$empleado->core_tercero_id])
                                        ->get()
                                        ->first();

                $vec_empleados[$i]['valor_concepto'] = 0;
                $vec_empleados[$i]['cantidad_horas'] = 0;
                $vec_empleados[$i]['nom_registro_id'] = "no";
                
                // Si el persona tiene calificacion se envian los datos de esta para editar
                if( !is_null($datos) )
                {
                    switch ($concepto->naturaleza)
                    {
                        case 'devengo':
                            $vec_empleados[$i]['valor_concepto'] = $datos->valor_devengo;
                            break;
                        case 'deduccion':
                            $vec_empleados[$i]['valor_concepto'] = $datos->valor_deduccion;
                            break;
                        
                        default:
                            # code...
                            break;
                    }

                    if ( (float)$concepto->porcentaje_sobre_basico != 0 )
                    {
                        $vec_empleados[$i]['cantidad_horas'] = $datos->cantidad_horas;
                    }

                    $vec_empleados[$i]['nom_registro_id'] = $datos->id;

                }
                
                $i++;
            } // Fin foreach (llenado de array con datos)
            return ['vec_empleados'=>$vec_empleados,
                'cantidad_empleados'=>count($empleados),
                'concepto'=>$concepto,
                'documento'=>$documento,
                'ruta'=>$ruta];
        }else{
            // Si no tienen datos, se crean por primera vez
            return ['empleados'=>$empleados,
                'cantidad_empleados'=>count($empleados),
                'concepto'=>$concepto,
                'documento'=>$documento,
                'ruta'=>$ruta];
        }
    }

    /**
     * Para almacenar los registros de documentos
     *  Normalmente para conceptos tipo Manuales
     */
    public function store(Request $request)
    {
        $datos = [];
        $usuario = Auth::user();

        $concepto = NomConcepto::find($request->nom_concepto_id);
        $documento = NomDocEncabezado::find($request->nom_doc_encabezado_id);

        $datos['nom_doc_encabezado_id'] = $request->nom_doc_encabezado_id;
        $datos['fecha'] = $documento->fecha;
        $datos['core_empresa_id'] = $documento->core_empresa_id;
        $datos['nom_concepto_id'] = $request->nom_concepto_id;
        $datos['estado'] = 'Activo';
        $datos['creado_por'] = $usuario->email;
        $datos['modificado_por'] = '';

        // Guardar los valores para cada persona      
        for( $i=0; $i < (int)$request->cantidad_empleados; $i++)
        {
            if ( isset( $request->valor ) )
            {
                $this->registrar_por_valor( $concepto, $request->input('core_tercero_id.'.$i), $datos, $request->input('valor.'.$i), $documento );
            }

            if ( isset( $request->cantidad_horas ) )
            {
                $this->registrar_por_cantidad_horas( $concepto, $request->input('core_tercero_id.'.$i), $datos, $request->input('cantidad_horas.'.$i) );
            }
        }

        $this->actualizar_totales_documento($documento->id);

        return redirect( 'web?id='.$request->app_id.'&id_modelo='.$request->modelo_id )->with( 'flash_message','Registros CREADOS correctamente. Nómina: '.$documento->descripcion.', Concepto: '.$concepto->descripcion );
    }
    
}