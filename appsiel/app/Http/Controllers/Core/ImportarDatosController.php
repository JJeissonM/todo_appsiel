<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use DB;
use View;
use Auth;
use Input;
use Form;

use App\Core\Colegio;
use App\Core\Empresa;
use App\Core\Tercero;
use App\Sistema\Modelo;

// Modelos

use App\Contabilidad\ContabCuentaGrupo;
use App\Contabilidad\ContabCuenta;

use App\CxC\CxcServicio;


use App\Http\Requests\ExcelRequest;
use \Excel;


class ImportarDatosController extends Controller
{	

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
		$this->middleware('auth');
    }
    
    // Muestra formulario 
    public function formulario()
    {
        $registros = Modelo::orderBy('descripcion')->get();  
        $modelos['']=''; 
        foreach ($registros as $fila) {
            $modelos[$fila->id]=$fila->descripcion; 
        }

        $registros2 = ContabCuenta::where('core_empresa_id','=',Auth::user()->empresa_id)->orderBy('codigo')->get();
        $cuentas[''] = '';
        foreach ($registros2 as $fila) {
            $cuentas[$fila->id]=$fila->codigo." ".$fila->descripcion; 
        }

        $registros3 = CxcServicio::where('core_empresa_id','=',Auth::user()->empresa_id)->orderBy('descripcion')->get();  
        $cxc_servicios['']=''; 
        foreach ($registros3 as $fila) {
            $cxc_servicios[$fila->id] = $fila->descripcion." (".$fila->precio_venta.") "; 
        }        

        $miga_pan = [
                        ['url'=>'configuracion?id='.Input::get('id'),'etiqueta'=>'Configuración'],
                        ['url'=>'NO','etiqueta'=>'Importar Datos']
                    ];

        return view('core.data_import', compact('modelos','miga_pan','cuentas','cxc_servicios'));

    }

    // Procesa el archivo cargado y almacena los datos
    public function importar_formulario(Request $request)
    {
        $modelo = Modelo::find($request->modelo_id);

        $core_empresa_id = Auth::user()->empresa_id;
        $email_usuario = Auth::user()->email;
        $fecha = date('Y-m-d h:i:s');

        $excel = Excel::selectSheetsByIndex(0)->load($request->archivo);        

        $i=0;
        foreach ($excel->all() as $key => $value) {

            // Se obtiene la primera fila del excel (exceptuando el encabezado) y se converte en un array, cuyas key son los nombres de la primera fila
            $row = $value->toArray();

            // Se obtiene un array con las keys de la fila
            $Keys = array_keys($row);
            // Si el valor de la primera celda está vacio, se termina el foreach 
            if ( $row[$Keys[0]] == '') {
                //echo ". fuera";
                break;
            }

            // Se Agregan otros campos y se cambia el contab_cuenta_grupo_id
            $datos[$i] = array_merge( $value->toArray(),
                        [ 'core_empresa_id' => $core_empresa_id,
                            'creado_por' => $email_usuario,
                            'modificado_por' => $email_usuario, 
                            'created_at' => $fecha, 
                            'updated_at' => $fecha,
                            'estado' => 'Activo' ] );

            $i++;            

        }

        switch ($modelo->name_space) {
            case 'App\Contabilidad\ContabCuentaGrupo':
                $cantidad_registros = $this->contab_grupo_cuentas($modelo, $datos, $core_empresa_id);
                break;

            case 'App\Contabilidad\ContabCuenta':
                $cantidad_registros = $this->contab_cuentas($modelo, $datos, $core_empresa_id);
                break;

            case 'App\Core\Tercero':
                $cantidad_registros = $this->terceros($modelo, $datos, $core_empresa_id, $request->contab_anticipo_cta_id, $request->contab_cartera_cta_id, $request->contab_cxp_cta_id);
                break;

            case 'App\PropiedadHorizontal\Propiedad':
                $cantidad_registros = $this->inmuebles($modelo, $datos, $request->cxc_servicio_id);
                break;

            case 'App\Contabilidad\ContabMovimiento':
                $cantidad = count($datos);
                for ($j=0; $j < $cantidad; $j++) {                    
                    // Se Almacena el registro
                    $registro_creado = app($modelo->name_space)->create( $datos[$j] );
                    $cantidad_registros = $j;
                }
                break;
            
            default:
                $cantidad = count($datos);
                for ($j=0; $j < $cantidad; $j++) {                    
                    // Se Almacena el registro
                    $registro_creado = app($modelo->name_space)->create( $datos[$j] );
                    $cantidad_registros = $j;
                }
                $cantidad_registros++;
                break;
        }

        return redirect('importar/formulario?id=7')->with('flash_message', 'Proceso finalizado. <br/> Cantidad de registros creados: '.$cantidad_registros);
    }    

    public function contab_grupo_cuentas($modelo, $datos, $core_empresa_id)
    {
        //CREACION DEL GRUPO DE CUENTAS POR DEFECTO PARA LA EMPRESA

                $cantidad = count($datos);
        for ($i=0; $i < $cantidad; $i++) 
        {
            // Se obtiene el ID del grupo de cuenta PADRE a través de la descripcion
            $contab_cuenta_grupo_id = ContabCuentaGrupo::where('descripcion',$datos[$i]['grupo_padre'])
                                ->where('core_empresa_id',$core_empresa_id)
                                ->value('id');

            if ( $contab_cuenta_grupo_id > 0) {
                # code...
            }else{
                $contab_cuenta_grupo_id = 0;
            }

            // Se cambia el contab_cuenta_grupo_id
            $datos2 = array_merge($datos[$i],
                        [ 'grupo_padre_id' => $contab_cuenta_grupo_id ] );
            
            // Almaceno el registro
            $registro_creado = app($modelo->name_space)->create( $datos2 );

        }      

        return $i;
        
    }

    public function contab_cuentas($modelo, $datos, $core_empresa_id)
    {
        //CREACION DE CUENTAS PARA LA EMPRESA

        // NOTA: YA DEBEN ESTAR CREADOS LOS GRUPOS DE CUENTAS PARA LA EMPRESA A LA QUE SE VAYAN A CARGAR
        $cantidad = count($datos);
        for ($i=0; $i < $cantidad; $i++) 
        {

            // se obtiene el ID del grupo de cuenta a través de la descripcion
            $contab_cuenta_grupo_id = ContabCuentaGrupo::where('descripcion',$datos[$i]['contab_cuenta_grupo_id'])
                                ->where('core_empresa_id',$core_empresa_id)
                                ->value('id');

            // Se cambia el contab_cuenta_grupo_id
            $datos2 = array_merge($datos[$i],
                        [ 'contab_cuenta_grupo_id' => $contab_cuenta_grupo_id ] );
            
            // Almaceno el registro
            $registro_creado = app($modelo->name_space)->create( $datos2 );

        }
        
        return $i;
    }


    public function terceros($modelo, $datos, $core_empresa_id, $contab_anticipo_cta_id, $contab_cartera_cta_id, $contab_cxp_cta_id)
    {
        //CREACION DE TERCEROS

        // NOTA: YA DEBEN ESTAR CREADAS LAS CUENTAS

        $creados = 0;
        $cantidad = count($datos);
        for ($i=0; $i < $cantidad; $i++) 
        {

            $core_tercero_id = Tercero::where(['numero_identificacion' => $datos[$i]['numero_identificacion'], 'core_empresa_id' => $core_empresa_id ])->value('id');
            
            //Se crea el tercero, Solo si SU numero_documento no existe para la empresa
            if ( $core_tercero_id == '' ) {
                // Se Agregan otros campos
                $datos2 = array_merge( $datos[$i],
                            [ 'contab_anticipo_cta_id' => $contab_anticipo_cta_id,
                                'contab_cartera_cta_id' => $contab_cartera_cta_id,
                                'contab_cxp_cta_id' => $contab_cxp_cta_id ] );
                
                // Se Almacena el registro
                $registro_creado = app($modelo->name_space)->create( $datos2 );
                $creados++;
            }         
            
        }

        return $creados;

    }


    public function inmuebles($modelo, $datos, $cxc_servicio_id)
    {
        //CREACION DE INMUEBLES

        // NOTA: YA DEBEN ESTAR CREADOS LOS TERCEROS Y EL SERVICIO POR DEFECTO
        $cantidad = count($datos);
        for ($i=0; $i < $cantidad; $i++) {

            // Se Agregan otros campos y se cambia el contab_cuenta_grupo_id
            $datos2 = array_merge( $datos[$i],
                        [ 'cxc_servicio_id' => $cxc_servicio_id ] );
            
            // Se Almacena el registro
            $registro_creado = app($modelo->name_space)->create( $datos2 );
        }

        return $i;    
    }
    


}