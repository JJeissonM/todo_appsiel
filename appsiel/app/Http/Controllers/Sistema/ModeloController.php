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


use App\Sistema\Html\Boton;
use App\Sistema\Html\MigaPan;

use App\Sistema\Aplicacion;
use App\Sistema\TipoTransaccion;
use App\Core\TipoDocApp;
use App\Sistema\Modelo;
use App\Sistema\Campo;
use App\Core\Tercero;
use App\Core\ModeloEavValor;
use App\User;
use App\Matriculas\Matricula;

use App\Calificaciones\Logro;
use App\Calificaciones\Asignatura;
use App\Core\ConsecutivoDocumento;

use App\Inventarios\InvMovimiento;
use App\Inventarios\InvBodega;

use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;

use App\Contabilidad\ContabCuenta;
use App\PropiedadHorizontal\Propiedad;

class ModeloController extends Controller
{
    protected $app, $modelo, $datos;

    public function __construct()
    {
        $this->middleware('auth');

        $this->aplicacion = Aplicacion::find( Input::get('id') );

        // Se obtiene el modelo
        if ( !is_null( Input::get('id_modelo') ) ) {
            $this->modelo = Modelo::find( Input::get('id_modelo') );
        }
    }

    /*
        * Muestra una tabla con los registros de un modelo
    */
    public function index()
    {
        $registros = app($this->modelo->name_space)->consultar_registros();//->take(20);

        $miga_pan = MigaPan::get_array( $this->aplicacion, $this->modelo, 'Listado');

        $encabezado_tabla = app($this->modelo->name_space)->encabezado_tabla;

        $id_transaccion = TipoTransaccion::where( 'core_modelo_id', (int)Input::get('id_modelo') )->value('id');
        
        // Se le asigna a cada variable url, su valor en el modelo correspondiente
        $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.$id_transaccion;

        $url_crear = '';
        $url_edit = '';
        $url_print = '';
        $url_ver = '';
        $url_estado = '';
        $url_eliminar = '';
        $botones = [];
        
        // @can('crear_'.$modelo->modelo)
        if ($this->modelo->url_crear!='') {
            $url_crear = $this->modelo->url_crear.$variables_url;
        }
        // @endcan

        if ($this->modelo->url_edit!='') {
            $url_edit = $this->modelo->url_edit.$variables_url;
        }
        if ($this->modelo->url_print!='') {
            $url_print = $this->modelo->url_print.$variables_url;
        }
        if ($this->modelo->url_ver!='') {
            $url_ver = $this->modelo->url_ver.$variables_url;
        }

        // ENLACES
        if ( $this->modelo->enlaces != '') 
        {
            $enlaces = json_decode( $this->modelo->enlaces );
            $i=0;
            foreach ($enlaces as $fila) {
                $botones[$i] = new Boton($fila);
                $i++;
            }
        }

        if ($this->modelo->url_estado!='') {
            $url_estado = $this->modelo->url_estado.$variables_url;
        }
        if ($this->modelo->url_eliminar!='') {
            $url_eliminar = $this->modelo->url_eliminar.$variables_url;
        }

        // Si el modelo tiene un archivo js particular
        $archivo_js = app($this->modelo->name_space)->archivo_js;
        
        //dd( $registros );

        // ¿Cómo saber qué métodos estan llamando a la vista layouts.index?
        // Si modifico esa vista, cómo se qué partes del software se verán afectadas???
        return view('layouts.index', compact('registros','miga_pan','url_crear','encabezado_tabla','url_edit','url_print','url_ver','url_estado','url_eliminar','archivo_js','botones'));
    }

    // FORMULARIO PARA CREAR UN NUEVO REGISTRO
    public function create()
    {   
        // Se obtienen los campos que el Modelo tiene asignados
        $lista_campos = ModeloController::get_campos_modelo($this->modelo,'','create');


        /*
            Agregar campos adicionales 
            Algunas Modelos necesitan campos formateados o compuestos de una manera única
            También se pueden personalizar los campos asignados al Modelo
        */
        if ( method_exists( app( $this->modelo->name_space ), 'get_campos_adicionales_create' ) )
        {
            $lista_campos = app( $this->modelo->name_space )->get_campos_adicionales_create( $lista_campos );
        }        

        // Se crear un array para generar el formulario
        // Este array se envía a la vista layouts.create, que carga la plantilla principal del formulario CREAR
        // La vista layouts.create incluye a la vista core.vistas.form_create que es la que usa al array form_create para generar un formulario html

        $url_form_create = 'web';
        if ( $this->modelo->url_form_create != '')
        {
            $url_form_create = $this->modelo->url_form_create;
        }

        $form_create = [
                        'url' => $url_form_create,
                        'campos' => $lista_campos
                    ];

        $miga_pan = MigaPan::get_array( $this->aplicacion, $this->modelo, 'Crear nuevo');

        // Si el modelo tiene un archivo js particular
        $archivo_js = app($this->modelo->name_space)->archivo_js;
        $vista = 'layouts.create';


        /*  Lo ideal es que las URLs se manejen desde cada modelo
            y no, desde la base de datos
                 */
        $vistas = json_decode( app($this->modelo->name_space)->vistas );
        if ( !is_null($vistas) )
        {
            if ( !is_null($vistas->create) )
            {
                $vista = $vistas->create;
            }
        }

        if ( Input::get('vista') != null ) {
            return view( Input::get('vista'), compact('form_create','miga_pan','archivo_js') );
        }

        // Si HAY tareas adicionales u otros modelos que afectar (almacenar en otras tablas)
        if ($this->modelo->controller_complementario!='') {
            return \App::call( $this->modelo->controller_complementario.'@create', [ 'form_create' => $form_create, 'miga_pan' => $miga_pan, 'archivo_js' => $archivo_js] );
        }else{ // Si no, se envía al index del ModeloController
            return view( $vista,compact('form_create','miga_pan','archivo_js'));
        }        
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
        if ( isset($request->consecutivo) and isset($request->core_tipo_doc_app_id) ) 
        {
            // Seleccionamos el consecutivo actual (si no existe, se crea) y le sumamos 1
            $consecutivo = TipoDocApp::get_consecutivo_actual($request->core_empresa_id,$request->core_tipo_doc_app_id) + 1;

            // Se incementa el consecutivo para ese tipo de documento y la empresa
            TipoDocApp::aumentar_consecutivo($request->core_empresa_id,$request->core_tipo_doc_app_id);

            $registro->consecutivo = $consecutivo;
            $registro->save();
        }

        $this->almacenar_imagenes( $request, $this->modelo->ruta_storage_imagen, $registro );


        $url_ver = 'web/'.$registro->id;
        if ($this->modelo->url_ver!='') {
            $url_ver = str_replace('id_fila', $registro->id, $this->modelo->url_ver);
        }

        /*
            Tareas adicionales de almacenamiento (guardar en otras tablas, crear otros modelos, etc.)
            Este método debe reemplazar el condicional de más abajo que usa controller_complementario
        */
        if ( method_exists( app( $this->modelo->name_space ), 'store_adicional' ) )
        {
            app( $this->modelo->name_space )->store_adicional( $datos, $registro );
        }

        // Si HAY tareas adicionales u otros modelos que afectar (almacenar en otras tablas)
        if ($this->modelo->controller_complementario!='') 
        {
            return \App::call( $this->modelo->controller_complementario.'@store',['request'=>$request,'registro'=>$registro] );
        }else{ // Si no, se envía a la vista SHOW del ModeloController
            return redirect( $url_ver.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion )->with( 'flash_message','Registro CREADO correctamente.' );
        }

    }

    /*
        Crear nuevo registro con los datos enviados por POST
        La función recibe un objeto Request
        Además, el los datos del request debe venir un campo con el ID del modelo del cúal se le va a crear el registro
    */
    public function crear_nuevo_registro( $request )
    {
        $this->modelo = Modelo::find($request->url_id_modelo);


        $this->validar_requeridos_y_unicos($request, $this->modelo);

        // Se verifican si vienen campos con valores tipo array. Normalmente para los campos tipo chexkbox.
        foreach ( $request->all() as $key => $value)
        {
            if ( is_array($value) )
            {
                $request[$key] = implode(",", $value);
            }
        }

        // Crear el nuevo registro
        return app($this->modelo->name_space)->create( $request->all() );
    }


    // USAR Solo cuando se está almacenando un nuevo registro
    // !!! Revisar cuando se está editando
    public function validar_requeridos_y_unicos($request, $registro_modelo)
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
        $lista_nombres = '';
        $nombre_es_el_primero = true;
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
            if ( $nombre_es_el_primero )
            {
                $lista_nombres .= $nuevo_nombre;
                $nombre_es_el_primero = false;
            }else{
                $lista_nombres .= ','.$nuevo_nombre;
            }
            

            // Guardar la imagen en disco
            Storage::put( $ruta_storage_imagen.$nuevo_nombre, file_get_contents( $archivo->getRealPath() ) );

            // Guardar nombre en la BD
            $registro->$key = $nuevo_nombre;
            $registro->save();
        }

        return $lista_nombres;
    }


    // FOMRULARIO PARA EDITAR UN REGISTRO
    public function edit($id)
    {
        // Se obtiene el registro a modificar del modelo
        $registro = app($this->modelo->name_space)->find($id);

        $lista_campos = $this->get_campos_modelo($this->modelo,$registro,'edit');

        /*
            Agregar campos adicionales 
            Algunas Modelos necesitan campos formateados o compuestos de una manera única
            También se pueden personalizar los campos asignados al Modelo
        */
        if ( method_exists( app( $this->modelo->name_space ), 'get_campos_adicionales_edit' ) )
        {
            $lista_campos = app( $this->modelo->name_space )->get_campos_adicionales_edit( $lista_campos, $registro );
        } 

        // Se crear un array para generar el formulario
        // Este array se envía a la vista layouts.create, que carga la platilla principal,
        // La vista layouts.create incluye a la vista core.vistas.form_create que es la usa al array
        // form_create para generar un formulario html 
        $form_create = [
                        'url' => $this->modelo->url_form_create,
                        'campos' => $lista_campos
                    ];

        $miga_pan = MigaPan::get_array( $this->aplicacion, $this->modelo, $registro->descripcion);

        $archivo_js = app($this->modelo->name_space)->archivo_js;
        $vistas = json_decode( app($this->modelo->name_space)->vistas ); // solo se intentó usar con el modelo Logro

        $vista = 'layouts.edit';
        if ( !is_null($vistas) )
        {
            if ( !is_null($vistas->edit) )
            {
                $vista = $vistas->edit;
            }
        }

        $url_action = 'web/'.$id;
        if ($this->modelo->url_form_create != '') {
            $url_action = $this->modelo->url_form_create.'/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion');
        }

        return view( $vista,compact('form_create','miga_pan','registro','archivo_js','url_action'));
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

        /*
            Tareas adicionales de almacenamiento (guardar en otras tablas, crear otros modelos, etc.)
            Este método debe reemplazar el condicional de más abajo que usa controller_complementario
        */
        if ( method_exists( app( $modelo->name_space ), 'update_adicional' ) )
        {
            app( $modelo->name_space )->update_adicional( $datos, $id );
        }
        
        if ($modelo->controller_complementario!='') {
            return \App::call($modelo->controller_complementario.'@update',['request'=>$request,'id'=>$id]);
        }else{
            return redirect('web/'.$registro->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion)->with('flash_message','Registro MODIFICADO correctamente.');
        }/**/
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
        
        $lista_campos = $this->asignar_valores_de_campo_al_registro($this->modelo, $registro, $lista_campos1->toArray() );


        /*
            Tareas adicionales para mostrar el registro
        */
        if ( method_exists( app( $this->modelo->name_space ), 'show_adicional' ) )
        {
            $lista_campos = app( $this->modelo->name_space )->show_adicional( $lista_campos, $registro );
        }

        $form_create = [
                        'url' => $this->modelo->url_form_create,
                        'campos' => $lista_campos
                    ];
        
        $miga_pan = MigaPan::get_array( $this->aplicacion, $this->modelo, $registro->descripcion);

        $url_crear = '';
        $url_edit = '';
        
        $id_transaccion = TipoTransaccion::where( 'core_modelo_id', (int)Input::get('id_modelo') )->value('id');
        
        // Se le asigna a cada variable url, su valor en el modelo correspondiente
        $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.$id_transaccion;
        if ($this->modelo->url_crear!='') {
            $url_crear = $this->modelo->url_crear.$variables_url;    
        }
        if ($this->modelo->url_edit!='') {
            $url_edit = $this->modelo->url_edit.$variables_url;
        }

        // ENLACES
        $botones = [];
        if ( $this->modelo->enlaces != '') 
        {
            $enlaces = json_decode( $this->modelo->enlaces );
            $i=0;
            foreach ($enlaces as $fila) {
                $botones[$i] = new Boton($fila);
            }
        }

        // Para lo modelos que tienen otro modelo relacionado. Ejemplo, El modelo Modelo tiene Campos. El modelo Cuestionario, tiene Preguntas
        $respuesta = ModeloController::get_tabla_relacionada($this->modelo,$registro);
        
        $tabla=$respuesta['tabla'];
        $opciones = $respuesta['opciones'];
        $registro_modelo_padre_id = $respuesta['registro_modelo_padre_id'];
        $titulo_tab = $respuesta['titulo_tab'];
        
        return view('layouts.show',compact('form_create','miga_pan','registro','url_crear','url_edit','tabla','opciones','registro_modelo_padre_id','reg_anterior','reg_siguiente','titulo_tab','botones'));       
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

    public static function personalizar_campos($id_transaccion,$tipo_transaccion,$lista_campos,$cantidad_campos,$accion,$tipo_tranferencia=null)
    {

        // Se crea un select SOLO con las opciones asignadas a la transacción
        //if ($tipo_transaccion != 0) {
            $tipo_docs_app = $tipo_transaccion->tipos_documentos;
            foreach ($tipo_docs_app as $fila) {
                $opciones[$fila->id]=$fila->prefijo." - ".$fila->descripcion; 
            }
         //} 
                    
        //Personalización de la lista de campos
        for ($i=0; $i <$cantidad_campos ; $i++) { 
            
            if ($lista_campos[$i]['name']=='core_tipo_doc_app_id') {
                $lista_campos[$i]['opciones'] = $opciones;
            }

            // Valores predeterminados para los campos ocultos
            if ($accion=='create') {
                if ($lista_campos[$i]['name']=='core_tipo_transaccion_id') {
                    $lista_campos[$i]['value'] = $tipo_transaccion->id;
                }
                if ($lista_campos[$i]['name']=='estado') {
                    $lista_campos[$i]['value'] = 'Activo';
                }

                if ($lista_campos[$i]['name']=='user_id') {
                    $lista_campos[$i]['value'] = Auth::user()->id;
                }

                // Cuando la transacción es "Generar CxC"
                if ($lista_campos[$i]['name']=='core_tercero_id' and $id_transaccion==5) {
                    $lista_campos[$i]['requerido'] = false;
                    $lista_campos[$i]['tipo'] = 'hidden';
                }
            }else{
                if ($lista_campos[$i]['name']=='core_tipo_transaccion_id') {
                    $lista_campos[$i]['value'] = null;
                }
                if ($lista_campos[$i]['name']=='estado') {
                    $lista_campos[$i]['value'] = null;
                }
            }

            if ($lista_campos[$i]['name']=='teso_medio_recaudo_id') {

                $registros = TesoMedioRecaudo::all();  
                $vec_m['']=''; 
                foreach ($registros as $fila) {
                    $vec_m[$fila->id.'-'.$fila->comportamiento]=$fila->descripcion; 
                }

                $lista_campos[$i]['opciones'] = $vec_m;
            }
            
            unset($vec_m);
            if ($lista_campos[$i]['name']=='teso_caja_id') {
                $registros = TesoCaja::where('core_empresa_id',Auth::user()->empresa_id)->get();       
                foreach ($registros as $fila) {
                    $vec_m[$fila->id]=$fila->descripcion; 
                }

                if ( count($vec_m) == 0 ) {
                    $vec_m[ '' ]= '';
                }

                $lista_campos[$i]['opciones'] = $vec_m;
            }

            unset($vec_m);
            if ($lista_campos[$i]['name']=='teso_cuenta_bancaria_id') {

                $registros = TesoCuentaBancaria::leftJoin('teso_entidades_financieras','teso_entidades_financieras.id','=','teso_cuentas_bancarias.entidad_financiera_id')
                            ->where('core_empresa_id',Auth::user()->empresa_id)
                            ->select('teso_cuentas_bancarias.id','teso_cuentas_bancarias.descripcion AS cta_bancaria','teso_entidades_financieras.descripcion AS entidad_financiera')
                            ->get();        
                foreach ($registros as $fila) {
                    $vec_m[$fila->id] = $fila->entidad_financiera.': '.$fila->cta_bancaria; 
                }
                
                if ( count($vec_m) == 0 ) {
                    $vec_m[ '' ]= '';
                }

                $lista_campos[$i]['opciones'] = $vec_m;
            }
            
            unset($vec_m);
            if ($lista_campos[$i]['name']=='user_asignado_id') {

                $registros = User::whereHas('roles', function($q){
                                    $q->whereIn('name', ['Administrador PH','SuperAdmin']);
                                })->get();

                //$registros = TesoCaja::where('core_empresa_id',Auth::user()->empresa_id)->get();       
                foreach ($registros as $fila) {
                    $vec_m[$fila->id]=$fila->name; 
                }

                if ( count($vec_m) == 0 ) {
                    $vec_m[ '' ]= '';
                }

                $lista_campos[$i]['opciones'] = $vec_m;
            }
                
        }

        // Si es una transferencia se agrega un nuevo campo para la bodega destino
        if ($id_transaccion==$tipo_tranferencia) {
            $lista_campos[$i]['id'] = 0;
            $lista_campos[$i]['tipo'] = 'select';
            $lista_campos[$i]['name'] = 'bodega_destino_id';
            $lista_campos[$i]['descripcion'] = 'Bodega destino';
            $bodegas = InvBodega::where('estado','Activo')
                            ->get();
            foreach ($bodegas as $fila) {
                $vec_b[$fila->id]=$fila->descripcion; 
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

        $lista_campos = ModeloController::ajustar_valores_lista_campos( $lista_campos1->toArray() );
        
        // Ajustar los valores según la acción
        $lista_campos = ModeloController::ajustar_valores_lista_campos_segun_accion( $lista_campos, $registro, $modelo, $accion );
        
        return $lista_campos;
    }

    public static function ajustar_valores_lista_campos( $lista_campos )
    {
        $cant = count($lista_campos);
        for ($i=0; $i < $cant; $i++) 
        { 
            $nombre_campo = $lista_campos[$i]['name'];
            
            // El campo Atributos se ingresa en  formato JSON {"campo1":"valor1","campo2":"valor2"}
            // Luego se tranforma a un array para que pueda ser aceptado por el Facade Form:: de LaravelCollective
            if ($lista_campos[$i]['atributos']!='') {
                $lista_campos[$i]['atributos'] = json_decode($lista_campos[$i]['atributos'],true);
            }else{
                $lista_campos[$i]['atributos'] = [];
            }
            
            // Cuando el campo es requerido se agrega el atributo al control html
            if ($lista_campos[$i]['requerido']) {
                $lista_campos[$i]['atributos']=array_merge($lista_campos[$i]['atributos'],['required' => 'required']);
            }

            // Cuando se está editando un registro, el formulario llamado por LaravelCollective Form::model(), llena los campos que tienen valor null con los valores del registro del modelo instanciado

            if ($lista_campos[$i]['value']=='null') {
                $lista_campos[$i]['value'] = null;
            }

            // Para llenar los campos tipo select y checkbox
            if ($lista_campos[$i]['tipo'] == 'select' || $lista_campos[$i]['tipo'] == 'bsCheckBox') 
            {
                $lista_campos[$i]['opciones'] = VistaController::get_opciones_campo_tipo_select( $lista_campos[$i] );
            }
        }
        return $lista_campos;
    }



    public static function ajustar_valores_lista_campos_segun_accion( $lista_campos, $registro, $modelo, $accion )
    {
        $cant = count($lista_campos);
        for ($i=0; $i < $cant; $i++) 
        { 
            $nombre_campo = $lista_campos[$i]['name'];
            
            if ($accion=='create') 
            {    
                // Valores predeterminados para Algunos campos ocultos
                switch ( $lista_campos[$i]['name'] ) {
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

            }else{ // Si se está editando
                
                // asignar valor almacenado en la BD al cada campo
                if ( isset( $registro->$nombre_campo ) ) {
                    $lista_campos[$i]['value'] = $registro->$nombre_campo;
                }
                

                // Si el campo NO es editable, se muestra deshabilitado
                if ( !$lista_campos[$i]['editable'] ) 
                {
                    /*
                        Advertencia cuando el campo está deshabilitado NO es enviado en el request del formulario
                        Su valor no es actualizado.
                        No se puede usar su valor (que no existe) en otras acciones.
                    */
                    $lista_campos[$i]['atributos'] = ['disabled'=>'disabled','style'=>'background-color:#FBFBFB;'];

                    if ( $lista_campos[$i]['tipo'] == 'personalizado' ) {
                        $lista_campos[$i]['value'] = '';
                    }

                }

                switch ( $lista_campos[$i]['name'] ) {
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
                        $logros = Logro::get_logros_periodo_curso_asignatura( $registro->periodo_id, $registro->curso_id, $registro->asignatura_id);
                        $descripciones = [];
                        $el_primero = true;
                        foreach ($logros as $un_logro)
                        {
                            $descripciones[$un_logro->escala_valoracion_id] = $un_logro->descripcion;
                        }

                        $lista_campos[$i]['value'] = $descripciones;
                        break;

                    default:
                        # code...
                        break;
                }

                // Si hay campo tipo imagen, se envía la URL de la imagen para mostrala
                if ( $lista_campos[$i]['tipo'] == 'imagen' ) {
                    $lista_campos[$i]['value'] = config('configuracion.url_instancia_cliente')."/storage/app/".$modelo->ruta_storage_imagen.$registro->$nombre_campo;
                }

                // Si hay campo tipo imagenes_multiples, se envía la imagen para mostrala
                if ( $lista_campos[$i]['tipo'] == 'imagenes_multiples' ) {
                    // Esto debe cambiar!!!!!!
                    $lista_campos[$i]['value'] = config('configuracion.url_instancia_cliente')."/storage/app/".$modelo->ruta_storage_imagen.$registro->$nombre_campo;               
                }

                // Si se está editando un checkbox
                if ($lista_campos[$i]['tipo']=='bsCheckBox') 
                {                    
                    //dd($lista_campos[$i]);
                    // Si el name del campo enviado tiene la palabra core_campo_id-ID, se trata de un campo Atributo de EAV
                    if ( strpos( $lista_campos[$i]['name'], "core_campo_id-") !== false ) 
                    {
                        $lista_campos[$i]['value'] = ModeloEavValor::where( [ "modelo_padre_id" => Input::get('modelo_padre_id'), "registro_modelo_padre_id" => Input::get('registro_modelo_padre_id'), "modelo_entidad_id" => Input::get('modelo_entidad_id'), "core_campo_id" => $lista_campos[$i]['id'] ] )->value('valor');
                    }else{
                        $lista_campos[$i]['value'] = $registro->$lista_campos[$i]['name'];
                    }
                }
            }
        }
        return $lista_campos;
    }

    //  M I G A   D E   P A N (de tres niveles: MEJORAR)
    // Retorna el array para la miga de pan
    public function get_miga_pan( $modelo, $etiqueta_final )
    {
        return MigaPan::get_array( $this->aplicacion, $modelo, $etiqueta_final );
    }

    public function get_tabla_relacionada($modelo_padre,$registro_modelo_padre){
        $tabla='';
        $todos_campos = '';
        $registro_modelo_padre_id = '';
        $titulo_tab = '';
        $opciones='';        

        // Si el modelo tiene otro modelo relacionado
        if ( $modelo_padre->modelo_relacionado != '') 
        {

            $registro_modelo_padre_id = $registro_modelo_padre->id;

            $metodo_modelo_relacionado = $modelo_padre->modelo_relacionado;
            
            // etiqueta del tab de formuralio show
            $titulo_tab = ucfirst($metodo_modelo_relacionado);

            // Se obtienen los registros asignados al registro del modelo padre que se va a visualizar
            $registros_asignados = $registro_modelo_padre->$metodo_modelo_relacionado()->orderBy('orden')->get()->toArray();//
            

                        //dd($registros_asignados);
            // Se obtiene una tabla con los registros asociados al registro del modelo
            $tabla = app($modelo_padre->name_space)->get_tabla($registro_modelo_padre,$registros_asignados);

            unset($opciones);
            $opciones = app($modelo_padre->name_space)->get_opciones_modelo_relacionado($registro_modelo_padre->id);
        }else{
            $registros_asignados = [];
        }

        return compact('tabla','opciones','registro_modelo_padre_id','titulo_tab');
    }
    

    // ASIGNACIÓN DE UN CAMPO A UN MODELO
    public function guardar_asignacion(Request $request){
        
        // Se obtiene el modelo "Padre"
        $modelo = Modelo::find($request->url_id_modelo);

        $datos = app($modelo->name_space)->get_datos_asignacion();

        $this->validate($request,['registro_modelo_hijo_id' => 'required']);
        
        DB::table($datos['nombre_tabla'])
            ->insert([
                        $datos['nombre_columna1'] => $request->nombre_columna1,
                        $datos['registro_modelo_padre_id'] => $request->registro_modelo_padre_id, 
                        $datos['registro_modelo_hijo_id'] => $request->registro_modelo_hijo_id
                    ]);

        return redirect('web/'.$request->registro_modelo_padre_id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Asignación CREADA correctamente'); 
    }

    // ELIMINACIÓN DE UN CAMPO A UN MODELO
    public function eliminar_asignacion($registro_modelo_hijo_id,$registro_modelo_padre_id,$id_app,$id_modelo_padre)
    {

        // Se obtiene el modelo "Padre"
        $modelo = Modelo::find($id_modelo_padre);

        /*print_r($modelo);*/
        $datos = app($modelo->name_space)->get_datos_asignacion();

        DB::table($datos['nombre_tabla'])->where($datos['registro_modelo_hijo_id'], '=', $registro_modelo_hijo_id)
                        ->where($datos['registro_modelo_padre_id'], '=', $registro_modelo_padre_id)
                        ->delete();

        return redirect('web/'.$registro_modelo_padre_id.'?id='.$id_app.'&id_modelo='.$id_modelo_padre)->with('flash_message','Asignación ELIMINADA correctamente'); 
        
    }

    public function activar_inactivar($id_registro)
    {
        $registro = app($this->modelo->name_space)->find($id_registro);
        
        if ($registro->estado == 'Activo') {
            $registro->estado = 'Inactivo';
        }else{
            $registro->estado = 'Activo';
        }

        $registro->save();

        $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo');

        return redirect('web/'.$variables_url)->with('flash_message','Estado ACTUALIZADO correctamente.'); 
           
    }

    public function duplicar($id_registro)
    {
        $registro = app($this->modelo->name_space)->find($id_registro);
        
        $nuevo_registro = $registro->replicate();
        $nuevo_registro->save();

        return redirect('web/'.$nuevo_registro->id.'/edit?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'))->with('flash_message','Registro DUPLICADO correctamente.');           
    }

    /*
        Permite actualizar los valores en la tabla que relaciona Modelos ( MODELOPADRE_tiene_MODELOHIJO )
        Ejemplo, sys_modelo_tiene_campos, pw_pagina_tiene_secciones, 
        Los datos vienen por GET
    */
    public function actualizar_campos_modelos_relacionados()
    {
        // Se obtiene el modelo "Padre"
        $modelo = Modelo::find( Input::get('modelo_id') );

        $datos = app($modelo->name_space)->get_datos_asignacion();

        DB::table( $datos['nombre_tabla'] )
                        ->where($datos['registro_modelo_padre_id'], '=', Input::get('registro_modelo_padre_id') )
                        ->where($datos['registro_modelo_hijo_id'], '=', Input::get('registro_modelo_hijo_id') )
                        ->update( [ $datos['nombre_columna1'] => Input::get('valor_nuevo') ] );

        return 'ok';           
    }

    public function ajax_datatable()
    {
        $datos_consulta = app($this->modelo->name_space)->consultar_datatable();

        return Datatables::of( $datos_consulta )
            ->addColumn('action', function ( $datos_consulta ) {
                //if ($modelo->url_edit!='') {
                    $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo');
                    $url_edit = $this->modelo->url_edit.$variables_url;
                    $url_ver = $this->modelo->url_ver.$variables_url;
                //}
                return '<a class="btn btn-warning btn-xs btn-detail" href="'.url( str_replace('id_fila', $datos_consulta->id, $url_edit) ).'" title="Modificar"><i class="fa fa-btn fa-edit"></i>&nbsp;</a> &nbsp;&nbsp;&nbsp;<a class="btn btn-primary btn-xs btn-detail" href="'.url( str_replace('id_fila', $datos_consulta->id, $url_ver) ).'" title="Ver"><i class="fa fa-btn fa-eye"></i>&nbsp;</a>';
            })
            ->editColumn('id', 'ID: {{$id}}')
            ->filterColumn('nombre_completo', function($query, $keyword) {
                $query->whereRaw("TRIM(CONCAT(core_terceros.nombre1,' ',core_terceros.otros_nombres,' ',core_terceros.apellido1,' ',core_terceros.apellido2)) like ?", ["%{$keyword}%"]);
            })
            ->filterColumn('numero_identificacion', function($query, $keyword) {
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
}