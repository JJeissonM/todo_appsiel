<?php

  namespace App\Http\Controllers\CxC;
  
  use Illuminate\Http\Request;

  use App\Http\Requests;
  use App\Http\Controllers\Controller;

  use Auth;
  use DB;
  use View;
  use Lava;
  use Input;
  use NumerosEnLetras;
  use Form;
  use Storage;


  use App\Http\Controllers\Core\ConfiguracionController;


  // Modelos
  use App\Matriculas\Grado;
  use App\Matriculas\Estudiante;
  use App\Matriculas\Matricula;
  use App\Core\Colegio;
  use App\Core\Empresa;
  use App\Sistema\Aplicacion;
  use App\Sistema\TipoTransaccion;
  use App\Core\TipoDocApp;
  use App\Sistema\Modelo;
  use App\Core\Tercero;

  use App\Tesoreria\TesoLibretasPago;
  use App\Tesoreria\TesoRecaudosLibreta;
  use App\Tesoreria\TesoCuentaBancaria;


  use App\CxC\CxcMovimiento;
  use App\CxC\CxcDocEncabezado;
  use App\CxC\CxcInteresMora;
  use App\CxC\CxcDocRegistro;
  use App\CxC\CxcServicio;
use App\CxC\CxcEstadoCartera;

  use App\Contabilidad\ContabMovimiento;
  use App\PropiedadHorizontal\Propiedad;



  class InteresesMoraController extends Controller
  {
    protected $datos = [];
    protected $encabezado_doc;
    protected $inmueble;
    protected $tercero;

    public function __construct()
    {
        $this->middleware('auth');
    }

    // MUESTRA FORMULARIO PARA calcular_intereses
    public function calcular_intereses()
    {
      $app = Aplicacion::find(Input::get('id'));
      $modelo = Modelo::find(Input::get('id_modelo'));

      $registros = CxcServicio::where('core_empresa_id',Auth::user()->empresa_id)
                       ->orderBy('descripcion')
                       ->get();
      $servicios[''] = '';
      foreach ($registros as $fila) {
          $servicios[$fila->id] = $fila->descripcion; 
      }

      $miga_pan = [
                  ['url'=>'cxc?id='.Input::get('id'),'etiqueta'=> $app->descripcion],
                  ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta' => $modelo->descripcion ],
                  ['url'=>'NO','etiqueta' => 'Calcular intereses' ]
              ];

      return view('cxc.intereses_mora.calcular_intereses_mora', compact( 'servicios', 'miga_pan'));
    }

    // PROCESO PARA calcular_intereses
    public function cxc_ajax_calcular_intereses(Request $request)
    {
      
      /*  OBSERVACIONES
      // 1. Se dejan por fuera los anticipos 'saldo_pendiente','>',0
      // 2. Se deben dejar por fuera las facturas de intereses anteriores. (Anatocismo). Para esto se debe asignar una fecha de vencimiento muy lejos (2100-01-01) a las facturas de intereses para que nunca las tenga en cuenta este proceso de calculo
      */

      $empresa_id = Auth::user()->empresa_id;

      // Vaciar tabla de intereses
      CxcInteresMora::where('core_empresa_id', $empresa_id)->delete();

      // Actualizar estados de cartera
      CxcEstadoCartera::actualizar_estado_cartera($request->fecha_corte);

      // Generar tabla de intereses (por cada propiedad)
      $propiedades = Propiedad::where('core_empresa_id',Auth::user()->empresa_id)->where('estado','Activo')->orderBy('codigo')->get();

      $i = 0;
      foreach ($propiedades as $fila) 
      {
        switch ($request->calculado_sobre) {
          case 'Saldo total vencido':
            
            $saldo_vencido = CxcMovimiento::where('cxc_movimientos.codigo_referencia_tercero',$fila->id)
                  ->where('cxc_movimientos.fecha','<=',$request->fecha_corte)
                  ->where('cxc_movimientos.core_empresa_id',$empresa_id)
                  ->where('cxc_movimientos.estado','=','Vencido')
                  ->where('saldo_pendiente','>',0)
                  ->sum('saldo_pendiente');

            break;

          case 'Última factura vencida':
            
            $saldo_vencido = CxcMovimiento::where('cxc_movimientos.codigo_referencia_tercero',$fila->id)
                  ->where('cxc_movimientos.fecha','<=',$request->fecha_corte)
                  ->where('cxc_movimientos.core_empresa_id',$empresa_id)
                  ->where('cxc_movimientos.estado','=','Vencido')
                  ->where('saldo_pendiente','>',0)
                  ->latest('fecha')
                  ->value('saldo_pendiente');

            break;
          
          default:
            # code...
            break;
        }

        $valor_interes = $saldo_vencido * ($request->tasa_interes / 100);

        if ( $valor_interes > 0 ) 
        {
            CxcInteresMora::create( [ 'core_empresa_id' => $fila->core_empresa_id ] +
                            ['core_tercero_id' => $fila->core_tercero_id] +
                            ['codigo_referencia_tercero' => $fila->id] +
                            ['fecha_corte' => $request->fecha_corte] +
                            ['calculado_sobre' => $request->calculado_sobre] +
                            ['saldo_vencido' => $saldo_vencido] +
                            ['tasa_interes' => $request->tasa_interes] +
                            ['cxc_servicio_id' => $request->cxc_servicio_id] +
                            ['valor_interes' => $valor_interes] +
                            ['creado_por' => Auth::user()->email] );
            $i++;
        }else{
          $tabla = '<h4> No hubo registros de cartera para calcular intereses</h4> <hr>';
        }          
      }

      if ( $i > 0 ) 
      {
        $tabla = '<h4> Intereses calculados <b>('.$i.' registros) </b> </h4> <hr> '.$this->get_tabla_intereses();
          $i++;
      }else{
        $tabla = '<h4> No hubo registros de cartera para calcular intereses</h4> <hr>';
      }

      return $tabla;
    }



    // MUESTRA FORMULARIO PARA CAUSAR INTERESES
    public function causar_intereses()
    {
      $app = Aplicacion::find(Input::get('id'));
      $modelo = Modelo::find(Input::get('id_modelo'));

      $tabla = $this->get_tabla_intereses();

      $miga_pan = [
                  ['url'=>'cxc?id='.Input::get('id'),'etiqueta'=> $app->descripcion],
                  ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta' => $modelo->descripcion ],
                  ['url'=>'NO','etiqueta' => 'Causar intereses' ]
              ];

      return view('cxc.intereses_mora.causar_intereses_mora', compact( 'tabla', 'miga_pan'));
    }

    // PROCESO PARA CAUSAR INTERESES
    public function cxc_ajax_causar_intereses(Request $request)
    {
      $intereses = CxcInteresMora::where('core_empresa_id',Auth::user()->empresa_id)->get();

      switch ($request->modo_causacion) {
        case 'Programada':

          $i=0;
          foreach ($intereses as $fila) 
          {
            DB::table('ph_propiedad_tiene_servicios')->insert([['propiedad_id' => $fila->codigo_referencia_tercero] + ['cxc_servicio_id' => $fila->cxc_servicio_id] + ['valor_servicio' => $fila->valor_interes] + ['orden' => 7]]);
            $i++;
          }

          if ( $i > 0) 
          {
            $respuesta = '<br/><div class="alert alert-success">
                                          <strong>¡Muy bien!</strong> Se Asignaron intereses de mora a cada inmueble.
                                        </div>';
          }else{
            $respuesta = '<br/><div class="alert alert-danger">
                                          <strong>¡Lo sentimos!</strong> No hubo intereses de mora para asignar.
                                        </div>';
          }
            
            
          break;

        case 'Inmediata':
          
          $respuesta = '<h4> Cuentas de cobro generadas </h4> <hr> <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Propiedad</th>
                    <th>Propietario</th>
                    <th>Documento</th>
                    <th>Valor total</th>
                </tr>
            </thead>
            <tbody>';
          $precio_total=0;
          $i=0;
          $cant_propiedades = 0;
          $primer_registro = 0;

          foreach ($intereses as $fila) 
          {
                       
            // 1ro. Crear encabezado documento
            //$modelo = Modelo::find();
            // Seleccionamos el consecutivo actual (si no existe, se crea) y le sumamos 1
            $consecutivo = TipoDocApp::get_consecutivo_actual($request->core_empresa_id,$request->core_tipo_doc_app_id) + 1;

            // Se incementa el consecutivo para ese tipo de documento y la empresa
            TipoDocApp::aumentar_consecutivo($request->core_empresa_id,$request->core_tipo_doc_app_id);

            // Datos para el encabezado
            $fecha_vencimiento = '2100-01-01'; // Para que nunca se venza y no se vaya a incurrir en Anatocismo
            $this->datos = array_merge( $request->all(), [ 'consecutivo' => $consecutivo, 
              'fecha' => $request->fecha, 'fecha_vencimiento' => $fecha_vencimiento, 'core_tercero_id' => $fila->core_tercero_id, 'codigo_referencia_tercero' => $fila->codigo_referencia_tercero, 'descripcion' => 'Liquidación intereses por mora', 'valor_total' => $fila->valor_interes, 'estado' => 'Activo', 'creado_por' => Auth::user()->email ] );
            
            

            // Almacenar encabezado del documento
            $registro_encabezado_doc = CxcDocEncabezado::create( $this->datos );

            

            // Crear registro del documento
            CxcDocRegistro::create(
                [ 'cxc_doc_encabezado_id' => $registro_encabezado_doc->id ] +
                [ 'cxc_motivo_id' => 0 ] + 
                [ 'cxc_servicio_id' => $fila->cxc_servicio_id ] + 
                [ 'valor_unitario' => $fila->valor_interes ] + 
                [ 'cantidad' => 1 ] +
                [ 'valor_total' => $fila->valor_interes ] +
                [ 'descripcion' => 'Liquidación intereses por mora' ] +
                [ 'estado' => 'Activo' ] );

            

            // Contabilizar Cartera VS Ingresos
            // CARTERA (DB)
            $contab_cuenta = Tercero::find($fila->core_tercero_id)->cuenta_cartera;
            $contab_cuenta_id = $contab_cuenta->id; 

            $valor_debito = $fila->valor_interes;

            $valor_credito = 0;

            $this->contabilizar_registro( $contab_cuenta_id, 'Liquidación intereses por mora', $valor_debito, $valor_credito);

            // INGRESOS (CR)
            $servicio_default = CxcServicio::find($fila->cxc_servicio_id);
            $contab_cuenta_id = $servicio_default->contab_cuenta_id;                
            $valor_debito = 0;
            $valor_credito = $fila->valor_interes;
            $this->contabilizar_registro( $contab_cuenta_id, 'Liquidación intereses por mora', $valor_debito, $valor_credito);


            // Almacenar movimiento de cartera
            CxcMovimiento::create( $this->datos +  
                            [ 'valor_cartera' => $fila->valor_interes ] +  
                            [ 'saldo_pendiente' => $fila->valor_interes ] +
                            [ 'detalle_operacion' => 'Liquidación intereses por mora' ]+
                            [ 'estado' => 'Pendiente' ] );

            // Se va armando la tabla para el resultado
            $tipo_doc_app = TipoDocApp::find($request->core_tipo_doc_app_id);
            $documento  = $tipo_doc_app->prefijo.' '.$consecutivo;

            $tercero = Tercero::find($fila->core_tercero_id);
            
            $propiedad = Propiedad::find($fila->codigo_referencia_tercero);

            $respuesta.='<tr>
                    <td>'.$propiedad->codigo.'</td>
                    <td>'.$tercero->descripcion.'</td>
                    <td> <a href="'.url('cxc/'.$registro_encabezado_doc->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo).'" target="_blank" title="Vista previa">'.$documento.'</a> </td>
                    <td>'.number_format($fila->valor_interes, 0, ',', '.').'</td>
                </tr>';

            $i++;
          }

          if ( $i > 0) 
          {
            $respuesta.='</tbody></table>';
          }else{
            $respuesta = '<br/><div class="alert alert-danger">
                                          <strong>¡Lo sentimos!</strong> No hubo intereses de mora para causar.
                                        </div>';
          }
          

          break;
        
        default:
          # code...
          break;
      }

      $intereses = CxcInteresMora::where('core_empresa_id',Auth::user()->empresa_id)->delete();

      return $respuesta;
    }

    // ELIMINAR 
    public function eliminar_interes($id)
    {
      
      //Se elimina el registro
      CxcInteresMora::find($id)->delete();

      return redirect('web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'))->with('flash_message','Registro de interes por mora eliminado correctamente.');
    }


    function get_tabla_intereses()
    {
      $registros = CxcInteresMora::consultar_registros();
      $encabezado_tabla = app('App\CxC\CxcInteresMora')->encabezado_tabla;

      $tabla = '<table class="table table-bordered table-striped" id="myTable">'.Form::bsTableHeader($encabezado_tabla).'<tbody>';

      foreach ($registros as $fila)
      {
        $tabla .= '<tr>';
          for($i=1;$i<count($fila);$i++)
          {
            $tabla .= '<td class="table-text">
                        '.$fila['campo'.$i].'</td>';
          }
          
          $tabla .= '<td>&nbsp;</td>
                      </tr>';
      }
      $tabla .= '</tbody>
                  </table>';

      return $tabla;
    }


    function contabilizar_registro($contab_cuenta_id,$detalle_operacion,$valor_debito,$valor_credito)
    {
        ContabMovimiento::create( $this->datos + 
                            [ 'contab_cuenta_id' => $contab_cuenta_id ] +
                            [ 'detalle_operacion' => $detalle_operacion] + 
                            [ 'valor_debito' => $valor_debito] + 
                            [ 'valor_credito' => ($valor_credito * -1) ] + 
                            [ 'valor_saldo' => ( $valor_debito - $valor_credito ) ]
                        );
    }
}