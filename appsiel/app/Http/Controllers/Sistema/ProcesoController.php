<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Sistema\ModeloController;

use Illuminate\Http\Request;

use Input;
use DB;
use PDF;
use Auth;
use Storage;
use View;
use File;
use Hash;

use App\Http\Requests;

use Spatie\Permission\Models\Role;

use App\Core\PasswordReset;

use App\UserHasRole;
use App\User;

class ProcesoController extends ModeloController
{

    public function form_exportar_importar_tablas_bd()
    {

        $miga_pan = [
                        ['url' => $this->aplicacion->app.'?id='.Input::get('id'), 'etiqueta'=> $this->aplicacion->descripcion ],
                        ['url' => 'NO','etiqueta'=> 'Proceso: Exportar/importar tablas de la BD']
                    ];

        //$tablas_bd = $this->get_array_tablas_bd();

        $tablas_bd = [
                        'permissions',
                        'roles',
                        'role_has_permissions',
                        'sys_campos',
                        'sys_modelos',
                        'sys_modelo_tiene_campos',
                        'sys_reportes',
                        'sys_reporte_tiene_campos',
                        'sys_tipos_transacciones',
                        'pw_seccion'];

        return view( 'core.procesos.exportar_importar_registros_bd', compact( 'miga_pan', 'tablas_bd') );
    }


    public function exportar_tablas_bd( Request $request )
    {
        if ( isset( $request->all()['tablas_a_exportar'] ) )
        {
            return $this->generar_registros_archivo_configuracion( $request->all()['tablas_a_exportar'] );
        }
        
        return 'Ninguna tabla enviada.';
    }

    /*
        Este proceso almacena en el archivo de configuración registros_tablas_bd.php
        todos los registros de las tablas recibidas
    */
    public function generar_registros_archivo_configuracion( array $tablas_a_exportar )
    {
        
        foreach ($tablas_a_exportar as $key => $tabla)
        {
            $valores = DB::table( $tabla )->get();
            $valores_tabla = [];
            $i = 0;
            foreach ($valores as $fila)
            {
                $valores_tabla[$i] = (array)$fila;
                $i++;
            }

            $datos_a_insertar[$tabla] = $valores_tabla;

        }


        /*
            It use PHP‘s var_export function to convert the array to a parsable string representation of the array, just like JSON string and in this case the array gets wrapped into quotes, so following line gives me the array as string:
        */
        $data = var_export($datos_a_insertar, 1);
        //dd($data);

        if( File::put( app_path() . '/../config/registros_tablas_bd.php', "<?php\n return $data ;" ) )
        {   
            return 'Se guardó CORRECTAMENTE el archivo de configuración <b>registros_tablas_bd.php</b>';
            //return redirect( 'config?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo )->with( 'flash_message','Configuración ACTUALIZADA correctamente.' );
        }else{
            return 'No se guardó el archivo de configuración <b>registros_tablas_bd.php</b>. Consultar con el administrador del Sistema.';
        }
    }


    public function visualizar_tablas_archivo()
    {
        // Se obtienen las tablas del archivo de configuración
        $tablas = array_keys( config('registros_tablas_bd') );

        $lista = '<ol>';
        foreach ($tablas as $key => $value) {
            $lista .= '<li>'.$value.'</li>';
        }

        $lista .= '</ol>';

        return $lista;
    }


    /*
        Este proceso almacena en la BD la información contenida en el archivo de configuración registros_tablas_bd.php
        Nota: NO se hacen validaciones de datos
    */
    public function insertar_registros_tablas_bd()
    {
        // Se obtienen las tablas del archivo de configuración
        $tablas = config('registros_tablas_bd');

        // Se recorre cada tabla del archivo
        $cantidad_registros = 0;
        foreach ($tablas as $nombre_tabla => $registros)
        {
            // 1ro. Primero vaciar la tabla
            DB::table( $nombre_tabla )->truncate();

            // 2do. Se almacena cada registro de la tabla (uno por uno)
            foreach ($registros as $fila)
            {
                DB::table( $nombre_tabla )->insert( $fila );
                $cantidad_registros++;
            }
        }

        echo 'Tablas actualizadas correctamente. Se crearon '.($cantidad_registros-1).' registros.';
    }


    // Obtener un array con los nombre de todas las tablas de la base de datos
    public function get_array_tablas_bd()
    {
        $bd_nombre = 'Tables_in_'.env('DB_DATABASE');

        // Obtener array con las tablas de la base de datos
        $tables = DB::select('SHOW TABLES'); // Esto genera un array de objetos
        
        $array = [];
        $i = 0;
        foreach($tables as $table)
        {
            $array[$i] = $table->$bd_nombre;
            $i++;
        }

        return $array;
    }


    // Obtener cadena de los campos de una tabla separador por coma
    public function get_cadena_campos_tabla( $tabla_nombre )
    {
        $campos = DB::select('SHOW COLUMNS FROM '.$tabla_nombre );

        $cadena_campos = '';
        $el_primero = true;
        foreach ($campos as $campo)
        {
            if ( $el_primero )
            {
                $cadena_campos .= $campo->Field;
                $el_primero = false;
            }else{
                $cadena_campos .= ','.$campo->Field;
            }
            
        }

        return $cadena_campos;
    }

    public function generar_lista_tablas_con_sus_campos()
    {
        $tablas = $this->get_array_tablas_bd();

        $salida = '<table border="1">
                        <tr>
                            <td>No.</td>
                            <td>Tabla</td>
                            <td>Field</td>
                            <td>Type</td>
                            <td>Null</td>
                            <td>Key</td>
                            <td>Default</td>
                            <td>Extra</td>
                        </tr>';
        $i = 0;
        foreach ($tablas as $key => $value)
        {
            $campos = DB::select('SHOW COLUMNS FROM '.$value );

            foreach ($campos as $fila)
            {
                $salida .= '<tr>
                                <td>'.$i.'</td>
                                <td>'.$value.'</td>
                                <td>'.$fila->Field.'</td>
                                <td>'.$fila->Type.'</td>
                                <td>'.$fila->Null.'</td>
                                <td>'.$fila->Key.'</td>
                                <td>'.$fila->Default.'</td>
                                <td>'.$fila->Extra.'</td>
                            </tr>';
                $i++;
            }
        }

        $salida .= '</table>';
                
        echo $salida;
    }


    // PROCESO. Resetear contraseñas: Este proceso crea nuevas contraseñas para TODOS los usuarios del perfil seleccionado.
    public function form_password_resets()
    {
        $opciones = Role::all();
        
        $roles['']='';
        foreach ($opciones as $opcion)
        {
            if ( $opcion->id != 1) // Exceptuando al SuperAdmin
            {
                $roles[$opcion->id] = $opcion->name;
            }
        }

       $miga_pan = $this->get_miga_pan($this->modelo, 'Ejecutar proceso');

        // Se llama un formulario específico para cada aplicación
        return view( 'core.form_password_resets', compact( 'roles', 'miga_pan' ) );

    }
    public function config_password_resets( $role_id )
    {
        $usuarios = UserHasRole::leftJoin('users', 'users.id', '=', 'user_has_roles.user_id')
                                ->leftJoin('roles', 'roles.id', '=', 'user_has_roles.role_id')
                                ->where( [ 'roles.id' => $role_id ] )
                                ->select('users.id')
                                ->get()
                                ->toArray();
            
        foreach ($usuarios as $key => $value)
        {   
            $usuario = User::find( $value['id'] );

            if ( !is_null( $usuario ) )
            {
                // Borrar registro anterior, si ya ha sido reseteada
                PasswordReset::where('email',$usuario->email)->delete();

                // Almacenar nueva contraseña en 
                $token = str_random(7);
                PasswordReset::insert([
                                        'email' => $usuario->email,
                                        'token' => $token ]);

                // Actualizar contraseña
                $usuario->password = Hash::make( $token );
                $usuario->save();
            }
        }

        return 'ok';
    }
}
