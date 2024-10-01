<?php

namespace App\Http\Controllers\Sistema;

use App\Calificaciones\Asignatura;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

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
use App\Core\Tercero;
use App\Matriculas\Curso;
use App\Sistema\Services\AppModel;
use App\Sistema\Services\ImagenService;
use App\Sistema\Services\ModeloService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;

class ModeloController extends Controller
{
    protected $aplicacion, $modelo, $datos;

    public function __construct()
    {
        // Se obtiene el modelo
        if (!is_null(Input::get('id_modelo'))) {
            
            $this->modelo = Modelo::find(Input::get('id_modelo'));
            
            /*$this->modelo = Modelo::where('name',$model)
                                    ->get()
                                    ->first();*/
        }

        // No requiere autenticación para el CRUD del modelo ClienteWeb (id_modelo=218)
        if (!is_null($this->modelo)) {
            if ($this->modelo->id != 218) {
                $this->middleware('auth');
            }
        } else {
            //$this->middleware('auth');
        }


        $this->aplicacion = Aplicacion::find(Input::get('id'));
    }


    public function trigger( $app, $model, $action, $view )
    {

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

        $id_modelo = Input::get('id_modelo');
        $id_app = Input::get('id');

        $id_transaccion = TipoTransaccion::where('core_modelo_id', (int) Input::get('id_modelo'))->where('estado', 'Activo')->value('id');

        // Se le asigna a cada variable url, su valor en el modelo correspondiente
        $variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . $id_transaccion;

        $acciones = $this->acciones_basicas_modelo($this->modelo, $variables_url);

        $url_crear = $acciones->create;
        $url_edit = $acciones->edit;
        $url_print = $acciones->imprimir;
        $url_ver = $acciones->show;
        $url_estado = $acciones->cambiar_estado;
        $url_eliminar = $acciones->eliminar;

        $botones = [];
        $enlaces = json_decode($acciones->otros_enlaces);

        if (!is_null($enlaces)) {
            $i = 0;
            foreach ($enlaces as $fila) {
                $botones[$i] = new Boton($fila);
                $i++;
            }
        }

        // Si el modelo tiene un archivo js particular
        $archivo_js = app($this->modelo->name_space)->archivo_js;

        $registros = [];

        //determinar la cantidad de registros a mostrar
        $nro_registros = 10;
        $temp = Input::get('nro_registros');
        if ($temp != null) {
            $nro_registros = $temp;
        }
        $sqlString = "";
        $tituloExport = "";
        //determinar la busqueda
        $search = "";
        $temp2 = Input::get('search');
        if ($temp2 != null) {
            $search = trim($temp2);
        }
        if (method_exists(app($this->modelo->name_space), 'consultar_registros')) {

            $registros = app($this->modelo->name_space)->consultar_registros($nro_registros, $search);
            $sqlString = app($this->modelo->name_space)->sqlString($search);
            $tituloExport = app($this->modelo->name_space)->tituloExport();
        }

        $vista = 'layouts.index';
        $vistas = json_decode(app($this->modelo->name_space)->vistas);
        if (!is_null($vistas)) {
            if (isset($vistas->index)) {
                $vista = $vistas->index;
                $registros = app($this->modelo->name_space)->consultar_registros2($nro_registros, $search);
                $sqlString = app($this->modelo->name_space)->sqlString($search);
                $tituloExport = app($this->modelo->name_space)->tituloExport();
            }
        }

        $source = "INDEX1";
        $curso = new Curso();
        $asignatura = new Asignatura();

        // ¿Cómo saber qué métodos estan llamando a la vista layouts.index?
        // Si modifico esa vista, cómo se qué partes del software se verán afectadas???
        return view($vista, compact('id_app', 'asignatura', 'curso', 'tituloExport', 'sqlString', 'search', 'source', 'nro_registros', 'id_modelo', 'id_transaccion', 'registros', 'miga_pan', 'url_crear', 'encabezado_tabla', 'url_edit', 'url_print', 'url_ver', 'url_estado', 'url_eliminar', 'archivo_js', 'botones'));
    }

    /*
        NOTA: Para evitar resultados inesperados, se debe comprobar que la variable $urls_acciones tenga un formato JSON correcto.

        Se movimo este método a ModeloService(), se debe reemplaza todas las llamadas a ModeloController@acciones_basicas_modelo($modelo, $parametros_url) por (new ModeloService())->acciones_basicas_modelo($modelo, $parametros_url)
    */
    public function acciones_basicas_modelo($modelo, $parametros_url)
    {
        return (new ModeloService())->acciones_basicas_modelo($modelo, $parametros_url);
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

        $acciones = $this->acciones_basicas_modelo($this->modelo, '');

        $form_create = [
            'url' => $acciones->store,
            'campos' => $lista_campos
        ];

        $miga_pan = MigaPan::get_array($this->aplicacion, $this->modelo, 'Crear nuevo');

        // Si el modelo tiene un archivo js particular
        $archivo_js = app($this->modelo->name_space)->archivo_js;


        $vista = 'layouts.create';
        $vistas = json_decode(app($this->modelo->name_space)->vistas);
        if (!is_null($vistas)) {
            if (isset($vistas->create)) {
                $vista = $vistas->create;
            }
        }

        if (Input::get('vista') != null) {
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
        $registro = $this->crear_nuevo_registro($request);

        // Si se está almacenando una transacción que maneja consecutivo
        if (isset($request->consecutivo) and isset($request->core_tipo_doc_app_id))
        {
            // Seleccionamos el consecutivo actual (si no existe, se crea) y le sumamos 1
            $consecutivo = TipoDocApp::get_consecutivo_actual($request->core_empresa_id, $request->core_tipo_doc_app_id) + 1;

            // Se incementa el consecutivo para ese tipo de documento y la empresa
            TipoDocApp::aumentar_consecutivo($request->core_empresa_id, $request->core_tipo_doc_app_id);

            $registro->consecutivo = $consecutivo;
            $registro->save();
        }

        // $this->modelo se actualiza en el método de arriba crear_nuevo_registro()
        $this->almacenar_imagenes($request, $this->modelo->ruta_storage_imagen, $registro);

        /*
            Tareas adicionales de almacenamiento (guardar en otras tablas, crear otros modelos, etc.)
        */
        if (method_exists(app($this->modelo->name_space), 'store_adicional'))
        {
            // Aquí mismo se puede hacer el return
            $url_respuesta = app($this->modelo->name_space)->store_adicional($datos, $registro);

            if ( !is_null($url_respuesta) )
            {
                if (gettype($url_respuesta) != "object")
                {
                    return redirect($url_respuesta)->with('flash_message', 'Registro CREADO correctamente.');
                }
            }
        }

        $acciones = $this->acciones_basicas_modelo($this->modelo, '');

        $url_ver = str_replace('id_fila', $registro->id, $acciones->show);

        return redirect($url_ver . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo . '&id_transaccion=' . $request->url_id_transaccion)->with('flash_message', 'Registro CREADO correctamente.');
    }

    /*
        Crear nuevo registro con los datos enviados por POST
        La función recibe un objeto Request
        Además, el los datos del request debe venir un campo con el ID del modelo del cúal se le va a crear el registro
    */
    public function crear_nuevo_registro($request)
    {
        $this->modelo = Modelo::find( $request->url_id_modelo );
        
        $this->validar_requeridos_y_unicos($request, $this->modelo);

        // Se verifican si vienen campos con valores tipo array. Normalmente para los campos tipo chexkbox.
        foreach ($request->all() as $key => $value)
        {
            if (is_array($value))
            {
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
        for ($i = 0; $i < $cant; $i++)
        {
            // Se valida solo si el campo pertenece al Modelo directamente
            if (in_array($lista_campos[$i]['name'], $registro->getFillable()))
            {
                if ($lista_campos[$i]['requerido'])
                {
                    $this->validate($request, [$lista_campos[$i]['name'] => 'required']);
                }

                if ($lista_campos[$i]['unico'])
                {
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
        return (new ImagenService())->almacenar_imagenes($request, $ruta_storage_imagen, $registro, $modo);
    }


    // FORMULARIO PARA EDITAR UN REGISTRO
    public function edit($id)
    {
        // Se obtiene el registro a modificar del modelo
        $registro = app($this->modelo->name_space)->find($id);
        
        $acciones = $this->acciones_basicas_modelo($this->modelo, '');

        if($registro == null)
        {
            return redirect('web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion'))->with('mensaje_error', 'Registro no encontrado.');
        }

        $lista_campos = $this->get_campos_modelo($this->modelo, $registro, 'edit');

        /*
            Agregar campos adicionales 
            Algunas Modelos necesitan campos formateados o compuestos de una manera única
            También se pueden personalizar los campos asignados al Modelo
        */
        if (method_exists(app($this->modelo->name_space), 'get_campos_adicionales_edit')) {
            $lista_campos = app($this->modelo->name_space)->get_campos_adicionales_edit($lista_campos, $registro);
        }

        if (isset($lista_campos[0])) {
            if (is_null($lista_campos[0])) {

                $url_index = str_replace('id_fila', $registro->id, $acciones->index);

                return redirect($url_index . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion'))->with('mensaje_error', $lista_campos[1]);
            }
        }


        $acciones = $this->acciones_basicas_modelo($this->modelo, '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion'));

        $url_action = str_replace('id_fila', $registro->id, $acciones->update);

        $form_create = [
            'url' => $url_action,
            'campos' => $lista_campos
        ];

        $miga_pan = MigaPan::get_array($this->aplicacion, $this->modelo, $registro->descripcion);

        $archivo_js = app($this->modelo->name_space)->archivo_js;

        $vista = 'layouts.edit';
        $vistas = json_decode(app($this->modelo->name_space)->vistas);
        if (!is_null($vistas)) {
            if (isset($vistas->edit)) {
                $vista = $vistas->edit;
            }
        }

        return view($vista, compact('form_create', 'miga_pan', 'registro', 'archivo_js', 'url_action'));
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
        for ($i = 0; $i < $cant; $i++)
        {
            if ($lista_campos[$i]['editable'] == 1) 
            {
                if ($lista_campos[$i]['requerido']) 
                {
                    $this->validate($request, [$lista_campos[$i]['name'] => 'required']);
                }
                
                if ($lista_campos[$i]['unico']) 
                {
                    $this->validate($request, [$lista_campos[$i]['name'] => 'unique:' . $registro->getTable() . ',' . $lista_campos[$i]['name'] . ',' . $id]);
                }
            }
            // Cuando se edita una transacción
            if ($lista_campos[$i]['name'] == 'movimiento')
            {
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
        if (method_exists(app($modelo->name_space), 'update_adicional')) {
            $url_respuesta = app($modelo->name_space)->update_adicional($datos, $id);

            if (!is_null($url_respuesta)) {
                return redirect($url_respuesta)->with('flash_message', 'Registro MODIFICADO correctamente.');
            }
        }

        $acciones = $this->acciones_basicas_modelo($modelo, '');
        $url_ver = str_replace('id_fila', $registro->id, $acciones->show);

        return redirect($url_ver . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo . '&id_transaccion=' . $request->url_id_transaccion)->with('flash_message', 'Registro MODIFICADO correctamente.');
    }

    /*
        **
    */
    // VISTA TIPO TABLA PARA MOSTRAR UN REGISTRO
    public function show($id)
    {
        // Se obtiene el registro del modelo indicado y el anterior y siguiente registro
        $registro = app($this->modelo->name_space)->find($id);

        if (is_null($registro)) {
            return redirect('web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'))->with('mensaje_error', 'ModeloController@show() > El registro que quiere consultar ha sido eliminado.');
            //echo 'No existe el registro con ID: ' . $id . ' para el modelo: ' . $this->modelo->modelo;
            //die();
        }

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

        $acciones = $this->acciones_basicas_modelo($this->modelo, $variables_url);

        $url_crear = $acciones->create;
        $url_edit = $acciones->edit;
        $url_print = $acciones->imprimir;
        $url_ver = $acciones->show;
        $url_estado = $acciones->cambiar_estado;
        $url_eliminar = $acciones->eliminar;

        $botones = [];
        $enlaces = json_decode($acciones->otros_enlaces);

        if (!is_null($enlaces)) {
            $i = 0;
            foreach ($enlaces as $fila) {
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
        if (!is_null($vistas)) {
            if (isset($vistas->show)) {
                $vista = $vistas->show;
            }
        }

        // Para lo modelos que tienen otro modelo relacionado. Ejemplo, El modelo Modelo tiene Campos. El modelo Cuestionario, tiene Preguntas
        $respuesta = ModeloController::get_tabla_relacionada($this->modelo, $registro);

        $tabla = $respuesta['tabla'];
        $opciones = $respuesta['opciones'];
        $registro_modelo_padre_id = $respuesta['registro_modelo_padre_id'];
        $titulo_tab = $respuesta['titulo_tab'];

        return view($vista, compact('form_create', 'miga_pan', 'registro', 'url_crear', 'url_edit', 'tabla', 'opciones', 'registro_modelo_padre_id', 'reg_anterior', 'reg_siguiente', 'titulo_tab', 'botones'));
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

            if ($lista_campos[$i]['name'] == 'core_tercero_id' and $modelo->id == 178) {
                $lista_campos[$i]['opciones'] = 'model_App\AcademicoDocente\Profesor';
                $tercero_usuario_actual = Tercero::where('user_id', $registro->id)->get()->first();
                if ($tercero_usuario_actual != null) {
                    $lista_campos[$i]['value'] = $tercero_usuario_actual->id;
                };
            }

            if ($lista_campos[$i]['tipo'] == 'imagen')
            {
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

    public static function personalizar_campos($id_transaccion, $tipo_transaccion, $lista_campos, $cantidad_campos, $accion )
    {
        return (new ModeloService())->personalizar_campos($id_transaccion, $tipo_transaccion, $lista_campos, $cantidad_campos, $accion );
    }

    // Construir un array con los campos asociados al modelo
    public static function get_campos_modelo($modelo, $registro, $accion)
    {
        return (new ModeloService())->get_campos_modelo($modelo, $registro, $accion);
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

        if (isset($nuevo_registro->imagen)) {
            $nuevo_registro->imagen = '';
        }

        if (isset($nuevo_registro->descripcion)) {
            $nuevo_registro->descripcion = 'COPIA DE ' . $registro->descripcion;
        }

        $nuevo_registro->save();

        if (method_exists(app($this->modelo->name_space), 'duplicar_adicional')) {
            app($this->modelo->name_space)->duplicar_adicional($registro, $nuevo_registro);
        }

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
                $acciones = $this->acciones_basicas_modelo($this->modelo, $variables_url);

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

    public function create_registro_modelo($modelo_id)
    {
        $modelo = Modelo::find($modelo_id);

        $lista_campos = $this->get_campos_modelo($modelo, '', 'create');

        if (method_exists(app($modelo->name_space), 'get_campos_adicionales_create')) {
            $lista_campos = app($modelo->name_space)->get_campos_adicionales_create($lista_campos);
        }

        $form_create = [
            'url' => 'web', // Siempre se almacenará con ModeloController@store()
            'campos' => $lista_campos
        ];

        $miga_pan = [];

        $vista = 'layouts.registro_modelo_create';

        $archivo_js = app($modelo->name_space)->archivo_js;

        return view($vista, compact('modelo', 'form_create', 'miga_pan', 'archivo_js'));
    }
}
