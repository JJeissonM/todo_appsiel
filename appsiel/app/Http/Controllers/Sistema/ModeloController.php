<?php

namespace App\Http\Controllers\Sistema;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use Input;
use DB;
use PDF;
use Auth;
use Storage;
use View;
use Yajra\Datatables\Facades\Datatables;


use App\Sistema\Html\Boton;
use App\Sistema\Html\MigaPan;

use App\Sistema\Aplicacion;
use App\Sistema\TipoTransaccion;
use App\Core\TipoDocApp;
use App\Sistema\Modelo;
use App\Core\ModeloEavValor;
use App\User;

use App\Calificaciones\Logro;



use App\Inventarios\InvBodega;

use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;


class ModeloController extends Controller
{
    protected $aplicacion, $modelo, $datos;

    public function __construct()
    {
        // Se obtiene el modelo
        if ( !is_null( Input::get('id_modelo') ) )
        {
            $this->modelo = Modelo::find(Input::get('id_modelo'));
        }

        // No requiere autenticación para el CRUD del modelo ClienteWeb (id_modelo=218)
        if ( !is_null( $this->modelo ) )
        {
            if ( $this->modelo->id != 218 )
            {
                $this->middleware('auth');
            }
        }else{
            //$this->middleware('auth');
        }
                    

        $this->aplicacion = Aplicacion::find(Input::get('id'));

    }

    /*
        * Muestra una tabla con los registros de un modelo
    */
    public function index()
    {
        $miga_pan = MigaPan::get_array($this->aplicacion, $this->modelo, 'Listado');

        $encabezado_tabla = app($this->modelo->name_space)->encabezado_tabla;
        if (is_null($encabezado_tabla)) {
            $encabezado_tabla = [];
        }

        $id_transaccion = TipoTransaccion::where('core_modelo_id', (int) Input::get('id_modelo') )->where('estado', 'Activo' )->value('id');

        // Se le asigna a cada variable url, su valor en el modelo correspondiente
        $variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . $id_transaccion;

        $acciones = $this->acciones_basicas_modelo( $this->modelo, $variables_url );
        
        $url_crear = $acciones->create;
        $url_edit = $acciones->edit;
        $url_print = $acciones->imprimir;
        $url_ver = $acciones->show;
        $url_estado = $acciones->cambiar_estado;
        $url_eliminar = $acciones->eliminar;

        $botones = [];
        $enlaces = json_decode( $acciones->otros_enlaces );

        if( !is_null($enlaces) )
        {
            $i = 0;
            foreach ($enlaces as $fila)
            {
                $botones[$i] = new Boton($fila);
                $i++;
            }
        }

        // Si el modelo tiene un archivo js particular
        $archivo_js = app($this->modelo->name_space)->archivo_js;

        $registros = [];

        if (method_exists(app($this->modelo->name_space), 'consultar_registros'))
        {
            $registros = app($this->modelo->name_space)->consultar_registros();
        }

        $vista = 'layouts.index';
        $vistas = json_decode(app($this->modelo->name_space)->vistas);
        if (!is_null($vistas))
        {
            if (isset($vistas->index))
            {
                $vista = $vistas->index;
                $registros = app($this->modelo->name_space)->consultar_registros2();
                $registros->setPath('?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . $id_transaccion);
            }
        }

        // ¿Cómo saber qué métodos estan llamando a la vista layouts.index?
        // Si modifico esa vista, cómo se qué partes del software se verán afectadas???
        return view($vista, compact('registros', 'miga_pan', 'url_crear', 'encabezado_tabla', 'url_edit', 'url_print', 'url_ver', 'url_estado', 'url_eliminar', 'archivo_js', 'botones'));
    }


    /*
        NOTA: Para evitar resultados inesperados, se debe comprobar que la variable $urls_acciones tenga un formato JSON correcto.
    */
    public function acciones_basicas_modelo( $modelo, $parametros_url )
    {
        // Acciones predeterminadas
        $acciones = (object)[
                                'index' => 'web' . $parametros_url,
                                'create' => '',
                                'edit' => '',
                                'store' => 'web',
                                'update' => 'web/id_fila',
                                'show' => 'web/id_fila' . $parametros_url,
                                'imprimir' => '',
                                'eliminar' => '',
                                'cambiar_estado' => '',
                                'otros_enlaces' => ''
                            ];


        // Se agregan los enlaces que tiene el modelo en la base de datos (ESTO DEBE DESAPARECER, PERO PRIMERO SE DEBEN MIGRAR LOS MODELOS ANTIGUOS)
        if ($modelo->url_crear != '')
        {
            $acciones->create = $modelo->url_crear . $parametros_url;
        }

        if ($modelo->url_edit != '')
        {
            $acciones->edit = $modelo->url_edit . $parametros_url;
        }

        if ($modelo->url_form_create != '')
        {
            $acciones->store = $modelo->url_form_create;
            $acciones->update = $modelo->url_form_create . '/id_fila';
        }

        if ($modelo->url_print != '')
        {
            $acciones->imprimir = $modelo->url_print . $parametros_url;
        }

        if ($modelo->url_ver != '')
        {
            $acciones->show = $modelo->url_ver . $parametros_url;
        }

        if ($modelo->url_estado != '')
        {
            $acciones->cambiar_estado = $modelo->url_estado . $parametros_url;
        }

        if ($modelo->url_eliminar != '')
        {
            $acciones->eliminar = $modelo->url_eliminar . $parametros_url;
        }

        // Otros enlaces en formato JSON
        if ($modelo->enlaces != '')
        {
            $acciones->otros_enlaces = $modelo->enlaces;
        }


        // MANEJO DE URLs DESDE EL ARCHIVO CLASS DEL PROPIO MODELO 
        // Se llaman las urls desde la class (name_space) del modelo
        $urls_acciones = json_decode( app( $modelo->name_space )->urls_acciones );

        if ( !is_null($urls_acciones) )
        {

            // Acciones particulares, si están definidas en la variable $urls_acciones de la class del modelo
            
            if ( isset( $urls_acciones->create ) )
            {
                if ( $urls_acciones->create != 'no' )
                {
                    $acciones->create = $urls_acciones->create . $parametros_url;
                }
            }

            if ( isset( $urls_acciones->edit ) )
            {
                if ( $urls_acciones->edit != 'no' )
                {
                    $acciones->edit = $urls_acciones->edit . $parametros_url;
                }
            }
            
            if ( isset( $urls_acciones->store ) )
            {
                if ( $urls_acciones->store != 'no' )
                {
                    $acciones->store = $urls_acciones->store;
                }
            }
            
            if ( isset( $urls_acciones->update ) )
            {
                if ( $urls_acciones->update != 'no' )
                {
                    $acciones->update = $urls_acciones->update;
                }
            }
            
            if ( isset( $urls_acciones->show ) )
            {
                if ( $urls_acciones->show != 'no' )
                {
                    $acciones->show = $urls_acciones->show . $parametros_url;
                }

                if ( $urls_acciones->show == 'no' )
                {
                    $acciones->show = '';
                }
            }
            
            if ( isset( $urls_acciones->imprimir ) )
            {
                if ( $urls_acciones->imprimir != 'no' )
                {
                    $acciones->imprimir = $urls_acciones->imprimir . $parametros_url;
                }
            }
            
            if ( isset( $urls_acciones->eliminar ) )
            {
                if ( $urls_acciones->eliminar != 'no' )
                {
                    $acciones->eliminar = $urls_acciones->eliminar . $parametros_url;
                }
            }
            
            if ( isset( $urls_acciones->cambiar_estado ) )
            {
                if ( $urls_acciones->cambiar_estado != 'no' )
                {
                    $acciones->cambiar_estado = $urls_acciones->cambiar_estado . $parametros_url;
                }
            }
            
            // Otros enlaces en formato JSON
            if ( isset( $urls_acciones->otros_enlaces ) )
            {
                if ( $urls_acciones->otros_enlaces != 'no' )
                {
                    $acciones->otros_enlaces = $urls_acciones->otros_enlaces;
                }
            }
            
        }

        return $acciones;
    }



    // FORMULARIO PARA CREAR UN NUEVO REGISTRO
    public function create()
    {
        // Se obtienen los campos que el Modelo tiene asignados
        $lista_campos = ModeloController::get_campos_modelo($this->modelo, '', 'create');

        /*
            Agregar campos adicionales 
            Algunas Modelos necesitan campos formateados o compuestos de una manera única
            También se pueden personalizar los campos asignados al Modelo
        */
        if (method_exists(app($this->modelo->name_space), 'get_campos_adicionales_create')) {
            $lista_campos = app($this->modelo->name_space)->get_campos_adicionales_create($lista_campos);
        }


        if (Input::get('id_transaccion') != '') {
            $tipo_transaccion = TipoTransaccion::find(Input::get('id_transaccion'));
            $cantidad_campos = count($lista_campos);
            $lista_campos = $this->personalizar_campos(Input::get('id_transaccion'), $tipo_transaccion, $lista_campos, $cantidad_campos, 'create');
        }

        // Se crear un array para generar el formulario
        // Este array se envía a la vista layouts.create, que carga la plantilla principal del formulario CREAR
        // La vista layouts.create incluye a la vista core.vistas.form_create que es la que usa al array form_create para generar un formulario html

        $acciones = $this->acciones_basicas_modelo( $this->modelo, '' );

        $form_create = [
                        'url' => $acciones->store,
                        'campos' => $lista_campos
                        ];

        $miga_pan = MigaPan::get_array($this->aplicacion, $this->modelo, 'Crear nuevo');

        // Si el modelo tiene un archivo js particular
        $archivo_js = app($this->modelo->name_space)->archivo_js;


        $vista = 'layouts.create';
        $vistas = json_decode(app($this->modelo->name_space)->vistas);
        if (!is_null($vistas))
        {
            if (isset($vistas->create))
            {
                $vista = $vistas->create;
            }
        }
        
        if (Input::get('vista') != null)
        {
            return view(Input::get('vista'), compact('form_create', 'miga_pan', 'archivo_js'));
        }

        return view($vista, compact('form_create', 'miga_pan', 'archivo_js'));
    }

    /*
    //     A L M A C E N A R UN NUEVO REGISTRO
    */
    public function store(Request $request)
    {
        $datos = $request->all(); // Datos originales
        // Se crea un nuevo registro para el ID del modelo enviado en el request 
        $registro = $this->crear_nuevo_registro( $request );

        // Si se está almacenando una transacción que maneja consecutivo
        if (isset($request->consecutivo) and isset($request->core_tipo_doc_app_id)) {
            // Seleccionamos el consecutivo actual (si no existe, se crea) y le sumamos 1
            $consecutivo = TipoDocApp::get_consecutivo_actual($request->core_empresa_id, $request->core_tipo_doc_app_id) + 1;

            // Se incementa el consecutivo para ese tipo de documento y la empresa
            TipoDocApp::aumentar_consecutivo($request->core_empresa_id, $request->core_tipo_doc_app_id);

            $registro->consecutivo = $consecutivo;
            $registro->save();
        }

        // $this->modelo se actualiza en el método de arriba crear_nuevo_registro()
        $this->almacenar_imagenes($request, $this->modelo->ruta_storage_imagen, $registro);

        $acciones = $this->acciones_basicas_modelo( $this->modelo, '' );
        
        $url_ver = str_replace('id_fila', $registro->id, $acciones->show);

        /*
            Tareas adicionales de almacenamiento (guardar en otras tablas, crear otros modelos, etc.)
        */
        if (method_exists(app($this->modelo->name_space), 'store_adicional'))
        {
            // Aquí mismo se puede hacer el return
            $url_respuesta = app($this->modelo->name_space)->store_adicional($datos, $registro);

            if( !is_null( $url_respuesta ) )
            {
                if ( gettype( $url_respuesta ) != "object" )
                {
                    return redirect( $url_respuesta )->with('flash_message', 'Registro CREADO correctamente.');
                }
            }
        }

        return redirect( $url_ver . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo . '&id_transaccion=' . $request->url_id_transaccion)->with('flash_message', 'Registro CREADO correctamente.');
    }

    /*
        Crear nuevo registro con los datos enviados por POST
        La función recibe un objeto Request
        Además, el los datos del request debe venir un campo con el ID del modelo del cúal se le va a crear el registro
    */
    public function crear_nuevo_registro($request)
    {
        $this->modelo = Modelo::find($request->url_id_modelo);

        $this->validar_requeridos_y_unicos($request, $this->modelo);

        // Se verifican si vienen campos con valores tipo array. Normalmente para los campos tipo chexkbox.
        foreach ($request->all() as $key => $value) {
            if (is_array($value)) {
                $request[$key] = implode(",", $value);
            }
        }

        // Crear el nuevo registro
        return app($this->modelo->name_space)->create($request->all());
    }


    // USAR Solo cuando se está almacenando un nuevo registro
    // !!! Revisar cuando se está editando
    public function validar_requeridos_y_unicos($request, $registro_modelo_crud)
    {
        // Obtener la table de ese modelo
        $registro = new $registro_modelo_crud->name_space;
        $nombre_tabla = $registro->getTable();

        // LLamar a los campos del modelo para verificar los que son requeridos
        $lista_campos = $registro_modelo_crud->campos->toArray();

        $cant = count($lista_campos);
        for ($i = 0; $i < $cant; $i++) {
            // Se valida solo si el campo pertenece al Modelo directamente
            if (in_array($lista_campos[$i]['name'], $registro->getFillable())) {
                if ($lista_campos[$i]['requerido']) {
                    $this->validate($request, [$lista_campos[$i]['name'] => 'required']);
                }

                if ($lista_campos[$i]['unico']) {
                    $this->validate($request, [$lista_campos[$i]['name'] => 'unique:' . $nombre_tabla]);
                }
            }
        }
    }



    /*
      * Esta función debe estar en ImagenController y en lugar de recibir todo el $request, solo necesita el array archivos tipo file
    */
    public function almacenar_imagenes($request, $ruta_storage_imagen, $registro, $modo = null)
    {

        $lista_nombres = '';
        $nombre_es_el_primero = true;
        // Si se envía archivos tipo file (imagenes, adjuntos)
        $archivos_enviados = $request->file();
        foreach ($archivos_enviados as $key => $value)
        {
            // Si se envía un nuevo archivo, se borran el archivo anterior del disco
            if ($modo == 'edit' && $request->file($key) != '') {
                Storage::delete($ruta_storage_imagen . $registro->$key);
            }

            $archivo = $request->file($key);
            $extension =  $archivo->clientExtension();

            $nuevo_nombre = str_slug($archivo->getClientOriginalName()) . '-' . uniqid() . '.' . $extension;

            // Crear un nombre unico para el archivo con su misma extensión
            //$nuevo_nombre = uniqid() . '.' . $extension;
            if ($nombre_es_el_primero) {
                $lista_nombres .= $nuevo_nombre;
                $nombre_es_el_primero = false;
            } else {
                $lista_nombres .= ',' . $nuevo_nombre;
            }


            // Guardar la imagen en disco
            Storage::put($ruta_storage_imagen . $nuevo_nombre, file_get_contents($archivo->getRealPath()));

            // Guardar nombre en la BD
            $registro->$key = $nuevo_nombre;
            $registro->save();
        }

        return $lista_nombres;
    }


    // FORMULARIO PARA EDITAR UN REGISTRO
    public function edit($id)
    {
        // Se obtiene el registro a modificar del modelo
        $registro = app($this->modelo->name_space)->find($id);

        $lista_campos = $this->get_campos_modelo($this->modelo, $registro, 'edit');

        /*
            Agregar campos adicionales 
            Algunas Modelos necesitan campos formateados o compuestos de una manera única
            También se pueden personalizar los campos asignados al Modelo
        */
        if (method_exists(app($this->modelo->name_space), 'get_campos_adicionales_edit')) {
            $lista_campos = app($this->modelo->name_space)->get_campos_adicionales_edit($lista_campos, $registro);
        }

        if ( is_null($lista_campos[0]) )
        {
            $acciones = $this->acciones_basicas_modelo( $this->modelo, '' );
        
            $url_ver = str_replace('id_fila', $registro->id, $acciones->show);

            return redirect( $url_ver . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion') )->with('mensaje_error', $lista_campos[1]);
        }
        
        $acciones = $this->acciones_basicas_modelo( $this->modelo, '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion') );

        $url_action = str_replace('id_fila', $registro->id, $acciones->update);
        
        $form_create = [
            'url' => $url_action,
            'campos' => $lista_campos
        ];

        $miga_pan = MigaPan::get_array($this->aplicacion, $this->modelo, $registro->descripcion);

        $archivo_js = app($this->modelo->name_space)->archivo_js;


        $vista = 'layouts.edit';
        $vistas = json_decode(app($this->modelo->name_space)->vistas);
        if (!is_null($vistas))
        {
            if (isset($vistas->edit))
            {
                $vista = $vistas->edit;
            }
        }

        return view( $vista, compact('form_create', 'miga_pan', 'registro', 'archivo_js', 'url_action'));
    }



    //     A L M A C E N A R  LA MODIFICACION DE UN REGISTRO
    public function update(Request $request, $id)
    {

        $datos = $request->all(); // Datos originales

        // Se obtiene el modelo según la variable modelo_id de la url
        $modelo = Modelo::find($request->url_id_modelo);

        // Se obtinene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);

        $registro2 = '';
        // Si se envían datos tipo file
        //if ( count($request->file()) > 0)
        if (!empty($request->file())) {
            // Copia identica del registro del modelo, pues cuando se almacenan los datos cambia la instancia
            $registro2 = app($modelo->name_space)->find($id);
        }

        // LLamar a los campos del modelo para verificar los que son requeridos
        // y los que son únicos
        $lista_campos = $modelo->campos->toArray();
        $cant = count($lista_campos);
        for ($i = 0; $i < $cant; $i++) {
            if ($lista_campos[$i]['editable'] == 1) {
                if ($lista_campos[$i]['requerido']) {
                    $this->validate($request, [$lista_campos[$i]['name'] => 'required']);
                }
                if ($lista_campos[$i]['unico']) {
                    $this->validate($request, [$lista_campos[$i]['name'] => 'unique:' . $registro->getTable() . ',' . $lista_campos[$i]['name'] . ',' . $id]);
                }
            }
            // Cuando se edita una transacción
            if ($lista_campos[$i]['name'] == 'movimiento') {
                $lista_campos[$i]['value'] = 1;
            }
        }

        // Se verifican si vienen campos con valores tipo array. Normalmente para los campos tipo chexkbox.
        foreach ($request->all() as $key => $value) {
            if (is_array($value)) {
                $request[$key] = implode(",", $value);
            }
        }

        $registro->fill( $request->all() );
        $registro->save();

        $this->almacenar_imagenes($request, $modelo->ruta_storage_imagen, $registro2, 'edit');

        /*
            Tareas adicionales de almacenamiento (guardar en otras tablas, crear otros modelos, etc.)
        */
        if( method_exists(app($modelo->name_space), 'update_adicional') )
        {
            $url_respuesta = app($modelo->name_space)->update_adicional($datos, $id);


            if( !is_null( $url_respuesta ) )
            {
                return redirect( $url_respuesta )->with('flash_message', 'Registro MODIFICADO correctamente.');
            }
        }

        $acciones = $this->acciones_basicas_modelo( $modelo, '' );
        $url_ver = str_replace('id_fila', $registro->id, $acciones->show);

        return redirect( $url_ver . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo . '&id_transaccion=' . $request->url_id_transaccion)->with('flash_message', 'Registro MODIFICADO correctamente.');
    }

    /*
        **
    */
    // VISTA TIPO TABLA PARA MOSTRAR UN REGISTRO
    public function show($id)
    {
        // Se obtiene el registro del modelo indicado y el anterior y siguiente registro
        $registro = app($this->modelo->name_space)->find($id);
        $reg_anterior = app($this->modelo->name_space)->where('id', '<', $registro->id)->max('id');
        $reg_siguiente = app($this->modelo->name_space)->where('id', '>', $registro->id)->min('id');

        // Se obtienen los campos asociados a ese modelo
        $lista_campos1 = $this->modelo->campos()->orderBy('orden')->get();

        $lista_campos = $this->asignar_valores_de_campo_al_registro($this->modelo, $registro, $lista_campos1->toArray());


        /*
            Tareas adicionales para mostrar el registro
        */
        if (method_exists(app($this->modelo->name_space), 'show_adicional')) {
            $lista_campos = app($this->modelo->name_space)->show_adicional($lista_campos, $registro);
        }


        $miga_pan = MigaPan::get_array($this->aplicacion, $this->modelo, $registro->descripcion);

        $id_transaccion = TipoTransaccion::where('core_modelo_id', (int) Input::get('id_modelo'))->value('id');

        // Se le asigna a cada variable url, su valor en el modelo correspondiente
        $variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . $id_transaccion;
        
        $acciones = $this->acciones_basicas_modelo( $this->modelo, $variables_url );

        $url_crear = $acciones->create;
        $url_edit = $acciones->edit;
        $url_print = $acciones->imprimir;
        $url_ver = $acciones->show;
        $url_estado = $acciones->cambiar_estado;
        $url_eliminar = $acciones->eliminar;

        $botones = [];
        $enlaces = json_decode( $acciones->otros_enlaces );

        if( !is_null($enlaces) )
        {
            $i = 0;
            foreach ($enlaces as $fila)
            {
                $botones[$i] = new Boton($fila);
                $i++;
            }
        }


        $form_create = [
            'url' => $acciones->store,
            'campos' => $lista_campos
        ];

        $vista = 'layouts.show';
        $vistas = json_decode(app($this->modelo->name_space)->vistas);
        if (!is_null($vistas))
        {
            if (isset($vistas->show))
            {
                $vista = $vistas->show;
            }
        }

        // Para lo modelos que tienen otro modelo relacionado. Ejemplo, El modelo Modelo tiene Campos. El modelo Cuestionario, tiene Preguntas
        $respuesta = ModeloController::get_tabla_relacionada($this->modelo, $registro);

        $tabla = $respuesta['tabla'];
        $opciones = $respuesta['opciones'];
        $registro_modelo_padre_id = $respuesta['registro_modelo_padre_id'];
        $titulo_tab = $respuesta['titulo_tab'];

        return view( $vista , compact('form_create', 'miga_pan', 'registro', 'url_crear', 'url_edit', 'tabla', 'opciones', 'registro_modelo_padre_id', 'reg_anterior', 'reg_siguiente', 'titulo_tab', 'botones'));
    }


    /*
        ** Esta función crea el array lista_campos que es el que se va a pasar a las vistas (create, edit, show) para visualizar los campos a través de VistaController según los tipos de campos y la vista.
        ** 
        $lista_campo = [ 'tipo', 'name', 'descripcion', 'opciones', 'value', 'atributos', 'definicion', 'html_clase', 'html_id', 'requerido', 'editable', 'unico' ];
        
        Por ahora solo se usa para la vista show
    */
    function asignar_valores_de_campo_al_registro($modelo, $registro, $lista_campos)
    {
        // Se recorre la lista de campos 
        // para formatear-asignar el valor correspondiente del registro del modelo 
        $cantidad_campos = count($lista_campos);

        for ($i = 0; $i < $cantidad_campos; $i++) {
            //echo $i.' '.$lista_campos[$i]['name'].'<br/>';
            $nombre_campo = $lista_campos[$i]['name'];

            if (isset($registro->$nombre_campo)) {
                $lista_campos[$i]['value'] = $registro->$nombre_campo;
            }

            // PARA LAS ACTIVIDADES ESCOLARES modelo_id=38
            if ($lista_campos[$i]['name'] == 'asignatura_id' and $modelo->id == 38) {
                $lista_campos[$i]['opciones'] = 'table_asignaturas';
            }

            if ($lista_campos[$i]['tipo'] == 'imagen') {
                if ($registro->$nombre_campo == '' && $nombre_campo == 'imagen') {
                    $campo_imagen = 'avatar.png';
                    $btn_quitar_img = '';
                } else {
                    $campo_imagen = $registro->$nombre_campo;
                    $btn_quitar_img = '<a type="button" class="close" href="' . url('quitar_imagen?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&registro_id=' . $registro->id) . '" title="Quitar imagen">&times;</a>';
                }
                $url = config('configuracion.url_instancia_cliente') . "/storage/app/" . $modelo->ruta_storage_imagen . $campo_imagen;
                $imagen = '<div class="form-group" style="border:1px solid gray; text-align:center; overflow:auto;" oncontextmenu="return false" onkeydown="return false">' . $btn_quitar_img . '<img alt="imagen.jpg" src="' . asset($url) . '" style="width: auto; height: 160px;" />
                        </div>';
                $lista_campos[$i]['value'] = $imagen;
            }
        } // Cierre for cada campo

        return $lista_campos;
    }

    public static function personalizar_campos($id_transaccion, $tipo_transaccion, $lista_campos, $cantidad_campos, $accion, $tipo_tranferencia = null)
    {

        $opciones = [];
        // Se crea un select SOLO con las opciones asignadas a la transacción
        //if ($tipo_transaccion != 0) {
        $tipo_docs_app = $tipo_transaccion->tipos_documentos;
        foreach ($tipo_docs_app as $fila)
        {
            $opciones[$fila->id] = $fila->prefijo . " - " . $fila->descripcion;
        }
        //} 

        //Personalización de la lista de campos
        for ($i = 0; $i < $cantidad_campos; $i++)
        {

            if ($lista_campos[$i]['name'] == 'core_tipo_doc_app_id') {
                $lista_campos[$i]['opciones'] = $opciones;
            }

            // Valores predeterminados para los campos ocultos
            if ($accion == 'create') {
                if ($lista_campos[$i]['name'] == 'core_tipo_transaccion_id') {
                    $lista_campos[$i]['value'] = $tipo_transaccion->id;
                }
                if ($lista_campos[$i]['name'] == 'estado') {
                    $lista_campos[$i]['value'] = 'Activo';
                }

                if ($lista_campos[$i]['name'] == 'user_id') {
                    $lista_campos[$i]['value'] = Auth::user()->id;
                }

                // Cuando la transacción es "Generar CxC"
                if ($lista_campos[$i]['name'] == 'core_tercero_id' and $id_transaccion == 5) {
                    $lista_campos[$i]['requerido'] = false;
                    $lista_campos[$i]['tipo'] = 'hidden';
                }
            } else {
                if ($lista_campos[$i]['name'] == 'core_tipo_transaccion_id') {
                    $lista_campos[$i]['value'] = null;
                }
                if ($lista_campos[$i]['name'] == 'estado') {
                    $lista_campos[$i]['value'] = null;
                }
            }

            if ($lista_campos[$i]['name'] == 'teso_medio_recaudo_id')
            {
                $registros = TesoMedioRecaudo::all();
                $vec_m[''] = '';
                foreach ($registros as $fila) {
                    $vec_m[$fila->id . '-' . $fila->comportamiento] = $fila->descripcion;
                }

                $lista_campos[$i]['opciones'] = $vec_m;
                

                if ($accion == 'edit')
                {
                    $medio_recaudo = TesoMedioRecaudo::find( $lista_campos[$i]['value'] );
                    $lista_campos[$i]['value'] = $lista_campos[$i]['value'] . '-' . $medio_recaudo->comportamiento;
                }
            }

            unset($vec_m);
            if ($lista_campos[$i]['name'] == 'teso_caja_id') {
                $registros = TesoCaja::where('core_empresa_id', Auth::user()->empresa_id)->get();
                foreach ($registros as $fila) {
                    $vec_m[$fila->id] = $fila->descripcion;
                }

                if (count($vec_m) == 0) {
                    $vec_m[''] = '';
                }

                $lista_campos[$i]['opciones'] = $vec_m;
            }

            unset($vec_m);
            if ($lista_campos[$i]['name'] == 'teso_cuenta_bancaria_id') {

                $registros = TesoCuentaBancaria::leftJoin('teso_entidades_financieras', 'teso_entidades_financieras.id', '=', 'teso_cuentas_bancarias.entidad_financiera_id')
                    ->where('core_empresa_id', Auth::user()->empresa_id)
                    ->select('teso_cuentas_bancarias.id', 'teso_cuentas_bancarias.descripcion AS cta_bancaria', 'teso_entidades_financieras.descripcion AS entidad_financiera')
                    ->get();
                foreach ($registros as $fila) {
                    $vec_m[$fila->id] = $fila->entidad_financiera . ': ' . $fila->cta_bancaria;
                }

                if (count($vec_m) == 0) {
                    $vec_m[''] = '';
                }

                $lista_campos[$i]['opciones'] = $vec_m;
            }

            unset($vec_m);
            if ($lista_campos[$i]['name'] == 'user_asignado_id') {

                $registros = User::whereHas('roles', function ($q) {
                    $q->whereIn('name', ['Administrador PH', 'SuperAdmin']);
                })->get();

                //$registros = TesoCaja::where('core_empresa_id',Auth::user()->empresa_id)->get();       
                foreach ($registros as $fila) {
                    $vec_m[$fila->id] = $fila->name;
                }

                if (count($vec_m) == 0) {
                    $vec_m[''] = '';
                }

                $lista_campos[$i]['opciones'] = $vec_m;
            }
        }

        // Si es una transferencia se agrega un nuevo campo para la bodega destino
        if ($id_transaccion == $tipo_tranferencia) {
            $lista_campos[$i]['id'] = 0;
            $lista_campos[$i]['tipo'] = 'select';
            $lista_campos[$i]['name'] = 'bodega_destino_id';
            $lista_campos[$i]['descripcion'] = 'Bodega destino';
            $bodegas = InvBodega::where('estado', 'Activo')
                ->get();
            foreach ($bodegas as $fila) {
                $vec_b[$fila->id] = $fila->descripcion;
            }
            $lista_campos[$i]['opciones'] = $vec_b;
            $lista_campos[$i]['value'] = null;
            $lista_campos[$i]['atributos'] = [];
            $lista_campos[$i]['requerido'] = true;
        }
        return $lista_campos;
    }

    // Construir un array con los campos asociados al modelo
    public static function get_campos_modelo($modelo, $registro, $accion)
    {
        // Se obtienen los campos asociados a ese modelo
        $lista_campos1 = $modelo->campos()->orderBy('orden')->get();

        $lista_campos = ModeloController::ajustar_valores_lista_campos($lista_campos1->toArray());

        // Ajustar los valores según la acción
        $lista_campos = ModeloController::ajustar_valores_lista_campos_segun_accion($lista_campos, $registro, $modelo, $accion);

        return $lista_campos;
    }

    public static function ajustar_valores_lista_campos($lista_campos)
    {
        $cant = count($lista_campos);
        for ($i = 0; $i < $cant; $i++) {
            $nombre_campo = $lista_campos[$i]['name'];

            // El campo Atributos se ingresa en  formato JSON {"campo1":"valor1","campo2":"valor2"}
            // Luego se tranforma a un array para que pueda ser aceptado por el Facade Form:: de LaravelCollective
            if ($lista_campos[$i]['atributos'] != '') {
                
                $lista_campos[$i]['atributos'] = json_decode($lista_campos[$i]['atributos'], true);
                
                // Para el tipo de campo Input Lista Sugerencias
                if( isset( $lista_campos[$i]['atributos']['data-url_busqueda'] ) )
                {
                    $lista_campos[$i]['atributos']['data-url_busqueda'] = url( $lista_campos[$i]['atributos']['data-url_busqueda'] );
                }

            } else {
                $lista_campos[$i]['atributos'] = [];
            }

            // Cuando el campo es requerido se agrega el atributo al control html
            if ($lista_campos[$i]['requerido']) {
                $lista_campos[$i]['atributos'] = array_merge($lista_campos[$i]['atributos'], ['required' => 'required']);
            }

            // Cuando se está editando un registro, el formulario llamado por LaravelCollective Form::model(), llena los campos que tienen valor null con los valores del registro del modelo instanciado

            if ($lista_campos[$i]['value'] == 'null') {
                $lista_campos[$i]['value'] = null;
            }

            // Para llenar los campos tipo select y checkbox
            if ($lista_campos[$i]['tipo'] == 'select' || $lista_campos[$i]['tipo'] == 'bsCheckBox') {
                $lista_campos[$i]['opciones'] = VistaController::get_opciones_campo_tipo_select($lista_campos[$i]);
            }
        }
        return $lista_campos;
    }



    public static function ajustar_valores_lista_campos_segun_accion($lista_campos, $registro, $modelo, $accion)
    {
        $cant = count($lista_campos);
        for ($i = 0; $i < $cant; $i++) {
            $nombre_campo = $lista_campos[$i]['name'];

            if ($accion == 'create') {

                // Valores predeterminados para Algunos campos ocultos
                switch ($lista_campos[$i]['name']) {
                    case 'creado_por':
                        $lista_campos[$i]['value'] = Auth::user()->email;
                        break;
                    case 'modificado_por':
                        $lista_campos[$i]['value'] = 0;
                        break;
                    case 'user_id':
                        $lista_campos[$i]['value'] = Auth::user()->id;
                        break;
                    default:
                        # code...
                        break;
                }

                if ($lista_campos[$i]['tipo'] == 'input_lista_sugerencias')
                {
                    // value es un array con los valores para text_input y para el input hidden
                    $lista_campos[$i]['value'] = ['',''];
                }

            } else { // Si se está editando

                // asignar valor almacenado en la BD al cada campo
                if (isset($registro->$nombre_campo)) {
                    $lista_campos[$i]['value'] = $registro->$nombre_campo;
                }


                // Si el campo NO es editable, se muestra deshabilitado
                if (!$lista_campos[$i]['editable']) {
                    /*
                        Advertencia cuando el campo está deshabilitado NO es enviado en el request del formulario
                        Su valor no es actualizado.
                        No se puede usar su valor (que no existe) en otras acciones.
                    */
                    $lista_campos[$i]['atributos'] = ['disabled' => 'disabled', 'style' => 'background-color:#FBFBFB;'];

                    if ($lista_campos[$i]['tipo'] == 'personalizado') {
                        $lista_campos[$i]['value'] = '';
                    }
                }

                switch ($lista_campos[$i]['name']) {
                    case 'creado_por':
                        $lista_campos[$i]['value'] = null;
                        break;

                    case 'modificado_por':
                        $lista_campos[$i]['value'] = Auth::user()->email;
                        break;

                    case 'role':
                        $usuario = User::find($registro->id);
                        $role = $usuario->roles()->get()[0];
                        $lista_campos[$i]['value'] = $role->id;
                        break;

                    case 'escala_valoracion':
                        $logros = Logro::get_logros_periodo_curso_asignatura($registro->periodo_id, $registro->curso_id, $registro->asignatura_id);
                        $descripciones = [];
                        $el_primero = true;
                        foreach ($logros as $un_logro) {
                            $descripciones[$un_logro->escala_valoracion_id] = $un_logro->descripcion;
                        }

                        $lista_campos[$i]['value'] = $descripciones;
                        break;

                    default:
                        # code...
                        break;
                }

                // Si hay campo tipo imagen, se envía la URL de la imagen para mostrala
                if ($lista_campos[$i]['tipo'] == 'imagen')
                {
                    $lista_campos[$i]['value'] = config('configuracion.url_instancia_cliente') . "/storage/app/" . $modelo->ruta_storage_imagen . $registro->$nombre_campo;
                }

                // Si hay campo tipo imagenes_multiples, se envía la imagen para mostrala
                if ($lista_campos[$i]['tipo'] == 'imagenes_multiples') {
                    // Esto debe cambiar!!!!!!
                    $lista_campos[$i]['value'] = config('configuracion.url_instancia_cliente') . "/storage/app/" . $modelo->ruta_storage_imagen . $registro->$nombre_campo;
                }

                // Si se está editando un checkbox
                if ($lista_campos[$i]['tipo'] == 'bsCheckBox')
                {
                    // Si el name del campo enviado tiene la palabra core_campo_id-ID, se trata de un campo Atributo de EAV
                    if (strpos($lista_campos[$i]['name'], "core_campo_id-") !== false)
                    {
                        $lista_campos[$i]['value'] = ModeloEavValor::where(["modelo_padre_id" => Input::get('modelo_padre_id'), "registro_modelo_padre_id" => Input::get('registro_modelo_padre_id'), "modelo_entidad_id" => Input::get('modelo_entidad_id'), "core_campo_id" => $lista_campos[$i]['id']])->value('valor');
                    } else {
                        $lista_campos[$i]['value'] = $registro->$nombre_campo;
                    }
                }



                if ($lista_campos[$i]['tipo'] == 'input_lista_sugerencias')
                {
                    $campo_del_modelo = $lista_campos[$i]['name'];
                    $registro_input = app( $lista_campos[$i]['atributos']['data-clase_modelo'] )->find( $registro->$campo_del_modelo );

                    // value es un array con los valores para text_input y para el input hidden
                    $lista_campos[$i]['value'] = [ $registro_input->descripcion.' ('.number_format($registro_input->numero_identificacion,0,',','.').')', $registro->$campo_del_modelo ];
                }

            }
        }
        return $lista_campos;
    }

    //  M I G A   D E   P A N (de tres niveles: MEJORAR)
    // Retorna el array para la miga de pan
    public function get_miga_pan($modelo, $etiqueta_final)
    {
        return MigaPan::get_array($this->aplicacion, $modelo, $etiqueta_final);
    }

    public function get_tabla_relacionada($modelo_padre, $registro_modelo_padre)
    {
        $tabla = '';
        $todos_campos = '';
        $registro_modelo_padre_id = '';
        $titulo_tab = '';
        $opciones = '';

        // Si el modelo tiene otro modelo relacionado
        if ($modelo_padre->modelo_relacionado != '') {

            $registro_modelo_padre_id = $registro_modelo_padre->id;

            $metodo_modelo_relacionado = $modelo_padre->modelo_relacionado;
            // etiqueta del tab de formuralio show
            $titulo_tab = ucfirst($metodo_modelo_relacionado);

            // Se obtienen los registros asignados al registro del modelo padre que se va a visualizar
            $registros_asignados = $registro_modelo_padre->$metodo_modelo_relacionado()->orderBy('orden')->get()->toArray(); //


            // Se obtiene una tabla con los registros asociados al registro del modelo
            $tabla = app($modelo_padre->name_space)->get_tabla($registro_modelo_padre, $registros_asignados);

            unset($opciones);
            $opciones = app($modelo_padre->name_space)->get_opciones_modelo_relacionado($registro_modelo_padre->id);
        } else {
            $registros_asignados = [];
        }

        return compact('tabla', 'opciones', 'registro_modelo_padre_id', 'titulo_tab');
    }


    // ASIGNACIÓN DE UN CAMPO A UN MODELO
    public function guardar_asignacion(Request $request)
    {

        // Se obtiene el modelo "Padre"
        $modelo = Modelo::find($request->url_id_modelo);

        $datos = app($modelo->name_space)->get_datos_asignacion();

        $this->validate($request, ['registro_modelo_hijo_id' => 'required']);

        DB::table($datos['nombre_tabla'])
            ->insert([
                $datos['nombre_columna1'] => $request->nombre_columna1,
                $datos['registro_modelo_padre_id'] => $request->registro_modelo_padre_id,
                $datos['registro_modelo_hijo_id'] => $request->registro_modelo_hijo_id
            ]);

        return redirect('web/' . $request->registro_modelo_padre_id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo)->with('flash_message', 'Asignación CREADA correctamente');
    }

    // ELIMINACIÓN DE UN CAMPO A UN MODELO
    public function eliminar_asignacion($registro_modelo_hijo_id, $registro_modelo_padre_id, $id_app, $id_modelo_padre)
    {

        // Se obtiene el modelo "Padre"
        $modelo = Modelo::find($id_modelo_padre);

        /*print_r($modelo);*/
        $datos = app($modelo->name_space)->get_datos_asignacion();

        DB::table($datos['nombre_tabla'])->where($datos['registro_modelo_hijo_id'], '=', $registro_modelo_hijo_id)
            ->where($datos['registro_modelo_padre_id'], '=', $registro_modelo_padre_id)
            ->delete();

        return redirect('web/' . $registro_modelo_padre_id . '?id=' . $id_app . '&id_modelo=' . $id_modelo_padre)->with('flash_message', 'Asignación ELIMINADA correctamente');
    }

    public function activar_inactivar($id_registro)
    {
        $registro = app($this->modelo->name_space)->find($id_registro);

        if ($registro->estado == 'Activo') {
            $registro->estado = 'Inactivo';
        } else {
            $registro->estado = 'Activo';
        }

        $registro->save();

        $variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo');

        return redirect('web/' . $variables_url)->with('flash_message', 'Estado ACTUALIZADO correctamente.');
    }

    public function duplicar($id_registro)
    {
        $registro = app($this->modelo->name_space)->find($id_registro);

        $nuevo_registro = $registro->replicate();

        if ( isset( $nuevo_registro->imagen ) )
        {
            $nuevo_registro->imagen = '';
        }
        
        $nuevo_registro = $registro->replicate();


        $nuevo_registro->save();

        return redirect('web/' . $nuevo_registro->id . '/edit?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'))->with('flash_message', 'Registro DUPLICADO correctamente.');
    }

    /*
        Permite actualizar los valores en la tabla que relaciona Modelos ( MODELOPADRE_tiene_MODELOHIJO )
        Ejemplo, sys_modelo_tiene_campos, pw_pagina_tiene_secciones, 
        Los datos vienen por GET
    */
    public function actualizar_campos_modelos_relacionados()
    {
        // Se obtiene el modelo "Padre"
        $modelo = Modelo::find(Input::get('modelo_id'));

        $datos = app($modelo->name_space)->get_datos_asignacion();

        DB::table($datos['nombre_tabla'])
            ->where($datos['registro_modelo_padre_id'], '=', Input::get('registro_modelo_padre_id'))
            ->where($datos['registro_modelo_hijo_id'], '=', Input::get('registro_modelo_hijo_id'))
            ->update([$datos['nombre_columna1'] => Input::get('valor_nuevo')]);

        return 'ok';
    }

    public function ajax_datatable()
    {
        $datos_consulta = app($this->modelo->name_space)->consultar_datatable();

        return Datatables::of($datos_consulta)
            ->addColumn('action', function ($datos_consulta) {

                $variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo');
                $acciones = $this->acciones_basicas_modelo( $this->modelo, $variables_url );

                $url_edit = $acciones->edit;
                $url_ver = $acciones->show;

                return '<a class="btn btn-warning btn-xs btn-detail" href="' . url(str_replace('id_fila', $datos_consulta->id, $url_edit)) . '" title="Modificar"><i class="fa fa-btn fa-edit"></i>&nbsp;</a> &nbsp;&nbsp;&nbsp;<a class="btn btn-primary btn-xs btn-detail" href="' . url(str_replace('id_fila', $datos_consulta->id, $url_ver)) . '" title="Ver"><i class="fa fa-btn fa-eye"></i>&nbsp;</a>';
            })
            ->editColumn('id', 'ID: {{$id}}')
            ->filterColumn('nombre_completo', function ($query, $keyword) {
                $query->whereRaw("TRIM(CONCAT(core_terceros.nombre1,' ',core_terceros.otros_nombres,' ',core_terceros.apellido1,' ',core_terceros.apellido2)) like ?", ["%{$keyword}%"]);
            })
            ->filterColumn('numero_identificacion', function ($query, $keyword) {
                $query->whereRaw('core_terceros.numero_identificacion like ?', ["%{$keyword}%"]);
            })
            ->make(true);
    }

    //Método generación cadena aleatoria con rand()
    public static function generar_cadena_aleatoria($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }



    public function create_registro_modelo( $modelo_id )
    {
        $modelo = Modelo::find( $modelo_id );

        $lista_campos = $this->get_campos_modelo( $modelo, '', 'create');

        if ( method_exists(app( $modelo->name_space ), 'get_campos_adicionales_create') )
        {
            $lista_campos = app( $modelo->name_space )->get_campos_adicionales_create($lista_campos);
        }

        $form_create = [
                        'url' => 'web', // Siempre se almacenará con ModeloController@store()
                        'campos' => $lista_campos
                        ];

        $miga_pan = [];

        $vista = 'layouts.registro_modelo_create';

        return view( $vista, compact( 'modelo', 'form_create', 'miga_pan', 'archivo_js') );
    }

}
