<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/


/*
		PRUEBAS UNIFICAR DB - LOCAL - REMOTA
*/

Route::get('/', 'PaginaWeb\FrontEndController@inicio');

Route::get('pagina_no_encontrada/{url}', 'PaginaWeb\FrontEndController@pagina_no_encontrada');

Route::get('dashboard', 'HomeController@index');
Route::get('custom_route', 'HomeController@index');
Route::get('inicio', 'HomeController@inicio'); // Cambiar la palabra "inicio" por "appsiel"... o crear automatización de rutas, vistas, acciones

Route::get('ayuda/videos', 'AyudaController@videos')->name('ayuda.videos');


//Route::get('tipo_vista={tipo_vista}', 'Sistema\VistaController@dibujar_vista');

Route::get('select_dependientes/{id_modelo}/{id_select_padre}', 'Core\CoreController@select_dependientes');

Route::get('ajax_datatable', 'Sistema\ModeloController@ajax_datatable');
Route::get('web/eliminar_asignacion/registro_modelo_hijo_id/{registro_modelo_hijo_id}/registro_modelo_padre_id/{registro_modelo_padre_id}/id_app/{id_app}/id_modelo_padre/{id_modelo_padre}', 'Sistema\ModeloController@eliminar_asignacion');
Route::post('web/guardar_asignacion', 'Sistema\ModeloController@guardar_asignacion');
Route::get('a_i/{id_registro}', 'Sistema\ModeloController@activar_inactivar');


Route::get('core_create_registro_modelo/{modelo_id}', 'Sistema\ModeloController@create_registro_modelo');



Route::auth();

//          C O R E

// Ruta principal (web)

Route::get('actualizar_campos_modelos_relacionados', 'Sistema\ModeloController@actualizar_campos_modelos_relacionados');
Route::get('duplicar/{id}', 'Sistema\ModeloController@duplicar');
Route::get('web_eliminar/{id}', 'Sistema\CrudController@eliminar_registro');

Route::get('web_delete_record/{app_id}/{model_id}/{record_id}/{arr_url_redirect}', 'Sistema\CrudController@delete_record');


// Sistema de rutas #1
Route::resource('web', 'Sistema\ModeloController');

// API Example
Route::get('api10/get_example', 'Sistema\PublicController@get_example');
Route::post('api10/post_example', 'Sistema\PublicController@post_example');

//  CRUD AJAX

Route::get('formulario_ajax_modelo/{modelo_id}/{registro_id}/{accion}', 'Sistema\CrudAjaxController@formulario_ajax_modelo');
Route::resource('web_ajax', 'Sistema\CrudAjaxController');

//Configuraciones

// Ver lo modelos a los que pertence un campo


Route::get('prueba_directa', 'Core\PruebasController@prueba_directa');

Route::get('config', 'Core\ConfiguracionController@config_form');

Route::post('guardar_config', 'Core\ConfiguracionController@guardar_config');
Route::get('core/modelo_tiene_campos/{modelo_id}/{campo_id}', 'Core\ConfiguracionController@modelo_tiene_campos');


// creacion_masiva_registros_form
Route::get('creacion_masiva_registros_form', 'Core\ConfiguracionController@creacion_masiva_registros_form');
Route::post('creacion_masiva_registros_procesar', 'Core\ConfiguracionController@creacion_masiva_registros_procesar');
Route::post('creacion_masiva_registros_store', 'Core\ConfiguracionController@creacion_masiva_registros_store');


Route::resource('configuracion', 'Core\ConfiguracionController');



// validar_numero_identificacion de tercero
Route::get('core_consultar_terceros_v2', 'Core\TerceroController@consultar_terceros_v2');
Route::get('core_consultar_terceros', 'Core\TerceroController@consultar_terceros'); // reemplazar por la v2
Route::get('core/validar_numero_identificacion/{numero_identificacion}', 'Core\TerceroController@validar_numero_identificacion');
Route::get('core/validar_numero_identificacion2/{numero_identificacion}', 'Core\TerceroController@validar_numero_identificacion2');

Route::get('core/validar_email/{email}', 'Core\TerceroController@validar_email');



//Route::group(['middleware' => ['role:SuperAdmin']], function () {
Route::resource('core/usuarios', 'UserController');
Route::resource('core/roles', 'Core\RoleController');
//Route::resource('/core/permisos', 'Core\PermissionController');


// Los usuarios administradores pueden cambiar la contraseña de cualquier usuario
Route::get('core/usuario/cambiarpasswd/{user_id}', 'UserController@form_cambiarpasswd');
Route::post('core/usuario/cambiarpasswd', 'UserController@cambiarpasswd');

Route::get('core/usuario/cambiarpasswd_vendedor/{user_id}', 'UserController@form_cambiarpasswd_vendedor');
Route::get('core/usuario/cambiarpasswd_vehiculo/{vehiculo_id}', 'UserController@form_cambiar_passwd_vehiculo');

Route::get('core/validate_password/{user_id}/{password}', 'UserController@validate_password');
Route::get('core_validate_usuario_supervisor/{email}/{password}', 'UserController@validate_usuario_supervisor');

// Perfil del usuario
Route::get('core/usuario/perfil', 'UserController@perfil');

Route::get('core/usuario/perfil/cambiar_empresa', 'UserController@form_cambiar_empresa');
Route::post('core/usuario/perfil/cambiar_empresa', 'UserController@cambiar_empresa');

Route::get('core/usuario/perfil/cambiar_mi_passwd', 'UserController@form_cambiar_mi_passwd');
Route::post('core/usuario/perfil/cambiar_mi_passwd', 'UserController@cambiar_mi_passwd');

Route::get('crear_usuarios_masivos', 'UserController@crear_usuarios_masivos');

// importar datos
Route::get('importar/formulario', 'Core\ImportarDatosController@formulario');
Route::post('importar/formulario', 'Core\ImportarDatosController@importar_formulario');


Route::get('importar/importar_manualmente/terceros', 'Core\ImportarDatosController@terceros');
Route::get('importar/importar_manualmente/inmuebles', 'Core\ImportarDatosController@inmuebles');


// MODULO DISEÑADOR DE FORMATOS
Route::get('core/dis_formatos/secciones_formato/{id_formato}', 'Core\DisFormatosController@secciones_formato');
Route::post('core/dis_formatos/guardar_asignacion', 'Core\DisFormatosController@guardar_asignacion');
Route::post('core/dis_formatos/eliminar_asignacion', 'Core\DisFormatosController@eliminar_asignacion');


Route::resource('get_eventos', 'Core\EventoController@get_eventos');


// MODELO EAV
Route::post('core/eliminar_registros_eav', 'Core\ModeloEavController@eliminar_registros_eav');
Route::post('core_eav_update_db', 'Core\ModeloEavController@update_db');
Route::post('core_eav_cancelar_update_db', 'Core\ModeloEavController@cancelar_update_db');
Route::resource('core/eav', 'Core\ModeloEavController');



// MESSENGER
Route::group(['prefix' => 'messages'], function () {
    Route::get('/', ['as' => 'messages', 'uses' => 'Core\MessagesController@index']);
    Route::get('create', ['as' => 'messages.create', 'uses' => 'Core\MessagesController@create']);
    Route::post('/', ['as' => 'messages.store', 'uses' => 'Core\MessagesController@store']);
    Route::get('{id}', ['as' => 'messages.show', 'uses' => 'Core\MessagesController@show']);
    Route::put('{id}', ['as' => 'messages.update', 'uses' => 'Core\MessagesController@update']);
});

// MODULO GESTION DOCUMENTAL
Route::get('gestion_documental/imprimir_formato', 'GestionDocumentalController@imprimir_formato');
Route::get('gestion_documental/cargar_controles/{formato_id}', 'GestionDocumentalController@cargar_controles');
Route::post('gestion_documental/generar_formato', 'GestionDocumentalController@generar_formato');

Route::get('get_select_estudiantes_del_curso/{curso_id}', 'GestionDocumentalController@get_select_estudiantes_del_curso');

Route::resource('gestion_documental', 'GestionDocumentalController', ['except' => ['show']]);


//             PROPIEDAD HORIZONTAL

Route::get('mi_conjunto/mi_cartera', 'PropiedadHorizontal\MiConjuntoController@mi_cartera');
Route::resource('mi_conjunto', 'PropiedadHorizontal\MiConjuntoController', ['except' => ['show']]);

Route::post('pqr/guardar_nota', 'PropiedadHorizontal\PqrController@guardar_nota');

Route::get('propiedad_horizontal/generar_cxc', 'PropiedadHorizontal\PropiedadHorizontalController@generar_cxc');
Route::post('propiedad_horizontal/generar_consulta_preliminar_cxc', 'PropiedadHorizontal\PropiedadHorizontalController@generar_consulta_preliminar_cxc');
Route::resource('propiedad_horizontal', 'PropiedadHorizontal\PropiedadHorizontalController', ['except' => ['show']]);


// Generación de reportes
Route::get('vista_reporte', 'Sistema\ReporteController@vista_reporte');
Route::get('core_print_reporte', 'Sistema\ReporteController@print_reporte');

// GENERAR PDF Con base en el nombre_listado almacenado en la Cache 
Route::get('generar_pdf/{reporte_id}', 'Sistema\VistaController@generar_pdf');

Route::get('core_descargar_pdf_desde_cache/{cache_key}/{pdf_name}', 'Sistema\VistaController@descargar_pdf_desde_cache');
Route::get('core_forget_cache/{cache_key}', 'Sistema\VistaController@forget_cache');


// Gestión de imagenes
Route::get('quitar_imagen', 'Sistema\ImagenController@quitar_imagen');

// Revisión de errores
Route::get('/logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

Route::post('carga_imagen_ckeditor', 'Sistema\ImagenController@carga_imagen_ckeditor');


// PROCESOS
Route::get('index_procesos/{vista_proceso}', 'Core\ProcesoController@principal');

// ************************ Exportar/Importar registros tablas BD a través de archivo de configuración
Route::get('form_exportar_importar_tablas_bd', 'Sistema\ProcesoController@form_exportar_importar_tablas_bd');
Route::post('exportar_tablas_bd', 'Sistema\ProcesoController@exportar_tablas_bd');
Route::get('visualizar_tablas_archivo', 'Sistema\ProcesoController@visualizar_tablas_archivo');

Route::get('generar_registros_archivo_configuracion/{tablas_a_exportar}', 'Sistema\ProcesoController@generar_registros_archivo_configuracion');
Route::get('insertar_registros_tablas_bd', 'Sistema\ProcesoController@insertar_registros_tablas_bd');
// ************************
// ************************	REVISAR ESTRUCTURA BD
Route::get('generar_lista_tablas_con_sus_campos', 'Sistema\ProcesoController@generar_lista_tablas_con_sus_campos');

Route::get('form_password_resets', 'Sistema\ProcesoController@form_password_resets');
Route::get('config_password_resets/{role_id}/{password_mode}', 'Sistema\ProcesoController@config_password_resets');


// Para transacciones
Route::get('enlace_show_documento/{core_tipo_transaccion_id}/{core_tipo_doc_app_id}/{consecutivo}', 'Core\TransaccionController@enlace_show_documento');
Route::resource('transaction', 'Core\TransaccionController');


Route::get('core_test_email', 'Sistema\EmailController@test_email');

//          REPORTES
Route::post('core_movimiento_con_fecha_distinta_a_su_creacion','Core\ReporteController@movimiento_con_fecha_distinta_a_su_creacion');


// RUTAS PARA LA EXPORTACIÓN DE TABLAS EN FORMATOS PDF Y EXCEL

Route::post('table/export', 'Sistema\ExporttableController@export')->name('export.table');

// SISTEMA
include __DIR__ . '/system.php';


// Aplicación MATRICULAS
include __DIR__ . '/matriculas_routes.php';

// Aplicación CALIFICACIONES
include __DIR__ . '/calificaciones_routes.php';

// Aplicación ACADÉMICO DOCENTE
include __DIR__ . '/academico_docente_routes.php';

// Aplicación ACADÉMICO ESTUDIANTE
include __DIR__ . '/academico_estudiante_routes.php';

// Aplicación T E S O R E R Í A
include __DIR__ . '/teso_routes.php';

// Aplicación INVENTARIOS
include __DIR__ . '/inv_routes.php';

// Aplicación VENTAS
include __DIR__ . '/vtas_routes.php';

// Aplicación VENTAS POS
include __DIR__ . '/ventas_pos_routes.php';

// Aplicación VENTAS POS
include __DIR__ . '/facturacion_electronica_routes.php';

// Aplicación COMPRAS
include __DIR__ . '/compras_routes.php';

// Aplicación CONTABILIDAD
include __DIR__ . '/contab_routes.php';

// Aplicación GESTIÓN DE COBROS (CxC)
include __DIR__ . '/cxc_routes.php';

// Aplicación GESTIÓN DE Ctas. por Paga (CxP)
include __DIR__ . '/cxp_routes.php';

// Aplicación NÓMINA
include __DIR__ . '/nomina_routes.php';

// Aplicación PÁGINA WEB
include __DIR__ . '/pagina_web_routes.php';

// Aplicación CONSULTORIO MÉDICO
include __DIR__ . '/consultorio_medico_routes.php';

// Rutas Adicionales Sistema Gestión Académica
include __DIR__ . '/sga_routes.php';

// Aplicación CONTRATO TRANSPORTE
include __DIR__ . '/contratotransporte_routes.php';

// Esta línea debe ir de última porque ya hay rutas específicas para /{slug}
// Ejemplo, /inicio, /ventas, /configuracion, etc. 
// Cada ruta de estas llama a sus propios controladores
Route::get('/{slug}', 'web\PaginaController@showPage');
