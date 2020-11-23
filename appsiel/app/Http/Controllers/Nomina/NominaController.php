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

class NominaController extends TransaccionController
{
    protected $total_devengos_empleado = 0;
    protected $total_deducciones_empleado = 0;
    protected $vec_totales = [];
    protected $pos = 0;
    protected $registros_procesados = 0;
    protected $vec_campos;
    protected $array_ids_modos_liquidacion_automaticos = [1, 6, 3, 4, 8]; // 1: tiempo laborado, 6: aux. transporte, 3: cuotas, 4: prestamos, 8: seguridad social

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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->set_variables_globales();

        return $this->crear( $this->app, $this->modelo, $this->transaccion, 'layouts.create', '' );
    }

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

        return redirect( 'web?id='.$request->app_id.'&id_modelo='.$request->modelo_id )->with( 'flash_message','Registros CREADOS correctamente. Nómina: '.$documento->descripcion.', Concepto:'.$concepto->descripcion );
    }

    public function registrar_por_valor( $concepto, $core_tercero_id, $datos, $valor )
    {
        if ( $valor > 0 ) 
        {            

            $valores = $this->get_valor_devengo_deduccion( $concepto->naturaleza, $valor );

            NomDocRegistro::create(
                                    $datos +
                                    [ 'core_tercero_id' => $core_tercero_id ] +
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

            NomDocRegistro::create(
                                    $datos +
                                    [ 'core_tercero_id' => $core_tercero_id ] +
                                    [ 'valor_devengo' => $valores[0] ] + 
                                    [ 'valor_deduccion' => $valores[1] ] + 
                                    [ 'cantidad_horas' => $cantidad_horas ]
                                );
        }

    }



    /**
     * Muestra un documento de liquidación con sus registros
     */
    public function show($id)
    {
        $modelo = Modelo::find(Input::get('id_modelo'));

        $reg_anterior = NomDocEncabezado::where('id', '<', $id)->max('id');
        $reg_siguiente = NomDocEncabezado::where('id', '>', $id)->min('id');

        $view_pdf = $this->vista_preliminar($id,'show');

        $miga_pan = [
                  ['url'=>'nomina?id='.Input::get('id'),'etiqueta'=>'Nómina'],
                  ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta' => $modelo->descripcion ],
                  ['url'=>'NO','etiqueta' => 'Consulta' ]
              ];

        return view( 'nomina.show',compact('reg_anterior','reg_siguiente','miga_pan','view_pdf','id') ); 

    }


    public function nomina_print($id)
    {
      $view_pdf = $this->vista_preliminar($id,'imprimir');

      $tam_hoja = 'folio';
      $orientacion='landscape';
      $pdf = \App::make('dompdf.wrapper');
      $pdf->loadHTML(($view_pdf))->setPaper($tam_hoja,$orientacion);
      return $pdf->stream('nomina'.$this->encabezado_doc->documento_app.'.pdf');
    }


    // Generar vista para SHOW o IMPRIMIR
    public function vista_preliminar($id,$vista)
    {

        $this->encabezado_doc =  NomDocEncabezado::get_un_registro($id);

        $empleados =$this->encabezado_doc->empleados;

        $conceptos = NomConcepto::conceptos_del_documento($this->encabezado_doc->id);

        $tabla = '<style> .celda_firma { width: 100px; }  .celda_nombre_empleado { width: 150px; } </style>
                    <br>
                     <table  class="tabla_registros table table-striped" style="margin-top: 1px; width: 100%;">
                    <thead>
                      <tr class="encabezado">
                          <th>
                             No.
                          </th>
                          <th>
                             Empleado
                          </th>
                          <th>
                             Identifcación
                          </th>';
        foreach ($conceptos as $registro)
        {          
          $tabla.='<th>'.$registro->abreviatura.'</th>';
        }

        $tabla.='<th>Tot. <br> Devengos</th>
                    <th>Tot. <br> Deducciones</th>
                    <th>Total a pagar</th>
                    <th width="100px">Firma</th>
                    </tr>
                    </thead>
                    <tbody>';

        $total_1=0;
        $i=1;

        $this->vec_totales = array_fill(0, count($conceptos)+3, 0);  
        
        foreach ($empleados as $empleado)
        {          
            $this->total_devengos_empleado = 0;
            $this->total_deducciones_empleado = 0;

            $tabla.='<tr>
                    <td>'.$i.'</td>
                    <td class="celda_nombre_empleado">'.$empleado->tercero->descripcion.'</td>
                    <td>'.number_format($empleado->tercero->numero_identificacion, 0, ',', '.').'</td>';

            $this->pos = 0;
            foreach ($conceptos as $un_concepto)
            {          
                $valor = $this->get_valor_celda( NomDocRegistro::where('nom_doc_encabezado_id',$this->encabezado_doc->id)->where('core_tercero_id',$empleado->core_tercero_id)->where('nom_concepto_id',$un_concepto->nom_concepto_id)->get(), $un_concepto );
                
                $tabla.='<td>'.$valor.'</td>';
                $this->pos++;
            }

            $total_devengos_empleado = NomDocRegistro::where( 'nom_doc_encabezado_id', $this->encabezado_doc->id )
                                                        ->where( 'core_tercero_id', $empleado->core_tercero_id )
                                                        ->sum('valor_devengo');

            $total_deducciones_empleado = NomDocRegistro::where( 'nom_doc_encabezado_id', $this->encabezado_doc->id )
                                                        ->where( 'core_tercero_id', $empleado->core_tercero_id )
                                                        ->sum('valor_deduccion');

            $tabla.='<td>'.Form::TextoMoneda( $total_devengos_empleado ).'</td>';

            $tabla.='<td>'.Form::TextoMoneda( $total_deducciones_empleado ).'</td>';

            $tabla.='<td>'.Form::TextoMoneda( $total_devengos_empleado - $total_deducciones_empleado ).'</td>';

            $tabla.='<td class="celda_firma"> &nbsp; </td>';

            $this->vec_totales[$this->pos] += $total_devengos_empleado;
            $this->pos++;
            $this->vec_totales[$this->pos] += $total_deducciones_empleado;
            $this->pos++;
            $this->vec_totales[$this->pos] += $total_devengos_empleado - $total_deducciones_empleado;

            $tabla.='</tr>';
            $i++;
        }

        $tabla.='<tr><td></td><td></td><td></td>';

        $cant = count( $this->vec_totales );
        for ($j=0; $j < $cant; $j++)
        {
            $tabla.='<td>'.Form::TextoMoneda( $this->vec_totales[$j] ).'</td>';
        }
        $tabla.='<td> &nbsp; </td>';
        $tabla.='</tr>';

        // DATOS ADICIONALES
        $tipo_doc_app = TipoDocApp::find($this->encabezado_doc->core_tipo_doc_app_id);
        $descripcion_transaccion = $tipo_doc_app->descripcion;

        $elaboro = $this->encabezado_doc->creado_por;
        $empresa = Empresa::find($this->encabezado_doc->core_empresa_id);
        $ciudad = DB::table('core_ciudades')
              ->where('id','=',$empresa->codigo_ciudad)
              ->value('descripcion');

        $encabezado_doc = $this->encabezado_doc;

        $firmas = '';
              
        $view_1 = View::make('nomina.incluir.encabezado_transaccion',compact('encabezado_doc','descripcion_transaccion','empresa','vista','ciudad') )->render();

        $view_pdf = '<link rel="stylesheet" type="text/css" href="'.asset('assets/css/estilos_formatos.css').'" media="screen" /> '.$view_1.$tabla.$firmas.'<div class="page-break"></div>';
        
        return $view_pdf;
    }

    function get_valor_celda($registro, $un_concepto)
    {
        if ( count($registro) > 0) 
        {
            // Se suma devengo y deduccion (alguno de los dos es cero)
            $valor = Form::TextoMoneda( $registro[0]->valor_devengo + $registro[0]->valor_deduccion );

            switch ($un_concepto->naturaleza) 
            {
                case 'devengo':
                    $this->total_devengos_empleado += $registro[0]->valor_devengo;
                    break;
                case 'deduccion':
                    $this->total_deducciones_empleado += $registro[0]->valor_deduccion;
                    break;
                
                default:
                    # code...
                    break;
            }

            $this->vec_totales[$this->pos] += $registro[0]->valor_devengo + $registro[0]->valor_deduccion;
        }else{
            $valor = '';
        }

        return $valor;
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

            return redirect( 'web?id='.$request->app_id.'&id_modelo='.$request->modelo_id )->with( 'flash_message','Registros ACTUALIZADOS correctamente. Nómina: '.$documento->descripcion.', Concepto:'.$concepto->descripcion );

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
                        ['url'=>'NO','etiqueta'=>'Ingresar registros']
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
                        ['url'=>'nomina/crear_registros?id='.$id_app,'etiqueta'=>'Ingresar'],
                        ['url'=>'NO','etiqueta'=>'Registros de nómina']
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

    /*
        Por cada empleado activo liquida los conceptos automáticos, las cuotas y préstamos
        Además actualiza el total de devengos y deducciones en el documento de nómina
    */
    public function liquidacion($id)
    {
        $this->registros_procesados = 0;

        $usuario = Auth::user();

        $core_empresa_id = $usuario->empresa_id;

        $documento = NomDocEncabezado::find($id);

        // Se obtienen los Empleados del documento
        $empleados_documento = $documento->empleados;

        // Guardar los valores para cada persona      
        foreach ($empleados_documento as $empleado) 
        {
            $cant = count( $this->array_ids_modos_liquidacion_automaticos );

            for ($i=0; $i < $cant; $i++) 
            { 
                $this->liquidar_automaticos_empleado( $this->array_ids_modos_liquidacion_automaticos[$i], $empleado, $documento, $usuario);
            }
        }

        $this->actualizar_totales_documento($id);

        return redirect( 'nomina/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with( 'flash_message','Liquidación realizada correctamente. Se procesaron '.$this->registros_procesados.' registros.' );
    }

    /*
        Recibe doc. de nómina, al empleado y el modo de liquidación para calcular el valor de devengo o deducción de cada concepto
    */
    public function liquidar_automaticos_empleado($modo_liquidacion_id, $empleado, $documento_nomina, $usuario)
    {
        $conceptos_automaticos = NomConcepto::where('estado','Activo')->where('modo_liquidacion_id', $modo_liquidacion_id)->get();
        
        foreach ($conceptos_automaticos as $un_concepto)
        {
            // Se valida si ya hay una liquidación previa del concepto en ese documento
            $cant = NomDocRegistro::where('nom_doc_encabezado_id', $documento_nomina->id)
                                    ->where('core_tercero_id', $empleado->core_tercero_id)
                                    ->where('nom_concepto_id', $un_concepto->id)
                                    ->count();

            if ( $cant != 0 ) 
            {
                continue;
            }

            // Las horas laborales se traen de la configuración de nómina (240 horas al mes)
            $salario_x_hora = $empleado->sueldo / config('nomina')['horas_laborales'];

            switch ($modo_liquidacion_id) 
            {

                case '1': // Tiempo laborado

                    $valor_a_liquidar = ($salario_x_hora * $documento_nomina->tiempo_a_liquidar) * $un_concepto->porcentaje_sobre_basico / 100;

                    $valores = $this->get_valor_devengo_deduccion( $un_concepto->naturaleza, $valor_a_liquidar );

                    $this->vec_campos = (object)['nom_cuota_id' => 0, 'nom_prestamo_id' => 0, 'valor_devengo' => $valores[0], 'valor_deduccion' => $valores[1] ];

                    break;

                case '6': // Aux. Transporte

                    /*
                        falta completar: solo se debe liquidar el tiempo proporcional a la quincena
                    */

                    // Se toma el valor directo del concepto
                    $valor_a_liquidar = $un_concepto->valor_fijo / ( config('nomina')['horas_laborales'] / $documento_nomina->tiempo_a_liquidar );

                    $this->vec_campos = (object)['nom_cuota_id' => 0, 'nom_prestamo_id' => 0, 'valor_devengo' => $valor_a_liquidar, 'valor_deduccion' => 0 ];

                    break;

                case '3': // Cuotas
                    $this->liquidar_cuotas($empleado, $un_concepto, $documento_nomina);
                    break;

                case '4': // Préstamos
                    $this->liquidar_prestamos($empleado, $un_concepto, $documento_nomina);
                    break;

                case '8': // Seguridad social

                    // Se suman los valores liquidados en los conceptos de la Agrupación para cálculo
                    $conceptos_de_la_agrupacion = AgrupacionConcepto::find( $un_concepto->nom_agrupacion_id )->conceptos->pluck('id')->toArray();

                    // Ingreso Base Cotización
                    $total_ibc_devengos = NomDocRegistro::whereIn( 'nom_concepto_id', $conceptos_de_la_agrupacion )
                                                        ->where( 'nom_doc_encabezado_id', $documento_nomina->id )
                                                        ->where( 'core_tercero_id', $empleado->core_tercero_id )
                                                        ->sum('valor_devengo');

                    $total_ibc_deducciones = NomDocRegistro::whereIn( 'nom_concepto_id', $conceptos_de_la_agrupacion )
                                                        ->where( 'nom_doc_encabezado_id', $documento_nomina->id )
                                                        ->where( 'core_tercero_id', $empleado->core_tercero_id )
                                                        ->sum('valor_deduccion');

                    $total_IBC = ($total_ibc_devengos - $total_ibc_deducciones);

                    $valor_a_liquidar = $total_IBC * $un_concepto->porcentaje_sobre_basico / 100;

                    $valores = $this->get_valor_devengo_deduccion( $un_concepto->naturaleza, $valor_a_liquidar );

                    $this->vec_campos = (object)['nom_cuota_id' => 0, 'nom_prestamo_id' => 0, 'valor_devengo' => $valores[0], 'valor_deduccion' => $valores[1] ];

                    break;
                
                default:
                    # code...
                    break;
            }    

            if( ($this->vec_campos->valor_devengo +$this->vec_campos->valor_deduccion ) > 0)
            {
                $registro = NomDocRegistro::create(
                    ['nom_doc_encabezado_id' => $documento_nomina->id ] + 
                    ['nom_concepto_id' => $un_concepto->id ] + 
                    ['nom_cuota_id' => $this->vec_campos->nom_cuota_id ] + 
                    ['nom_prestamo_id' => $this->vec_campos->nom_prestamo_id ] + 
                    ['core_tercero_id' => $empleado->core_tercero_id ] + 
                    ['fecha' => $documento_nomina->fecha] + 
                    ['core_empresa_id' => $documento_nomina->core_empresa_id] + 
                    ['valor_devengo' => $this->vec_campos->valor_devengo ] + 
                    ['valor_deduccion' => $this->vec_campos->valor_deduccion ] + 
                    ['estado' => 'Activo'] + 
                    ['creado_por' => $usuario->email] + 
                    ['modificado_por' => '']
                    );

                $this->registros_procesados++;
            }
        } // Fin Por cada concepto
    }



    public function liquidar_cuotas($una_persona, $un_concepto, $documento)
    {
        $cuota = NomCuota::where('estado', 'Activo')
                        ->where('core_tercero_id', $una_persona->core_tercero_id)
                        ->where('nom_concepto_id', $un_concepto->id)
                        ->where('fecha_inicio', '<=', $documento->fecha)
                        ->get();

        if ( count($cuota) > 0)
        {
            if ( $cuota[0]->tope_maximo != '' ) // si la cuota maneja tope máximo 
            {
                // El valor_acumulado no se puede pasar del tope_maximo
                $saldo_pendiente = $cuota[0]->tope_maximo - $cuota[0]->valor_acumulado;
                
                if ( $saldo_pendiente < $cuota[0]->valor_cuota )
                {
                    $cuota[0]->valor_acumulado += $saldo_pendiente;
                    $valor_real_cuota = $saldo_pendiente;
                }else{
                    $cuota[0]->valor_acumulado += $cuota[0]->valor_cuota;
                    $valor_real_cuota = $cuota[0]->valor_cuota;
                }

                if ( $cuota[0]->valor_acumulado >= $cuota[0]->tope_maximo ) 
                {
                    $cuota[0]->estado = "Inactivo";
                }
            }else{
                $cuota[0]->valor_acumulado += $cuota[0]->valor_cuota;
                $valor_real_cuota = $cuota[0]->valor_cuota;
            }
            
            $cuota[0]->save();
            
            $valores = $this->get_valor_devengo_deduccion( $un_concepto->naturaleza, $valor_real_cuota );

            $this->vec_campos = (object)['nom_cuota_id' => $cuota[0]->id, 'nom_prestamo_id' => 0, 'valor_devengo' => $valores[0], 'valor_deduccion' => $valores[1] ];
        }/*else{
            $this->vec_campos = (object)[ 'nom_cuota_id' => 0, 'nom_prestamo_id' => 0, 'valor_devengo' => 0, 'valor_deduccion' => 0 ];
        }*/
    }

    public function liquidar_prestamos($una_persona, $un_concepto, $documento)
    {
        // un solo préstamo por concepto
        $prestamo = NomPrestamo::where('estado', 'Activo')
                                ->where('core_tercero_id', $una_persona->core_tercero_id)
                                ->where('nom_concepto_id', $un_concepto->id)
                                ->where('fecha_inicio', '<=', $documento->fecha)
                                ->get()
                                ->first();

        if ( !is_null($prestamo) )
        {
            // El valor_acumulado no se puede pasar del valor_prestamo
            $saldo_pendiente = $prestamo->valor_prestamo - $prestamo->valor_acumulado;
                
            if ( $saldo_pendiente < $prestamo->valor_cuota )
            {
                $prestamo->valor_acumulado += $saldo_pendiente;
                $valor_real_prestamo = $saldo_pendiente;
            }else{
                $prestamo->valor_acumulado += $prestamo->valor_cuota;
                $valor_real_prestamo = $prestamo->valor_cuota;
            }

            if ( $prestamo->valor_acumulado >= $prestamo->valor_prestamo ) 
            {
                $prestamo->estado = "Inactivo";
            }
            
            $prestamo->save();
            
            $valores = $this->get_valor_devengo_deduccion( $un_concepto->naturaleza, $valor_real_prestamo );

            $this->vec_campos = (object)['nom_cuota_id' => 0, 'nom_prestamo_id' => $prestamo->id, 'valor_devengo' => $valores[0], 'valor_deduccion' => $valores[1] ];
        }else{
            $this->vec_campos = (object)[ 'nom_cuota_id' => 0, 'nom_prestamo_id' => 0, 'valor_devengo' => 0, 'valor_deduccion' => 0 ];
        }
    }


    public function retirar_liquidacion($id)
    {
        $conceptos = NomConcepto::where('estado','Activo')->whereIn('modo_liquidacion_id', $this->array_ids_modos_liquidacion_automaticos)->get();

        foreach ($conceptos as $un_concepto)
        {
            
            // LOS REGISTROS QUE TIENEN ESE CONCEPTO
            $registros = NomDocRegistro::where( 'nom_doc_encabezado_id', $id)
                                        ->where( 'nom_concepto_id', $un_concepto->id)
                                        ->get();

            // Para cuotas y préstamos
            foreach ($registros as $un_registro)
            {
                // Para cuotas, reverso los valores acumulados y el estado
                if ( $un_concepto->modo_liquidacion_id == 3) 
                {
                    $cuota = NomCuota::find( $un_registro->nom_cuota_id );

                    if ( !is_null($cuota) )
                    {
                        switch( $un_concepto->naturaleza )
                        {
                            case 'devengo':
                                $cuota->valor_acumulado -= $un_registro->valor_devengo;
                                break;
                            case 'deduccion':
                                $cuota->valor_acumulado -= $un_registro->valor_deduccion;
                                break;
                            default:
                                break;
                        }

                        $cuota->estado = "Activo";
                        $cuota->save();
                    }       

                }

                // Para Préstamos, reverso los valores acumulados y el estado
                if ( $un_concepto->modo_liquidacion_id == 4) 
                {
                    $prestamo = NomPrestamo::find( $un_registro->nom_prestamo_id );

                    if ( !is_null($prestamo) ) 
                    {
                        switch( $un_concepto->naturaleza )
                        {
                            case 'devengo':
                                $prestamo->valor_acumulado -= $un_registro->valor_devengo;
                                break;
                            case 'deduccion':
                                $prestamo->valor_acumulado -= $un_registro->valor_deduccion;
                                break;
                            default:
                                break;
                        }

                        $prestamo->estado = "Activo";
                        $prestamo->save();
                    }
                }
            }

            NomDocRegistro::where('nom_doc_encabezado_id', $id)
                            ->where('nom_concepto_id', $un_concepto->id)
                            ->delete();                
        }

        $this->actualizar_totales_documento($id);

        return redirect( 'nomina/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with( 'mensaje_error','Registros automáticos retirados correctamente.' );
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