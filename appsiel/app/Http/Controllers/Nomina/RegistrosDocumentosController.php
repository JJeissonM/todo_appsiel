<?php

namespace App\Http\Controllers\Nomina;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;
use DB;
use View;
use Lava;
use Input;
use Form;
use NumerosEnLetras;

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
use App\Nomina\NomCuota;
use App\Nomina\NomPrestamo;
use App\Nomina\AgrupacionConcepto;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;
use App\Nomina\ModosLiquidacion\ModoLiquidacion; // Facade

class RegistrosDocumentosController extends TransaccionController
{
    protected $total_devengos_empleado = 0;
    protected $total_deducciones_empleado = 0;
    protected $vec_totales = [];
    protected $pos = 0;
    protected $registros_procesados = 0;
    protected $vec_campos;
    protected $array_ids_modos_liquidacion_automaticos = [1, 6, 3, 4, 8]; // 1: tiempo laborado, 6: aux. transporte, 3: cuotas, 4: prestamos, 8: seguridad social


    /**
     * Para almacenar los registros de documentos
     *  Normalmente para conceptos tipo Manuales
     */
    public function store(Request $request)
    {
        $datos = [];
        $usuario = Auth::user();

        $core_empresa_id = $usuario->empresa_id;

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
        for( $i=0; $i < $request->cantidad_empleados; $i++)
        {
            if ( isset( $request->valor ) )
            {
                $this->registrar_por_valor( $concepto, $request->input('core_tercero_id.'.$i), $datos, $request->input('valor.'.$i) );
            }

            if ( isset( $request->cantidad_horas ) )
            {
                $this->registrar_por_cantidad_horas( $concepto, $request->input('core_tercero_id.'.$i), $datos, $request->input('cantidad_horas.'.$i) );
            }/**/
        }

        $this->actualizar_totales_documento($documento->id);

        return redirect( 'web?id='.$request->app_id.'&id_modelo='.$request->modelo_id )->with( 'flash_message','Registros CREADOS correctamente. Nómina: '.$documento->descripcion.', Concepto: '.$concepto->descripcion );
    }

    public function registrar_por_valor( $concepto, $core_tercero_id, $datos, $valor )
    {
        if ( $valor > 0 ) 
        {
            $valores = $this->get_valor_devengo_deduccion( $concepto->naturaleza, $valor );

            $contrato = NomContrato::where('core_tercero_id',$core_tercero_id)->get()->first();

            NomDocRegistro::create(
                                    $datos +
                                    [ 'core_tercero_id' => $core_tercero_id ] +
                                    [ 'nom_contrato_id' => $contrato->id ] +
                                    [ 'valor_devengo' => $valores[0] ] + 
                                    [ 'valor_deduccion' => $valores[1] ]
                                );
        }
    }

    public function registrar_por_cantidad_horas( $concepto, $core_tercero_id, $datos, $cantidad_horas )
    {
        if ( $cantidad_horas > 0 )
        {
            $sueldo = NomContrato::where('core_tercero_id',$core_tercero_id)->where('estado','Activo')->value('sueldo');

            if ( is_null( $sueldo ) )
            {
                return false;
            }

            $salario_x_hora = $sueldo / config('nomina')['horas_laborales'];

            $valor_a_liquidar = $salario_x_hora * ( 1 + $concepto->porcentaje_sobre_basico / 100 ) * $cantidad_horas;

            $valores = $this->get_valor_devengo_deduccion( $concepto->naturaleza, $valor_a_liquidar );

            $contrato = NomContrato::where('core_tercero_id',$core_tercero_id)->get()->first();

            NomDocRegistro::create(
                                    $datos +
                                    [ 'core_tercero_id' => $core_tercero_id ] +
                                    [ 'nom_contrato_id' => $contrato->id ] +
                                    [ 'valor_devengo' => $valores[0] ] + 
                                    [ 'valor_deduccion' => $valores[1] ] + 
                                    [ 'cantidad_horas' => $cantidad_horas ]
                                );
        }

    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        switch($id){
            case 'editar1':

            $usuario = Auth::user();

            $core_empresa_id = $usuario->empresa_id;

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
            for($i=0;$i<$request->cantidad_empleados;$i++)
            {
                
                if ( $request->input('nom_registro_id.'.$i) == "no" ) 
                {
                    if ( isset( $request->valor ) )
                    {
                        $this->registrar_por_valor( $concepto, $request->input('core_tercero_id.'.$i), $datos, $request->input('valor.'.$i) );
                    }

                    if ( isset( $request->cantidad_horas ) )
                    {
                        $this->registrar_por_cantidad_horas( $concepto, $request->input('core_tercero_id.'.$i), $datos, $request->input('cantidad_horas.'.$i) );
                    }

                }else{
                    // Se actualiza el registro
                    $registro = NomDocRegistro::find( $request->input('nom_registro_id.'.$i) );

                    if ( isset( $request->valor ) )
                    {
                        $this->actualizar_por_valor( $registro, $concepto, $request->input('valor.'.$i), $usuario );
                    }

                    if ( isset( $request->cantidad_horas ) )
                    {
                        $this->actualizar_por_cantidad_horas( $registro, $concepto, $request->input('core_tercero_id.'.$i), $request->input('cantidad_horas.'.$i), $usuario );
                    }
                }

                    
            }

            $this->actualizar_totales_documento($documento->id);

            return redirect( 'web?id='.$request->app_id.'&id_modelo='.$request->modelo_id )->with( 'flash_message','Registros ACTUALIZADOS correctamente. Nómina: '.$documento->descripcion.', Concepto: '.$concepto->descripcion );

            break;

            default:
                // code
            break;

        }
    }

    public function actualizar_por_valor( $registro, $concepto, $valor, $usuario )
    {
        $valores = $this->get_valor_devengo_deduccion( $concepto->naturaleza, $valor );

        if ( $valor == 0 )
        {
            // Eliminar el registro
            $registro->delete();
        }else{
            $registro->fill( 
                            [ 'valor_devengo' => $valores[0] ] + 
                            [ 'valor_deduccion' => $valores[1] ] + 
                            [ 'modificado_por' => $usuario->email] );
            $registro->save();
        }
    }

    public function actualizar_por_cantidad_horas( $registro, $concepto, $core_tercero_id, $cantidad_horas, $usuario)
    {
        if ( $cantidad_horas == 0 )
        {
            // Eliminar el registro
            $registro->delete();
        }else{

            $sueldo = NomContrato::where('core_tercero_id',$core_tercero_id)->where('estado','Activo')->value('sueldo');

            if ( is_null( $sueldo ) )
            {
                return false;
            }

            $salario_x_hora = $sueldo / config('nomina')['horas_laborales'];

            $valor_a_liquidar = $salario_x_hora * ( 1 + $concepto->porcentaje_sobre_basico / 100 ) * $cantidad_horas;

            $valores = $this->get_valor_devengo_deduccion( $concepto->naturaleza, $valor_a_liquidar );

            $registro->fill( 
                            [ 'valor_devengo' => $valores[0] ] + 
                            [ 'valor_deduccion' => $valores[1] ] + 
                            [ 'cantidad_horas' => $cantidad_horas ]  + 
                            [ 'modificado_por' => $usuario->email ] );
            $registro->save();
        }
    }


    /*
        Pre-formulario donde seleccionar documento y concepto
    */
    public function crear_registros1()
    {
        $opciones1 = NomDocEncabezado::where('estado','Activo')->get();
        $vec1['']='';
        foreach ($opciones1 as $opcion){
            $vec1[$opcion->id] = $opcion->descripcion;
        }
        $documentos = $vec1;

        $modo_liquidacion_id = 2; //2 = Manual
        $opciones2 = NomConcepto::where('estado','Activo')->where('modo_liquidacion_id', $modo_liquidacion_id)->get();
        $vec2['']='';
        foreach ($opciones2 as $opcion){
            $vec2[$opcion->id] = $opcion->descripcion;
        }
        $conceptos = $vec2;


        $miga_pan = [
                        ['url'=>'nomina?id='.Input::get('id'),'etiqueta'=>'Nómina'],
                        ['url'=>'web?id=17&id_modelo=91','etiqueta'=>'Registros documentos nómina'],
                        ['url'=>'NO','etiqueta'=>'Ingreso de registros: seleccionar filtros']
                    ];

        return view('nomina.create_registros1',compact('documentos','conceptos','miga_pan'));
    }


    /*
        Formulario para registrar los valores a liquidar del concepto y el documento seleccionado
    */
    public function crear_registros2(Request $request)
    {

        // Se obtienen las descripciones del concepto y documento de nómina
        $concepto = NomConcepto::find($request->nom_concepto_id);
        $documento = NomDocEncabezado::find($request->nom_doc_encabezado_id);

        // Se obtienen los Empleados del documento
        $empleados = $documento->empleados;

        
        // Verificar si ya se han ingresado registro para ese concepto y documento
        $cant_registros = NomDocRegistro::where(['nom_doc_encabezado_id'=>$request->nom_doc_encabezado_id,
                'nom_concepto_id'=>$request->nom_concepto_id])
                ->count();
        
        $id_app = Input::get('id');

        $miga_pan = [
                        ['url'=>'nomina?id='.$id_app,'etiqueta'=>'Nómina'],
                        ['url'=>'web?id=17&id_modelo=91','etiqueta'=>'Registros documentos nómina'],
                        ['url'=>'nomina/crear_registros?id=17&id_modelo=91','etiqueta'=>'Ingreso de registros: seleccionar filtros'],
                        ['url'=>'NO','etiqueta'=>'Ingresar datos']
                    ];
         
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
                $datos = NomDocRegistro::where(['nom_doc_encabezado_id'=>$request->nom_doc_encabezado_id,
                                                'nom_concepto_id'=>$request->nom_concepto_id,
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
            return view('nomina.editar_registros1',['vec_empleados'=>$vec_empleados,
                'cantidad_empleados'=>count($empleados),
                'concepto'=>$concepto,
                'documento'=>$documento,
                'ruta'=>$request->ruta,
                'miga_pan'=>$miga_pan]);
        }else{
            // Si no tienen datos, se crean por primera vez
            return view('nomina.create_registros2',['empleados'=>$empleados,
                'cantidad_empleados'=>count($empleados),
                'concepto'=>$concepto,
                'documento'=>$documento,
                'ruta'=>$request->ruta,
                'miga_pan'=>$miga_pan]);
        }
    }


    function actualizar_totales_documento($nom_doc_encabezado_id)
    {
        $documento = NomDocEncabezado::find($nom_doc_encabezado_id);
        $documento->total_devengos = NomDocRegistro::where('nom_doc_encabezado_id',$nom_doc_encabezado_id)->sum('valor_devengo');
        $documento->total_deducciones = NomDocRegistro::where('nom_doc_encabezado_id',$nom_doc_encabezado_id)->sum('valor_deduccion');
        $documento->save();
    }
    
    function get_valor_devengo_deduccion( $naturaleza, $valor )
    {
        $valor_devengo = 0;
        $valor_deduccion = 0;
        switch ($naturaleza) {
            case 'devengo':
                $valor_devengo = $valor;
                $valor_deduccion = 0;
                break;
            case 'deduccion':
                $valor_devengo = 0;
                $valor_deduccion = $valor;
                break;
            
            default:
                # code...
                break;
        }

        return [$valor_devengo, $valor_deduccion];
    }
}