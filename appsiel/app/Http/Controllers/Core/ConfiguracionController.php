<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Sistema\VistaController;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\User;

use DB;
use Auth;
use Storage;
use Input;
use File;
use Artisan;

use App\Sistema\Aplicacion;
use App\Sistema\Modelo;
use App\Sistema\Campo;
use App\Matriculas\Matriculas;

use App\Http\Requests\ExcelRequest;
use \Excel;

use App\Calificaiones\Asignatura;

class ConfiguracionController extends ModeloController
{

	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		$permisos=Permission::where('core_app_id',Input::get('id'))
                                ->where('parent',0)
                                ->orderBy('orden','ASC')
                                ->get()
                                ->toArray();

        return view('core.index',compact('permisos'));
    }

    public static function nombre_mes($numero_mes){
    	switch($numero_mes){
            case '01':
                $mes="Enero";
                break;
            case '02':
                $mes="Febrero";
                break;
            case '03':
                $mes="Marzo";
                break;
            case '04':
                $mes="Abril";
                break;
            case '05':
                $mes="Mayo";
                break;
            case '06':
                $mes="Junio";
                break;
            case '07':
                $mes="Julio";
                break;
            case '08':
                $mes="Agosto";
                break;
            case '09':
                $mes="Septiembre";
                break;
            case '10':
                $mes="Octubre";
                break;
            case '11':
                $mes="Noviembre";
                break;
            case '12':
                $mes="Diciembre";
                break;
            default:
                $mes="";
                break;
        }
        return $mes;
    }

    /*
    **
    **
    */
    public static function creacion_masiva_registros_form()
    {
        dd('El método campos_en_fila() en la línea 191 de este archivo ya no existe, buscar otra forma.');

        $registros = Modelo::orderBy('descripcion')->get();
        foreach ($registros as $fila) {
            $modelos[$fila->id]=$fila->descripcion; 
        }

        $miga_pan = [
                ['url'=>'configuracion?id='.Input::get('id'),'etiqueta'=>'Configuración'],
                ['url'=>'NO','etiqueta'=>'Creación masiva de registros']
            ];

        return view('core.creacion_masiva_registros',compact('modelos','miga_pan'));
    }

    /*
    **
    **
    */
    public static function creacion_masiva_registros_procesar(Request $request)
    {
        // se crea una tabla con tantas colunas como campos tenga el modelo
        $modelo = Modelo::find($request->modelo_id);

        $core_empresa_id = Auth::user()->empresa_id;

        $tabla = '<table class="table table-striped" id="tabla_registros">
                        <thead>
                            <tr>';

        // Se obtienen los campos asociados a ese modelo
        $lista_campos = $modelo->campos()->orderBy('orden')->get()->toArray();        
        // Se recorre la lista de campos para formatear o ajustar los valores según el tipo de campo
        for ($i=0; $i < count($lista_campos); $i++) 
        { 
            if($lista_campos[$i]['tipo'] != 'bsLabel')
            {
                $tabla .= '<th data-override="'.$lista_campos[$i]['name'].'">'.$lista_campos[$i]['descripcion'].'</th>';
            }
        }
        $tabla .= '</thead>
                    <tbody>';

        $excel = Excel::selectSheetsByIndex(0)->load($request->archivo);
        
        foreach ($excel->all() as $key => $value) 
        {

            // Se obtiene cada fila del excel (exceptuando el encabezado) y se converte en un array, cuyas key son los nombres de la primera fila
            $row = $value->toArray();

            // Se obtiene un array con las keys de la fila
            $Keys = array_keys($row);

            // Si el valor de la primera celda está vacio, se termina el foreach (cuando ya no hay más columnas)
            if ( $row[$Keys[0]] == '') 
            {
                //echo ". fuera";
                break;
            }

            $lista_campos = ModeloController::get_campos_modelo($modelo,'','create');

            for ($i=0; $i < count($lista_campos); $i++) 
            { 
                for ($j=0; $j < count($Keys); $j++) { 
                    if ( $lista_campos[$i]['name'] == $Keys[$j] ) 
                    {
                        $lista_campos[$i]['value'] = $row[$Keys[$j]];
                    }
                }
            }

            $tabla .= VistaController::campos_en_fila($lista_campos);         

        }

        $tabla .= '</tbody>
                    </table>';


        //echo $tabla;

        $miga_pan = [
                ['url'=>'configuracion?id='.Input::get('id'),'etiqueta'=>'Configuración'],
                ['url'=>'NO','etiqueta'=>'Creación masiva de registros'],
                ['url'=>'NO','etiqueta'=>'Crear']
            ];

        $modelo_id = $request->modelo_id;

        return view( 'core.creacion_masiva_registros_create',compact('tabla','miga_pan','modelo_id') );
    }

    /*
    */
    public static function creacion_masiva_registros_store(Request $request)
    {
        // se crea una tabla con tantas colunas como campos tenga el modelo
        $modelo = Modelo::find($request->url_id_modelo);

        $core_empresa_id = Auth::user()->empresa_id;


        $tabla_registros = json_decode($request->lista_registros);

        echo "<br>Tabla registros: <br>";
        print_r($tabla_registros);

        for ($i=0; $i < count($tabla_registros); $i++) 
        {
            foreach ($tabla_registros[$i] as $key => $value)
            {
                $valor_celda = explode("-", $value);
                $datos[$key] = $valor_celda[0];
            }

            app($modelo->name_space)->create( array_merge( $datos, ['core_empresa_id' => $core_empresa_id] ) );
        }

        return redirect('web?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Resgistros CREADOS correctamente.');
    }

    /*
    ** Para visualizar los modelos que tiene
    ** Un campo específico
    */
    public static function modelo_tiene_campos($modelo_id, $campo_id)
    {
      
      if ($modelo_id == 0) {
          $miga_pan = [
                ['url'=>'NO','etiqueta'=>'Modelos del campo']
            ];

        $campos = Campo::find($campo_id);
        $modelos = $campos->modelos;
      }

      //dd($campos);

      return view('core.modelo_tiene_campos',compact('miga_pan','modelos','campos') );
    }


    // Muestra el formulario para editar la configuración general de la aplicación
    public function config_form()
    {
        $app = Aplicacion::find( Input::get('id') );
        
        $parametros = config( $app->app );

        $miga_pan = [
                        [ 'url' => $app->app.'?id='.Input::get('id'),'etiqueta'=> $app->descripcion ],
                        [ 'url' => 'NO','etiqueta'=>'Configuración']
                    ];
        
        $archivo_js = 'assets/js/configuracion/config.js';

        // Se llama un formulario específico para cada aplicación
        return view( 'core.config_aplicacion.'.$app->app, compact( 'parametros','miga_pan','archivo_js' ) );

    }


    // Actualiza la configuración general de la aplicación, esto lo hace sobreescribiendo el contenido del archivo según el nombre de la aplicación
    public function guardar_config(Request $request)
    {
        $app = Aplicacion::find( $request->url_id );

        $array = [];
        // NOTA: La variable que no sea enviada en el request será borrada del archivo de configuración
        // Si se quiere agregar una nueva variable al archivo de configuración, hay que agregar también un campo nuevo al formulario específico de configuración de la aplicación
        foreach ( $request->all() as $key => $value)
        {
            // Se excluyen variables innecesarias
            if ( ( $key != '_token' ) && ( $key != 'url_id' ) ) 
            {
                $array[$key] = $value;
            }
        }

        /*
            It use PHP‘s var_export function to convert the array to a parsable string representation of the array, just like JSON string and in this case the array gets wrapped into quotes, so following line gives me the array as string:
        */
        $data = var_export($array, 1);

        if( File::put( app_path() . '/../config/'.$app->app.'.php', "<?php\n return $data ;") )
        {
            return redirect( 'config?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo )->with( 'flash_message','Configuración ACTUALIZADA correctamente.' );
        }else{
            echo 'No se guardó el archivo de configuración << '.$app->app.'.php >>. Consultar con el administrador del Sistema.';
        }
    }
}
