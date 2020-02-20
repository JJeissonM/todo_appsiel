<?php

namespace App\Http\Controllers\Contabilidad;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Input;
use DB;
use Auth;
use Form;
use View;
use Cache;

use App\Sistema\Aplicacion;
use App\Sistema\TipoTransaccion;
use App\Core\TipoDocApp;
use App\Sistema\Modelo;
use App\Sistema\Campo;
use App\Core\Tercero;
use App\Core\Empresa;

use App\Contabilidad\ContabCuenta;
use App\Contabilidad\ContabCuentaGrupo;
use App\Contabilidad\ContabDocEncabezado;
use App\Contabilidad\ContabDocRegistro;
use App\Contabilidad\ContabMovimiento;
use App\Contabilidad\ContabReporteEeff;
use App\Contabilidad\ContabBloqueEeff;
use App\Contabilidad\ContabElementoEeff;
use App\Contabilidad\ContabNotaEeff;
use App\Contabilidad\ContabArbolGruposCuenta;

use App\CxC\CxcDocEncabezado;
use App\CxC\CxcDocRegistro;
use App\CxC\CxcMovimiento;
use App\CxC\CxcEstadoCartera;

use App\PropiedadHorizontal\Propiedad;

class ContabReportesController extends Controller
{
    protected $datos = [];
    protected $grupos_cuentas = [];
    protected $total1_reporte = 0;
    protected $total2_reporte = 0;
    protected $lapso1_lbl, $lapso2_lbl, $lapso3_lbl;
    protected $lapso1_ini, $lapso2_ini, $lapso3_ini;
    protected $lapso1_fin, $lapso2_fin, $lapso3_fin;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /*
        Método para probar consultas que se usarán en reportes
        Recibe los parámetros por GET
    */
    public function reporte_prueba()
    {
        

        $tabla = '<style> table, td { border: 1px solid; border-collapsed: collapsed; } </style> <table>';
            $tabla .= '<tr>
                        <td> transaccion_descripcion</td>
                        <td> impuesto_descripcion</td>
                        <td> impuesto_tasa</td>
                        <td> cuenta_descripcion</td>
                        <td> cuenta_codigo </td>
                        <td> producto_descripcion </td>
                        <td> producto_unidad_medida </td>
                        <td> movimiento_tasa </td>
                        <td> valor_debito </td>
                        <td> valor_credito </td>
                            </tr>';
        foreach ($movimiento as $fila)
        {
            $tabla .= '<tr>
                        <td>'.$fila->transaccion_descripcion.'</td>
                        <td>'.$fila->impuesto_descripcion.'</td>
                        <td>'.$fila->impuesto_tasa.'</td>
                        <td>'.$fila->cuenta_descripcion.'</td>
                        <td>'.$fila->cuenta_codigo.'</td>
                        <td>'.$fila->producto_descripcion.'</td>
                        <td>'.$fila->producto_unidad_medida.'</td>
                        <td>'.$fila->movimiento_tasa.'</td>
                        <td>'.$fila->valor_debito.'</td>
                        <td>'.$fila->valor_credito.'</td>
                            </tr>';
        }

        $tabla .= '</table>';
        echo $tabla;
    }

    public function balance_comprobacion()
    {
        $registros = ContabCuenta::where('core_empresa_id','=',Auth::user()->empresa_id)->orderBy('codigo')->get();
        $cuentas['todas'] = '';

        foreach ($registros as $fila) {
            $cuentas[$fila->id]=$fila->codigo." ".$fila->descripcion; 
        }

        $registros_c = ContabCuenta::where('core_empresa_id','=',Auth::user()->empresa_id)->groupBy('contab_cuenta_grupo_id')->get();

        $opciones_c['todos'] = '';
        foreach ($registros_c as $campo) {
            $grupo = DB::table('contab_cuenta_grupos')
                ->where( 'id', $campo->contab_cuenta_grupo_id )
                ->value('descripcion');

            $opciones_c[$campo->contab_cuenta_grupo_id] = $grupo;
        }

        $grupos = $opciones_c;

        $miga_pan = [
                ['url'=>'contabilidad?id='.Input::get('id'),'etiqueta'=>'Contabilidad'],
                ['url'=>'NO','etiqueta'=>'Estados finacieros'],
                ['url'=>'NO','etiqueta'=>'Balance de comprobación']
            ];

        return view('contabilidad.balance_comprobacion',compact('cuentas','grupos','miga_pan'));
    }

    public function contab_ajax_balance_comprobacion(Request $request)
    {
        $contab_grupo_cuenta_id = $request->contab_grupo_cuenta_id;
        $contab_cuenta_id = $request->contab_cuenta_id;
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta = $request->fecha_hasta;
        
        if ( $contab_grupo_cuenta_id != 'todos' ) {
            $detallar_grupo_cuentas = 1;
            // cuando se escoge un grupo de cuenta, se omite la selección de una cuenta específica
            $contab_cuenta_id = 'todas';
        }
        
        $detallar_grupo_cuentas = $request->detallar_grupo_cuentas;
        $detallar_terceros = $request->detallar_terceros;
        $detallar_documentos = $request->detallar_documentos;

        if ( $contab_cuenta_id == 'todas' ) {
            $cuentas_con_movimiento = ContabMovimiento::leftJoin('contab_cuentas','contab_cuentas.id','=','contab_movimientos.contab_cuenta_id')
                                ->where('contab_movimientos.core_empresa_id','=',Auth::user()->empresa_id)
                                ->select( 'contab_cuentas.id','contab_cuentas.codigo','contab_cuentas.descripcion' )
                                ->groupBy('contab_movimientos.contab_cuenta_id')
                                ->get()
                                ->toArray();

            $vista = View::make( 'contabilidad.formatos.balance_comprobacion_1', compact('cuentas_con_movimiento','fecha_desde', 'fecha_hasta') )->render();

            Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );
  
        }else{

        }
   
        return $vista;
    }

    public function impuestos( Request $request )
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta = $request->fecha_hasta;        
        $nivel_detalle = $request->nivel_detalle;

        // Transacciones de ingresos (23) y devoluciones por ventas (38, 41)
        $ingresos = ContabMovimiento::get_movimiento_impuestos( [23, 38, 41], $fecha_desde, $fecha_hasta, $nivel_detalle );
        
        //$ingresos_vista = View::make( 'contabilidad.incluir.tabla_impuestos' , compact('movimiento') )->render();

        // Transacciones de compras (25, 29) y devoluciones por compras (36, 40)
        $compras = ContabMovimiento::get_movimiento_impuestos( [25, 29, 36, 40], $fecha_desde, $fecha_hasta, $nivel_detalle );
        
        $vista = View::make( 'contabilidad.incluir.tabla_impuestos2' , compact('ingresos','compras') )->render();


        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );

        return $vista;
    }

    /*
        generacion_eeff
    */
    public function generacion_eeff()
    {
        $registros = ContabReporteEeff::where('core_empresa_id',Auth::user()->empresa_id)->get();
        $reportes[''] = '';
        foreach ($registros as $fila) {
            $reportes[$fila->id] = $fila->descripcion; 
        }

        $miga_pan = [
                ['url'=>'contabilidad?id='.Input::get('id'),'etiqueta'=>'Contabilidad'],
                ['url'=>'NO','etiqueta'=>'Estados finacieros'],
                ['url'=>'NO','etiqueta'=>'Generación']
            ];

        return view('contabilidad.generacion_eeff',compact('reportes','miga_pan'));
    }

    /*
    ** Cada EEFF es un reporte que tiene asociados grupos de cuentas. Se deben asignar GRUPOS PADRES
    ** Los grupos de cuentas estan estructurados en forma de arbol en una tabla de la base de datos. De manera que al asignar un grupo padre al reporte, se traigan todo sus grupos descendientes hasta llegar a las cuentas
    */
    public function contab_ajax_generacion_eeff(Request $request)
    {
        
        // Solo se debería crear el arbol cuando se crean nuevos grupos
        $this->crear_arbol_grupo_cuentas(); 

        $reporte_id = $request->reporte_id;

        $this->lapso1_lbl = $request->lapso1_lbl;
        $lapso1_lbl = $request->lapso1_lbl;

        $this->lapso1_ini = $request->lapso1_ini;
        $this->lapso1_fin = $request->lapso1_fin;

        $this->lapso2_lbl = $request->lapso2_lbl;
        $lapso2_lbl = $request->lapso2_lbl;
        $this->lapso2_ini = $request->lapso2_ini;
        $this->lapso2_fin = $request->lapso2_fin;
        
        $this->lapso3_lbl = $request->lapso3_lbl;
        $lapso3_lbl = $request->lapso3_lbl;
        $this->lapso3_ini = $request->lapso3_ini;
        $this->lapso3_fin = $request->lapso3_fin;

        //$cols = 1; // cantidad de columnas, una por cada lapso a mostrar 

        $tabla = view( 'contabilidad.incluir.eeff.encabezado_tabla_generacion_eeff', compact('lapso1_lbl','lapso2_lbl','lapso3_lbl') )->render();

        // Obtener el reporte
        $reporte = ContabReporteEeff::find($reporte_id);
        
        // 
        $grupos = $reporte->grupos_cuentas()->orderBy('orden')->get()->toArray();

        foreach ($grupos as $fila) 
        {
            $tabla.=$this->get_arbol_movimiento_grupo_cuenta($fila['pivot']['contab_grupo_cuenta_id'], $this->lapso1_ini,$this->lapso1_fin);         
        }

        $tabla.='<tr>
                    <td> TOTAL </td>
                    <td></td>
                    <td>'.number_format( $this->total1_reporte , 0, ',', '.').'</td>';
        if ( $this->lapso2_lbl != '' ) 
        {
            $tabla.='<td>'.number_format( $this->total2_reporte , 0, ',', '.').'</td>';
        }

        $tabla.='</tr>';

        $tabla.='</tbody> </table>';

        echo $tabla;
    }

    // A los reportes se le asigna el grupo de cuentas superior (Abuelo)
    public function get_arbol_movimiento_grupo_cuenta($grupo_abuelo_id, $fecha_inicial, $fecha_final)
    {

        $empresa_id = Auth::user()->empresa_id;

        // Se obtienen los valores del movimiento
        $cuentas = ContabMovimiento::get_movimiento_arbol_grupo_cuenta($empresa_id, $fecha_inicial, $fecha_final, $grupo_abuelo_id );

        /*$cuentas2 = ContabMovimiento::get_movimiento_arbol_grupo_cuenta($empresa_id, $this->lapso2_ini, $this->lapso2_fin, $grupo_abuelo_id );*/

        // Si hay un segundo lapso, se agrega otro campo de valor al array $cuentas (valor_saldo2)
        if ( $this->lapso2_lbl != '' ) 
        {
            $cuentas2 = ContabMovimiento::get_movimiento_arbol_grupo_cuenta($empresa_id, $this->lapso2_ini, $this->lapso2_fin, $grupo_abuelo_id );

            $tam_cuentas2 = count($cuentas2);

            for ($i=0; $i < count($cuentas); $i++) 
            {
                $agregado = true;
                for ($j=0; $j < $tam_cuentas2; $j++) 
                {
                    // Como se han eliminado elementos del segundo array, se verifica primero si existe la key para el valor de $j
                    if ( isset($cuentas2[$j]) ) 
                    {
                        // Si la cuenta del array2 ya está en el array1, se agrega el valor_saldo2 en el array1
                        if ( $cuentas[$i]['cuenta_id'] == $cuentas2[$j]['cuenta_id']) 
                        {
                            $agregado = true;
                            $cuentas[$i]['valor_saldo2'] = $cuentas2[$j]['valor_saldo'];
                            unset($cuentas2[$j]);
                            break;
                        }else{
                            $agregado = false;
                        }
                    }
                }
                

                // Si la cuenta no está en el segundo array se agrega cero 
                if ( !$agregado ) {
                    $cuentas[$i]['valor_saldo2'] = 0;
                }
            }

            // Después de hacer los recorridos, en el segundo array, quedan los elementos que no están en el primero; entonces, se agregan esos elementos al primer array
            for ($j=0; $j < $tam_cuentas2; $j++) 
            {
                if ( isset($cuentas2[$j]) ) 
                {
                    $valor_saldo2 = $cuentas2[$j]['valor_saldo'];
                    $cuentas2[$j]['valor_saldo'] = 0;
                    $cuentas2[$j]['valor_saldo2'] = $valor_saldo2;
                    $cuentas[$i] = $cuentas2[$j];
                    $i++;
                }
            }

        }

        /*echo "<br> LAPSO 1<br>";
        print_r($cuentas);
        echo "<br>";

        echo "<br> LAPSO 2<br>";
        print_r($cuentas2);
        echo "<br>";

        echo "<br> Fución<br>";
        print_r($cuentas);
        echo "<br>";
        */

        // Se crea el bloque de la tabla a visualizar
        if ( $this->lapso2_lbl != '' ) 
        {
            // Cuando tiene lapso2
            $bloque_tabla = $this->printtd_2($this->ordenar_2($cuentas));
        }else{
            $bloque_tabla = $this->printtd($this->ordenar($cuentas));
        }

        return $bloque_tabla;
    }


    function printtd($cuentas) 
    {
        $tr =  '';
        
        foreach ($cuentas as $cuenta) 
        {
            $tr.='<tr>
                    <td>'.$cuenta["etiqueta"].'</td>
                    <td></td>
                    <td>'.number_format( $cuenta["valor"] , 0, ',', '.').'</td>';

            if ( $this->lapso2_lbl != '' ) 
            {
                $tr.='<td></td>';
            }

            $tr.='</tr>';
            $tr.=$this->printtd($cuenta["hijos"]);
        }
        
        return $tr;
    }

    function ordenar($cuentas) {
        $arr = [];

        foreach ($cuentas as $fila) {
            
            $abuelo = $fila["abuelo_id"];
            $this->acumular(
                $arr, 
                $abuelo, 
                '<span class="label label-default"> <i class="'.$abuelo.'"></i>'.$fila["abuelo_descripcion"]."</span>", 
                $fila["valor_saldo"]
            );
            
            $padre = $fila["padre_id"];
            $this->acumular(
                $arr[$abuelo]["hijos"], 
                $padre, 
                '&nbsp;&nbsp;<span class="label label-primary"> <i class="'.$padre.'"></i>'.$fila["padre_descripcion"]."</span>", 
                $fila["valor_saldo"]
            );
            
            $hijo = $fila["hijo_id"];
            $this->acumular(
                $arr[$abuelo]["hijos"][$padre]["hijos"], 
                $hijo, 
                '&nbsp;&nbsp;&nbsp;&nbsp;<span class="label label-warning"> <i class="'.$hijo.'"></i>'.$fila["hijo_descripcion"]."</span>", 
                $fila["valor_saldo"]
            );
            
            array_push($arr[$abuelo]["hijos"][$padre]["hijos"][$hijo]["hijos"], [
                "etiqueta" =>  '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$fila["cuenta_descripcion"],
                "valor" =>  $fila["valor_saldo"],
                "hijos" => []
            ]);
            

            $this->total1_reporte+=$fila["valor_saldo"];
        }
        
        return $arr;
    }

    function acumular(&$collection, $key, $descripcion, $valor) 
    {
        if(!array_key_exists($key, $collection)) {
            $collection[$key] = [
                "etiqueta" =>  $descripcion,
                "valor" =>  $valor,
                "hijos" => []
            ];
        } else {
            $collection[$key]["valor"] += $valor;
        }
    }

    // cuando el informe tiene un segundo lapso
    function printtd_2($cuentas)
    {
        $tr =  '';
        
        foreach ($cuentas as $cuenta) 
        {
            $tr.='<tr>
                    <td>'.$cuenta["etiqueta"].'</td>
                    <td></td>
                    <td>'.number_format( $cuenta["valor"] , 0, ',', '.').'</td>
                    <td>'.number_format( $cuenta["valor2"] , 0, ',', '.').'</td>
                    </tr>';
            $tr.=$this->printtd_2($cuenta["hijos"]);
        }
        
        return $tr;
    }

    function ordenar_2($cuentas) {
        $arr = [];

        foreach ($cuentas as $fila) {
            $fila["valor_saldo2"] = 0;
            $abuelo = $fila["abuelo_id"];
            $this->acumular_2(
                $arr, 
                $abuelo, 
                '<span class="label label-default"> <i class="'.$abuelo.'"></i>'.$fila["abuelo_descripcion"]."</span>", 
                $fila["valor_saldo"], 
                $fila["valor_saldo2"]
            );
            
            $padre = $fila["padre_id"];
            $this->acumular_2(
                $arr[$abuelo]["hijos"], 
                $padre, 
                '&nbsp;&nbsp;<span class="label label-primary"> <i class="'.$padre.'"></i>'.$fila["padre_descripcion"]."</span>", 
                $fila["valor_saldo"], 
                $fila["valor_saldo2"]
            );
            
            $hijo = $fila["hijo_id"];
            $this->acumular_2(
                $arr[$abuelo]["hijos"][$padre]["hijos"], 
                $hijo, 
                '&nbsp;&nbsp;&nbsp;&nbsp;<span class="label label-warning"> <i class="'.$hijo.'"></i>'.$fila["hijo_descripcion"]."</span>", 
                $fila["valor_saldo"], 
                $fila["valor_saldo2"]
            );
            
            array_push($arr[$abuelo]["hijos"][$padre]["hijos"][$hijo]["hijos"], [
                "etiqueta" =>  '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$fila["cuenta_descripcion"],
                "valor" =>  $fila["valor_saldo"],
                "valor2" =>  $fila["valor_saldo2"],
                "hijos" => []
            ]);
            

            $this->total1_reporte+=$fila["valor_saldo"];
            $this->total2_reporte+=$fila["valor_saldo2"];
        }
        
        return $arr;
    }

    function acumular_2(&$collection, $key, $descripcion, $valor, $valor2) 
    {
        if(!array_key_exists($key, $collection)) {
            $collection[$key] = [
                "etiqueta" =>  $descripcion,
                "valor" =>  $valor,
                "valor2" =>  $valor2,
                "hijos" => []
            ];
        } else {
            $collection[$key]["valor"] += $valor;
            $collection[$key]["valor2"] += $valor2;
        }
    }

    // Almacenar en la base de datos el arbol de grupo de cuentas
    public function crear_arbol_grupo_cuentas()
    {

        $empresa_id = Auth::user()->empresa_id;

        $this->grupos_cuentas = ContabCuentaGrupo::where('core_empresa_id', $empresa_id)->get()->toArray();

        // ITERACIÓN 1
        // Se crea un array de padres (id_padre = 0)
        // y se destina una key_2 del array para almacenar un vector sus padre
        $abuelo = $this->get_hijos(0);

        // ITERACIÓN 2
        // Se recorre el array de padres.
        // por cada "Papá" se recorre todo el array de grupos_cuentas para agregar los hijo en la key_2 de cada "Papá" (id_padre = abuelo[$j][0])
        for($j=0; $j < count($abuelo); $j++) {
            $abuelo[$j][2] = $this->get_hijos( $abuelo[$j][0] );
        }

        

        // ITERACIÓN 3
        for($i=0; $i < count($abuelo); $i++) {

            $padre = $abuelo[$i][2];

            $key = 0;
            for($j=0; $j < count($padre); $j++) {
                array_push($padre[$j][2], $this->get_hijos( $padre[$j][0]) );                
            }

            $abuelo[$i][2] = $padre;

        }
        
        // CUARDAR EL ARBOL

        // Se vacía la tabla
        ContabArbolGruposCuenta::where('core_empresa_id',$empresa_id)
            ->delete();

        for($i=0; $i < count($abuelo); $i++) 
        {
            ContabArbolGruposCuenta::create(
                [ 'core_empresa_id' => $empresa_id ] +
                [ 'abuelo_id' => $abuelo[$i][0] ]+
                ['padre_id' => $abuelo[$i][0] ] +
                ['hijo_id' => $abuelo[$i][0] ] +
                ['nivel' => 1 ] +
                ['abuelo_descripcion' => $abuelo[$i][1] ] +
                ['padre_descripcion' => $abuelo[$i][1] ] +
                ['hijo_descripcion' => $abuelo[$i][1] ]
                );

            $padre = $abuelo[$i][2];

            for($j=0; $j < count($padre); $j++) 
            {
                ContabArbolGruposCuenta::create(
                    [ 'core_empresa_id' => $empresa_id ] +
                    [ 'abuelo_id' => $abuelo[$i][0] ]+
                    ['padre_id' => $padre[$j][0] ] +
                    ['hijo_id' => $padre[$j][0] ] +
                    ['nivel' => 2 ] +
                    ['abuelo_descripcion' => $abuelo[$i][1] ] +
                    ['padre_descripcion' => $padre[$j][1] ] +
                    ['hijo_descripcion' => $padre[$j][1] ]
                );

                $hijo = $padre[$j][2][0];

                for($k=0; $k < count($hijo); $k++) 
                {
                    ContabArbolGruposCuenta::create(
                        [ 'core_empresa_id' => $empresa_id ] +
                        [ 'abuelo_id' => $abuelo[$i][0] ]+
                        ['padre_id' => $padre[$j][0] ] +
                        ['hijo_id' => $hijo[$k][0] ] +
                        ['nivel' => 3 ] +
                        ['abuelo_descripcion' => $abuelo[$i][1] ] +
                        ['padre_descripcion' => $padre[$j][1] ] +
                        ['hijo_descripcion' => $hijo[$k][1] ]
                    );
                }                     
            }   
        }
    }

    public function get_hijos($id_padre)
    {
        $key = 0;
        $padre = [];
        for($i=0;$i<count($this->grupos_cuentas);$i++) 
        {

            if ( $this->grupos_cuentas[$i]['grupo_padre_id'] == $id_padre ) 
            {
                //echo $this->grupos_cuentas[$i]['descripcion']."</br>";
                $padre[$key][0] = $this->grupos_cuentas[$i]['id'];
                $padre[$key][1] = $this->grupos_cuentas[$i]['descripcion'];
                $padre[$key][2] = [];
                $key++;
            }

        }

        return $padre;
    }


    /*
        contab_auxiliar_por_cuenta
    */
    public function contab_auxiliar_por_cuenta()
    {
        $registros = ContabCuenta::where('core_empresa_id','=',Auth::user()->empresa_id)->orderBy('codigo')->get();
        $cuentas[''] = '';
        foreach ($registros as $fila) {
            $cuentas[$fila->codigo]=$fila->codigo." ".$fila->descripcion; 
        }

        // Verificar módulo de propiedad horizontal
        if ( Aplicacion::find(11)->estado == 'Activo'  ) {
            $registros = Propiedad::where('core_empresa_id',Auth::user()->empresa_id)
                               ->orderBy('codigo')->get();
              $opciones[''] = '';
              foreach ($registros as $fila) {
                  $opciones[$fila->id]=$fila->codigo." - ".$fila->nomenclatura; 
              }
            $propiedades = $opciones;
        }else{
            $propiedades = 'NO';
        }
        
        $registros2 = Tercero::where('core_empresa_id','=',Auth::user()->empresa_id)->orderBy('descripcion')->get();
        $terceros[''] = '';
        foreach ($registros2 as $fila) {
            $terceros[$fila->numero_identificacion]=$fila->numero_identificacion." ".$fila->descripcion; 
        }

        $miga_pan = [
                ['url'=>'contabilidad?id='.Input::get('id'),'etiqueta'=>'Contabilidad'],
                ['url'=>'NO','etiqueta'=>'Informes y listados'],
                ['url'=>'NO','etiqueta'=>'Auxiliar por cuenta']
            ];

        return view('contabilidad.auxiliar_por_cuenta', compact('cuentas','miga_pan','terceros','propiedades') );      
    }

    public function contab_ajax_auxiliar_por_cuenta(Request $request)
    {
        $contab_cuenta_id = "%".$request->contab_cuenta_id."%";
        $fecha_inicial = $request->fecha_inicial;
        $fecha_final = $request->fecha_final;

        $numero_identificacion = "%".$request->core_tercero_id."%";        

        if ( $request->codigo_referencia_tercero == '') {
          $codigo_referencia_tercero = '%'.$request->codigo_referencia_tercero.'%';
          $operador = 'LIKE';
        }else{
          $codigo_referencia_tercero = $request->codigo_referencia_tercero;
          $operador = '=';
        }

        $core_empresa_id = Auth::user()->empresa_id;   

        $saldo_inicial = ContabMovimiento::get_saldo_inicial($fecha_inicial, $contab_cuenta_id, $numero_identificacion, $operador, $codigo_referencia_tercero, $core_empresa_id );
        
        $movimiento_cuenta = ContabMovimiento::get_movimiento_cuenta($fecha_inicial, $fecha_final, $contab_cuenta_id, $numero_identificacion, $operador, $codigo_referencia_tercero, $core_empresa_id );

        // 
        $total_debito = 0;
        $total_credito = 0;
        $saldo = 0;
        $j = 0;
        $i = 0;

        // OJO, esto es para una sola cuenta
        $cuenta = ContabCuenta::where( 'codigo', $request->contab_cuenta_id )->get()[0];

        $tabla2 = '<h3>Cuenta: '.$cuenta->codigo.' '.$cuenta->descripcion.'</h3><table id="myTable" class="table table-striped tabla_registros" style="margin-top: -4px;">
                        <thead>
                            <tr>
                                <th>
                                   Fecha
                                </th>
                                <th>
                                   Documento
                                </th>
                                <th>
                                   Tercero
                                </th>
                                <th>
                                   Detalle
                                </th>
                                <th>
                                   Mov. Débito
                                </th>
                                <th>
                                   Mov. Crédito
                                </th>
                                <th>
                                   Saldo
                                </th>
                            </tr>
                        </thead>';

        $tabla2.='<tr  class="fila-'.$j.'" >
                            <td>'.$fecha_inicial.'
                            </td>
                            <td>
                            </td>
                            <td>
                            </td>
                            <td>
                            </td>
                            <td>
                            </td>
                            <td>
                            </td>
                            <td>
                               '.number_format( $saldo_inicial , 0, ',', '.').'
                            </td>
                        </tr>';
        $j++;
        for ($i=0; $i < count($movimiento_cuenta) ; $i++) {           

            $debito = $movimiento_cuenta[$i]['debito'];
            $credito = $movimiento_cuenta[$i]['credito'];

            $saldo = $saldo_inicial + $debito + $credito;
            
            $tabla2.='<tr  class="fila-'.$j.'" >
                            <td>
                               '.$movimiento_cuenta[$i]['fecha'].'
                            </td>
                            <td>
                               '.$movimiento_cuenta[$i]['documento'].'
                            </td>
                            <td>
                               '.$movimiento_cuenta[$i]['tercero'].'
                            </td>
                            <td>
                               '.$movimiento_cuenta[$i]['detalle_operacion'].'
                            </td>
                            <td>
                               '.number_format($debito, 0, ',', '.').'
                            </td>
                            <td>
                               '.number_format($credito, 0, ',', '.').'
                            </td>
                            <td>
                               '.number_format( $saldo , 0, ',', '.').'
                            </td>
                        </tr>';

            $saldo_inicial = $saldo;
            $j++;
            if ($j==3) {
                $j=1;
            }
            $total_debito+=$debito;
            $total_credito+=$credito;
            /**/
        }

        $tabla2.='<tr  class="fila-'.$j.'" >
                            <td colspan="4">
                               &nbsp;
                            </td>
                            <td>
                               '.number_format($total_debito, 0, ',', '.').'
                            </td>
                            <td>
                               '.number_format($total_credito, 0, ',', '.').'
                            </td>
                            <td>
                               '.number_format($saldo, 0, ',', '.').'
                            </td>
                        </tr>';
        $tabla2.='</table>';

        return $tabla2;
    }

    public function contab_pdf_estados_de_cuentas()
    {

        $contab_cuenta_id = Input::get('contab_cuenta_id');

        $fecha_inicial = Input::get('fecha_inicial');
        $fecha_final = Input::get('fecha_final');
        $estado = '%'.Input::get('estado').'%';

        if ( Input::get('codigo_referencia_tercero') == '') {
          $codigo_referencia_tercero = '%'.Input::get('codigo_referencia_tercero').'%';
          $operador = 'LIKE';
        }else{
          $codigo_referencia_tercero = Input::get('codigo_referencia_tercero');
          $operador = '=';
        }
          
        $numero_identificacion = '%'.Input::get('core_tercero_id').'%';

        /*echo "contab_cuenta_id: ".$contab_cuenta_id.'<br/>';
        echo "fecha_inicial: ".$fecha_inicial.'<br/>';
        echo "fecha_final: ".$fecha_final.'<br/>';
        echo "codigo_referencia_tercero: ".$codigo_referencia_tercero.'<br/>';
        echo "operador: ".$operador.'<br/>';
        echo "numero_identificacion: ".$numero_identificacion.'<br/>';
        */
        
        $core_empresa_id = Auth::user()->empresa_id;   

        $saldo_inicial = ContabMovimiento::get_saldo_inicial($fecha_inicial, $contab_cuenta_id, $numero_identificacion, $operador, $codigo_referencia_tercero, $core_empresa_id );
        
        $movimiento_cuenta = ContabMovimiento::get_movimiento_cuenta($fecha_inicial, $fecha_final, $contab_cuenta_id, $numero_identificacion, $operador, $codigo_referencia_tercero, $core_empresa_id );

        $empresa = Empresa::find(Auth::user()->empresa_id);
        $vista = 'imprimir';

        $view = View::make( 'contabilidad.incluir.contab_estados_de_cuentas_pdf',compact('saldo_inicial','movimiento_cuenta','empresa','vista') );

        $tam_hoja = 'Letter';
        $orientacion='portrait';
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($view)->setPaper($tam_hoja,$orientacion);

        return $pdf->download('estado_de_cuentas.pdf');
        
    }

    public function get_saldo_grupo_cuentas_entre_fechas($fecha_ini,$fecha_fin,$grupo_cuenta_id)
    {
        $saldo_inicial = ContabMovimiento::where('contab_movimientos.core_empresa_id','=',Auth::user()->empresa_id)
                ->where('contab_cuentas.contab_cuenta_grupo_id','=',$grupo_cuenta_id)
                ->select( DB::raw( 'sum(contab_movimientos.valor_saldo) AS valor_saldo' ))
                ->get()
                ->toArray()[0]['valor_saldo'];

        return ContabMovimiento::leftJoin('contab_cuentas','contab_cuentas.id','=','contab_movimientos.contab_cuenta_id')->where('contab_movimientos.fecha','>=',$fecha_ini)
                ->where('contab_movimientos.fecha','<=',$fecha_fin)
                ->where('contab_movimientos.core_empresa_id','=',Auth::user()->empresa_id)
                ->where('contab_cuentas.contab_cuenta_grupo_id','=',$grupo_cuenta_id)
                ->select( DB::raw( 'sum(contab_movimientos.valor_saldo) AS valor_saldo' ))
                ->get()
                ->toArray()[0]['valor_saldo'] + $saldo_inicial;
    }

    public function get_saldo_cuentas_entre_fechas($fecha_ini,$fecha_fin,$cuenta_id)
    {
        $saldo_inicial = ContabMovimiento::where('contab_movimientos.fecha','<', $fecha_ini)
                ->where('contab_movimientos.core_empresa_id','=',Auth::user()->empresa_id)
                ->where('contab_movimientos.contab_cuenta_id','=', $cuenta_id )
                ->select( DB::raw( 'sum(contab_movimientos.valor_saldo) AS valor_saldo' ) )
                ->get()
                ->toArray()[0]['valor_saldo'];

        return ContabMovimiento::leftJoin('contab_cuentas','contab_cuentas.id','=','contab_movimientos.contab_cuenta_id')->where('contab_movimientos.fecha','>=',$fecha_ini)
                ->where('contab_movimientos.fecha','<=',$fecha_fin)
                ->where('contab_movimientos.core_empresa_id','=',Auth::user()->empresa_id)
                ->where('contab_cuentas.id','=',$cuenta_id)
                ->select( DB::raw( 'sum(contab_movimientos.valor_saldo) AS valor_saldo' ))
                ->get()
                ->toArray()[0]['valor_saldo'] + $saldo_inicial;
    }



    // Proceso especial para crear los encabezado y el movimiento de cxc con base en el movimiento contable
    public function proceso_1()
    {
        /*echo "Proceso INACTIVO. Descomentar el código para activarlo.";*/
        
        $core_empresa_id = 5;
        $fecha_inicial = '2012-01-01';
        $fecha_final = '2018-12-31';

        //$contab_cuenta_id = '130505'; // empresa = 3 bambuterra
        $contab_cuenta_id = '13050%';

        $operador = '=';
        $numero_identificacion = '%'.''.'%';

        $inmuebles = Propiedad::leftJoin('core_terceros','core_terceros.id','=','ph_propiedades.core_tercero_id')->where('ph_propiedades.core_empresa_id',$core_empresa_id)->select('ph_propiedades.id AS codigo_referencia_tercero','ph_propiedades.codigo AS inmueble','core_terceros.id AS core_tercero_id','core_terceros.descripcion AS tercero')->get();

        $num_registro = 1;
        foreach ($inmuebles as $un_inmueble) {
            $saldo_inicial = ContabMovimiento::get_saldo_inicial($fecha_inicial, $contab_cuenta_id, $numero_identificacion, $operador, $un_inmueble->codigo_referencia_tercero, $core_empresa_id );
            
            $movimiento_cuenta = ContabMovimiento::get_movimiento_cuenta($fecha_inicial, $fecha_final, $contab_cuenta_id, $numero_identificacion, $operador, $un_inmueble->codigo_referencia_tercero, $core_empresa_id );

            // Crear movimiento de cartera con los saldos pendientes ( Saldo != 0 )
            $tabla = $this->crear_movimiento_cartera($saldo_inicial, $movimiento_cuenta);

            if ( count( $tabla[0] ) > 0 ) {

                echo $num_registro.") Tercero: ".$un_inmueble->tercero.' - '.$un_inmueble->codigo_referencia_tercero.'<br/>';
                echo "Cod. Inmueble: ".$un_inmueble->inmueble.'<br/>';
                for ($i=0; $i < count( $tabla[0] ); $i++) {
                    
                    $encabezado = CxcDocEncabezado::create($tabla[0][$i]);

                    CxcDocRegistro::create( ['cxc_doc_encabezado_id' => $encabezado->id] +
                        ['cxc_motivo_id' => 0] +
                        ['cxc_servicio_id' => 1] + 
                        ['valor_unitario' => $encabezado->valor_total] +
                        ['cantidad' => 1] +
                        ['valor_total' => $encabezado->valor_total] +
                        ['descripcion' => $encabezado->descripcion] +
                        ['estado' => 'Activo'] );                    
                    // Crear movimiento
                    $cxc_movimiento = CxcMovimiento::create($tabla[1][$i]);

                    // Crear Estado de cartera
                    CxcEstadoCartera::crear($cxc_movimiento->id, $cxc_movimiento->fecha, 0, $cxc_movimiento->valor_cartera, $cxc_movimiento->estado, $cxc_movimiento->creado_por, $cxc_movimiento->modificado_por);
                    
                    echo 'Encabezado <br/>';
                    print_r( $tabla[0][$i] );
                    echo '<br/>';

                    echo 'Movimiento <br/>';
                    print_r( $tabla[1][$i] );
                    echo '<br/>';
                }
                $num_registro++;
            }
        }
    }


    public function crear_movimiento_cartera($saldo_inicial, $movimiento_cuenta)
    {
        
        $vector_encabezado = [];
        $vector_movimiento = [];
                
        $total_debito = 0;
        $total_credito = 0;
        $saldo = 0;
        $i = 0;

        $linea = 0;

        for ($i=0; $i < count($movimiento_cuenta) ; $i++) 
        {           

            $debito = $movimiento_cuenta[$i]['debito'];

            // Si se trata de un documento de cartera
            if ( $debito > 0 ) {
                $credito = 0;
                $valor_cartera = $debito;
                $tipo_movimiento = 'Cartera';
            }else{
                // Si es un recaudo, se tomará como un anticipo para luego hacer los cruces manualmente
                // Para anticipos el valor_cartera es negativo
                $debito = 0;
                $credito = $movimiento_cuenta[$i]['credito'];
                $valor_cartera = $movimiento_cuenta[$i]['credito'];
                $tipo_movimiento = 'Anticipo';
            }
            
            $valor_total = $valor_cartera;
            
            $vector_aux = ['core_tipo_transaccion_id' => $movimiento_cuenta[$i]['core_tipo_transaccion_id']] + 
                    ['core_tipo_doc_app_id' => $movimiento_cuenta[$i]['core_tipo_doc_app_id']] +
                    ['consecutivo' => $movimiento_cuenta[$i]['consecutivo']] +
                    ['fecha' => $movimiento_cuenta[$i]['fecha']] +
                    ['fecha_vencimiento' => $movimiento_cuenta[$i]['fecha']] +
                    ['core_empresa_id' => $movimiento_cuenta[$i]['core_empresa_id']] +
                    ['core_tercero_id' => $movimiento_cuenta[$i]['core_tercero_id']] +
                    ['codigo_referencia_tercero' => $movimiento_cuenta[$i]['codigo_referencia_tercero'] ] + 
                    ['creado_por' => 'administrator@appsiel.com.co'] + 
                    ['modificado_por' => 'administrator@appsiel.com.co'];


            $vector_encabezado[$linea] = $vector_aux + 
                    ['tipo_movimiento' => $tipo_movimiento] + 
                    ['documento_soporte' => $movimiento_cuenta[$i]['documento_soporte'] ] + 
                    ['descripcion' => $movimiento_cuenta[$i]['detalle_operacion'] ] + 
                    ['valor_total' => $valor_total] +
                    ['estado' => 'Activo'];


            $vector_movimiento[$linea] = $vector_aux + 
                    ['valor_cartera' => $valor_cartera] +
                    ['estado' => 'Pendiente'] +
                    ['detalle_operacion' => $movimiento_cuenta[$i]['detalle_operacion'] ];


            $saldo = $saldo_inicial + $debito + $credito;

            // Se actualiza el saldo incial para la siguiente línea
            $saldo_inicial = $saldo;

            // Se incrementa el registro de los vectores 
            $linea++;

            // Si el saldo llega a cero, se borra el movimiento hcia atrás (todo lo guardado hasta el momento)
            /*if ( $saldo == 0) {
                unset($vector_aux);
                $vector_encabezado = [];
                $vector_movimiento = [];
                $linea = 0;
            }*/
        }

        return [ $vector_encabezado, $vector_movimiento ];
    }

    public function reasignar_grupos_cuentas_form()
    {
        $empresa_id = Auth::user()->empresa_id;
        $tabla = '<table class="table table-striped">
                    <thead>
                        <tr>
                            <th> Código </th>
                            <th> Cuenta </th>
                            <th> Grupo </th>
                        </tr>
                    </thead>
                    <tbody>';

        $cuentas = ContabCuenta::where('core_empresa_id', $empresa_id)->orderBy('codigo','ASC')->get();

        foreach ($cuentas as $fila) {
            $tabla .= '<tr>
                        <td>'.$fila->codigo.'</td>
                        <td>'.$fila->descripcion.'</td>
                        <td>'.$this->get_select_grupo_cuentas($fila->contab_cuenta_grupo_id, $fila->id).'<span id="span_'.$fila->id.'" style="color:red;"></span></td>
                        </tr>';
        }


        $miga_pan = [
                ['url'=>'contabilidad?id='.Input::get('id'),'etiqueta'=>'Contabilidad'],
                ['url'=>'NO','etiqueta'=>'Configuración'],
                ['url'=>'NO','etiqueta'=>'Reasignar grupos a cuentas']
            ];

        return view('contabilidad.reasignar_grupos_cuentas', compact('tabla','miga_pan'));

    }

    function get_select_grupo_cuentas( $grupo_id, $cuenta_id )
    {
        $grupos = ContabCuentaGrupo::where('core_empresa_id', Auth::user()->empresa_id)->get();

        foreach ($grupos as $fila) {
            $vec[$fila->id] = $fila->descripcion;
        }

        return Form::select('cuenta_id_'.$cuenta_id, $vec, $grupo_id, ['id' => $cuenta_id, 'class' => 'combobox2']);
    }

    public function reasignar_grupos_cuentas_save($cuenta_id, $grupo_id)
    {
        ContabCuenta::where('id', $cuenta_id)->update( ['contab_cuenta_grupo_id' => $grupo_id ]);

        return "  Grupo Actualizado.";
    }


}