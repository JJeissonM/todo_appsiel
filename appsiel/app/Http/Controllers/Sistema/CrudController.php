<?php

namespace App\Http\Controllers\Sistema;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Input;
use DB;
use PDF;
use Auth;
use Storage;
use View;
use Yajra\Datatables\Facades\Datatables;


use App\User;

use App\Sistema\Html\Boton;
use App\Sistema\TipoTransaccion;
use App\Sistema\Aplicacion;
use App\Sistema\Modelo;
use App\Sistema\Campo;

use App\Core\TipoDocApp;
use App\Core\Empresa;
use App\Core\Tercero;
use App\Core\ModeloEavValor;
use App\Matriculas\Matricula;
use App\Calificaciones\Asignatura;
use App\Core\ConsecutivoDocumento;

use App\Inventarios\InvMovimiento;
use App\Inventarios\InvBodega;

use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;

use App\Contabilidad\ContabCuenta;
use App\PropiedadHorizontal\Propiedad;

class CrudController extends Controller
{

    protected $empresa, $app, $modelo, $transaccion, $variables_url;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function set_variables_globales()
    {
        $this->empresa = Empresa::find( Auth::user()->empresa_id );
        $this->app = Aplicacion::find( Input::get('id') );
        $this->modelo = Modelo::find( Input::get('id_modelo') );
        $this->transaccion = TipoTransaccion::find( Input::get('id_transaccion') );

        $this->variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion');
    }


    /*
    //     A L M A C E N A R UN NUEVO REGISTRO
    */
    public function store(Request $request)
    {   
        // Se crea un nuevo registro para el ID del modelo enviado en el request 
        $registro = $this->crear_nuevo_registro( $request, $request->url_id_modelo );

        $this->almacenar_imagenes( $request, $modelo->ruta_storage_imagen, $registro );

        // Si HAY tareas adicionales u otros modelos que afectar (almacenar en otras tablas)
        if ($modelo->controller_complementario!='') 
        {
            return \App::call( $modelo->controller_complementario.'@store',['request'=>$request,'registro'=>$registro] );
        }else{ // Si no, se envía a la vista SHOW del ModeloController
            return redirect( 'web/'.$registro->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo )->with( 'flash_message','Registro CREADO correctamente.' );
        }

    }

    /*
        Crear nuevo registro con los datos enviados por POST
        La función recibe un objeto Request y una instancia del modelo que se está creando
        Retorna una instancia del registro creado
    */
    public static function crear_nuevo_registro( request $request, $modelo_id )
    {

        $modelo = Modelo::find( $modelo_id );

        CrudController::validar_requeridos_y_unicos($request, $modelo);

        // Se verifican si vienen campos con valores tipo arra_tokeny. Normalmente para los campos tipo chexkbox.
        foreach ( $request->all() as $key => $value)
        {
            if ( is_array($value) )
            {
                $request[$key] = implode(",", $value);
            }
        }

        // Crear el nuevo registro
        $registro = app($modelo->name_space)->create($request->all());

        // Si se está almacenando una transacción que maneja consecutivo
        if ( isset($request->consecutivo) and isset($request->core_tipo_doc_app_id) ) 
        {
            // Seleccionamos el consecutivo actual (si no existe, se crea) y le sumamos 1
            $consecutivo = TipoDocApp::get_consecutivo_actual($request->core_empresa_id,$request->core_tipo_doc_app_id) + 1;

            // Se incementa el consecutivo para ese tipo de documento y la empresa
            TipoDocApp::aumentar_consecutivo($request->core_empresa_id,$request->core_tipo_doc_app_id);

            $registro->consecutivo = $consecutivo;
            $registro->save();
        }

        return $registro;

    }


    // USAR Solo cuando se está almacenando un nuevo registro
    // !!! Revisar cuando se está editando
    public static function validar_requeridos_y_unicos($request, $registro_modelo)
    {


        // Obtener la table de ese modelo
        //$any_registro = New $registro_modelo->name_space;
        $nombre_tabla = $registro_modelo->getTable();       

        // LLamar a los campos del modelo para verificar los que son requeridos
        $lista_campos = $registro_modelo->campos->toArray();

        $cant = count($lista_campos);
        for ($i=0; $i < $cant; $i++) 
        { 
            // Se valida solo si el campo pertenece al Modelo directamente
            if ( in_array( $lista_campos[$i]['name'], $registro_modelo->getFillable() )  ) 
            {
                if ($lista_campos[$i]['requerido']) 
                {
                    $this->validate($request,[$lista_campos[$i]['name']=>'required']);
                }

                if ($lista_campos[$i]['unico']) 
                {
                    $this->validate($request,[$lista_campos[$i]['name']=>'unique:'.$nombre_tabla]);
                }
            }
                
        }
    }

    /*
      * Esta función debe estar en ImagenController y en lugar de recibir todo el $request, solo necesita el array archivos tipo file
    */
    public function almacenar_imagenes( $request, $ruta_storage_imagen, $registro, $modo = null )
    {
        // Si se envía archivos tipo file (imagenes, adjuntos)
        $archivos_enviados = $request->file();
        foreach ($archivos_enviados as $key => $value) 
        {
            // Si se envía un nuevo archivo, se borran el archivo anterior del disco
            if ( $modo == 'edit' && $request->file($key) != '') 
            {
                Storage::delete($ruta_storage_imagen.$registro->$key);
            }

            $archivo = $request->file($key);

            $extension =  $archivo->clientExtension();

            // Crear un nombre unico para el archivo con su misma extensión
            $nuevo_nombre = uniqid().'.'.$extension;

            // Guardar la imagen en disco
            Storage::put( $ruta_storage_imagen.$nuevo_nombre, file_get_contents( $archivo->getRealPath() ) );

            // Guardar nombre en la BD
            $registro->$key = $nuevo_nombre;
            $registro->save();
        }
    }


    // FOMRULARIO PARA EDITAR UN REGISTRO
    public function edit($id)
    {
        // Se obtiene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);

        $lista_campos = $this->get_campos_modelo($modelo,$registro,'edit');

        // Se crear un array para generar el formulario
        // Este array se envía a la vista layouts.create, que carga la platilla principal,
        // La vista layouts.create incluye a la vista core.vistas.form_create que es la usa al array
        // form_create para generar un formulario html 
        $form_create = [
                        'url' => $modelo->url_form_create,
                        'campos' => $lista_campos
                    ];

        $miga_pan = $this->get_miga_pan($modelo,$registro->descripcion);

        $archivo_js = app($modelo->name_space)->archivo_js;

        $url_action = 'web/'.$id;
        if ($modelo->url_form_create != '') {
            $url_action = $modelo->url_form_create.'/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo');
        }

        return view('layouts.edit',compact('form_create','miga_pan','registro','archivo_js','url_action'));        
    }



    //     A L M A C E N A R  LA MODIFICACION DE UN REGISTRO
    public function update(Request $request, $id)
    {

        // Se obtiene el modelo según la variable modelo_id de la url
        $modelo = Modelo::find($request->url_id_modelo);

        // Se obtinene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);

        $registro2 = '';
        // Si se envían datos tipo file
        //if ( count($request->file()) > 0)
        if( !empty( $request->file() ) )
        {   
            // Copia identica del registro del modelo, pues cuando se almacenan los datos cambia la instancia
            $registro2 = $registro;
        }

        // LLamar a los campos del modelo para verificar los que son requeridos
        // y los que son únicos
        $lista_campos = $modelo->campos->toArray();
        $cant = count($lista_campos);
        for ($i=0; $i < $cant; $i++) {
            if ( $lista_campos[$i]['editable'] == 1 ) 
            { 
                if ($lista_campos[$i]['requerido']) 
                {
                    $this->validate($request,[$lista_campos[$i]['name']=>'required']);
                }
                if ($lista_campos[$i]['unico']) 
                {
                    $this->validate($request,[$lista_campos[$i]['name']=>'unique:'.$registro->getTable().','.$lista_campos[$i]['name'].','.$id]);
                }
            }
            // Cuando se edita una transacción
            if ($lista_campos[$i]['name']=='movimiento') {
                $lista_campos[$i]['value']=1;
            }
        }

        // Se verifican si vienen campos con valores tipo array. Normalmente para los campos tipo chexkbox.
        foreach ( $request->all() as $key => $value)
        {
            if ( is_array($value) )
            {
                $request[$key] = implode(",", $value);
            }
        }

        $registro->fill( $request->all() );
        $registro->save();

        $this->almacenar_imagenes( $request, $modelo->ruta_storage_imagen, $registro2, 'edit' );
        
        if ($modelo->controller_complementario!='') {
            return \App::call($modelo->controller_complementario.'@update',['request'=>$request,'id'=>$id]);
        }else{
            return redirect('web/'.$registro->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Registro MODIFICADO correctamente.');
        }/**/
    }

    /*
        **
    */
    // VISTA TIPO TABLA PARA MOSTRAR UN REGISTRO
    public function show($id)
    {
        // Se obtiene el registro del modelo indicado y el anterior y siguiente registro
        $registro = app($modelo->name_space)->find($id);
        $reg_anterior = app($modelo->name_space)->where('id', '<', $registro->id)->max('id');
        $reg_siguiente = app($modelo->name_space)->where('id', '>', $registro->id)->min('id');
        
        // Se obtienen los campos asociados a ese modelo
        $lista_campos1 = $modelo->campos()->orderBy('orden')->get();
        
        $lista_campos = $this->asignar_valores_de_campo_al_registro($modelo, $registro, $lista_campos1->toArray() );

        $form_create = [
                        'url' => $modelo->url_form_create,
                        'campos' => $lista_campos
                    ];
        
        $miga_pan = ModeloController::get_miga_pan($modelo,$registro->descripcion);

        // Se le asigna a cada variable url, su valor en el modelo correspondiente
        $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo');
        if ($modelo->url_crear!='') {
            $url_crear = $modelo->url_crear.$variables_url;    
        }
        if ($modelo->url_edit!='') {
            $url_edit = $modelo->url_edit.$variables_url;
        }

        // Para lo modelos que tienen otro modelo relacionado. Ejemplo, El modelo Modelo tiene Campos. El modelo Cuestionario, tiene Preguntas
        $respuesta = ModeloController::get_tabla_relacionada($modelo,$registro);
        
        $tabla=$respuesta['tabla'];
        $opciones = $respuesta['opciones'];
        $registro_modelo_padre_id = $respuesta['registro_modelo_padre_id'];
        $titulo_tab = $respuesta['titulo_tab'];
        
        return view('layouts.show',compact('form_create','miga_pan','registro','url_crear','url_edit','tabla','opciones','registro_modelo_padre_id','reg_anterior','reg_siguiente','titulo_tab'));       
    }


    /*
        ** Esta función crea el array lista_campos que es el que se va a pasar a las vistas (create, edit, show) para visualizar los campos a través de VistaController según los tipos de campos y la vista.
        ** 
        $lista_campo = [ 'tipo', 'name', 'descripcion', 'opciones', 'value', 'atributos', 'definicion', 'html_clase', 'html_id', 'requerido', 'editable', 'unico' ];
        
        Por ahora solo se usa para la vista show
    */
    function asignar_valores_de_campo_al_registro($modelo, $registro, $lista_campos )
    {
        // Se recorre la lista de campos 
        // para formatear-asignar el valor correspondiente del registro del modelo 
        $cantidad_campos = count($lista_campos);

        for ($i=0; $i < $cantidad_campos; $i++) 
        {
            //echo $i.' '.$lista_campos[$i]['name'].'<br/>';
            $nombre_campo = $lista_campos[$i]['name'];

            if ( isset( $registro->$nombre_campo ) ) 
            {
                $lista_campos[$i]['value'] = $registro->$nombre_campo;
            }

            // PARA LAS ACTIVIDADES ESCOLARES modelo_id=38
            if ($lista_campos[$i]['name']=='asignatura_id' and $modelo->id==38) {
                $lista_campos[$i]['opciones'] = 'table_asignaturas';
            }            

            if ($lista_campos[$i]['tipo'] == 'imagen') 
            {
                if ( $registro->$nombre_campo == '' && $nombre_campo == 'imagen') {
                    $campo_imagen = 'avatar.png';
                    $btn_quitar_img = '';
                }else{
                    $campo_imagen = $registro->$nombre_campo;
                    $btn_quitar_img = '<a type="button" class="close" href="'.url('quitar_imagen?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&registro_id='.$registro->id).'" title="Quitar imagen">&times;</a>';
                }
                $url = config('configuracion.url_instancia_cliente')."/storage/app/".$modelo->ruta_storage_imagen.$campo_imagen;
                $imagen = '<div class="form-group" style="border:1px solid gray; text-align:center; overflow:auto;" oncontextmenu="return false" onkeydown="return false">'.$btn_quitar_img.'<img alt="imagen.jpg" src="'.asset($url).'" style="width: auto; height: 160px;" />
                        </div>';
                $lista_campos[$i]['value'] = $imagen;
            }
        } // Cierre for cada campo

        return $lista_campos;
    }

    public function eliminar_registro($id)
    {
        $this->set_variables_globales();

        if( method_exists( app($this->modelo->name_space), 'validar_eliminacion') )
        {
            $mensaje = app($this->modelo->name_space)->validar_eliminacion($id);
            if( $mensaje != 'ok' )
            {
                return redirect('web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'))->with('mensaje_error','Registro No puede ser ELIMINADO. '.$mensaje );
            }
        }

        $registro = app($this->modelo->name_space)->find($id);

        $registro->delete();

        return redirect('web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'))->with('flash_message','Registro ELIMINADO correctamente.');
    }

}