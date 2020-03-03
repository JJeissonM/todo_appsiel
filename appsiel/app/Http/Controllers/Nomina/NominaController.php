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

class NominaController extends TransaccionController
{
    protected $total_devengos_empleado = 0;
    protected $total_deducciones_empleado = 0;
    protected $vec_totales = [];
    protected $pos = 0;
    protected $registros_procesados = 0;
    protected $vec_campos;

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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $usuario = Auth::user();

        $core_empresa_id = $usuario->empresa_id;

        $concepto = NomConcepto::find($request->nom_concepto_id);
        $documento = NomDocEncabezado::find($request->nom_doc_encabezado_id);
        
        // Guardar los valores para cada persona      
        for($i=0;$i<$request->cantidad_personas;$i++)
        {
            if ( $request->input('valor.'.$i) > 0) 
            {
                $valores = $this->get_valor_devengo_deduccion( $concepto->naturaleza, $request->input('valor.'.$i) );

                $registro = NomDocRegistro::create(
                    ['nom_doc_encabezado_id' => $request->nom_doc_encabezado_id] + 
                    ['nom_concepto_id' => $request->nom_concepto_id] + 
                    ['core_tercero_id' => $request->input('core_tercero_id.'.$i)] + 
                    ['fecha' => $documento->fecha] + 
                    ['core_empresa_id' => $documento->core_empresa_id] + 
                    ['valor_devengo' => $valores[0] ] + 
                    ['valor_deduccion' => $valores[1] ] + 
                    ['estado' => 'Activo'] + 
                    ['creado_por' => $usuario->email] + 
                    ['modificado_por' => '']
                    );
              }
        }

        $this->actualizar_totales_documento($documento->id);

        return redirect( 'web?id='.$request->app_id.'&id_modelo='.$request->modelo_id )->with( 'flash_message','Registros CREADOS correctamente. Nómina: '.$documento->descripcion.', Concepto:'.$concepto->descripcion );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
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

        //echo $view_pdf;
    }


    public function nomina_print($id)
    {
      $view_pdf = $this->vista_preliminar($id,'imprimir');

      $tam_hoja = 'folio';
      $orientacion='landscape';
      $pdf = \App::make('dompdf.wrapper');
      $pdf->loadHTML(($view_pdf))->setPaper($tam_hoja,$orientacion);
      return $pdf->download('nomina'.$this->encabezado_doc->documento_app.'.pdf');
    }


    // Generar vista para SHOW o IMPRIMIR
    public function vista_preliminar($id,$vista)
    {

        $this->encabezado_doc =  NomDocEncabezado::get_un_registro($id);

        $personas = NomContrato::get_empleados( 'Activo' );

        $conceptos = NomConcepto::conceptos_del_documento($this->encabezado_doc->id);

        $tabla = '<table  class="tabla_registros table table-striped" style="margin-top: 1px;">
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

        $tabla.='<th>Tot. Deducciones</th>
                    <th>Total a pagar</th>
                    </tr>
                    </thead>
                    <tbody>';

        $total_1=0;
        $i=1;

        $this->vec_totales = array_fill(0, count($conceptos)+2, 0);  
        
        foreach ($personas as $persona)
        {          
            $this->total_devengos_empleado = 0;
            $this->total_deducciones_empleado = 0;

            $tabla.='<tr>
                    <td>'.$i.'</td>
                    <td>'.$persona->empleado.'</td>
                    <td>'.number_format($persona->cedula, 0, ',', '.').'</td>';

            $this->pos = 0;
            foreach ($conceptos as $un_concepto)
            {          
                $valor = $this->get_valor_celda( NomDocRegistro::where('nom_doc_encabezado_id',$this->encabezado_doc->id)->where('core_tercero_id',$persona->core_tercero_id)->where('nom_concepto_id',$un_concepto->nom_concepto_id)->get(), $un_concepto );
                
                $tabla.='<td>'.$valor.'</td>';
                $this->pos++;
            }


            $tabla.='<td>'.Form::TextoMoneda( $this->total_deducciones_empleado ).'</td>';

            $tabla.='<td>'.Form::TextoMoneda( $this->total_devengos_empleado - $this->total_deducciones_empleado ).'</td>';

            $this->vec_totales[$this->pos] += $this->total_deducciones_empleado;
            $this->pos++;
            $this->vec_totales[$this->pos] += $this->total_devengos_empleado - $this->total_deducciones_empleado;

            $tabla.='</tr>';
            $i++;
        }

        $tabla.='<tr><td></td><td></td><td></td>';

        $cant = count( $this->vec_totales );
        for ($j=0; $j < $cant; $j++) { 
            $tabla.='<td>'.Form::TextoMoneda( $this->vec_totales[$j] ).'</td>';
        }
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
                case 'Devengo':
                    $this->total_devengos_empleado += $registro[0]->valor_devengo;
                    break;
                case 'Deduccion':
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
            
            // Guardar los valores para cada persona      
            for($i=0;$i<$request->cantidad_personas;$i++)
            {
                $valores = $this->get_valor_devengo_deduccion( $concepto->naturaleza, $request->input('valor.'.$i) );
                
                if ( $request->input('nom_registro_id.'.$i) == "no" ) 
                {
                    // Se crea un nuevo registro
                    if ( $request->input('valor.'.$i) > 0) 
                    {

                        $registro = NomDocRegistro::create(
                            ['nom_doc_encabezado_id' => $request->nom_doc_encabezado_id] + 
                            ['nom_concepto_id' => $request->nom_concepto_id] + 
                            ['core_tercero_id' => $request->input('core_tercero_id.'.$i)] + 
                            ['fecha' => $documento->fecha] + 
                            ['core_empresa_id' => $documento->core_empresa_id] + 
                            ['valor_devengo' => $valores[0] ] + 
                            ['valor_deduccion' => $valores[1] ] + 
                            ['estado' => 'Activo'] + 
                            ['creado_por' => $usuario->email] + 
                            ['modificado_por' => '']
                            );
                    } // FIN - Si valor mayor a cero
                }else{
                    // Se actualiza el registro
                    $registro = NomDocRegistro::find( $request->input('nom_registro_id.'.$i) );

                    if ( $request->input('valor.'.$i) == 0) {
                        // Eliminar el registro
                        $registro->delete();
                    }else{
                        $registro->fill( ['valor_devengo' => $valores[0] ] + 
                            ['valor_deduccion' => $valores[1] ] + 
                            ['modificado_por' => $usuario->email] );
                        $registro->save();
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }



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

    public function crear_registros2(Request $request)
    {
        // Se obtienen los Empleados Activos
        $personas = NomContrato::get_empleados( 'Activo' );

        // Se obtienen las descripciones del concepto y documento de nómina
        $concepto = NomConcepto::find($request->nom_concepto_id);
        $documento = NomDocEncabezado::find($request->nom_doc_encabezado_id);
            
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
            foreach($personas as $persona)
            {
                $vec_personas[$i]['core_tercero_id'] = $persona->core_tercero_id;
                $vec_personas[$i]['nombre'] = $persona->empleado;
                
                // Se verifica si cada persona tiene valor ingresado
                $datos = NomDocRegistro::where(['nom_doc_encabezado_id'=>$request->nom_doc_encabezado_id,
                'nom_concepto_id'=>$request->nom_concepto_id,
                'core_tercero_id'=>$persona->core_tercero_id])
                ->get();

                $vec_personas[$i]['valor_concepto'] = 0;
                $vec_personas[$i]['nom_registro_id'] = "no";
                
                // Si el persona tiene calificacion se envian los datos de esta para editar
                if( !is_null($datos) )
                {
                    switch ($concepto->naturaleza) {
                        case 'Devengo':
                            $vec_personas[$i]['valor_concepto'] = $datos[0]->valor_devengo;
                            break;
                        case 'Deduccion':
                            $vec_personas[$i]['valor_concepto'] = $datos[0]->valor_deduccion;
                            break;
                        
                        default:
                            # code...
                            break;
                    }

                    $vec_personas[$i]['nom_registro_id'] = $datos[0]->id;

                }
                
                $i++;
            } // Fin foreach (llenado de array con datos)
            return view('nomina.editar_registros1',['vec_personas'=>$vec_personas,
                'cantidad_personas'=>count($personas),
                'concepto'=>$concepto,
                'documento'=>$documento,
                'ruta'=>$request->ruta,
                'miga_pan'=>$miga_pan]);
        }else{
            // Si no tienen datos, se crean por primera vez
            return view('nomina.create_registros2',['personas'=>$personas,
                'cantidad_personas'=>count($personas),
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

        // Se obtienen los Empleados Activos
        $personas = NomContrato::get_empleados( 'Activo' );

        // Guardar los valores para cada persona      
        foreach ($personas as $una_persona) 
        {
            // Para los conceptos automáticos
            // 1=Automático
            // 3=Cuota
            // 4=Préstamo
            $modo_liquidacion_id = [1, 3, 4];//
            $cant = count($modo_liquidacion_id);
            for ($i=0; $i < $cant; $i++) 
            { 
                $this->liquidar_automaticos($modo_liquidacion_id[$i], $id, $una_persona, $documento, $usuario);
            }
        }

        $this->actualizar_totales_documento($id);

        return redirect( 'nomina/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with( 'flash_message','Liquidación realizada correctamente. Se procesaron '.$this->registros_procesados.' registros.' );
    }

    /*
        Recibe doc. de nómina, al empleado y el modo de liquidación para calcular el valor de devengo o deducción de cada concepto
    */
    public function liquidar_automaticos($modo_liquidacion_id, $nom_doc_encabezado_id, $una_persona, $documento, $usuario)
    {
        $conceptos = NomConcepto::where('estado','Activo')->where('modo_liquidacion_id', $modo_liquidacion_id)->get();
        
        foreach ($conceptos as $un_concepto) 
        {
            // Se valida si no hay una liquidación previa del concepto en ese documento
            $cant = NomDocRegistro::where('nom_doc_encabezado_id', $documento->id)
                                    ->where('core_tercero_id', $una_persona->core_tercero_id)
                                    ->where('nom_concepto_id', $un_concepto->id)
                                    ->count();

            if ( $cant == 0) 
            {
                // Las horas lanborales se traen de la configuración de nómina (240 horas al mes)
                $salario_x_hora = $una_persona->salario / config('nomina')['horas_laborales'];

                switch ($modo_liquidacion_id) 
                {

                    case '1': //automáticos
                        $valor_a_liquidar = $salario_x_hora * $una_persona->salario * $un_concepto->porcentaje_sobre_basico / 100;

                        $valores = $this->get_valor_devengo_deduccion( $un_concepto->naturaleza, $valor_a_liquidar );

                        $this->vec_campos = (object)['nom_cuota_id' => 0, 'nom_prestamo_id' => 0, 'valor_devengo' => $valores[0], 'valor_deduccion' => $valores[1] ];

                        break;

                    case '3': //Cuotas
                        $this->liquidar_cuotas($una_persona, $un_concepto, $documento);
                        break;

                    case '4': //Préstamos
                        $this->liquidar_prestamos($una_persona, $un_concepto, $documento);
                        break;
                    
                    default:
                        # code...
                        break;
                }

                if( ($this->vec_campos->valor_devengo +$this->vec_campos->valor_deduccion ) > 0)
                {
                    $registro = NomDocRegistro::create(
                        ['nom_doc_encabezado_id' => $nom_doc_encabezado_id] + 
                        ['nom_concepto_id' => $un_concepto->id ] + 
                        ['nom_cuota_id' => $this->vec_campos->nom_cuota_id ] + 
                        ['nom_prestamo_id' => $this->vec_campos->nom_prestamo_id ] + 
                        ['core_tercero_id' => $una_persona->core_tercero_id ] + 
                        ['fecha' => $documento->fecha] + 
                        ['core_empresa_id' => $documento->core_empresa_id] + 
                        ['valor_devengo' => $this->vec_campos->valor_devengo ] + 
                        ['valor_deduccion' => $this->vec_campos->valor_deduccion ] + 
                        ['estado' => 'Activo'] + 
                        ['creado_por' => $usuario->email] + 
                        ['modificado_por' => '']
                        );

                    $this->registros_procesados++;
                }
            }
        } // Fin Por cada concepto
    }

    public function liquidar_cuotas($una_persona, $un_concepto, $documento)
    {
        $cuota = NomCuota::where('estado', 'Activo')->where('core_tercero_id', $una_persona->core_tercero_id)->where('nom_concepto_id', $un_concepto->id)->where('fecha_inicio', '<=', $documento->fecha)->get();

        if ( count($cuota) > 0)
        {
            if ( $cuota[0]->tope_maximo != '' ) 
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
        }else{
            $this->vec_campos = (object)[ 'nom_cuota_id' => 0, 'nom_prestamo_id' => 0, 'valor_devengo' => 0, 'valor_deduccion' => 0 ];
        }
    }

    public function liquidar_prestamos($una_persona, $un_concepto, $documento)
    {
        $prestamo = NomPrestamo::where('estado', 'Activo')->where('core_tercero_id', $una_persona->core_tercero_id)->where('nom_concepto_id', $un_concepto->id)->where('fecha_inicio', '<=', $documento->fecha)->get();

        if ( count($prestamo) > 0)
        {
            // El valor_acumulado no se puede pasar del valor_prestamo
            $saldo_pendiente = $prestamo[0]->valor_prestamo - $prestamo[0]->valor_acumulado;
                
            if ( $saldo_pendiente < $prestamo[0]->valor_prestamo )
            {
                $prestamo[0]->valor_acumulado += $saldo_pendiente;
                $valor_real_prestamo = $saldo_pendiente;
            }else{
                $prestamo[0]->valor_acumulado += $prestamo[0]->valor_cuota;
                $valor_real_prestamo = $prestamo[0]->valor_cuota;
            }

            if ( $prestamo[0]->valor_acumulado >= $prestamo[0]->valor_prestamo ) 
            {
                $prestamo[0]->estado = "Inactivo";
            }
            
            $prestamo[0]->save();
            
            $valores = $this->get_valor_devengo_deduccion( $un_concepto->naturaleza, $valor_real_prestamo );

            $this->vec_campos = (object)['nom_cuota_id' => 0, 'nom_prestamo_id' => $prestamo[0]->id, 'valor_devengo' => $valores[0], 'valor_deduccion' => $valores[1] ];
        }else{
            $this->vec_campos = (object)[ 'nom_cuota_id' => 'no', 'nom_prestamo_id' => 'no', 'valor_devengo' => 0, 'valor_deduccion' => 0 ];
        }
    }


    public function retirar_liquidacion($id)
    {
        
        $modo_liquidacion_id = [1, 3, 4]; // 1=Automático, 3=Cuota, 4=Préstamo
        $conceptos = NomConcepto::where('estado','Activo')->whereIn('modo_liquidacion_id', $modo_liquidacion_id)->get();

        foreach ($conceptos as $un_concepto) {
            
            // LOS REGISTROS QUE TIENEN ESE CONCEPTO
            $registros = NomDocRegistro::where('nom_doc_encabezado_id', $id)->where('nom_concepto_id', $un_concepto->id)->get();

            foreach ($registros as $un_registro)
            {
                // Para cuotas, reverso los valores acumulados y el estado
                if ( $un_concepto->modo_liquidacion_id == 3) 
                {
                    $cuota = NomCuota::find( $un_registro->nom_cuota_id );

                    switch( $un_concepto->naturaleza )
                    {
                        case 'Devengo':
                            $cuota->valor_acumulado -= $un_registro->valor_devengo;
                            break;
                        case 'Deduccion':
                            $cuota->valor_acumulado -= $un_registro->valor_deduccion;
                            break;
                        default:
                            break;
                    }

                    $cuota->estado = "Activo";
                    $cuota->save();                    

                }

                // Para Préstamos, reverso los valores acumulados y el estado
                if ( $un_concepto->modo_liquidacion_id == 4) 
                {
                    $prestamo = NomPrestamo::find( $un_registro->nom_prestamo_id );

                    switch( $un_concepto->naturaleza )
                    {
                        case 'Devengo':
                            $prestamo->valor_acumulado -= $un_registro->valor_devengo;
                            break;
                        case 'Deduccion':
                            $prestamo->valor_acumulado -= $un_registro->valor_deduccion;
                            break;
                        default:
                            break;
                    }

                    $prestamo->estado = "Activo";
                    $prestamo->save();                    

                }
            }

            NomDocRegistro::where('nom_doc_encabezado_id', $id)->where('nom_concepto_id', $un_concepto->id)->delete();                
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
        switch ($naturaleza) {
            case 'Devengo':
                $valor_devengo = $valor;
                $valor_deduccion = 0;
                break;
            case 'Deduccion':
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