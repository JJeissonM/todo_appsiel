<?php
 return array (
  'sys_campos' => 
  array (
    0 => 
    array (
      'id' => 1,
      'descripcion' => 'Nivel Académico',
      'tipo' => 'select',
      'name' => 'nivel_grado',
      'opciones' => 'table_sga_niveles',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2018-09-10 10:48:04',
    ),
    1 => 
    array (
      'id' => 2,
      'descripcion' => 'Descripción',
      'tipo' => 'bsText',
      'name' => 'descripcion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Este campo identifica al campo y se llama en los select por defecto.',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2019-07-26 15:26:02',
    ),
    2 => 
    array (
      'id' => 3,
      'descripcion' => 'Código',
      'tipo' => 'bsText',
      'name' => 'codigo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2018-10-18 13:48:22',
    ),
    3 => 
    array (
      'id' => 4,
      'descripcion' => 'Maneja calificación',
      'tipo' => 'select',
      'name' => 'maneja_calificacion',
      'opciones' => '{"1":"Si","0":"No"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2018-09-02 10:36:57',
    ),
    4 => 
    array (
      'id' => 6,
      'descripcion' => 'Aplicación',
      'tipo' => 'select',
      'name' => 'core_app_id',
      'opciones' => 'model_App\\Sistema\\Aplicacion',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2019-09-13 20:25:55',
    ),
    5 => 
    array (
      'id' => 7,
      'descripcion' => 'Name',
      'tipo' => 'bsText',
      'name' => 'name',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2019-01-03 13:43:47',
    ),
    6 => 
    array (
      'id' => 8,
      'descripcion' => 'Detalle',
      'tipo' => 'bsTextArea',
      'name' => 'descripcion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2018-09-07 21:42:20',
    ),
    7 => 
    array (
      'id' => 9,
      'descripcion' => 'Menú padre',
      'tipo' => 'select',
      'name' => 'parent',
      'opciones' => 'table_permissions',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2018-10-19 16:13:02',
    ),
    8 => 
    array (
      'id' => 10,
      'descripcion' => 'Orden',
      'tipo' => 'bsText',
      'name' => 'orden',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => NULL,
    ),
    9 => 
    array (
      'id' => 11,
      'descripcion' => 'Icono',
      'tipo' => 'bsText',
      'name' => 'fa_icon',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => NULL,
    ),
    10 => 
    array (
      'id' => 12,
      'descripcion' => 'URL',
      'tipo' => 'bsText',
      'name' => 'url',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => NULL,
    ),
    11 => 
    array (
      'id' => 13,
      'descripcion' => 'Hidden',
      'tipo' => 'hidden',
      'name' => 'enabled',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2018-09-03 13:37:24',
    ),
    12 => 
    array (
      'id' => 14,
      'descripcion' => 'Modelo',
      'tipo' => 'select',
      'name' => 'modelo_id',
      'opciones' => 'table_sys_modelos',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2018-10-19 16:13:27',
    ),
    13 => 
    array (
      'id' => 17,
      'descripcion' => 'Colegio',
      'tipo' => 'select',
      'name' => 'id_colegio',
      'opciones' => 'table_sga_colegios',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => NULL,
    ),
    14 => 
    array (
      'id' => 18,
      'descripcion' => 'Email',
      'tipo' => 'bsText',
      'name' => 'email',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '{"type":"email"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 1,
      'created_at' => NULL,
      'updated_at' => '2020-02-24 10:32:49',
    ),
    15 => 
    array (
      'id' => 19,
      'descripcion' => 'Contraseña',
      'tipo' => 'password',
      'name' => 'password',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2018-09-28 14:16:32',
    ),
    16 => 
    array (
      'id' => 20,
      'descripcion' => 'Confirmar Contraseña',
      'tipo' => 'password',
      'name' => 'password_confirmation',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2018-09-28 14:16:44',
    ),
    17 => 
    array (
      'id' => 21,
      'descripcion' => 'check box roles create usuario',
      'tipo' => 'personalizado',
      'name' => '',
      'opciones' => '',
      'value' => '<div class="form-group">
    <select name="cars">
    <option value="volvo">Volvo</option>
    <option value="saab">Saab</option>
    <option value="fiat">Fiat</option>
    <option value="audi">Audi</option>
  </select>
</div>',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => NULL,
    ),
    18 => 
    array (
      'id' => 22,
      'descripcion' => 'Estado',
      'tipo' => 'select',
      'name' => 'estado',
      'opciones' => '{"Activo":"Activo","Inactivo":"Inactivo"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2018-09-02 10:36:24',
    ),
    19 => 
    array (
      'id' => 24,
      'descripcion' => 'Tipo campo',
      'tipo' => 'select',
      'name' => 'tipo',
      'opciones' => '{ "bsText":"Text","bsNumber":"Number", "select":"select", "monetario":"Monetario", "bsTextArea":"TextArea", "password":"Password", "hidden":"Hidden", "fecha":"Fecha", "date":"date", "hora":"Hora", "bsCheckBox":"CheckBox", "bsRadioBtn":"RadioBtn", "bsLabel":"Label", "personalizado":"Custom", "constante":"Constante", "imagen":"Imagen", "file":"File", "html_ayuda":"Pieza HTML ayuda", "spin":"Spin", "json_simple":"Json Simple", "escala_valoracion":"Escala de Valoración (SGA)", "frame_ajax":"Marco Ajax", "textarea":"textarea" ,"text":"text","input_lista_sugerencias":"Input Lista Sugerencias"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Usado para el modelo Campos.',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2020-05-30 03:58:34',
    ),
    20 => 
    array (
      'id' => 25,
      'descripcion' => 'Opciones',
      'tipo' => 'bsTextArea',
      'name' => 'opciones',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Se llena cuando el campo es tipo Select.
<br/>
Hay dos clase de opciones:
<br/>
1. Cuando el select se llena con una tabla relacionada. Se debe escribir: el prefijo "table_" seguido del nombre exacto de la tabla en la base de datos. Ejemplo, si voy a mostrar un select con los cursos, se debe llenar este campo con "table_cursos".
<br/>
2. Cuando el select es un conjuto de datos fijos, se debe ingresar en formato JSON de la forma {"valor_op1":"Opción 1","valor_op2":"Opción 2"}. Ejemplo, {"Alto":"Nivel alto","Medio":"Nivel medio","Bajo":"Nivel bajo"}',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2019-07-29 13:38:27',
    ),
    21 => 
    array (
      'id' => 26,
      'descripcion' => 'Valor ',
      'tipo' => 'bsTextArea',
      'name' => 'value',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Normalmente se llena con la palabra "null" (sin comillas) para que el modelo se encargue de llenarlo con el valor específico que le corresponde.
Si es un campo personalizado, se llena con el código HTML que será mostrado en el formulario, aunque al momento de llamarlo en el formulario edit, se puede mostrar algo inesperado.(REVISAR',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2018-09-01 09:19:44',
    ),
    22 => 
    array (
      'id' => 27,
      'descripcion' => 'Atributos HTML',
      'tipo' => 'bsText',
      'name' => 'atributos',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Se pueden ingresar los atributos HTML que se les quiera asignar al campo. Ejemplo, id, min, max, disabled, etc.
Se deben ingresar en formato JSON.',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2018-09-03 13:36:41',
    ),
    23 => 
    array (
      'id' => 28,
      'descripcion' => 'Definición',
      'tipo' => 'bsTextArea',
      'name' => 'definicion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Una descripción del campo que detalle sus alcances, usos y limitaciones. Es posible usar un campo en varios modelos.
Se debe detallar bien el comportamiento del campo para evitar ambigüedades.',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2018-09-02 09:57:00',
    ),
    24 => 
    array (
      'id' => 29,
      'descripcion' => 'Requerido',
      'tipo' => 'select',
      'name' => 'requerido',
      'opciones' => '{"1":"Si","0":"No"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Se usa para validar el campo al momento de guardar un nuevo registro o modificarlo en la base de datos.
1= El campo es obligatorio
0= No es obligatorio para almacenar el registro.',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2018-09-02 09:19:47',
    ),
    25 => 
    array (
      'id' => 30,
      'descripcion' => 'Editable',
      'tipo' => 'select',
      'name' => 'editable',
      'opciones' => '{"1":"Si","0":"No"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Determina si el campo se puede modificar cuando se esta editando un registro.
1= No se podrá modificar el campo, aparecerá deshabilitado.
0= El campo puede ser modificado cuando se edita un registro.',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2018-09-02 09:20:14',
    ),
    26 => 
    array (
      'id' => 31,
      'descripcion' => 'Presentación',
      'tipo' => 'select',
      'name' => 'presentacion',
      'opciones' => '{"div":"Párrafo","table":"Tabla"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Creado para el model Secciones',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-01 09:00:59',
      'updated_at' => '2018-10-06 09:36:24',
    ),
    27 => 
    array (
      'id' => 32,
      'descripcion' => 'Modelo',
      'tipo' => 'bsText',
      'name' => 'modelo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Nombre único del modelo.',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 1,
      'created_at' => '2018-09-02 09:16:47',
      'updated_at' => '2019-07-29 12:17:19',
    ),
    28 => 
    array (
      'id' => 33,
      'descripcion' => 'Ubicación modelo',
      'tipo' => 'bsText',
      'name' => 'name_space',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'El nombre del modelo con su ruta de ubucación absoluta.
Ejemplo. 
Spatie\\Permission\\Models\\Permission
App\\Core\\CoreModelo
App\\User',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-02 09:19:26',
      'updated_at' => '2018-09-02 09:19:26',
    ),
    29 => 
    array (
      'id' => 34,
      'descripcion' => 'Modelo relacionado',
      'tipo' => 'bsText',
      'name' => 'modelo_relacionado',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Debe ir vacio para todos los modelos.
Por shora solo se usa para el modelo CoreModelo.
Esto es para traer mostrar una tabla de datos relacionados al momento de hacer clic en Ver (web/show) en la consulta del modelo.
Cuando se está en la configuración de Modelo, si se hace clic en Ver, se mostrará una tabla de los campos que tiene ese modelo y se pueden asignar los campos al modelo.
Solo se usa en esta configuración.',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-02 09:24:10',
      'updated_at' => '2018-09-02 09:24:10',
    ),
    30 => 
    array (
      'id' => 35,
      'descripcion' => 'Home miga de pan',
      'tipo' => 'bsText',
      'name' => 'home_miga_pan',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Etiqueta y URL para la primera opción (home) de la miga de pan.
Se debe ingreas una dupla separada por coma: url,etiqueta.',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-02 09:26:47',
      'updated_at' => '2020-05-02 09:13:19',
    ),
    31 => 
    array (
      'id' => 36,
      'descripcion' => 'URL form_create (store)',
      'tipo' => 'bsText',
      'name' => 'url_form_create',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-02 09:57:36',
      'updated_at' => '2018-09-02 09:57:36',
    ),
    32 => 
    array (
      'id' => 37,
      'descripcion' => 'Primer apellido',
      'tipo' => 'bsText',
      'name' => 'apellido1',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Primer apellido del tercero',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-02 11:34:40',
      'updated_at' => '2018-09-03 10:45:16',
    ),
    33 => 
    array (
      'id' => 38,
      'descripcion' => 'Mostrar en menú',
      'tipo' => 'select',
      'name' => 'enabled',
      'opciones' => '{"1":"Si","0":"No"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-02 11:51:09',
      'updated_at' => '2019-07-22 10:09:04',
    ),
    34 => 
    array (
      'id' => 39,
      'descripcion' => 'Tercero',
      'tipo' => 'select',
      'name' => 'core_tercero_id',
      'opciones' => 'model_App\\Core\\Tercero',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-02 16:25:21',
      'updated_at' => '2019-06-25 21:19:48',
    ),
    35 => 
    array (
      'id' => 40,
      'descripcion' => 'Título tercero',
      'tipo' => 'bsText',
      'name' => 'titulo_tercero',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-02 16:33:47',
      'updated_at' => '2018-09-02 16:33:47',
    ),
    36 => 
    array (
      'id' => 41,
      'descripcion' => 'Tipo tercero',
      'tipo' => 'select',
      'name' => 'tipo',
      'opciones' => '{"":"","Persona natural":"Persona natural","Persona jurídica":"Persona jurídica","Interno":"Interno"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-03 02:58:51',
      'updated_at' => '2018-10-19 15:58:17',
    ),
    37 => 
    array (
      'id' => 42,
      'descripcion' => 'Razón social',
      'tipo' => 'bsText',
      'name' => 'razon_social',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => NULL,
    ),
    38 => 
    array (
      'id' => 43,
      'descripcion' => 'Nombre',
      'tipo' => 'bsText',
      'name' => 'nombre1',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => NULL,
    ),
    39 => 
    array (
      'id' => 44,
      'descripcion' => 'Segundo apellido',
      'tipo' => 'bsText',
      'name' => 'apellido2',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => NULL,
    ),
    40 => 
    array (
      'id' => 45,
      'descripcion' => 'Tipo documento',
      'tipo' => 'select',
      'name' => 'id_tipo_documento_id',
      'opciones' => 'table_core_tipos_docs_id',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2018-09-07 20:41:48',
    ),
    41 => 
    array (
      'id' => 46,
      'descripcion' => 'Número identificación',
      'tipo' => 'bsText',
      'name' => 'numero_identificacion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 1,
      'created_at' => NULL,
      'updated_at' => '2018-09-24 14:20:40',
    ),
    42 => 
    array (
      'id' => 47,
      'descripcion' => 'Dígito de verificación',
      'tipo' => 'bsText',
      'name' => 'digito_verificacion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => NULL,
    ),
    43 => 
    array (
      'id' => 48,
      'descripcion' => 'Ciudad expedición',
      'tipo' => 'bsText',
      'name' => 'ciudad_expedicion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => NULL,
    ),
    44 => 
    array (
      'id' => 49,
      'descripcion' => 'Posición fiscal',
      'tipo' => 'select',
      'name' => 'id_posicion_fiscal',
      'opciones' => 'table_core_posiciones_fiscales',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2018-09-03 10:41:19',
    ),
    45 => 
    array (
      'id' => 50,
      'descripcion' => 'Dirección 1',
      'tipo' => 'bsText',
      'name' => 'direccion1',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => NULL,
    ),
    46 => 
    array (
      'id' => 51,
      'descripcion' => 'Dirección 2',
      'tipo' => 'bsText',
      'name' => 'direccion2',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => NULL,
    ),
    47 => 
    array (
      'id' => 52,
      'descripcion' => 'Barrio',
      'tipo' => 'bsText',
      'name' => 'barrio',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => NULL,
    ),
    48 => 
    array (
      'id' => 53,
      'descripcion' => 'Ciudad',
      'tipo' => 'select',
      'name' => 'codigo_ciudad',
      'opciones' => 'model_App\\Core\\Ciudad',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2020-06-22 09:40:28',
    ),
    49 => 
    array (
      'id' => 54,
      'descripcion' => 'Código postal',
      'tipo' => 'bsText',
      'name' => 'codigo_postal',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => NULL,
    ),
    50 => 
    array (
      'id' => 55,
      'descripcion' => 'Teléfono 1',
      'tipo' => 'bsText',
      'name' => 'telefono1',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => NULL,
    ),
    51 => 
    array (
      'id' => 56,
      'descripcion' => 'Teléfono 2',
      'tipo' => 'bsText',
      'name' => 'telefono2',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => NULL,
    ),
    52 => 
    array (
      'id' => 57,
      'descripcion' => 'Página web',
      'tipo' => 'bsText',
      'name' => 'pagina_web',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => NULL,
    ),
    53 => 
    array (
      'id' => 58,
      'descripcion' => 'Otros nombres',
      'tipo' => 'bsText',
      'name' => 'otros_nombres',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => NULL,
    ),
    54 => 
    array (
      'id' => 59,
      'descripcion' => 'Intensidad horaria',
      'tipo' => 'bsText',
      'name' => 'intensidad_horaria',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-03 17:38:26',
      'updated_at' => '2018-09-03 17:38:26',
    ),
    55 => 
    array (
      'id' => 60,
      'descripcion' => 'Orden boletín',
      'tipo' => 'bsText',
      'name' => 'orden_boletin',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-03 17:38:49',
      'updated_at' => '2018-09-03 17:38:49',
    ),
    56 => 
    array (
      'id' => 61,
      'descripcion' => 'Número',
      'tipo' => 'bsText',
      'name' => 'numero',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-03 18:00:06',
      'updated_at' => '2018-09-03 18:00:06',
    ),
    57 => 
    array (
      'id' => 62,
      'descripcion' => 'Fecha desde',
      'tipo' => 'fecha',
      'name' => 'fecha_desde',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-03 18:03:57',
      'updated_at' => '2019-07-16 21:58:04',
    ),
    58 => 
    array (
      'id' => 63,
      'descripcion' => 'Fecha hasta',
      'tipo' => 'fecha',
      'name' => 'fecha_hasta',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-03 18:04:19',
      'updated_at' => '2019-07-16 21:57:55',
    ),
    59 => 
    array (
      'id' => 64,
      'descripcion' => 'Cerrado',
      'tipo' => 'select',
      'name' => 'cerrado',
      'opciones' => '{"0":"No","1":"Si"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-03 18:04:59',
      'updated_at' => '2018-09-03 18:35:38',
    ),
    60 => 
    array (
      'id' => 65,
      'descripcion' => 'Calificación mínima',
      'tipo' => 'bsText',
      'name' => 'calificacion_minima',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => NULL,
    ),
    61 => 
    array (
      'id' => 66,
      'descripcion' => 'Calificación máxima',
      'tipo' => 'bsText',
      'name' => 'calificacion_maxima',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => NULL,
    ),
    62 => 
    array (
      'id' => 67,
      'descripcion' => 'Nombre escala',
      'tipo' => 'bsText',
      'name' => 'nombre_escala',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2018-09-03 13:36:56',
    ),
    63 => 
    array (
      'id' => 68,
      'descripcion' => 'Sigla',
      'tipo' => 'bsText',
      'name' => 'sigla',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2018-09-03 13:36:41',
    ),
    64 => 
    array (
      'id' => 69,
      'descripcion' => 'Escala nacional',
      'tipo' => 'bsText',
      'name' => 'escala_nacional',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2019-04-08 21:50:15',
    ),
    65 => 
    array (
      'id' => 70,
      'descripcion' => 'Tipo aspecto',
      'tipo' => 'select',
      'name' => 'id_tipo_aspecto',
      'opciones' => 'table_sga_tipos_aspectos',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-04 01:26:14',
      'updated_at' => '2018-09-04 01:26:14',
    ),
    66 => 
    array (
      'id' => 71,
      'descripcion' => 'Estudiante',
      'tipo' => 'select',
      'name' => 'id_estudiante',
      'opciones' => 'model_App\\Matriculas\\Estudiante',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2018-09-05 13:37:24',
      'updated_at' => '2020-01-10 19:59:08',
    ),
    67 => 
    array (
      'id' => 72,
      'descripcion' => 'Periodo',
      'tipo' => 'select',
      'name' => 'id_periodo',
      'opciones' => 'model_App\\Calificaciones\\Periodo',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-05 13:39:40',
      'updated_at' => '2019-02-07 16:07:33',
    ),
    68 => 
    array (
      'id' => 73,
      'descripcion' => 'Fecha novedad',
      'tipo' => 'fecha',
      'name' => 'fecha_novedad',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-05 13:40:25',
      'updated_at' => '2018-09-05 13:40:25',
    ),
    69 => 
    array (
      'id' => 74,
      'descripcion' => 'Tipo novedad',
      'tipo' => 'select',
      'name' => 'tipo_novedad',
      'opciones' => 'model_App\\Matriculas\\TipoNovedad',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-05 13:41:04',
      'updated_at' => '2019-08-07 04:41:30',
    ),
    70 => 
    array (
      'id' => 75,
      'descripcion' => 'Tipo característica',
      'tipo' => 'select',
      'name' => 'tipo_caracteristica',
      'opciones' => '{"Fortaleza":"Fortaleza","Oportunidad":"Oportunidad","Debilidad":"Debilidad","Amenaza":"Amenaza"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-05 14:58:56',
      'updated_at' => '2018-09-05 14:58:56',
    ),
    71 => 
    array (
      'id' => 76,
      'descripcion' => 'Tipo precio',
      'tipo' => 'select',
      'name' => 'tipo_precio',
      'opciones' => '{"Fijo":"Fijo","Por usuario":"Por usuario"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-06 07:51:57',
      'updated_at' => '2018-09-06 10:46:32',
    ),
    72 => 
    array (
      'id' => 77,
      'descripcion' => 'Precio',
      'tipo' => 'bsText',
      'name' => 'precio',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-06 07:52:18',
      'updated_at' => '2018-09-06 07:52:18',
    ),
    73 => 
    array (
      'id' => 78,
      'descripcion' => 'Imagen',
      'tipo' => 'bsText',
      'name' => 'nombre_imagen',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-06 07:53:00',
      'updated_at' => '2018-10-04 19:47:48',
    ),
    74 => 
    array (
      'id' => 79,
      'descripcion' => 'Unidad medida 1',
      'tipo' => 'select',
      'name' => 'unidad_medida1',
      'opciones' => '{"":"","UND":"UND","KG":"KG","GR":"GR","LT":"LT","ML":"ML"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-06 10:08:39',
      'updated_at' => '2019-09-19 14:48:01',
    ),
    75 => 
    array (
      'id' => 80,
      'descripcion' => 'Unidad medida 2',
      'tipo' => 'select',
      'name' => 'unidad_medida2',
      'opciones' => '{"UND":"UND","":"","KG":"KG","GR":"GR","LT":"LT","ML":"ML"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-06 10:08:39',
      'updated_at' => '2019-09-17 20:35:16',
    ),
    76 => 
    array (
      'id' => 81,
      'descripcion' => 'Categoría',
      'tipo' => 'select',
      'name' => 'categoria_id',
      'opciones' => '{"mercancias":"Mercancías","manufacturado":"Manufacturado","activos_fijos":"Activos Fijos","servicios_para_venta":"Servicios para la venta","servicios_para_compra":"Servicios para la compra","gastos":"Costos y gastos""intangibles":"Intangibles"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Se refiere a la clasificación contable del los productos y servicios.',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-06 10:12:03',
      'updated_at' => '2019-09-12 08:45:50',
    ),
    77 => 
    array (
      'id' => 82,
      'descripcion' => 'Tipo Nivel',
      'tipo' => 'select',
      'name' => 'tipo_nivel',
      'opciones' => '{"Título":"Título","Detalle":"Detalle"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-06 10:16:25',
      'updated_at' => '2018-09-06 10:19:54',
    ),
    78 => 
    array (
      'id' => 83,
      'descripcion' => 'Nivel padre',
      'tipo' => 'select',
      'name' => 'nivel_padre',
      'opciones' => 'table_inv_grupos',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-06 10:18:32',
      'updated_at' => '2018-09-15 15:04:12',
    ),
    79 => 
    array (
      'id' => 84,
      'descripcion' => 'Grupo inventario',
      'tipo' => 'select',
      'name' => 'inv_grupo_id',
      'opciones' => 'table_inv_grupos',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-06 10:45:36',
      'updated_at' => '2018-09-15 15:01:53',
    ),
    80 => 
    array (
      'id' => 85,
      'descripcion' => 'Precio estándar de compra',
      'tipo' => 'bsText',
      'name' => 'precio_compra',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-06 10:46:54',
      'updated_at' => '2020-07-02 10:26:25',
    ),
    81 => 
    array (
      'id' => 86,
      'descripcion' => 'Precio estándar de venta',
      'tipo' => 'bsText',
      'name' => 'precio_venta',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-06 10:47:10',
      'updated_at' => '2020-07-02 10:25:55',
    ),
    82 => 
    array (
      'id' => 87,
      'descripcion' => 'Tipo Transacción (oculto)',
      'tipo' => 'hidden',
      'name' => 'core_tipo_transaccion_id',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2018-09-06 14:27:42',
      'updated_at' => '2018-09-08 10:06:33',
    ),
    83 => 
    array (
      'id' => 88,
      'descripcion' => 'Tipo documento',
      'tipo' => 'select',
      'name' => 'core_tipo_doc_app_id',
      'opciones' => 'model_App\\Core\\TipoDocApp',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2018-09-06 14:28:57',
      'updated_at' => '2019-12-01 10:38:37',
    ),
    84 => 
    array (
      'id' => 89,
      'descripcion' => 'Nombre interno App',
      'tipo' => 'bsText',
      'name' => 'app',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Este nombre es el usado para la definición de rutas, directorio de vistas, etc.',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-07 14:32:38',
      'updated_at' => '2018-09-07 14:34:23',
    ),
    85 => 
    array (
      'id' => 90,
      'descripcion' => 'Bodega origen',
      'tipo' => 'select',
      'name' => 'inv_bodega_id',
      'opciones' => 'model_App\\Inventarios\\InvBodega',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2018-09-07 20:07:14',
      'updated_at' => '2019-07-02 06:19:24',
    ),
    86 => 
    array (
      'id' => 91,
      'descripcion' => 'Bodega destino',
      'tipo' => 'select',
      'name' => 'inv_bodega_destino_id',
      'opciones' => 'table_inv_bodegas',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-07 20:08:06',
      'updated_at' => '2018-10-19 16:17:21',
    ),
    87 => 
    array (
      'id' => 92,
      'descripcion' => 'Documento soporte',
      'tipo' => 'bsText',
      'name' => 'documento_soporte',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-07 20:08:46',
      'updated_at' => '2018-09-07 21:36:39',
    ),
    88 => 
    array (
      'id' => 93,
      'descripcion' => 'Fecha',
      'tipo' => 'fecha',
      'name' => 'fecha',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-07 20:11:36',
      'updated_at' => '2018-09-07 20:11:36',
    ),
    89 => 
    array (
      'id' => 94,
      'descripcion' => 'Creado por',
      'tipo' => 'hidden',
      'name' => 'creado_por',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2018-09-07 20:14:28',
      'updated_at' => '2018-11-08 09:55:01',
    ),
    90 => 
    array (
      'id' => 95,
      'descripcion' => 'Modificado por',
      'tipo' => 'hidden',
      'name' => 'modificado_por',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-07 20:14:54',
      'updated_at' => '2018-11-08 09:55:19',
    ),
    91 => 
    array (
      'id' => 96,
      'descripcion' => 'Prefijo',
      'tipo' => 'bsText',
      'name' => 'prefijo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 1,
      'created_at' => '2018-09-07 21:31:52',
      'updated_at' => '2018-10-19 09:48:55',
    ),
    92 => 
    array (
      'id' => 97,
      'descripcion' => 'Separador',
      'tipo' => 'personalizado',
      'name' => 'separador1',
      'opciones' => '',
      'value' => ' &nbsp;',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-08 09:54:01',
      'updated_at' => '2020-06-14 04:35:53',
    ),
    93 => 
    array (
      'id' => 98,
      'descripcion' => 'Estado',
      'tipo' => 'hidden',
      'name' => 'estado',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2018-09-08 10:05:41',
      'updated_at' => '2018-12-04 08:07:35',
    ),
    94 => 
    array (
      'id' => 99,
      'descripcion' => 'Mov. inventarios',
      'tipo' => 'hidden',
      'name' => 'movimiento',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2018-09-10 08:51:43',
      'updated_at' => '2018-09-28 10:30:31',
    ),
    95 => 
    array (
      'id' => 100,
      'descripcion' => 'Consecutivo',
      'tipo' => 'hidden',
      'name' => 'consecutivo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2018-09-10 10:37:22',
      'updated_at' => '2019-09-09 20:11:50',
    ),
    96 => 
    array (
      'id' => 101,
      'descripcion' => 'Hay productos',
      'tipo' => 'hidden',
      'name' => 'hay_productos',
      'opciones' => '',
      'value' => '0',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-11 13:44:08',
      'updated_at' => '2018-09-11 13:44:08',
    ),
    97 => 
    array (
      'id' => 102,
      'descripcion' => 'Nombres',
      'tipo' => 'bsText',
      'name' => 'nombres',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-11 15:22:49',
      'updated_at' => '2018-09-11 15:22:49',
    ),
    98 => 
    array (
      'id' => 103,
      'descripcion' => 'Tipo Doc. ID',
      'tipo' => 'select',
      'name' => 'tipo_doc_id',
      'opciones' => 'table_core_tipos_docs_id',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-11 15:23:59',
      'updated_at' => '2018-09-11 15:39:14',
    ),
    99 => 
    array (
      'id' => 104,
      'descripcion' => 'Núm. Doc. ID',
      'tipo' => 'bsText',
      'name' => 'doc_identidad',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-11 15:24:29',
      'updated_at' => '2018-09-11 15:55:52',
    ),
    100 => 
    array (
      'id' => 105,
      'descripcion' => 'Genero',
      'tipo' => 'select',
      'name' => 'genero',
      'opciones' => '{"":"","Masculino":"Masculino","Femenino":"Femenino"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-11 15:25:45',
      'updated_at' => '2018-11-20 08:19:40',
    ),
    101 => 
    array (
      'id' => 106,
      'descripcion' => 'Fecha nacimiento',
      'tipo' => 'fecha',
      'name' => 'fecha_nacimiento',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-11 15:27:11',
      'updated_at' => '2018-12-30 05:39:22',
    ),
    102 => 
    array (
      'id' => 107,
      'descripcion' => 'Ciudad de nacimiento',
      'tipo' => 'bsText',
      'name' => 'ciudad_nacimiento',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-11 15:27:30',
      'updated_at' => '2018-09-11 15:44:03',
    ),
    103 => 
    array (
      'id' => 108,
      'descripcion' => 'Nombre mamá',
      'tipo' => 'bsText',
      'name' => 'mama',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-11 15:27:47',
      'updated_at' => '2018-09-11 15:29:14',
    ),
    104 => 
    array (
      'id' => 109,
      'descripcion' => 'Ocupación mamá',
      'tipo' => 'bsText',
      'name' => 'ocupacion_mama',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-11 15:28:10',
      'updated_at' => '2018-09-11 15:29:22',
    ),
    105 => 
    array (
      'id' => 110,
      'descripcion' => 'Teléfono mamá',
      'tipo' => 'bsText',
      'name' => 'telefono_mama',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-11 15:28:30',
      'updated_at' => '2018-09-11 15:29:05',
    ),
    106 => 
    array (
      'id' => 111,
      'descripcion' => 'Email mamá',
      'tipo' => 'bsText',
      'name' => 'email_mama',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-11 15:28:51',
      'updated_at' => '2018-09-11 15:28:51',
    ),
    107 => 
    array (
      'id' => 112,
      'descripcion' => 'Nombre papá',
      'tipo' => 'bsText',
      'name' => 'papa',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-11 15:27:47',
      'updated_at' => '2018-09-11 15:29:14',
    ),
    108 => 
    array (
      'id' => 113,
      'descripcion' => 'Ocupación papá',
      'tipo' => 'bsText',
      'name' => 'ocupacion_papa',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-11 15:28:10',
      'updated_at' => '2018-09-11 15:29:22',
    ),
    109 => 
    array (
      'id' => 114,
      'descripcion' => 'Teléfono papá',
      'tipo' => 'bsText',
      'name' => 'telefono_papa',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-11 15:28:30',
      'updated_at' => '2018-09-11 15:29:05',
    ),
    110 => 
    array (
      'id' => 115,
      'descripcion' => 'Email papá',
      'tipo' => 'bsText',
      'name' => 'email_papa',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-11 15:28:51',
      'updated_at' => '2018-09-11 15:28:51',
    ),
    111 => 
    array (
      'id' => 116,
      'descripcion' => 'Único',
      'tipo' => 'select',
      'name' => 'unico',
      'opciones' => '{"0":"No","1":"Si"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-11 15:47:41',
      'updated_at' => '2018-09-11 15:51:04',
    ),
    112 => 
    array (
      'id' => 117,
      'descripcion' => 'ID colegio',
      'tipo' => 'constante',
      'name' => 'id_colegio',
      'opciones' => '',
      'value' => 'id_colegio',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2018-09-18 08:41:06',
      'updated_at' => '2018-09-18 08:41:06',
    ),
    113 => 
    array (
      'id' => 118,
      'descripcion' => 'Nombre de establecimiento',
      'tipo' => 'bsText',
      'name' => 'descripcion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-24 14:11:51',
      'updated_at' => '2018-09-24 14:11:51',
    ),
    114 => 
    array (
      'id' => 119,
      'descripcion' => 'Tipo',
      'tipo' => 'select',
      'name' => 'tipo',
      'opciones' => '{"producto":"Producto","servicio":"Servicio"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Usado en el modelo Producto',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-27 09:12:59',
      'updated_at' => '2018-09-27 09:12:59',
    ),
    115 => 
    array (
      'id' => 120,
      'descripcion' => 'Referencia',
      'tipo' => 'bsText',
      'name' => 'referencia',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-28 08:32:59',
      'updated_at' => '2018-09-28 08:32:59',
    ),
    116 => 
    array (
      'id' => 121,
      'descripcion' => 'Código de barras',
      'tipo' => 'bsText',
      'name' => 'codigo_barras',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 1,
      'created_at' => '2018-09-28 08:33:32',
      'updated_at' => '2020-02-18 08:32:42',
    ),
    117 => 
    array (
      'id' => 122,
      'descripcion' => 'Estado Doc.',
      'tipo' => 'select',
      'name' => 'estado',
      'opciones' => '{"Activo":"Activo","Anulado":"Anulado"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-28 10:06:12',
      'updated_at' => '2018-09-28 10:06:12',
    ),
    118 => 
    array (
      'id' => 123,
      'descripcion' => 'Separador 2',
      'tipo' => 'personalizado',
      'name' => 'separador2',
      'opciones' => '',
      'value' => ' ',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-28 13:58:28',
      'updated_at' => '2019-04-24 02:58:29',
    ),
    119 => 
    array (
      'id' => 124,
      'descripcion' => 'Perfil',
      'tipo' => 'select',
      'name' => 'role',
      'opciones' => 'model_Spatie\\Permission\\Models\\Role',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-09-28 14:02:17',
      'updated_at' => '2020-01-21 15:43:11',
    ),
    120 => 
    array (
      'id' => 125,
      'descripcion' => 'Fecha incio',
      'tipo' => 'fecha',
      'name' => 'fecha_inicio',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-01 15:20:19',
      'updated_at' => '2018-10-01 15:20:19',
    ),
    121 => 
    array (
      'id' => 126,
      'descripcion' => 'Valor matrícula',
      'tipo' => 'bsText',
      'name' => 'valor_matricula',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-01 15:20:57',
      'updated_at' => '2018-10-01 15:20:57',
    ),
    122 => 
    array (
      'id' => 127,
      'descripcion' => 'Valor pensión anual',
      'tipo' => 'bsText',
      'name' => 'valor_pension_anual',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-01 15:21:12',
      'updated_at' => '2018-10-01 15:25:53',
    ),
    123 => 
    array (
      'id' => 128,
      'descripcion' => 'Núm. periodos',
      'tipo' => 'bsNumber',
      'name' => 'numero_periodos',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '{"max":"36"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-01 15:21:50',
      'updated_at' => '2020-01-14 04:33:47',
    ),
    124 => 
    array (
      'id' => 129,
      'descripcion' => 'Valor pensión mensual',
      'tipo' => 'bsText',
      'name' => 'valor_pension_mensual',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-01 15:22:31',
      'updated_at' => '2018-10-01 15:22:31',
    ),
    125 => 
    array (
      'id' => 130,
      'descripcion' => 'Url botón crear',
      'tipo' => 'bsText',
      'name' => 'url_crear',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-01 17:03:51',
      'updated_at' => '2018-11-06 09:29:29',
    ),
    126 => 
    array (
      'id' => 131,
      'descripcion' => 'Url botón edit',
      'tipo' => 'bsText',
      'name' => 'url_edit',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-01 17:04:08',
      'updated_at' => '2018-11-06 09:30:35',
    ),
    127 => 
    array (
      'id' => 132,
      'descripcion' => 'Url botón print',
      'tipo' => 'bsText',
      'name' => 'url_print',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-01 17:04:25',
      'updated_at' => '2018-11-06 09:29:39',
    ),
    128 => 
    array (
      'id' => 133,
      'descripcion' => 'Enlaces (URLs)',
      'tipo' => 'bsTextArea',
      'name' => 'enlaces',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-01 17:04:44',
      'updated_at' => '2019-08-13 09:13:13',
    ),
    129 => 
    array (
      'id' => 134,
      'descripcion' => 'Url botón ver',
      'tipo' => 'bsText',
      'name' => 'url_ver',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-01 17:05:06',
      'updated_at' => '2018-11-06 09:31:14',
    ),
    130 => 
    array (
      'id' => 135,
      'descripcion' => 'Controller complementario',
      'tipo' => 'bsText',
      'name' => 'controller_complementario',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Este campo debe contener la cadena del controller que se ejecutará luego de ejecutar el método GeneralController. El metodo a ejecutar en el controller complementario es el mismo que se ejecuta en el GeneralController. Por ejemplo,
* Despues de crear el encabezado de una transacción se debe continuar con el almacenamiento del movimiento.
* Después de crear una libreta de pagos se deben crear los registro de la cartera de estudiantes.',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-01 17:08:26',
      'updated_at' => '2018-10-01 21:08:02',
    ),
    131 => 
    array (
      'id' => 136,
      'descripcion' => 'Código',
      'tipo' => 'bsText',
      'name' => 'id',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Usar solo para los modelos que NO tienen llave primaria AUTOINCREMENT.',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 1,
      'created_at' => '2018-10-03 23:07:54',
      'updated_at' => '2018-10-04 00:27:38',
    ),
    132 => 
    array (
      'id' => 137,
      'descripcion' => 'Entidades financiera',
      'tipo' => 'select',
      'name' => 'entidad_financiera_id',
      'opciones' => 'table_teso_entidades_financieras',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-03 23:18:08',
      'updated_at' => '2018-10-04 01:56:19',
    ),
    133 => 
    array (
      'id' => 138,
      'descripcion' => 'Tipo de cuenta',
      'tipo' => 'select',
      'name' => 'tipo_cuenta',
      'opciones' => '{"Ahorros":"Ahorros","Corriente":"Corriente"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-04 01:53:24',
      'updated_at' => '2018-10-04 01:53:24',
    ),
    134 => 
    array (
      'id' => 139,
      'descripcion' => 'Número de cuenta',
      'tipo' => 'bsText',
      'name' => 'descripcion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-04 01:54:29',
      'updated_at' => '2018-10-04 01:54:29',
    ),
    135 => 
    array (
      'id' => 140,
      'descripcion' => 'Imagen',
      'tipo' => 'imagen',
      'name' => 'imagen',
      'opciones' => 'jpg,png,gif',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-04 09:02:33',
      'updated_at' => '2019-10-08 22:54:35',
    ),
    136 => 
    array (
      'id' => 141,
      'descripcion' => 'Ruta Storage Files',
      'tipo' => 'bsText',
      'name' => 'ruta_storage_imagen',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Ruta donde se almacenarán los archivos de imágenes y archivos adjuntos (control tipo File) que vaya a manejar el modelo que se esté creando.',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-04 10:31:09',
      'updated_at' => '2019-08-17 19:31:41',
    ),
    137 => 
    array (
      'id' => 142,
      'descripcion' => 'Forma de presentación',
      'tipo' => 'select',
      'name' => 'tipo',
      'opciones' => '{"Carta":"Carta","Formulario":"Formulario","":""}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-04 21:34:39',
      'updated_at' => '2018-10-04 21:34:39',
    ),
    138 => 
    array (
      'id' => 143,
      'descripcion' => 'Nota/Mensaje',
      'tipo' => 'bsTextArea',
      'name' => 'nota_mensaje',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-04 21:35:17',
      'updated_at' => '2018-10-04 21:35:17',
    ),
    139 => 
    array (
      'id' => 144,
      'descripcion' => 'Maneja firma autorizada',
      'tipo' => 'select',
      'name' => 'maneja_firma_autorizada',
      'opciones' => '{"No":"No","Si":"Si"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-04 21:36:04',
      'updated_at' => '2018-10-04 21:36:04',
    ),
    140 => 
    array (
      'id' => 145,
      'descripcion' => 'Maneja curso',
      'tipo' => 'select',
      'name' => 'maneja_curso',
      'opciones' => '{"No":"No","Si":"Si"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-04 21:36:43',
      'updated_at' => '2018-10-04 21:37:37',
    ),
    141 => 
    array (
      'id' => 146,
      'descripcion' => 'Curso predeterminado',
      'tipo' => 'select',
      'name' => 'curso_predeterminado',
      'opciones' => 'table_sga_cursos',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-04 21:37:26',
      'updated_at' => '2018-10-04 21:37:26',
    ),
    142 => 
    array (
      'id' => 147,
      'descripcion' => 'Maneja periodo',
      'tipo' => 'select',
      'name' => 'maneja_periodo',
      'opciones' => '{"No":"No","Si":"Si"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-04 21:38:14',
      'updated_at' => '2018-10-04 21:38:14',
    ),
    143 => 
    array (
      'id' => 148,
      'descripcion' => 'Periodo predeterminado',
      'tipo' => 'select',
      'name' => 'periodo_predeterminado',
      'opciones' => 'table_sga_periodos',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-04 21:39:25',
      'updated_at' => '2018-10-04 21:39:25',
    ),
    144 => 
    array (
      'id' => 149,
      'descripcion' => 'Maneja año',
      'tipo' => 'select',
      'name' => 'maneja_anio',
      'opciones' => '{"No":"No","Si":"Si"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-04 21:40:48',
      'updated_at' => '2018-10-04 21:40:48',
    ),
    145 => 
    array (
      'id' => 150,
      'descripcion' => 'Maneja estudiantes',
      'tipo' => 'select',
      'name' => 'maneja_estudiantes',
      'opciones' => '{"No":"No","Si":"Si"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-04 21:41:23',
      'updated_at' => '2018-10-04 21:41:23',
    ),
    146 => 
    array (
      'id' => 151,
      'descripcion' => 'Alineación',
      'tipo' => 'select',
      'name' => 'alineacion',
      'opciones' => '{"left":"Izquierda","center":"Centrada","right":"Derecha","justify":"Justificada"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-06 09:23:34',
      'updated_at' => '2018-10-06 09:37:54',
    ),
    147 => 
    array (
      'id' => 152,
      'descripcion' => 'Cantidad filas',
      'tipo' => 'bsText',
      'name' => 'cantidad_filas',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-06 09:24:16',
      'updated_at' => '2018-10-06 09:24:16',
    ),
    148 => 
    array (
      'id' => 153,
      'descripcion' => 'Cantidad columnas',
      'tipo' => 'bsText',
      'name' => 'cantidad_columnas',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-06 09:24:40',
      'updated_at' => '2018-10-06 09:24:40',
    ),
    149 => 
    array (
      'id' => 154,
      'descripcion' => 'Cantidad espacios antes',
      'tipo' => 'bsText',
      'name' => 'cantidad_espacios_antes',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-06 09:25:11',
      'updated_at' => '2018-10-06 09:25:11',
    ),
    150 => 
    array (
      'id' => 155,
      'descripcion' => 'Cantidad espacios después',
      'tipo' => 'bsText',
      'name' => 'cantidad_espacios_despues',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-06 09:25:42',
      'updated_at' => '2018-10-06 09:25:42',
    ),
    151 => 
    array (
      'id' => 156,
      'descripcion' => 'Contenido',
      'tipo' => 'bsTextArea',
      'name' => 'contenido',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-06 09:26:15',
      'updated_at' => '2018-10-06 09:35:11',
    ),
    152 => 
    array (
      'id' => 157,
      'descripcion' => 'Estilo letra',
      'tipo' => 'select',
      'name' => 'estilo_letra',
      'opciones' => '{"normal":"Normal","bold":"Negrita"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-06 09:30:57',
      'updated_at' => '2018-10-06 09:30:57',
    ),
    153 => 
    array (
      'id' => 158,
      'descripcion' => 'Ayuda crear seccion',
      'tipo' => 'html_ayuda',
      'name' => 'html_ayuda',
      'opciones' => 'ayuda_core.vistas.ayudas.difo-secciones-create',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-06 09:57:21',
      'updated_at' => '2018-10-06 10:14:08',
    ),
    154 => 
    array (
      'id' => 159,
      'descripcion' => 'Plantilla',
      'tipo' => 'bsText',
      'name' => 'plantilla',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-06 11:47:54',
      'updated_at' => '2018-10-06 11:47:54',
    ),
    155 => 
    array (
      'id' => 160,
      'descripcion' => 'Mostrar en página web',
      'tipo' => 'select',
      'name' => 'mostrar_en_pag_web',
      'opciones' => '{"1":"1","0":"0"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-11 01:54:15',
      'updated_at' => '2018-10-11 01:54:15',
    ),
    156 => 
    array (
      'id' => 161,
      'descripcion' => 'Usuario',
      'tipo' => 'select',
      'name' => 'user_id',
      'opciones' => 'table_users',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 1,
      'created_at' => '2018-10-11 11:09:37',
      'updated_at' => '2018-12-04 13:45:44',
    ),
    157 => 
    array (
      'id' => 162,
      'descripcion' => 'Fecha fin',
      'tipo' => 'fecha',
      'name' => 'fecha_fin',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-12 21:23:45',
      'updated_at' => '2018-10-12 21:23:45',
    ),
    158 => 
    array (
      'id' => 163,
      'descripcion' => 'Hora inicio',
      'tipo' => 'hora',
      'name' => 'hora_inicio',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-12 21:24:54',
      'updated_at' => '2018-10-12 21:25:26',
    ),
    159 => 
    array (
      'id' => 164,
      'descripcion' => 'Hora fin',
      'tipo' => 'hora',
      'name' => 'hora_fin',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-12 21:25:14',
      'updated_at' => '2018-10-12 21:25:14',
    ),
    160 => 
    array (
      'id' => 165,
      'descripcion' => 'Color',
      'tipo' => 'bsText',
      'name' => 'color',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-12 21:25:47',
      'updated_at' => '2018-10-12 21:26:22',
    ),
    161 => 
    array (
      'id' => 166,
      'descripcion' => 'Núm. día semana',
      'tipo' => 'select',
      'name' => 'dow',
      'opciones' => '{"":"","1":"Lunes","2":"Martes","3":"Miércoles","4":"Jueves","5":"Viernes","6":"Sábado","7":"Domingo"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-12 21:26:14',
      'updated_at' => '2018-10-12 21:30:43',
    ),
    162 => 
    array (
      'id' => 167,
      'descripcion' => 'Tipo pregunta',
      'tipo' => 'select',
      'name' => 'tipo',
      'opciones' => '{"Abierta":"Abierta","Seleccion multiple única respuesta":"Seleccion multiple única respuesta","Falso-Verdadero":"Falso-Verdadero"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-13 19:48:16',
      'updated_at' => '2018-10-13 19:48:16',
    ),
    163 => 
    array (
      'id' => 168,
      'descripcion' => 'Respuesta correcta',
      'tipo' => 'hidden',
      'name' => 'respuesta_correcta',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-13 19:48:58',
      'updated_at' => '2019-07-29 12:53:05',
    ),
    164 => 
    array (
      'id' => 169,
      'descripcion' => 'Descripción',
      'tipo' => 'bsTextArea',
      'name' => 'descripcion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-13 19:52:22',
      'updated_at' => '2019-07-29 11:38:53',
    ),
    165 => 
    array (
      'id' => 170,
      'descripcion' => 'Temática',
      'tipo' => 'bsText',
      'name' => 'tematica',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-16 10:36:56',
      'updated_at' => '2018-10-16 10:36:56',
    ),
    166 => 
    array (
      'id' => 171,
      'descripcion' => 'Instrucciones / GUIA',
      'tipo' => 'bsTextArea',
      'name' => 'instrucciones',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-16 10:37:20',
      'updated_at' => '2020-03-17 07:11:31',
    ),
    167 => 
    array (
      'id' => 172,
      'descripcion' => 'Tipo de recurso',
      'tipo' => 'select',
      'name' => 'tipo_recurso',
      'opciones' => '{"":"","Imagen":"Imagen","Video":"Video","Adjunto":"Adjunto"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-16 10:38:53',
      'updated_at' => '2018-10-16 10:38:53',
    ),
    168 => 
    array (
      'id' => 173,
      'descripcion' => 'Url del recurso',
      'tipo' => 'bsText',
      'name' => 'url_recurso',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-16 10:42:55',
      'updated_at' => '2018-10-16 10:42:55',
    ),
    169 => 
    array (
      'id' => 174,
      'descripcion' => 'Archivo adjunto',
      'tipo' => 'file',
      'name' => 'archivo_adjunto',
      'opciones' => 'xlsx,pdf,docx,ppt,pptx,doc,xls',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-16 10:43:19',
      'updated_at' => '2020-03-17 04:27:12',
    ),
    170 => 
    array (
      'id' => 175,
      'descripcion' => 'Cuestionario asociado',
      'tipo' => 'select',
      'name' => 'cuestionario_id',
      'opciones' => 'model_App\\Cuestionarios\\Cuestionario',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2018-10-16 10:44:16',
      'updated_at' => '2020-06-24 00:05:07',
    ),
    171 => 
    array (
      'id' => 176,
      'descripcion' => 'Fecha de entrega',
      'tipo' => 'fecha',
      'name' => 'fecha_entrega',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-16 10:44:50',
      'updated_at' => '2018-10-16 10:44:50',
    ),
    172 => 
    array (
      'id' => 177,
      'descripcion' => 'Periodo',
      'tipo' => 'select',
      'name' => 'periodo_id',
      'opciones' => 'model_App\\Calificaciones\\Periodo',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2018-10-16 10:45:50',
      'updated_at' => '2020-01-08 19:56:44',
    ),
    173 => 
    array (
      'id' => 178,
      'descripcion' => 'Asignatura ',
      'tipo' => 'select',
      'name' => 'asignatura_id',
      'opciones' => 'model_App\\Calificaciones\\Asignatura',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-16 10:46:47',
      'updated_at' => '2019-07-09 03:51:15',
    ),
    174 => 
    array (
      'id' => 179,
      'descripcion' => 'Ruta Storage Archivo Adjunto',
      'tipo' => 'bsText',
      'name' => 'ruta_storage_archivo_adjunto',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-16 12:12:59',
      'updated_at' => '2018-10-16 12:12:59',
    ),
    175 => 
    array (
      'id' => 180,
      'descripcion' => 'Spin',
      'tipo' => 'spin',
      'name' => 'Spin',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Div que muestra el icono de la rueda cuadno está cargan algún recurso.',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-17 08:31:19',
      'updated_at' => '2018-10-17 08:31:19',
    ),
    176 => 
    array (
      'id' => 181,
      'descripcion' => 'Nivel Académico',
      'tipo' => 'select',
      'name' => 'nivel_grado',
      'opciones' => 'table_sga_niveles',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-17 09:50:25',
      'updated_at' => '2018-10-17 09:50:25',
    ),
    177 => 
    array (
      'id' => 182,
      'descripcion' => 'Curso',
      'tipo' => 'select',
      'name' => 'curso_id',
      'opciones' => 'model_App\\Matriculas\\Curso',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-17 10:49:10',
      'updated_at' => '2019-07-09 03:49:01',
    ),
    178 => 
    array (
      'id' => 183,
      'descripcion' => 'Tipo propiedad',
      'tipo' => 'select',
      'name' => 'tipo_propiedad',
      'opciones' => '{"Casa":"Casa","Apartamento":"Apartamento","Local":"Local"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-18 10:48:04',
      'updated_at' => '2018-10-18 10:48:04',
    ),
    179 => 
    array (
      'id' => 184,
      'descripcion' => 'Nomenclatura',
      'tipo' => 'bsText',
      'name' => 'nomenclatura',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-18 10:48:04',
      'updated_at' => '2018-10-18 21:11:33',
    ),
    180 => 
    array (
      'id' => 185,
      'descripcion' => 'Nombre residente',
      'tipo' => 'bsText',
      'name' => 'nombre_arrendatario',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-18 13:51:51',
      'updated_at' => '2018-11-01 09:15:51',
    ),
    181 => 
    array (
      'id' => 186,
      'descripcion' => 'Cédula residente',
      'tipo' => 'bsText',
      'name' => 'cedula_arrendatario',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-18 08:29:26',
      'updated_at' => '2018-11-01 09:16:01',
    ),
    182 => 
    array (
      'id' => 187,
      'descripcion' => 'Teléfono residente',
      'tipo' => 'bsText',
      'name' => 'telefono_arrendatario',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-18 08:29:26',
      'updated_at' => '2018-11-01 09:16:15',
    ),
    183 => 
    array (
      'id' => 188,
      'descripcion' => 'Email residente',
      'tipo' => 'bsText',
      'name' => 'email_arrendatario',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-18 08:29:26',
      'updated_at' => '2018-11-01 09:16:26',
    ),
    184 => 
    array (
      'id' => 189,
      'descripcion' => 'Coeficiente copropiedad',
      'tipo' => 'bsText',
      'name' => 'coeficiente_copropiedad',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-18 08:29:26',
      'updated_at' => '2018-10-18 08:29:26',
    ),
    185 => 
    array (
      'id' => 190,
      'descripcion' => 'Vlr. cuota admón.',
      'tipo' => 'bsText',
      'name' => 'vlr_cuota_admon',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-18 08:29:26',
      'updated_at' => '2018-10-18 08:29:26',
    ),
    186 => 
    array (
      'id' => 191,
      'descripcion' => 'Condición de pago',
      'tipo' => 'select',
      'name' => 'Cond. Pago',
      'opciones' => '{"1":"Crédito 1 día","5":"Crédito 5 días","15":"Crédito 15 día"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-18 13:36:41',
      'updated_at' => '2018-10-18 13:36:41',
    ),
    187 => 
    array (
      'id' => 192,
      'descripcion' => 'Empresa',
      'tipo' => 'select',
      'name' => 'core_empresa_id',
      'opciones' => 'table_core_empresas',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-18 13:44:34',
      'updated_at' => '2018-10-25 00:38:20',
    ),
    188 => 
    array (
      'id' => 193,
      'descripcion' => 'Tipo transacción',
      'tipo' => 'select',
      'name' => 'core_tipo_transaccion_id',
      'opciones' => 'table_sys_tipos_transacciones',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-18 19:29:30',
      'updated_at' => '2018-10-18 19:29:30',
    ),
    189 => 
    array (
      'id' => 194,
      'descripcion' => 'Fecha vencimiento',
      'tipo' => 'fecha',
      'name' => 'fecha_vencimiento',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-18 22:21:01',
      'updated_at' => '2018-10-18 22:21:01',
    ),
    190 => 
    array (
      'id' => 195,
      'descripcion' => 'ID empresa',
      'tipo' => 'constante',
      'name' => 'empresa_id',
      'opciones' => '',
      'value' => 'empresa_id',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2018-09-18 08:41:06',
      'updated_at' => '2018-09-18 08:41:06',
    ),
    191 => 
    array (
      'id' => 196,
      'descripcion' => 'Por defecto',
      'tipo' => 'select',
      'name' => 'por_defecto',
      'opciones' => '{"":"","Si":"Si"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-22 09:45:31',
      'updated_at' => '2018-10-24 15:25:17',
    ),
    192 => 
    array (
      'id' => 197,
      'descripcion' => 'Comportamiento',
      'tipo' => 'select',
      'name' => 'comportamiento',
      'opciones' => '{"Efectivo":"Efectivo","Tarjeta bancaria":"Tarjeta bancaria","Cheque":"Cheque","Otro":"Otro"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-24 15:02:09',
      'updated_at' => '2018-10-24 15:02:09',
    ),
    193 => 
    array (
      'id' => 198,
      'descripcion' => 'Maneja puntos',
      'tipo' => 'select',
      'name' => 'maneja_puntos',
      'opciones' => '{"No":"No","Si":"Si"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-24 15:03:07',
      'updated_at' => '2018-10-24 15:03:07',
    ),
    194 => 
    array (
      'id' => 199,
      'descripcion' => 'Controla usuarios',
      'tipo' => 'select',
      'name' => 'controla_usuarios',
      'opciones' => '{"No":"No","Si":"Si"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-24 15:46:57',
      'updated_at' => '2018-10-24 15:46:57',
    ),
    195 => 
    array (
      'id' => 200,
      'descripcion' => 'Tipo recaudo',
      'tipo' => 'select',
      'name' => 'teso_tipo_motivo',
      'opciones' => '{"":"","Anticipo":"Anticipo","Otros recaudos":"Otros recaudos"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-24 16:31:13',
      'updated_at' => '2019-11-21 23:28:56',
    ),
    196 => 
    array (
      'id' => 201,
      'descripcion' => 'Empresa',
      'tipo' => 'bsLabel',
      'name' => 'core_empresa_id',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-25 11:00:53',
      'updated_at' => '2018-10-25 11:00:53',
    ),
    197 => 
    array (
      'id' => 202,
      'descripcion' => 'Tabla valores recaudo',
      'tipo' => 'hidden',
      'name' => 'tabla_valores_recaudo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Se usa para asignar los valores de la tablas de ingreso de medios de recaudo.',
      'requerido' => 0,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2018-10-30 09:21:12',
      'updated_at' => '2018-10-30 10:23:37',
    ),
    198 => 
    array (
      'id' => 203,
      'descripcion' => 'Tabla documentos a cancelar',
      'tipo' => 'hidden',
      'name' => 'tabla_documentos_a_cancelar',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Guarda la tabla con los registros de los documentos de CxC que se van a cancelar en el recaudo.',
      'requerido' => 0,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2018-10-30 09:24:27',
      'updated_at' => '2018-10-30 10:23:27',
    ),
    199 => 
    array (
      'id' => 204,
      'descripcion' => 'Detalle',
      'tipo' => 'bsTextArea',
      'name' => 'descripcion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-10-30 18:36:54',
      'updated_at' => '2018-10-30 18:38:35',
    ),
    200 => 
    array (
      'id' => 205,
      'descripcion' => 'Propietario',
      'tipo' => 'select',
      'name' => 'core_tercero_id',
      'opciones' => 'model_App\\Core\\Tercero',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-01 09:14:46',
      'updated_at' => '2019-06-25 21:21:46',
    ),
    201 => 
    array (
      'id' => 206,
      'descripcion' => 'Inmueble',
      'tipo' => 'bsLabel',
      'name' => 'core_empresa_id',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-01 09:25:22',
      'updated_at' => '2018-11-01 09:27:02',
    ),
    202 => 
    array (
      'id' => 207,
      'descripcion' => 'Naturaleza',
      'tipo' => 'select',
      'name' => 'naturaleza',
      'opciones' => '{"":"","debito":"Débito","credito":"Crédito"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-04 17:47:18',
      'updated_at' => '2018-11-04 17:47:18',
    ),
    203 => 
    array (
      'id' => 208,
      'descripcion' => 'Grupo padre',
      'tipo' => 'select',
      'name' => 'grupo_padre_id',
      'opciones' => '{"":""}',
      'value' => 'null',
      'atributos' => '{"class":"select_dependientes_hijo"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-04 17:51:52',
      'updated_at' => '2019-08-14 22:57:53',
    ),
    204 => 
    array (
      'id' => 209,
      'descripcion' => 'Mostrar en reportes',
      'tipo' => 'select',
      'name' => 'mostrar_en_reporte',
      'opciones' => '{"Si":"Si","No":"No"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-04 17:52:43',
      'updated_at' => '2018-11-04 17:52:43',
    ),
    205 => 
    array (
      'id' => 210,
      'descripcion' => 'Clase',
      'tipo' => 'select',
      'name' => 'contab_cuenta_clase_id',
      'opciones' => 'model_App\\Contabilidad\\ClaseCuenta',
      'value' => 'null',
      'atributos' => '{"class":"select_dependientes_padre"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-04 17:55:08',
      'updated_at' => '2020-04-06 10:41:12',
    ),
    206 => 
    array (
      'id' => 211,
      'descripcion' => 'Cuenta contable',
      'tipo' => 'select',
      'name' => 'contab_cuenta_id',
      'opciones' => 'model_App\\Contabilidad\\ContabCuenta',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-04 17:58:49',
      'updated_at' => '2019-12-20 06:16:34',
    ),
    207 => 
    array (
      'id' => 212,
      'descripcion' => 'Débito',
      'tipo' => 'monetario',
      'name' => 'valor_debito',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-04 18:01:43',
      'updated_at' => '2018-11-04 18:01:43',
    ),
    208 => 
    array (
      'id' => 213,
      'descripcion' => 'Crédito',
      'tipo' => 'monetario',
      'name' => 'valor_credito',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-04 18:02:14',
      'updated_at' => '2018-11-04 18:02:14',
    ),
    209 => 
    array (
      'id' => 214,
      'descripcion' => 'Detalle',
      'tipo' => 'bsText',
      'name' => 'detalle_operacion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-04 18:03:13',
      'updated_at' => '2018-11-04 18:03:13',
    ),
    210 => 
    array (
      'id' => 215,
      'descripcion' => 'Producto',
      'tipo' => 'select',
      'name' => 'inv_producto_id',
      'opciones' => 'model_App\\Inventarios\\InvProducto',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-04 18:05:00',
      'updated_at' => '2019-11-01 02:20:10',
    ),
    211 => 
    array (
      'id' => 216,
      'descripcion' => 'Cantidad',
      'tipo' => 'bsText',
      'name' => 'cantidad',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-04 18:05:29',
      'updated_at' => '2018-11-04 18:05:29',
    ),
    212 => 
    array (
      'id' => 217,
      'descripcion' => 'Impuestos',
      'tipo' => 'bsTextArea',
      'name' => 'impuestos_ids',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-04 22:15:17',
      'updated_at' => '2018-11-04 22:15:17',
    ),
    213 => 
    array (
      'id' => 218,
      'descripcion' => 'Valor base impuestos',
      'tipo' => 'monetario',
      'name' => 'valor_base_impuestos',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-04 22:15:44',
      'updated_at' => '2018-11-04 22:15:44',
    ),
    214 => 
    array (
      'id' => 219,
      'descripcion' => 'Caja',
      'tipo' => 'select',
      'name' => 'teso_caja_id',
      'opciones' => 'table_teso_cajas',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-04 22:16:15',
      'updated_at' => '2018-11-04 22:16:15',
    ),
    215 => 
    array (
      'id' => 220,
      'descripcion' => 'Cuenta bancaria',
      'tipo' => 'select',
      'name' => 'teso_cuenta_bancaria_id',
      'opciones' => 'table_teso_cuentas_bancarias',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-04 22:17:09',
      'updated_at' => '2018-11-04 22:17:09',
    ),
    216 => 
    array (
      'id' => 221,
      'descripcion' => 'URL botón Inactivar/Activar',
      'tipo' => 'bsText',
      'name' => 'url_estado',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-06 09:32:23',
      'updated_at' => '2018-11-06 09:32:23',
    ),
    217 => 
    array (
      'id' => 222,
      'descripcion' => 'Valor total',
      'tipo' => 'hidden',
      'name' => 'valor_total',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2018-11-06 10:32:17',
      'updated_at' => '2019-09-26 09:35:40',
    ),
    218 => 
    array (
      'id' => 223,
      'descripcion' => 'Ámbito',
      'tipo' => 'select',
      'name' => 'ambito',
      'opciones' => '{"Gestión Educativa":"Gestión Educativa","Gestión Empresarial":"Gestión Empresarial","Gestión Inmobiliaria":"Gestión Inmobiliaria","Core":"Core","Sector Salud":"Sector Salud"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-08 09:12:15',
      'updated_at' => '2019-06-04 20:47:01',
    ),
    219 => 
    array (
      'id' => 224,
      'descripcion' => 'Grupo',
      'tipo' => 'select',
      'name' => 'contab_cuenta_grupo_id',
      'opciones' => '{"":""}',
      'value' => 'null',
      'atributos' => '{"class":"select_dependientes_hijo"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-08 09:38:43',
      'updated_at' => '2018-11-08 19:52:42',
    ),
    220 => 
    array (
      'id' => 225,
      'descripcion' => 'tabla_registros_documento',
      'tipo' => 'hidden',
      'name' => 'tabla_registros_documento',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Se usa para asignar los valores de la tabla ingreso_registros cuando se elabora un documento .',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-12 09:56:33',
      'updated_at' => '2020-02-13 13:09:00',
    ),
    221 => 
    array (
      'id' => 226,
      'descripcion' => 'Cta. Anticipo Default',
      'tipo' => 'select',
      'name' => 'contab_anticipo_cta_id',
      'opciones' => 'model_App\\Contabilidad\\ContabCuenta',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-17 12:06:25',
      'updated_at' => '2019-02-04 02:26:41',
    ),
    222 => 
    array (
      'id' => 227,
      'descripcion' => 'Cta. X Cobrar Default',
      'tipo' => 'select',
      'name' => 'contab_cartera_cta_id',
      'opciones' => 'model_App\\Contabilidad\\ContabCuenta',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-17 12:07:33',
      'updated_at' => '2019-02-04 02:26:55',
    ),
    223 => 
    array (
      'id' => 228,
      'descripcion' => 'Cta. X Pagar Default',
      'tipo' => 'select',
      'name' => 'contab_cxp_cta_id',
      'opciones' => 'model_App\\Contabilidad\\ContabCuenta',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-17 12:08:21',
      'updated_at' => '2019-02-04 02:27:11',
    ),
    224 => 
    array (
      'id' => 229,
      'descripcion' => 'Movimiento tesorería',
      'tipo' => 'select',
      'name' => 'movimiento',
      'opciones' => '{"entrada":"Entrada","salida":"Salida"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-17 22:55:36',
      'updated_at' => '2018-11-17 22:55:36',
    ),
    225 => 
    array (
      'id' => 230,
      'descripcion' => 'Tipo transacción',
      'tipo' => 'select',
      'name' => 'teso_tipo_motivo',
      'opciones' => '{"":"","Recaudo cartera":"Recaudo cartera","Anticipo":"Anticipo","Otros recaudos":"Otros recaudos","Otros pagos":"Otros pagos","Anticipo proveedor":"Anticipo proveedor","Traslado":"Traslado","Pago anticipado":"Pago anticipado (Cartera CxC)","Prestamo financiero":"Prestamo financiero (CxP)"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-17 22:59:02',
      'updated_at' => '2020-05-11 05:46:33',
    ),
    226 => 
    array (
      'id' => 231,
      'descripcion' => 'Cuenta contrapartida',
      'tipo' => 'select',
      'name' => 'contab_cuenta_id',
      'opciones' => 'model_App\\Contabilidad\\ContabCuenta',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-17 23:07:59',
      'updated_at' => '2019-02-04 02:27:34',
    ),
    227 => 
    array (
      'id' => 232,
      'descripcion' => 'Cuenta ingresos',
      'tipo' => 'select',
      'name' => 'contab_cuenta_id',
      'opciones' => 'model_App\\Contabilidad\\ContabCuenta',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-18 01:30:12',
      'updated_at' => '2019-02-04 02:27:48',
    ),
    228 => 
    array (
      'id' => 233,
      'descripcion' => 'Cpto. Facturar Default',
      'tipo' => 'select',
      'name' => 'cxc_servicio_id',
      'opciones' => 'model_App\\CxC\\CxcServicio',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-19 08:39:10',
      'updated_at' => '2019-06-25 21:11:37',
    ),
    229 => 
    array (
      'id' => 234,
      'descripcion' => 'Slogan',
      'tipo' => 'bsText',
      'name' => 'slogan',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-20 07:28:30',
      'updated_at' => '2018-11-20 07:28:30',
    ),
    230 => 
    array (
      'id' => 235,
      'descripcion' => 'Resolución',
      'tipo' => 'bsText',
      'name' => 'resolucion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-20 07:28:45',
      'updated_at' => '2018-11-20 07:28:45',
    ),
    231 => 
    array (
      'id' => 236,
      'descripcion' => 'Dirección',
      'tipo' => 'bsText',
      'name' => 'direccion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-20 07:29:11',
      'updated_at' => '2018-11-20 07:29:11',
    ),
    232 => 
    array (
      'id' => 237,
      'descripcion' => 'Teléfonos',
      'tipo' => 'bsText',
      'name' => 'telefonos',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-20 07:29:24',
      'updated_at' => '2018-11-20 07:29:24',
    ),
    233 => 
    array (
      'id' => 238,
      'descripcion' => 'Ciudad',
      'tipo' => 'bsText',
      'name' => 'ciudad',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-20 07:29:57',
      'updated_at' => '2018-11-20 07:29:57',
    ),
    234 => 
    array (
      'id' => 239,
      'descripcion' => 'Pie de firma #1',
      'tipo' => 'bsText',
      'name' => 'piefirma1',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-20 07:30:24',
      'updated_at' => '2018-11-20 07:30:24',
    ),
    235 => 
    array (
      'id' => 240,
      'descripcion' => 'Pie de firma #2',
      'tipo' => 'bsText',
      'name' => 'piefirma2',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-20 07:30:37',
      'updated_at' => '2018-11-20 07:30:37',
    ),
    236 => 
    array (
      'id' => 241,
      'descripcion' => 'Maneja puesto',
      'tipo' => 'select',
      'name' => 'maneja_puesto',
      'opciones' => '{"":"","Si":"Si","No":"No"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-20 07:31:39',
      'updated_at' => '2018-11-20 07:31:39',
    ),
    237 => 
    array (
      'id' => 242,
      'descripcion' => 'Colegio',
      'tipo' => 'select',
      'name' => 'colegio_id',
      'opciones' => 'table_sga_colegios',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-20 08:02:33',
      'updated_at' => '2018-11-20 08:02:33',
    ),
    238 => 
    array (
      'id' => 243,
      'descripcion' => 'Empresa',
      'tipo' => 'select',
      'name' => 'empresa_id',
      'opciones' => 'table_core_empresas',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-20 08:13:25',
      'updated_at' => '2018-11-20 08:13:25',
    ),
    239 => 
    array (
      'id' => 244,
      'descripcion' => 'Primer apellido',
      'tipo' => 'bsText',
      'name' => 'apellido1',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-20 08:17:49',
      'updated_at' => '2018-11-20 08:17:49',
    ),
    240 => 
    array (
      'id' => 245,
      'descripcion' => 'Segundo apellido',
      'tipo' => 'bsText',
      'name' => 'apellido2',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-20 08:18:02',
      'updated_at' => '2018-11-20 08:18:02',
    ),
    241 => 
    array (
      'id' => 246,
      'descripcion' => 'Valor a cruzar',
      'tipo' => 'monetario',
      'name' => 'valor_cruce',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-24 12:10:34',
      'updated_at' => '2018-11-24 12:10:34',
    ),
    242 => 
    array (
      'id' => 247,
      'descripcion' => 'Tipo transacción',
      'tipo' => 'select',
      'name' => 'teso_tipo_motivo',
      'opciones' => '{"Otros pagos":"Otros pagos","Anticipo proveedor":"Anticipo proveedor","Pago anticipado":"Pago anticipado (Cartera CxC)","Prestamo financiero":"Prestamo financiero (CxP)"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-29 03:11:14',
      'updated_at' => '2020-05-11 05:42:34',
    ),
    243 => 
    array (
      'id' => 248,
      'descripcion' => 'Medio de pago',
      'tipo' => 'select',
      'name' => 'teso_medio_recaudo_id',
      'opciones' => 'table_teso_medios_recaudo',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-29 11:59:50',
      'updated_at' => '2018-11-29 12:52:05',
    ),
    244 => 
    array (
      'id' => 249,
      'descripcion' => 'Caja',
      'tipo' => 'select',
      'name' => 'teso_caja_id',
      'opciones' => '{"":""}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-29 12:02:53',
      'updated_at' => '2020-06-16 11:51:23',
    ),
    245 => 
    array (
      'id' => 250,
      'descripcion' => 'Cuenta bancaria',
      'tipo' => 'select',
      'name' => 'teso_cuenta_bancaria_id',
      'opciones' => '{"":""}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-29 12:03:13',
      'updated_at' => '2020-06-16 11:51:35',
    ),
    246 => 
    array (
      'id' => 251,
      'descripcion' => 'Valor total pago',
      'tipo' => 'bsText',
      'name' => 'valor_total',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-11-29 20:26:34',
      'updated_at' => '2020-06-16 14:08:56',
    ),
    247 => 
    array (
      'id' => 252,
      'descripcion' => 'Nivel visualización',
      'tipo' => 'select',
      'name' => 'nivel_visualizacion',
      'opciones' => '{"1":"Normal","2":"Destacado","3":"Emergente"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-03 01:38:50',
      'updated_at' => '2018-12-03 01:38:50',
    ),
    248 => 
    array (
      'id' => 253,
      'descripcion' => 'Enlace web',
      'tipo' => 'bsText',
      'name' => 'enlace_web',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-03 01:39:13',
      'updated_at' => '2018-12-03 01:39:53',
    ),
    249 => 
    array (
      'id' => 254,
      'descripcion' => 'Enlace Facebook',
      'tipo' => 'bsText',
      'name' => 'enlace_facebook',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-03 01:39:33',
      'updated_at' => '2018-12-03 01:39:43',
    ),
    250 => 
    array (
      'id' => 255,
      'descripcion' => 'Enlace Instagram',
      'tipo' => 'bsText',
      'name' => 'enlace_instagram',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-03 01:40:37',
      'updated_at' => '2018-12-03 01:40:37',
    ),
    251 => 
    array (
      'id' => 256,
      'descripcion' => 'Título',
      'tipo' => 'bsText',
      'name' => 'descripcion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-03 01:41:50',
      'updated_at' => '2018-12-03 01:41:50',
    ),
    252 => 
    array (
      'id' => 257,
      'descripcion' => 'Detalle',
      'tipo' => 'bsTextArea',
      'name' => 'detalle',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-03 01:43:00',
      'updated_at' => '2018-12-03 09:58:21',
    ),
    253 => 
    array (
      'id' => 258,
      'descripcion' => 'Asunto',
      'tipo' => 'bsText',
      'name' => 'asunto',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-03 23:58:49',
      'updated_at' => '2018-12-03 23:58:49',
    ),
    254 => 
    array (
      'id' => 259,
      'descripcion' => 'Enviar a',
      'tipo' => 'select',
      'name' => 'user_asignado_id',
      'opciones' => 'table_users',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-03 23:59:20',
      'updated_at' => '2018-12-03 23:59:20',
    ),
    255 => 
    array (
      'id' => 260,
      'descripcion' => 'Grado satisfacción',
      'tipo' => 'select',
      'name' => 'grado_satisfaccion',
      'opciones' => '{"":"","Muy satisfecho":"Muy satisfecho","Satisfecho":"Satisfecho","Normal":"Normal","Insatisfecho":"Insatisfecho","Muy insatisfecho":"Muy insatisfecho"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-04 00:03:25',
      'updated_at' => '2018-12-04 07:31:41',
    ),
    256 => 
    array (
      'id' => 261,
      'descripcion' => 'Prioridad',
      'tipo' => 'select',
      'name' => 'prioridad',
      'opciones' => '{"Normal":"Normal","Baja":"Baja","Alta":"Alta"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-04 00:04:21',
      'updated_at' => '2018-12-04 00:04:21',
    ),
    257 => 
    array (
      'id' => 262,
      'descripcion' => 'Modelo encabezados documentos',
      'tipo' => 'bsText',
      'name' => 'modelo_encabezados_documentos',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Modelo donde se almacenarán los encabezados de los documentos que se genera en la transacción.',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-05 09:47:44',
      'updated_at' => '2018-12-05 09:47:44',
    ),
    258 => 
    array (
      'id' => 263,
      'descripcion' => 'Modelo movimientos',
      'tipo' => 'bsText',
      'name' => 'modelo_movimientos',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Modelo donde se almacenarán los movimientos que se genera en la transacción.',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-05 09:49:16',
      'updated_at' => '2018-12-05 09:49:16',
    ),
    259 => 
    array (
      'id' => 264,
      'descripcion' => 'Modelo registros documentos',
      'tipo' => 'bsText',
      'name' => 'modelo_registros_documentos',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Modelo donde se almacena cada línea o registro del documento que se está generando.',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-05 10:02:46',
      'updated_at' => '2018-12-05 10:02:46',
    ),
    260 => 
    array (
      'id' => 265,
      'descripcion' => 'Tipo movimiento',
      'tipo' => 'select',
      'name' => 'tipo_movimiento',
      'opciones' => '{"":"","Cancelación documentos":"Cancelación documentos","Anticipo":"Anticipo"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-07 08:43:40',
      'updated_at' => '2018-12-08 16:22:41',
    ),
    261 => 
    array (
      'id' => 266,
      'descripcion' => 'Valor total',
      'tipo' => 'bsText',
      'name' => 'valor_total',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-07 08:49:21',
      'updated_at' => '2018-12-07 17:06:28',
    ),
    262 => 
    array (
      'id' => 267,
      'descripcion' => 'ID en directorio archivos',
      'tipo' => 'bsText',
      'name' => 'directorio_archivos',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-14 09:59:26',
      'updated_at' => '2018-12-14 10:04:17',
    ),
    263 => 
    array (
      'id' => 268,
      'descripcion' => 'Factor expresión valores',
      'tipo' => 'select',
      'name' => 'factor_expresion_valores',
      'opciones' => '{"1":"Real","1000":"Miles","1000000":"Millones"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-18 14:33:30',
      'updated_at' => '2018-12-18 14:33:30',
    ),
    264 => 
    array (
      'id' => 269,
      'descripcion' => 'Periodo contable',
      'tipo' => 'select',
      'name' => 'periodo_contable_id',
      'opciones' => 'table_contab_periodos_ejercicio',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-18 14:34:20',
      'updated_at' => '2018-12-18 14:39:05',
    ),
    265 => 
    array (
      'id' => 270,
      'descripcion' => 'Periodo ejercicio contable',
      'tipo' => 'select',
      'name' => 'periodo_ejercicio_id',
      'opciones' => 'table_contab_periodos_ejercicio',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-18 15:11:46',
      'updated_at' => '2018-12-18 15:11:46',
    ),
    266 => 
    array (
      'id' => 271,
      'descripcion' => 'Tipo elemento',
      'tipo' => 'select',
      'name' => 'tipo',
      'opciones' => '{"":"","Grupo de cuentas":"Grupo de cuentas","Cuenta contable":"Cuenta contable"}',
      'value' => 'null',
      'atributos' => '{"class":"select_dependientes_padre"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-18 15:17:20',
      'updated_at' => '2018-12-18 17:02:48',
    ),
    267 => 
    array (
      'id' => 272,
      'descripcion' => 'Cuenta / Grupo Cta.',
      'tipo' => 'select',
      'name' => 'grupo_o_cuenta_id',
      'opciones' => '{"":""}',
      'value' => 'null',
      'atributos' => '{"class":"select_dependientes_hijo"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-18 15:18:44',
      'updated_at' => '2018-12-18 17:03:30',
    ),
    268 => 
    array (
      'id' => 273,
      'descripcion' => 'Nota EEFF',
      'tipo' => 'select',
      'name' => 'nota_eeff_id',
      'opciones' => 'table_contab_notas_eeff',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-18 15:20:20',
      'updated_at' => '2018-12-18 15:43:03',
    ),
    269 => 
    array (
      'id' => 274,
      'descripcion' => 'Primer nombre',
      'tipo' => 'bsText',
      'name' => 'nombre1',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-22 13:00:50',
      'updated_at' => '2018-12-22 13:00:50',
    ),
    270 => 
    array (
      'id' => 275,
      'descripcion' => 'Segundo nombre',
      'tipo' => 'bsText',
      'name' => 'otros_nombres',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-22 13:01:15',
      'updated_at' => '2018-12-22 13:01:15',
    ),
    271 => 
    array (
      'id' => 276,
      'descripcion' => 'Primer apellido',
      'tipo' => 'bsText',
      'name' => 'apellido1',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-22 13:01:37',
      'updated_at' => '2018-12-22 13:01:37',
    ),
    272 => 
    array (
      'id' => 277,
      'descripcion' => 'Segundo apellido',
      'tipo' => 'bsText',
      'name' => 'apellido2',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-22 13:01:53',
      'updated_at' => '2018-12-22 13:01:53',
    ),
    273 => 
    array (
      'id' => 278,
      'descripcion' => 'Grado',
      'tipo' => 'bsText',
      'name' => 'grado',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-22 13:07:19',
      'updated_at' => '2018-12-22 13:07:19',
    ),
    274 => 
    array (
      'id' => 279,
      'descripcion' => 'Teléfonos',
      'tipo' => 'bsText',
      'name' => 'telefono1',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-22 13:10:15',
      'updated_at' => '2018-12-22 13:10:15',
    ),
    275 => 
    array (
      'id' => 280,
      'descripcion' => 'Observación',
      'tipo' => 'bsTextArea',
      'name' => 'observacion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-22 13:11:30',
      'updated_at' => '2018-12-22 13:35:58',
    ),
    276 => 
    array (
      'id' => 281,
      'descripcion' => 'Módulo',
      'tipo' => 'select',
      'name' => 'modulo',
      'opciones' => '{"":"","inscripciones":"Inscripciones","matriculas":"Matrículas","logros":"Logros"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2018-12-23 09:01:09',
      'updated_at' => '2018-12-23 09:01:09',
    ),
    277 => 
    array (
      'id' => 282,
      'descripcion' => 'Año (AA)',
      'tipo' => 'bsText',
      'name' => 'anio',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '{"maxlength":"2"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-23 09:01:57',
      'updated_at' => '2018-12-30 16:03:27',
    ),
    278 => 
    array (
      'id' => 283,
      'descripcion' => 'Consecutivo actual',
      'tipo' => 'bsText',
      'name' => 'consecutivo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-23 09:02:17',
      'updated_at' => '2020-01-19 06:43:06',
    ),
    279 => 
    array (
      'id' => 284,
      'descripcion' => 'Estructura de la secuencia',
      'tipo' => 'select',
      'name' => 'estructura_secuencia',
      'opciones' => '{"(consecutivo)":"(Consecutivo)","(anio)-(consecutivo)":"(Año)-(Consecutivo)","(anio)(consecutivo)-(grado)":"(Año)(Consecutivo)-(Grado)"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-23 09:47:37',
      'updated_at' => '2018-12-29 12:40:20',
    ),
    280 => 
    array (
      'id' => 285,
      'descripcion' => '¿Cómo se entero del Colegio?',
      'tipo' => 'select',
      'name' => 'enterado_por',
      'opciones' => '{"":"","Amigo/Familiar":"Amigo/Familiar","Internet/Redes Sociales":"Internet/Redes Sociales","Otros Medios de comunicación":"Otros Medios de comunicación"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-23 10:14:22',
      'updated_at' => '2018-12-30 00:47:27',
    ),
    281 => 
    array (
      'id' => 286,
      'descripcion' => 'Tipo bloque',
      'tipo' => 'select',
      'name' => 'tipo_bloque',
      'opciones' => '{"Agrupación de elementos":"Agrupación de elementos","Sumatoria de bloques":"Sumatoria de bloques"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-26 18:52:28',
      'updated_at' => '2018-12-26 18:52:28',
    ),
    282 => 
    array (
      'id' => 287,
      'descripcion' => 'Mostrar descripción',
      'tipo' => 'select',
      'name' => 'mostrar_descripcion',
      'opciones' => '{"1":"Si","0":"No"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-26 18:53:16',
      'updated_at' => '2020-02-17 05:13:57',
    ),
    283 => 
    array (
      'id' => 288,
      'descripcion' => ' Mostrar suma total',
      'tipo' => 'select',
      'name' => 'mostrar_suma_total',
      'opciones' => '{"Si":"Si","No":"No"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-26 18:54:03',
      'updated_at' => '2018-12-26 18:54:03',
    ),
    284 => 
    array (
      'id' => 289,
      'descripcion' => 'Grado',
      'tipo' => 'select',
      'name' => 'sga_grado_id',
      'opciones' => 'model_App\\Matriculas\\Grado',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-29 09:56:05',
      'updated_at' => '2020-01-19 06:45:45',
    ),
    285 => 
    array (
      'id' => 290,
      'descripcion' => 'Fecha matrícula',
      'tipo' => 'fecha',
      'name' => 'fecha_matricula',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-30 03:19:39',
      'updated_at' => '2018-12-30 04:48:58',
    ),
    286 => 
    array (
      'id' => 291,
      'descripcion' => 'Grado',
      'tipo' => 'select',
      'name' => 'sga_grado_id',
      'opciones' => 'table_sga_grados',
      'value' => 'null',
      'atributos' => '{"class":"select_dependientes_padre"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-30 03:22:43',
      'updated_at' => '2018-12-30 03:22:43',
    ),
    287 => 
    array (
      'id' => 292,
      'descripcion' => 'Curso',
      'tipo' => 'select',
      'name' => 'curso_id',
      'opciones' => '{"":""}',
      'value' => 'null',
      'atributos' => '{"class":"select_dependientes_hijo"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-30 03:24:28',
      'updated_at' => '2018-12-30 03:24:28',
    ),
    288 => 
    array (
      'id' => 293,
      'descripcion' => 'Año lectivo',
      'tipo' => 'select',
      'name' => 'periodo_lectivo_id',
      'opciones' => 'model_App\\Matriculas\\PeriodoLectivoAux',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-30 03:25:25',
      'updated_at' => '2020-01-30 14:42:12',
    ),
    289 => 
    array (
      'id' => 294,
      'descripcion' => 'Acudiente',
      'tipo' => 'bsText',
      'name' => 'acudiente',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-30 03:27:05',
      'updated_at' => '2018-12-30 03:27:05',
    ),
    290 => 
    array (
      'id' => 295,
      'descripcion' => 'Cédula acudiente',
      'tipo' => 'bsText',
      'name' => 'cedula_acudiente',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-30 03:27:31',
      'updated_at' => '2018-12-30 03:27:31',
    ),
    291 => 
    array (
      'id' => 296,
      'descripcion' => 'Teléfono acudiente',
      'tipo' => 'bsText',
      'name' => 'telefono_acudiente',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-30 03:27:53',
      'updated_at' => '2018-12-30 03:39:15',
    ),
    292 => 
    array (
      'id' => 297,
      'descripcion' => 'Email acudiente',
      'tipo' => 'bsText',
      'name' => 'email_acudiente',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2018-12-30 03:28:22',
      'updated_at' => '2018-12-30 03:39:38',
    ),
    293 => 
    array (
      'id' => 298,
      'descripcion' => 'Cédula mamá',
      'tipo' => 'bsText',
      'name' => 'cedula_mama',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-02 12:48:04',
      'updated_at' => '2019-01-02 12:51:24',
    ),
    294 => 
    array (
      'id' => 299,
      'descripcion' => 'Cédula papá',
      'tipo' => 'bsText',
      'name' => 'cedula_papa',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-02 12:48:30',
      'updated_at' => '2019-01-02 12:51:46',
    ),
    295 => 
    array (
      'id' => 300,
      'descripcion' => 'Grupo sanguíneo',
      'tipo' => 'bsText',
      'name' => 'grupo_sanguineo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-02 12:48:53',
      'updated_at' => '2019-01-02 12:53:20',
    ),
    296 => 
    array (
      'id' => 301,
      'descripcion' => 'Medicamentos',
      'tipo' => 'bsText',
      'name' => 'medicamentos',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-02 12:49:14',
      'updated_at' => '2019-01-02 12:53:42',
    ),
    297 => 
    array (
      'id' => 302,
      'descripcion' => 'Alergias',
      'tipo' => 'bsText',
      'name' => 'alergias',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-02 12:49:33',
      'updated_at' => '2019-01-02 12:52:47',
    ),
    298 => 
    array (
      'id' => 303,
      'descripcion' => 'E.P.S.',
      'tipo' => 'bsText',
      'name' => 'eps',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-02 12:49:48',
      'updated_at' => '2019-01-02 12:56:21',
    ),
    299 => 
    array (
      'id' => 304,
      'descripcion' => 'Código grado',
      'tipo' => 'bsText',
      'name' => 'codigo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-04 09:43:35',
      'updated_at' => '2019-01-04 09:44:01',
    ),
    300 => 
    array (
      'id' => 305,
      'descripcion' => 'Colegio anterior',
      'tipo' => 'bsText',
      'name' => 'colegio_anterior',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-15 07:46:05',
      'updated_at' => '2019-01-15 12:19:38',
    ),
    301 => 
    array (
      'id' => 306,
      'descripcion' => 'Nombre estudiante',
      'tipo' => 'select',
      'name' => 'matricula_id',
      'opciones' => 'model_App\\Matriculas\\EstudianteSinLibreta',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-16 13:16:33',
      'updated_at' => '2020-04-09 17:23:42',
    ),
    302 => 
    array (
      'id' => 307,
      'descripcion' => 'Área',
      'tipo' => 'select',
      'name' => 'area_id',
      'opciones' => 'table_sga_areas',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-31 10:51:53',
      'updated_at' => '2019-01-31 10:51:53',
    ),
    303 => 
    array (
      'id' => 308,
      'descripcion' => 'Escala de valoración',
      'tipo' => 'select',
      'name' => 'escala_valoracion_id',
      'opciones' => 'model_App\\Calificaciones\\EscalaValoracion',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-31 10:52:26',
      'updated_at' => '2020-01-07 09:09:15',
    ),
    304 => 
    array (
      'id' => 309,
      'descripcion' => 'Url botón eliminar',
      'tipo' => 'bsText',
      'name' => 'url_eliminar',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-31 10:57:59',
      'updated_at' => '2019-01-31 10:57:59',
    ),
    305 => 
    array (
      'id' => 310,
      'descripcion' => 'Código logro',
      'tipo' => 'bsLabel',
      'name' => 'codigo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-02-04 02:28:34',
      'updated_at' => '2019-02-04 02:28:34',
    ),
    306 => 
    array (
      'id' => 311,
      'descripcion' => 'Curso',
      'tipo' => 'select',
      'name' => 'curso_id',
      'opciones' => 'model_App\\Matriculas\\Curso',
      'value' => 'null',
      'atributos' => '{"class":"select_dependientes_padre"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2019-02-04 02:29:33',
      'updated_at' => '2020-01-08 19:57:59',
    ),
    307 => 
    array (
      'id' => 312,
      'descripcion' => 'Asignatura',
      'tipo' => 'select',
      'name' => 'asignatura_id',
      'opciones' => '{"":""}',
      'value' => 'null',
      'atributos' => '{"class":"select_dependientes_hijo"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2019-02-04 02:30:19',
      'updated_at' => '2020-01-08 19:58:31',
    ),
    308 => 
    array (
      'id' => 313,
      'descripcion' => 'Guardar y nuevo logro',
      'tipo' => 'personalizado',
      'name' => 'guardar_y_nuevo_logro',
      'opciones' => '',
      'value' => '<div id="guardar_y_nuevo">
<label for="guardar_y_nuevo" class="col-sm-3 control-label"> </label>
<div class="checkbox">
<label>
<input type="checkbox" checked name="guardar_y_nuevo" id="guardar_y_nuevo"> Guardar y agregar nuevo
</label>
</div>
</div>',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2019-02-04 02:31:17',
      'updated_at' => '2019-02-04 02:31:17',
    ),
    309 => 
    array (
      'id' => 314,
      'descripcion' => 'ID Colegio',
      'tipo' => 'constante',
      'name' => 'colegio_id',
      'opciones' => '',
      'value' => 'colegio_id',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2019-04-15 11:57:13',
      'updated_at' => '2019-04-15 12:16:24',
    ),
    310 => 
    array (
      'id' => 315,
      'descripcion' => 'ID Colegio',
      'tipo' => 'constante',
      'name' => 'colegio_id2',
      'opciones' => '',
      'value' => 'colegio_id',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2019-04-15 11:57:13',
      'updated_at' => '2019-04-15 12:16:24',
    ),
    311 => 
    array (
      'id' => 316,
      'descripcion' => 'Título',
      'tipo' => 'bsText',
      'name' => 'titulo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-21 23:14:25',
      'updated_at' => '2019-04-21 23:14:25',
    ),
    312 => 
    array (
      'id' => 317,
      'descripcion' => 'Resumen',
      'tipo' => 'bsTextArea',
      'name' => 'resumen',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '{"maxlength":"250"} ',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-21 23:14:25',
      'updated_at' => '2019-10-04 10:58:27',
    ),
    313 => 
    array (
      'id' => 318,
      'descripcion' => 'Palabras claves',
      'tipo' => 'bsTextArea',
      'name' => 'palabras_claves',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-21 23:14:25',
      'updated_at' => '2019-04-21 23:14:25',
    ),
    314 => 
    array (
      'id' => 319,
      'descripcion' => 'ID Usuario',
      'tipo' => 'hidden',
      'name' => 'user_id',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2019-04-21 23:14:25',
      'updated_at' => '2019-04-21 23:14:25',
    ),
    315 => 
    array (
      'id' => 320,
      'descripcion' => 'Categoria',
      'tipo' => 'select',
      'name' => 'categoria_id',
      'opciones' => 'model_App\\PaginaWeb\\Categoria',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-21 23:14:25',
      'updated_at' => '2019-04-21 23:14:25',
    ),
    316 => 
    array (
      'id' => 321,
      'descripcion' => 'ID Artículo',
      'tipo' => 'hidden',
      'name' => 'articulo_id',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2019-04-21 23:14:25',
      'updated_at' => '2019-04-21 23:14:25',
    ),
    317 => 
    array (
      'id' => 322,
      'descripcion' => 'Nombre comercial',
      'tipo' => 'bsText',
      'name' => 'descripcion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-18 10:53:55',
      'updated_at' => '2019-01-18 10:53:55',
    ),
    318 => 
    array (
      'id' => 323,
      'descripcion' => 'Valor cuota defecto',
      'tipo' => 'bsText',
      'name' => 'valor_cuota_defecto',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-18 21:36:05',
      'updated_at' => '2019-01-18 21:36:05',
    ),
    319 => 
    array (
      'id' => 324,
      'descripcion' => 'Fecha de entrega',
      'tipo' => 'fecha',
      'name' => 'fecha_entrega',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-18 21:36:47',
      'updated_at' => '2020-02-23 13:06:41',
    ),
    320 => 
    array (
      'id' => 325,
      'descripcion' => 'Tipo de uso',
      'tipo' => 'select',
      'name' => 'tipo_de_uso',
      'opciones' => '{"Uso propio":"Uso propio","Para arriendo":"Para arriendo"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-18 21:38:02',
      'updated_at' => '2019-01-18 21:38:02',
    ),
    321 => 
    array (
      'id' => 326,
      'descripcion' => 'Parqueadero asignado',
      'tipo' => 'bsText',
      'name' => 'parqueadero_asignado',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-18 21:38:32',
      'updated_at' => '2019-01-18 21:38:32',
    ),
    322 => 
    array (
      'id' => 327,
      'descripcion' => 'Depósito asignado',
      'tipo' => 'bsText',
      'name' => 'deposito_asignado',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-18 21:38:59',
      'updated_at' => '2019-01-18 21:38:59',
    ),
    323 => 
    array (
      'id' => 328,
      'descripcion' => 'Núm. matrícula inmobiliaria',
      'tipo' => 'bsText',
      'name' => 'numero_matricula_inmobiliaria',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-18 21:39:33',
      'updated_at' => '2019-01-19 18:53:13',
    ),
    324 => 
    array (
      'id' => 329,
      'descripcion' => 'Separador 3',
      'tipo' => 'personalizado',
      'name' => 'separador3',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-19 13:05:31',
      'updated_at' => '2019-01-19 13:05:31',
    ),
    325 => 
    array (
      'id' => 330,
      'descripcion' => 'Name (permiso)',
      'tipo' => 'bsText',
      'name' => 'name',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 1,
      'created_at' => '2019-01-19 13:13:32',
      'updated_at' => '2020-03-17 06:36:34',
    ),
    326 => 
    array (
      'id' => 331,
      'descripcion' => 'Tercero',
      'tipo' => 'select',
      'name' => 'core_tercero_inmueble_id',
      'opciones' => 'table_core_terceros',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-20 14:36:26',
      'updated_at' => '2019-01-20 14:37:33',
    ),
    327 => 
    array (
      'id' => 332,
      'descripcion' => 'Inmueble',
      'tipo' => 'select',
      'name' => 'tercero_id',
      'opciones' => 'model_App\\PropiedadHorizontal\\Propiedad',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2019-01-24 03:01:40',
      'updated_at' => '2019-01-24 17:17:08',
    ),
    328 => 
    array (
      'id' => 333,
      'descripcion' => 'Tipo tercero',
      'tipo' => 'personalizado',
      'name' => 'tipo_tercero',
      'opciones' => '',
      'value' => '<div class="row" style="padding: 5px 5px 5px 15px;">
<label>Tipo de tercero: </label>
<label class="radio-inline">
      <input type="radio" name="tipo_tercero" checked>Inmueble
    </label>
    <label class="radio-inline">
      <input type="radio" name="tipo_tercero">Tercero
    </label>
</div>',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-24 03:54:03',
      'updated_at' => '2019-01-24 04:04:45',
    ),
    329 => 
    array (
      'id' => 334,
      'descripcion' => 'ID Tercero',
      'tipo' => 'hidden',
      'name' => 'core_tercero_id',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2019-01-24 05:56:14',
      'updated_at' => '2019-01-24 05:56:14',
    ),
    330 => 
    array (
      'id' => 335,
      'descripcion' => 'Tercero',
      'tipo' => 'select',
      'name' => 'core_tercero_id_no',
      'opciones' => 'table_core_terceros',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-24 05:57:10',
      'updated_at' => '2019-01-24 06:01:03',
    ),
    331 => 
    array (
      'id' => 336,
      'descripcion' => 'Codigo referencia tercero',
      'tipo' => 'hidden',
      'name' => 'codigo_referencia_tercero',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2019-01-24 15:41:59',
      'updated_at' => '2019-01-24 15:41:59',
    ),
    332 => 
    array (
      'id' => 337,
      'descripcion' => 'Consecutivo actual',
      'tipo' => 'bsText',
      'name' => 'consecutivo_actual',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-24 21:13:16',
      'updated_at' => '2019-01-24 21:13:16',
    ),
    333 => 
    array (
      'id' => 338,
      'descripcion' => 'Url botón eliminar',
      'tipo' => 'bsText',
      'name' => 'url_eliminar',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-24 21:44:15',
      'updated_at' => '2019-01-24 21:44:15',
    ),
    334 => 
    array (
      'id' => 339,
      'descripcion' => 'Tipo documento',
      'tipo' => 'select',
      'name' => 'core_documento_app_id',
      'opciones' => 'model_App\\Core\\TipoDocApp',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-25 13:16:15',
      'updated_at' => '2019-06-25 22:00:29',
    ),
    335 => 
    array (
      'id' => 340,
      'descripcion' => 'Cuenta ingresos x defecto',
      'tipo' => 'select',
      'name' => 'cuenta_ingresos_id',
      'opciones' => 'model_App\\Contabilidad\\ContabCuenta',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-26 14:06:45',
      'updated_at' => '2019-06-25 21:24:54',
    ),
    336 => 
    array (
      'id' => 341,
      'descripcion' => 'Código inmueble',
      'tipo' => 'bsText',
      'name' => 'codigo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-01-26 14:07:48',
      'updated_at' => '2019-01-26 14:07:48',
    ),
    337 => 
    array (
      'id' => 342,
      'descripcion' => 'Cód. Google analitics',
      'tipo' => 'bsText',
      'name' => 'codigo_google_analitics',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-03-01 01:46:39',
      'updated_at' => '2019-03-01 01:46:39',
    ),
    338 => 
    array (
      'id' => 343,
      'descripcion' => 'Icono de página',
      'tipo' => 'imagen',
      'name' => 'favicon',
      'opciones' => 'ico',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-03-01 01:51:21',
      'updated_at' => '2019-07-25 17:30:01',
    ),
    339 => 
    array (
      'id' => 344,
      'descripcion' => 'Fecha corte',
      'tipo' => 'fecha',
      'name' => 'fecha_corte',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-03-06 03:48:09',
      'updated_at' => '2019-03-06 03:48:09',
    ),
    340 => 
    array (
      'id' => 345,
      'descripcion' => 'Calculado sobre',
      'tipo' => 'select',
      'name' => 'calculado_sobre',
      'opciones' => '{"Saldo total vencido":"Saldo total vencido","Última factura vencida":"Última factura vencida"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-03-06 03:49:42',
      'updated_at' => '2019-03-06 03:49:42',
    ),
    341 => 
    array (
      'id' => 346,
      'descripcion' => 'Saldo vencido',
      'tipo' => 'bsText',
      'name' => 'saldo_vencido',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-03-06 03:50:28',
      'updated_at' => '2019-03-06 03:50:28',
    ),
    342 => 
    array (
      'id' => 347,
      'descripcion' => 'Tasa interés',
      'tipo' => 'bsText',
      'name' => 'tasa_interes',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-03-06 03:50:54',
      'updated_at' => '2019-03-06 03:50:54',
    ),
    343 => 
    array (
      'id' => 348,
      'descripcion' => 'Concepto de interés de mora',
      'tipo' => 'select',
      'name' => 'cxc_servicio_id',
      'opciones' => 'model_App\\CxC\\CxcServicio',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-03-06 03:55:07',
      'updated_at' => '2019-06-25 21:12:31',
    ),
    344 => 
    array (
      'id' => 349,
      'descripcion' => 'Valor interés',
      'tipo' => 'bsText',
      'name' => 'valor_interes',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-03-06 03:56:32',
      'updated_at' => '2019-03-06 03:56:32',
    ),
    345 => 
    array (
      'id' => 350,
      'descripcion' => 'Plantilla',
      'tipo' => 'select',
      'name' => 'plantilla',
      'opciones' => '{"":"","appsiel_sas":"appsiel_sas","mi_colegio":"mi_colegio","colegio_tor":"colegio_tor","empresarial":"empresarial","la_paz":"la_paz","avipoulet":"Avipoulet"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-18 21:05:25',
      'updated_at' => '2020-01-23 09:24:15',
    ),
    346 => 
    array (
      'id' => 351,
      'descripcion' => 'Logo',
      'tipo' => 'imagen',
      'name' => 'logo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-19 05:24:20',
      'updated_at' => '2019-04-24 18:43:12',
    ),
    347 => 
    array (
      'id' => 352,
      'descripcion' => 'Alias',
      'tipo' => 'bsText',
      'name' => 'alias_sef_no',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2019-04-23 22:42:02',
      'updated_at' => '2019-04-23 22:42:02',
    ),
    348 => 
    array (
      'id' => 353,
      'descripcion' => 'Contenido',
      'tipo' => 'bsTextArea',
      'name' => 'contenido_articulo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-23 22:59:11',
      'updated_at' => '2019-04-23 22:59:11',
    ),
    349 => 
    array (
      'id' => 354,
      'descripcion' => 'Slug',
      'tipo' => 'bsText',
      'name' => 'slug',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '{"readonly":"readonly"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-23 23:23:45',
      'updated_at' => '2020-01-26 05:30:09',
    ),
    350 => 
    array (
      'id' => 355,
      'descripcion' => 'Mostrar título',
      'tipo' => 'select',
      'name' => 'mostrar_titulo',
      'opciones' => '{"1":"Si","0":"No"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-24 10:36:57',
      'updated_at' => '2019-04-24 18:24:48',
    ),
    351 => 
    array (
      'id' => 356,
      'descripcion' => 'Clase de contrato',
      'tipo' => 'select',
      'name' => 'clase_contrato',
      'opciones' => '{"normal":"Normal","labor_contratada":"Labor contratada"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2020-03-15 10:15:14',
    ),
    352 => 
    array (
      'id' => 357,
      'descripcion' => 'Cargo',
      'tipo' => 'select',
      'name' => 'cargo_id',
      'opciones' => 'model_App\\Nomina\\NomCargo',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    353 => 
    array (
      'id' => 358,
      'descripcion' => 'Horas laborales mes',
      'tipo' => 'bsText',
      'name' => 'horas_laborales',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-12-20 04:56:13',
    ),
    354 => 
    array (
      'id' => 359,
      'descripcion' => 'Salario básico',
      'tipo' => 'bsText',
      'name' => 'sueldo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2020-03-15 03:06:31',
    ),
    355 => 
    array (
      'id' => 360,
      'descripcion' => 'Fecha Ingreso',
      'tipo' => 'fecha',
      'name' => 'fecha_ingreso',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    356 => 
    array (
      'id' => 361,
      'descripcion' => 'Contrato Hasta',
      'tipo' => 'fecha',
      'name' => 'contrato_hasta',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    357 => 
    array (
      'id' => 362,
      'descripcion' => 'E.P.S.',
      'tipo' => 'select',
      'name' => 'entidad_salud_id',
      'opciones' => 'model_App\\Nomina\\NomEntidad',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    358 => 
    array (
      'id' => 363,
      'descripcion' => 'Fondo Pensión',
      'tipo' => 'select',
      'name' => 'entidad_pension_id',
      'opciones' => 'model_App\\Nomina\\NomEntidad',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    359 => 
    array (
      'id' => 364,
      'descripcion' => 'A.R.L.',
      'tipo' => 'select',
      'name' => 'entidad_arl_id',
      'opciones' => 'model_App\\Nomina\\NomEntidad',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    360 => 
    array (
      'id' => 365,
      'descripcion' => 'Modo liquidación',
      'tipo' => 'select',
      'name' => 'modo_liquidacion_id',
      'opciones' => 'model_App\\Nomina\\NomModoLiquidacion',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-11-02 06:11:31',
    ),
    361 => 
    array (
      'id' => 366,
      'descripcion' => 'Cargo padre',
      'tipo' => 'select',
      'name' => 'cargo_padre_id',
      'opciones' => 'model_App\\Nomina\\Nomcargo',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    362 => 
    array (
      'id' => 367,
      'descripcion' => 'Rango salarial',
      'tipo' => 'select',
      'name' => 'rango_salarial_id',
      'opciones' => '{}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    363 => 
    array (
      'id' => 368,
      'descripcion' => 'Concepto',
      'tipo' => 'select',
      'name' => 'nom_concepto_id',
      'opciones' => 'model_App\\Nomina\\ConceptoPrestamo',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2020-06-30 19:17:10',
    ),
    364 => 
    array (
      'id' => 369,
      'descripcion' => 'Valor cuota',
      'tipo' => 'bsText',
      'name' => 'valor_cuota',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    365 => 
    array (
      'id' => 370,
      'descripcion' => 'Tope máximo',
      'tipo' => 'bsText',
      'name' => 'tope_maximo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    366 => 
    array (
      'id' => 371,
      'descripcion' => 'Porcentaje sobre el básico',
      'tipo' => 'bsText',
      'name' => 'porcentaje_sobre_basico',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-29 21:26:08',
    ),
    367 => 
    array (
      'id' => 372,
      'descripcion' => 'Valor acumulado',
      'tipo' => 'bsText',
      'name' => 'valor_acumulado',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    368 => 
    array (
      'id' => 373,
      'descripcion' => 'Valor prestamo',
      'tipo' => 'bsText',
      'name' => 'valor_prestamo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    369 => 
    array (
      'id' => 374,
      'descripcion' => 'Núm. de cuotas',
      'tipo' => 'bsText',
      'name' => 'numero_cuotas',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    370 => 
    array (
      'id' => 375,
      'descripcion' => 'Código nacional',
      'tipo' => 'bsText',
      'name' => 'codigo_nacional',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-11-02 06:30:22',
    ),
    371 => 
    array (
      'id' => 376,
      'descripcion' => 'Tipo Entidad',
      'tipo' => 'select',
      'name' => 'tipo_entidad',
      'opciones' => '{"EPS":"E.P.S.","AFP":"A.F.P.","ARL":"A.R.L.","CCF":"C.C.F.","PARAFISCALES":"PARAFISCALES"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-11-02 06:28:00',
    ),
    372 => 
    array (
      'id' => 377,
      'descripcion' => 'Total devengos',
      'tipo' => 'bsText',
      'name' => 'total_devengos',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    373 => 
    array (
      'id' => 378,
      'descripcion' => 'Total deducción',
      'tipo' => 'bsText',
      'name' => 'total_deducciones',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    374 => 
    array (
      'id' => 379,
      'descripcion' => 'Naturaleza',
      'tipo' => 'select',
      'name' => 'naturaleza',
      'opciones' => '{"":"","devengo":"Devengo","deduccion":"Deducción","provision":"Provisión"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2020-03-15 11:40:27',
    ),
    375 => 
    array (
      'id' => 380,
      'descripcion' => 'Documento nómina',
      'tipo' => 'select',
      'name' => 'nom_doc_encabezado_id',
      'opciones' => 'model_App\\Nomina\\NomDocEncabezado',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    376 => 
    array (
      'id' => 381,
      'descripcion' => 'Concepto nómina',
      'tipo' => 'select',
      'name' => 'nom_concepto_id',
      'opciones' => 'model_App\\Nomina\\NomConcepto',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-30 10:16:30',
    ),
    377 => 
    array (
      'id' => 382,
      'descripcion' => 'Horas',
      'tipo' => 'bsText',
      'name' => 'cantidad_horas',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    378 => 
    array (
      'id' => 383,
      'descripcion' => 'Porcentaje',
      'tipo' => 'bsText',
      'name' => 'porcentaje',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    379 => 
    array (
      'id' => 384,
      'descripcion' => 'Devengo',
      'tipo' => 'bsText',
      'name' => 'valor_devengo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    380 => 
    array (
      'id' => 385,
      'descripcion' => 'Deducción',
      'tipo' => 'bsText',
      'name' => 'valor_deduccion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    381 => 
    array (
      'id' => 386,
      'descripcion' => 'ID Empresa',
      'tipo' => 'constante',
      'name' => 'core_empresa_id',
      'opciones' => '',
      'value' => 'empresa_id',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2019-04-30 04:04:11',
      'updated_at' => '2019-05-01 05:59:33',
    ),
    382 => 
    array (
      'id' => 387,
      'descripcion' => 'Abreviatura',
      'tipo' => 'bsText',
      'name' => 'abreviatura',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-05-01 05:01:40',
      'updated_at' => '2019-05-01 05:01:40',
    ),
    383 => 
    array (
      'id' => 388,
      'descripcion' => 'Sede',
      'tipo' => 'bsText',
      'name' => 'sede',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    384 => 
    array (
      'id' => 389,
      'descripcion' => 'Especialidad',
      'tipo' => 'bsText',
      'name' => 'especialidad',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    385 => 
    array (
      'id' => 390,
      'descripcion' => 'Registro Médico',
      'tipo' => 'bsText',
      'name' => 'numero_carnet_licencia',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    386 => 
    array (
      'id' => 391,
      'descripcion' => 'Código historia clínica',
      'tipo' => 'hidden',
      'name' => 'codigo_historia_clinica',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-06-12 10:08:05',
    ),
    387 => 
    array (
      'id' => 392,
      'descripcion' => 'Ocupación',
      'tipo' => 'bsText',
      'name' => 'ocupacion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    388 => 
    array (
      'id' => 393,
      'descripcion' => 'Estado civil',
      'tipo' => 'select',
      'name' => 'estado_civil',
      'opciones' => '{"":"","Soltero":"Soltero","Casado":"Casado","Unión de hecho":"Unión de hecho","Viudo":"Viudo","Divorciado":"Divorciado"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    389 => 
    array (
      'id' => 394,
      'descripcion' => 'Remitido por',
      'tipo' => 'bsText',
      'name' => 'remitido_por',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    390 => 
    array (
      'id' => 395,
      'descripcion' => 'Nivel académico',
      'tipo' => 'select',
      'name' => 'nivel_academico',
      'opciones' => '{"":"","Primaria":"Primaria","Secudaria":"Secundaria","Técnico":"Técnico","Tecnólogo":"Tecnólogo","Profesional":"Profesional","Postgrado":"Postgrado"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    391 => 
    array (
      'id' => 396,
      'descripcion' => 'Paciente',
      'tipo' => 'hidden',
      'name' => 'paciente_id',
      'opciones' => 'model_App\\Salud\\Paciente',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-06-18 06:08:09',
    ),
    392 => 
    array (
      'id' => 397,
      'descripcion' => 'Tipo de consulta',
      'tipo' => 'select',
      'name' => 'tipo_consulta',
      'opciones' => '{"Primera Vez":"Primera Vez","Control":"Control"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    393 => 
    array (
      'id' => 398,
      'descripcion' => 'Fecha',
      'tipo' => 'fecha',
      'name' => 'fecha_consulta',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    394 => 
    array (
      'id' => 399,
      'descripcion' => 'Profesional de la salud',
      'tipo' => 'hidden',
      'name' => 'profesional_salud_id',
      'opciones' => 'model_App\\Salud\\ProfesionalSalud',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-06-13 18:40:47',
    ),
    395 => 
    array (
      'id' => 400,
      'descripcion' => 'Consultorio',
      'tipo' => 'select',
      'name' => 'consultorio_id',
      'opciones' => 'model_App\\Salud\\Consultorio',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    396 => 
    array (
      'id' => 401,
      'descripcion' => 'Nombre acompañante',
      'tipo' => 'bsText',
      'name' => 'nombre_acompañante',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    397 => 
    array (
      'id' => 402,
      'descripcion' => 'Parentezco acompañante',
      'tipo' => 'bsText',
      'name' => 'parentezco_acompañante',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    398 => 
    array (
      'id' => 403,
      'descripcion' => 'Síntomas',
      'tipo' => 'bsCheckBox',
      'name' => 'sintomas',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    399 => 
    array (
      'id' => 404,
      'descripcion' => 'Diagnóstico',
      'tipo' => 'bsTextArea',
      'name' => 'core_campo_id-ID',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-06-25 09:29:02',
    ),
    400 => 
    array (
      'id' => 405,
      'descripcion' => 'Indicaciones',
      'tipo' => 'bsTextArea',
      'name' => 'indicaciones',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    401 => 
    array (
      'id' => 406,
      'descripcion' => 'Organo padre',
      'tipo' => 'select',
      'name' => 'organo_padre_id',
      'opciones' => 'model_App\\Salud\\OrganoDelCuerpo',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    402 => 
    array (
      'id' => 407,
      'descripcion' => 'Exámen',
      'tipo' => 'select',
      'name' => 'examen_a_mostrar_id',
      'opciones' => 'model_App\\Salud\\ExamenMedico',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    403 => 
    array (
      'id' => 408,
      'descripcion' => 'Control',
      'tipo' => 'select',
      'name' => 'proximo_control',
      'opciones' => '{"Seis (6) meses":"Seis (6) meses","Un (1) año":"Un (1) año"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    404 => 
    array (
      'id' => 409,
      'descripcion' => 'Tipo de lentes',
      'tipo' => 'select',
      'name' => 'tipo_de_lentes',
      'opciones' => '{"":"","Monofocal":"Monofocal","Bifocal invisible":"Bifocal invisible","Bifocal Flat Top":"Bifocal Flat Top","Progresivos":"Progresivos","Progresivo Panoramax DS AR Crizal Sapphire 360 Transitions S7 Gris":"Progresivo Panoramax DS AR Crizal Sapphire 360 Transitions S7 Gris"}',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-06-15 12:48:45',
    ),
    405 => 
    array (
      'id' => 410,
      'descripcion' => 'Material',
      'tipo' => 'select',
      'name' => 'material',
      'opciones' => '{"":"","CR39":"CR39","Teflón":"Teflón","Policarbonato":"Policarbonato","Vidrio":"Vidrio"}',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-06-13 08:20:15',
    ),
    406 => 
    array (
      'id' => 411,
      'descripcion' => 'Recomendaciones',
      'tipo' => 'bsTextArea',
      'name' => 'recomendaciones',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-06-14 09:55:44',
    ),
    407 => 
    array (
      'id' => 412,
      'descripcion' => 'EAV Aux. Modelo Padre',
      'tipo' => 'hidden',
      'name' => 'modelo_padre_id',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Corresponde al ID del modelo padre para el Modelo Entidad.',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-06-24 13:26:39',
    ),
    408 => 
    array (
      'id' => 413,
      'descripcion' => 'EAV Entidad (Modelo ID)',
      'tipo' => 'hidden',
      'name' => 'modelo_entidad_id',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Almacena el ID de la tabla core_modelos para la Entidad en el Modelo EAV. El Atributo es un ID de la tabla core_campos.',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-06-24 13:26:52',
    ),
    409 => 
    array (
      'id' => 414,
      'descripcion' => 'Tipo modelo relacionado',
      'tipo' => 'select',
      'name' => 'tipo_modelo_relacionado',
      'opciones' => '{"Real":"Real","Virtual":"Virtual"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    410 => 
    array (
      'id' => 415,
      'descripcion' => 'Exámen',
      'tipo' => 'select',
      'name' => 'examen_id',
      'opciones' => 'model_App\\Salud\\ExamenMedico',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-13 15:26:17',
      'updated_at' => '2019-06-13 15:28:14',
    ),
    411 => 
    array (
      'id' => 416,
      'descripcion' => 'Órgano',
      'tipo' => 'select',
      'name' => 'organo_id',
      'opciones' => 'model_App\\Salud\\OrganoDelCuerpo',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-13 15:27:14',
      'updated_at' => '2019-06-13 15:27:14',
    ),
    412 => 
    array (
      'id' => 417,
      'descripcion' => 'Variable',
      'tipo' => 'select',
      'name' => 'variable_id',
      'opciones' => 'model_App\\Salud\\VariableExamen',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-13 15:27:59',
      'updated_at' => '2019-06-13 15:27:59',
    ),
    413 => 
    array (
      'id' => 418,
      'descripcion' => 'Tipo campo',
      'tipo' => 'select',
      'name' => 'tipo_campo',
      'opciones' => '{"bsText":"Text","select":"Select","monetario":"Monetario","bsTextArea":"TextArea","password":"Password","hidden":"Hidden","fecha":"Fecha","hora":"Hora","bsCheckBox":"CheckBox","bsRadioBtn":"RadioBtn","bsLabel":"Label","personalizado":"Custom","constante":"Constante","imagen":"Imagen","file":"File","html_ayuda":"Pieza HTML ayuda","spin":"Spin"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Usado para la aplicación Consultorio Médico.',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => NULL,
      'updated_at' => '2020-05-30 03:57:58',
    ),
    414 => 
    array (
      'id' => 419,
      'descripcion' => 'Consulta ID',
      'tipo' => 'hidden',
      'name' => 'consulta_id',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2019-06-14 04:53:26',
      'updated_at' => '2019-06-14 04:53:26',
    ),
    415 => 
    array (
      'id' => 420,
      'descripcion' => 'Exámen ID',
      'tipo' => 'hidden',
      'name' => 'examen_id',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-14 04:54:05',
      'updated_at' => '2019-06-14 04:54:05',
    ),
    416 => 
    array (
      'id' => 421,
      'descripcion' => 'Documento Identidad Acompañante',
      'tipo' => 'bsText',
      'name' => 'documento_identidad_acompañante',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-15 04:46:07',
      'updated_at' => '2019-06-15 04:46:07',
    ),
    417 => 
    array (
      'id' => 422,
      'descripcion' => 'Tipo de Lentes',
      'tipo' => 'select',
      'name' => 'tipo_de_lentes',
      'opciones' => 'model_App\\Salud\\TipoLente',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-19 10:08:46',
      'updated_at' => '2019-06-19 10:08:46',
    ),
    418 => 
    array (
      'id' => 423,
      'descripcion' => 'Material',
      'tipo' => 'select',
      'name' => 'material',
      'opciones' => 'model_App\\Salud\\MaterialLente',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-19 10:09:10',
      'updated_at' => '2019-06-19 10:10:54',
    ),
    419 => 
    array (
      'id' => 424,
      'descripcion' => 'Uso',
      'tipo' => 'bsText',
      'name' => 'uso',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-20 10:37:19',
      'updated_at' => '2019-06-20 10:37:19',
    ),
    420 => 
    array (
      'id' => 425,
      'descripcion' => 'Menú',
      'tipo' => 'select',
      'name' => 'menu_id',
      'opciones' => 'model_App\\PaginaWeb\\Menu',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-06-22 16:53:56',
    ),
    421 => 
    array (
      'id' => 426,
      'descripcion' => 'Item padre',
      'tipo' => 'select',
      'name' => 'item_padre_id',
      'opciones' => 'model_App\\PaginaWeb\\MenuItem',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-06-22 16:54:16',
    ),
    422 => 
    array (
      'id' => 427,
      'descripcion' => 'Enlace',
      'tipo' => 'bsText',
      'name' => 'enlace',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    423 => 
    array (
      'id' => 428,
      'descripcion' => 'Destino del enlace',
      'tipo' => 'select',
      'name' => 'target',
      'opciones' => '{"":"Misma ventana","_blank":"Nueva ventana"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-09-30 11:44:46',
    ),
    424 => 
    array (
      'id' => 429,
      'descripcion' => 'Oftamológicos',
      'tipo' => 'bsText',
      'name' => 'core_campo_id-ID',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-24 10:11:32',
      'updated_at' => '2019-06-24 11:27:15',
    ),
    425 => 
    array (
      'id' => 430,
      'descripcion' => 'Familiares',
      'tipo' => 'bsText',
      'name' => 'core_campo_id-ID',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-24 10:45:07',
      'updated_at' => '2019-06-24 10:58:23',
    ),
    426 => 
    array (
      'id' => 431,
      'descripcion' => 'Lbl Antecedentes',
      'tipo' => 'personalizado',
      'name' => 'lbl_antecedentes',
      'opciones' => '',
      'value' => '<div style="padding-left: 15px;">
    <h3> Antecedentes </h3>
    <hr>
</div>',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2019-06-24 11:30:39',
      'updated_at' => '2020-06-14 04:37:08',
    ),
    427 => 
    array (
      'id' => 432,
      'descripcion' => 'EAV Aux Registro Modelo Principal ID',
      'tipo' => 'hidden',
      'name' => 'registro_modelo_padre_id',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-24 13:09:40',
      'updated_at' => '2019-06-24 13:26:21',
    ),
    428 => 
    array (
      'id' => 433,
      'descripcion' => 'Prescripción Farmacológica',
      'tipo' => 'bsTextArea',
      'name' => 'core_campo_id-ID',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-25 09:30:50',
      'updated_at' => '2019-06-25 09:30:50',
    ),
    429 => 
    array (
      'id' => 434,
      'descripcion' => 'Observaciones',
      'tipo' => 'bsTextArea',
      'name' => 'core_campo_id-ID',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-25 09:31:16',
      'updated_at' => '2019-06-25 09:31:16',
    ),
    430 => 
    array (
      'id' => 435,
      'descripcion' => 'Remisión',
      'tipo' => 'select',
      'name' => 'core_campo_id-ID',
      'opciones' => '{"":"","Optometría clínica":"Optometría clínica","Valoración por Oftalmología ":"Valoración por Oftalmología","Valoración por Ortóptica":"Valoración por Ortóptica"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-25 09:35:26',
      'updated_at' => '2019-06-25 09:35:26',
    ),
    431 => 
    array (
      'id' => 436,
      'descripcion' => 'Plan y/o Tratamiento',
      'tipo' => 'bsTextArea',
      'name' => 'core_campo_id-ID',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-25 09:38:57',
      'updated_at' => '2019-06-25 09:38:57',
    ),
    432 => 
    array (
      'id' => 437,
      'descripcion' => 'Patológicos',
      'tipo' => 'bsText',
      'name' => 'core_campo_id-ID',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-24 10:45:07',
      'updated_at' => '2019-06-24 10:58:23',
    ),
    433 => 
    array (
      'id' => 438,
      'descripcion' => 'Farmacológicos',
      'tipo' => 'bsText',
      'name' => 'core_campo_id-ID',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-24 10:45:07',
      'updated_at' => '2019-06-24 10:58:23',
    ),
    434 => 
    array (
      'id' => 439,
      'descripcion' => 'Quirúrgicos',
      'tipo' => 'bsText',
      'name' => 'core_campo_id-ID',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-24 10:45:07',
      'updated_at' => '2019-06-24 10:58:23',
    ),
    435 => 
    array (
      'id' => 440,
      'descripcion' => 'Toxicológicos',
      'tipo' => 'bsText',
      'name' => 'core_campo_id-ID',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-24 10:45:07',
      'updated_at' => '2019-06-24 10:58:23',
    ),
    436 => 
    array (
      'id' => 441,
      'descripcion' => 'Tipo de Lentes',
      'tipo' => 'select',
      'name' => 'core_campo_id-ID',
      'opciones' => 'model_App\\Salud\\TipoLente',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-19 10:08:46',
      'updated_at' => '2019-06-19 10:08:46',
    ),
    437 => 
    array (
      'id' => 442,
      'descripcion' => 'Motivo de consulta',
      'tipo' => 'bsTextArea',
      'name' => 'core_campo_id-ID',
      'opciones' => '  ',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-27 10:37:00',
      'updated_at' => '2020-06-01 11:27:01',
    ),
    438 => 
    array (
      'id' => 443,
      'descripcion' => 'Lbl Uso de lentes',
      'tipo' => 'personalizado',
      'name' => 'lbl_uso_de_lentes',
      'opciones' => '',
      'value' => '<div style="padding-left: 15px;">
    <h3> Uso de lentes </h3> <hr>
</div>',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2019-06-27 12:40:15',
      'updated_at' => '2020-06-14 04:37:46',
    ),
    439 => 
    array (
      'id' => 444,
      'descripcion' => 'Frecuencia de uso',
      'tipo' => 'select',
      'name' => 'core_campo_id-ID',
      'opciones' => '{"":"","Ocasional":"Ocasional","Permanente":"Permanente"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-27 20:36:26',
      'updated_at' => '2020-06-01 11:36:35',
    ),
    440 => 
    array (
      'id' => 445,
      'descripcion' => 'Lentes de contacto',
      'tipo' => 'select',
      'name' => 'core_campo_id-ID',
      'opciones' => '{"":"","Blandos":"Blandos","PMMA":"PMMA","Gas permeable":"Gas permeable","Híbridos":"Híbridos"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-27 20:38:31',
      'updated_at' => '2020-06-01 11:49:30',
    ),
    441 => 
    array (
      'id' => 446,
      'descripcion' => 'Separador4',
      'tipo' => 'personalizado',
      'name' => 'separador4',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-27 20:39:03',
      'updated_at' => '2020-06-01 11:41:27',
    ),
    442 => 
    array (
      'id' => 447,
      'descripcion' => 'Síntomas',
      'tipo' => 'bsCheckBox',
      'name' => 'core_campo_id-ID',
      'opciones' => '{"Asintomático":"Asintomático","Disminución visual de cerca":"Disminución visual de cerca","Disminución visual de lejos":"Disminución visual de lejos","Ardor ocular":"Ardor ocular","Lagrimeo":"Lagrimeo","Cansancio ocular":"Cansancio ocular","Irritación":"Irritación","Fotofobia":"Fotofobia","Prurito ocular":"Prurito ocular","Salto de renglón":"Salto de renglón","Cefaleas":"Cefaleas","Resequedad ocular":"Resequedad ocular"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-06-28 05:20:00',
      'updated_at' => '2020-06-01 11:52:08',
    ),
    443 => 
    array (
      'id' => 448,
      'descripcion' => 'Bodega',
      'tipo' => 'select',
      'name' => 'inv_bodega_id',
      'opciones' => 'model_App\\Inventarios\\InvBodega',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-07-02 06:42:47',
    ),
    444 => 
    array (
      'id' => 449,
      'descripcion' => 'Stock Mínimo',
      'tipo' => 'bsText',
      'name' => 'stock_minimo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    445 => 
    array (
      'id' => 450,
      'descripcion' => 'Empresa ID',
      'tipo' => 'hidden',
      'name' => 'core_empresa_id',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2019-04-28 09:46:22',
      'updated_at' => '2019-04-28 09:46:22',
    ),
    446 => 
    array (
      'id' => 451,
      'descripcion' => 'Imágenes',
      'tipo' => 'imagenes_multiples',
      'name' => 'core_campo_id-ID',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-04 09:46:22',
      'updated_at' => '2019-07-04 09:46:22',
    ),
    447 => 
    array (
      'id' => 452,
      'descripcion' => 'Textos imágenes',
      'tipo' => 'bsTextArea',
      'name' => 'core_campo_id-ID',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-04 09:46:22',
      'updated_at' => '2019-07-04 09:46:22',
    ),
    448 => 
    array (
      'id' => 453,
      'descripcion' => 'Altura máxima',
      'tipo' => 'bsText',
      'name' => 'core_campo_id-ID',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-04 09:46:22',
      'updated_at' => '2019-07-04 09:46:22',
    ),
    449 => 
    array (
      'id' => 454,
      'descripcion' => 'Nombre Carrusel',
      'tipo' => 'bsText',
      'name' => 'core_campo_id-ID',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-04 09:46:22',
      'updated_at' => '2019-07-04 09:46:22',
    ),
    450 => 
    array (
      'id' => 455,
      'descripcion' => 'Enlaces imágenes',
      'tipo' => 'bsTextArea',
      'name' => 'core_campo_id-ID',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-04 09:46:22',
      'updated_at' => '2019-07-04 09:46:22',
    ),
    451 => 
    array (
      'id' => 456,
      'descripcion' => 'Target',
      'tipo' => 'select',
      'name' => 'core_campo_id-ID',
      'opciones' => '{"":"Misma página","_blank":"Nueva página"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-04 09:46:22',
      'updated_at' => '2019-07-04 09:46:22',
    ),
    452 => 
    array (
      'id' => 457,
      'descripcion' => 'Estado',
      'tipo' => 'select',
      'name' => 'core_campo_id-ID',
      'opciones' => '{"Activo":"Activo","Inactivo":"Inactivo"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-04 09:46:22',
      'updated_at' => '2019-07-04 09:46:22',
    ),
    453 => 
    array (
      'id' => 458,
      'descripcion' => 'Modelo Entidad ID en EAV ',
      'tipo' => 'hidden',
      'name' => 'modelo_entidad_id',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-04 08:05:10',
      'updated_at' => '2019-07-04 08:05:10',
    ),
    454 => 
    array (
      'id' => 459,
      'descripcion' => 'Imágenes',
      'tipo' => 'imagenes_multiples',
      'name' => 'imagenes',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-06 09:46:22',
      'updated_at' => '2019-07-06 09:46:22',
    ),
    455 => 
    array (
      'id' => 460,
      'descripcion' => 'Textos imágenes',
      'tipo' => 'bsTextArea',
      'name' => 'textos_imagenes',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-06 09:46:22',
      'updated_at' => '2019-07-06 09:46:22',
    ),
    456 => 
    array (
      'id' => 461,
      'descripcion' => 'Altura máxima',
      'tipo' => 'bsText',
      'name' => 'altura_maxima',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-06 09:46:22',
      'updated_at' => '2019-07-06 09:46:22',
    ),
    457 => 
    array (
      'id' => 462,
      'descripcion' => 'Enlaces imágenes',
      'tipo' => 'bsTextArea',
      'name' => 'enlaces_imagenes',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-06 09:46:22',
      'updated_at' => '2019-07-06 09:46:22',
    ),
    458 => 
    array (
      'id' => 463,
      'descripcion' => 'Columna calificación',
      'tipo' => 'select',
      'name' => 'columna_calificacion',
      'opciones' => '{"":"","C1":"C1","C2":"C2","C3":"C3","C4":"C4","C5":"C5","C6":"C6","C7":"C7","C8":"C8","C9":"C9","C10":"C10","C11":"C11","C12":"C12","C13":"C13","C14":"C14","C15":"C15"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-09 09:46:22',
      'updated_at' => '2020-01-11 06:34:06',
    ),
    459 => 
    array (
      'id' => 464,
      'descripcion' => 'Misión',
      'tipo' => 'bsTextArea',
      'name' => 'core_campo_id-ID',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-12 07:22:39',
      'updated_at' => '2019-07-12 07:23:16',
    ),
    460 => 
    array (
      'id' => 465,
      'descripcion' => 'Visión',
      'tipo' => 'bsTextArea',
      'name' => 'core_campo_id-ID',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-12 07:23:01',
      'updated_at' => '2019-07-12 07:23:01',
    ),
    461 => 
    array (
      'id' => 466,
      'descripcion' => 'Valores',
      'tipo' => 'bsTextArea',
      'name' => 'core_campo_id-ID',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-12 07:24:03',
      'updated_at' => '2019-07-12 07:24:03',
    ),
    462 => 
    array (
      'id' => 467,
      'descripcion' => 'Ancho columnas (% separados por comas)',
      'tipo' => 'bsText',
      'name' => 'ancho_columnas',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-12 09:46:22',
      'updated_at' => '2019-07-12 09:46:22',
    ),
    463 => 
    array (
      'id' => 468,
      'descripcion' => 'Elementos de la sección',
      'tipo' => 'bsTextArea',
      'name' => 'elementos',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-12 19:17:34',
      'updated_at' => '2019-07-12 19:17:34',
    ),
    464 => 
    array (
      'id' => 469,
      'descripcion' => 'Tipo de enlace',
      'tipo' => 'select',
      'name' => 'tipo_enlace',
      'opciones' => '{"":"","mostrar_articulo":"Mostrar un artículo","mostrar_seccion":"Mostrar sección","url_externa":"URL externa"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-12 21:14:12',
      'updated_at' => '2020-01-26 13:00:38',
    ),
    465 => 
    array (
      'id' => 470,
      'descripcion' => 'Escoger artículo',
      'tipo' => 'select',
      'name' => 'pw_articulo_id',
      'opciones' => 'model_App\\PaginaWeb\\ArticuloItemMenu',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-12 22:53:56',
      'updated_at' => '2020-01-26 13:34:00',
    ),
    466 => 
    array (
      'id' => 471,
      'descripcion' => 'Escoger categoría',
      'tipo' => 'select',
      'name' => 'pw_categoria_id',
      'opciones' => 'model_App\\PaginaWeb\\Categoria',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-12 22:54:46',
      'updated_at' => '2019-07-12 22:54:46',
    ),
    467 => 
    array (
      'id' => 472,
      'descripcion' => 'Activar controles laterales',
      'tipo' => 'select',
      'name' => 'activar_controles_laterales',
      'opciones' => '{"1":"Si","0":"No"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-15 06:58:57',
      'updated_at' => '2019-07-15 06:58:57',
    ),
    468 => 
    array (
      'id' => 473,
      'descripcion' => 'Fecha desde',
      'tipo' => 'date',
      'name' => 'fecha_desde',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '{"class":"form-control"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-16 21:48:06',
      'updated_at' => '2019-07-16 21:58:16',
    ),
    469 => 
    array (
      'id' => 474,
      'descripcion' => 'Fecha hasta',
      'tipo' => 'date',
      'name' => 'fecha_hasta',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '{"class":"form-control"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-16 21:48:24',
      'updated_at' => '2019-07-16 21:58:24',
    ),
    470 => 
    array (
      'id' => 475,
      'descripcion' => 'Url Form Action',
      'tipo' => 'bsText',
      'name' => 'url_form_action',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-16 22:16:48',
      'updated_at' => '2019-07-16 22:16:48',
    ),
    471 => 
    array (
      'id' => 476,
      'descripcion' => 'Detalla terceros',
      'tipo' => 'select',
      'name' => 'detalla_terceros',
      'opciones' => '{"No":"No","Si":"Si"}',
      'value' => 'null',
      'atributos' => '{"class":"form-control"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-17 04:32:02',
      'updated_at' => '2019-07-17 04:32:02',
    ),
    472 => 
    array (
      'id' => 477,
      'descripcion' => 'Opciones',
      'tipo' => 'personalizado',
      'name' => 'input_textos_opciones',
      'opciones' => '',
      'value' => '<div style="border: solid 1px; padding: 10px; border-radius: 5px; overflow:auto; display:none;" id="input_textos_opciones">
<h4> Ingrese las opciones a las posibles respuestas </h4>
<div class="form-group">
    <label class="control-label col-sm-2"">a)</label>
    <div class="col-sm-10">
      <input type="text" class="form-control texto_opcion" name="texto_opcion" >
    </div>
  </div>
<div class="form-group">
    <label class="control-label col-sm-2"">b)</label>
    <div class="col-sm-10">
      <input type="text" class="form-control texto_opcion" name="texto_opcion" >
    </div>
  </div>
<div class="form-group">
    <label class="control-label col-sm-2"">c)</label>
    <div class="col-sm-10">
      <input type="text" class="form-control texto_opcion" name="texto_opcion" >
    </div>
  </div>
<div class="form-group">
    <label class="control-label col-sm-2"">d)</label>
    <div class="col-sm-10">
      <input type="text" class="form-control texto_opcion" name="texto_opcion" >
    </div>
  </div>
<div class="form-group">
    <label class="control-label col-sm-2"">e)</label>
    <div class="col-sm-10">
      <input type="text" class="form-control texto_opcion" name="texto_opcion" >
    </div>
  </div>
</div>',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-24 12:08:25',
      'updated_at' => '2019-07-24 16:06:00',
    ),
    473 => 
    array (
      'id' => 478,
      'descripcion' => 'Opciones',
      'tipo' => 'json_simple',
      'name' => 'opciones',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-07-26 10:38:15',
      'updated_at' => '2019-07-26 10:38:15',
    ),
    474 => 
    array (
      'id' => 479,
      'descripcion' => 'Activar resultados',
      'tipo' => 'select',
      'name' => 'activar_resultados',
      'opciones' => '{"0":"No","1":"Si"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-08-01 09:16:43',
      'updated_at' => '2019-08-01 09:16:43',
    ),
    475 => 
    array (
      'id' => 480,
      'descripcion' => 'Calificación',
      'tipo' => 'select',
      'name' => 'tipo_codigo',
      'opciones' => '{"positivo":"Positivo","negativo":"Negativo"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-08-07 04:48:30',
      'updated_at' => '2019-08-07 07:10:51',
    ),
    476 => 
    array (
      'id' => 481,
      'descripcion' => 'Orden listados',
      'tipo' => 'bsText',
      'name' => 'orden_listados',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-08-07 11:17:23',
      'updated_at' => '2019-08-07 11:17:23',
    ),
    477 => 
    array (
      'id' => 482,
      'descripcion' => 'Padre',
      'tipo' => 'select',
      'name' => 'padre_id',
      'opciones' => 'model_App\\PaginaWeb\\Seccion',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-08-13 11:56:53',
      'updated_at' => '2019-08-13 11:56:53',
    ),
    478 => 
    array (
      'id' => 483,
      'descripcion' => 'Parámetros',
      'tipo' => 'bsTextArea',
      'name' => 'parametros',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-08-13 09:46:22',
      'updated_at' => '2019-08-13 09:46:22',
    ),
    479 => 
    array (
      'id' => 484,
      'descripcion' => 'Sección padre',
      'tipo' => 'select',
      'name' => 'seccion_id',
      'opciones' => 'model_App\\PaginaWeb\\Seccion',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-08-13 09:46:22',
      'updated_at' => '2020-01-23 11:18:24',
    ),
    480 => 
    array (
      'id' => 485,
      'descripcion' => 'Tipo módulo',
      'tipo' => 'select',
      'name' => 'tipo_modulo',
      'opciones' => 'model_App\\PaginaWeb\\TipoModulo',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-08-13 09:46:22',
      'updated_at' => '2019-08-13 09:46:22',
    ),
    481 => 
    array (
      'id' => 486,
      'descripcion' => 'ID Tipo de módulo',
      'tipo' => 'hidden',
      'name' => 'tipo_modulo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-08-14 20:52:55',
      'updated_at' => '2019-08-14 20:52:55',
    ),
    482 => 
    array (
      'id' => 487,
      'descripcion' => 'Parámetros módulo',
      'tipo' => 'hidden',
      'name' => 'parametros',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-08-14 20:54:33',
      'updated_at' => '2020-01-23 12:02:46',
    ),
    483 => 
    array (
      'id' => 488,
      'descripcion' => 'Ruta Clase',
      'tipo' => 'bsText',
      'name' => 'modelo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-08-20 09:26:24',
      'updated_at' => '2019-09-05 08:52:10',
    ),
    484 => 
    array (
      'id' => 489,
      'descripcion' => 'Tipo reporte',
      'tipo' => 'select',
      'name' => 'tipo_reporte',
      'opciones' => '{"curso":"Curso","grado":"Grado","nivel_academico":"Nivel académico"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-08-24 21:26:33',
      'updated_at' => '2019-08-24 21:27:39',
    ),
    485 => 
    array (
      'id' => 490,
      'descripcion' => 'Mostrar foto',
      'tipo' => 'select',
      'name' => 'mostrar_foto',
      'opciones' => '{"0":"No","1":"Si"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-08-24 21:44:30',
      'updated_at' => '2019-08-24 21:44:30',
    ),
    486 => 
    array (
      'id' => 491,
      'descripcion' => 'Cantidad puestos',
      'tipo' => 'select',
      'name' => 'cantidad_puestos',
      'opciones' => '{"1":"1","2":"2","3":"3","4":"4","5":"5","6":"6","7":"7","8":"8","9":"9","10":"10","999":"Todos"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-08-24 21:47:10',
      'updated_at' => '2019-08-24 22:48:45',
    ),
    487 => 
    array (
      'id' => 492,
      'descripcion' => '12321',
      'tipo' => 'bsText',
      'name' => 'q12213',
      'opciones' => '',
      'value' => '13',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-06 08:41:25',
      'updated_at' => '2019-09-06 08:41:25',
    ),
    488 => 
    array (
      'id' => 494,
      'descripcion' => 'Aplicación asociada',
      'tipo' => 'select',
      'name' => 'core_app_id',
      'opciones' => 'model_App\\Sistema\\Aplicacion',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-13 21:10:56',
      'updated_at' => '2019-09-13 21:11:15',
    ),
    489 => 
    array (
      'id' => 495,
      'descripcion' => 'Dcto. pronto pago',
      'tipo' => 'select',
      'name' => 'encabezado_dcto_pp_id',
      'opciones' => 'model_App\\Ventas\\DescuentoPpEncabezado',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-15 09:46:22',
    ),
    490 => 
    array (
      'id' => 496,
      'descripcion' => 'Clase de cliente',
      'tipo' => 'select',
      'name' => 'clase_cliente_id',
      'opciones' => 'model_App\\Ventas\\ClaseCliente',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-15 09:46:22',
    ),
    491 => 
    array (
      'id' => 497,
      'descripcion' => 'Zona',
      'tipo' => 'select',
      'name' => 'zona_id',
      'opciones' => 'model_App\\Ventas\\Zona',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-15 09:46:22',
    ),
    492 => 
    array (
      'id' => 498,
      'descripcion' => 'Liquida IVA',
      'tipo' => 'select',
      'name' => 'liquida_impuestos',
      'opciones' => '{"1":"Si","0":"No"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2020-07-24 06:54:19',
    ),
    493 => 
    array (
      'id' => 499,
      'descripcion' => 'Condicion de pago',
      'tipo' => 'select',
      'name' => 'condicion_pago_id',
      'opciones' => 'model_App\\Ventas\\CondicionPago',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-16 01:16:55',
    ),
    494 => 
    array (
      'id' => 500,
      'descripcion' => 'Cupo crédito',
      'tipo' => 'bsText',
      'name' => 'cupo_credito',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-15 09:46:22',
    ),
    495 => 
    array (
      'id' => 501,
      'descripcion' => 'Bloquea por cupo',
      'tipo' => 'select',
      'name' => 'bloquea_por_cupo',
      'opciones' => '{"":"","1":"Si","0":"No"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-15 09:46:22',
    ),
    496 => 
    array (
      'id' => 502,
      'descripcion' => 'Bloquea por mora',
      'tipo' => 'select',
      'name' => 'bloquea_por_mora',
      'opciones' => '{"":"","1":"Si","0":"No"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-15 09:46:22',
    ),
    497 => 
    array (
      'id' => 503,
      'descripcion' => 'Cta x cobrar default',
      'tipo' => 'select',
      'name' => 'cta_x_cobrar_id',
      'opciones' => 'model_App\\Contabilidad\\ContabCuenta',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}	',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-16 01:32:07',
    ),
    498 => 
    array (
      'id' => 504,
      'descripcion' => 'Cta anticipo default',
      'tipo' => 'select',
      'name' => 'cta_anticipo_id',
      'opciones' => 'model_App\\Contabilidad\\ContabCuenta',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}	',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-16 01:32:20',
    ),
    499 => 
    array (
      'id' => 505,
      'descripcion' => 'Clase padre',
      'tipo' => 'select',
      'name' => 'clase_padre_id',
      'opciones' => 'model_App\\Ventas\\ClaseCliente',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-16 01:31:43',
    ),
    500 => 
    array (
      'id' => 506,
      'descripcion' => 'Días de plazo',
      'tipo' => 'bsText',
      'name' => 'dias_plazo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-15 09:46:22',
    ),
    501 => 
    array (
      'id' => 507,
      'descripcion' => 'Zona padre',
      'tipo' => 'select',
      'name' => 'zona_padre_id',
      'opciones' => 'model_App\\Ventas\\Zona',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-16 01:39:33',
    ),
    502 => 
    array (
      'id' => 508,
      'descripcion' => 'Equipo de ventas',
      'tipo' => 'select',
      'name' => 'equipo_ventas_id',
      'opciones' => 'model_App\\Ventas\\EquipoVentas',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-15 09:46:22',
    ),
    503 => 
    array (
      'id' => 509,
      'descripcion' => 'Clase vendedor',
      'tipo' => 'select',
      'name' => 'clase_vendedor_id',
      'opciones' => 'model_App\\Ventas\\ClaseVendedor',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-15 09:46:22',
    ),
    504 => 
    array (
      'id' => 510,
      'descripcion' => 'Equipo padre',
      'tipo' => 'select',
      'name' => 'equipo_padre_id',
      'opciones' => 'model_App\\Ventas\\EquipoVentas',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-15 09:46:22',
    ),
    505 => 
    array (
      'id' => 511,
      'descripcion' => 'Lista de descuentos',
      'tipo' => 'select',
      'name' => 'lista_descuentos_id',
      'opciones' => 'model_App\\Ventas\\ListaDctoEncabezado',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-15 09:46:22',
    ),
    506 => 
    array (
      'id' => 512,
      'descripcion' => 'fecha_activacion',
      'tipo' => 'fecha',
      'name' => 'fecha_activacion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-15 09:46:22',
    ),
    507 => 
    array (
      'id' => 513,
      'descripcion' => 'descuento1',
      'tipo' => 'bsText',
      'name' => 'descuento1',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-15 09:46:22',
    ),
    508 => 
    array (
      'id' => 514,
      'descripcion' => 'descuento2',
      'tipo' => 'bsText',
      'name' => 'descuento2',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-15 09:46:22',
    ),
    509 => 
    array (
      'id' => 515,
      'descripcion' => 'Lista de precios',
      'tipo' => 'select',
      'name' => 'lista_precios_id',
      'opciones' => 'model_App\\Ventas\\ListaPrecioEncabezado',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-15 09:46:22',
    ),
    510 => 
    array (
      'id' => 516,
      'descripcion' => 'Precio',
      'tipo' => 'bsText',
      'name' => 'precio',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-15 09:46:22',
    ),
    511 => 
    array (
      'id' => 517,
      'descripcion' => 'Impuestos incluidos',
      'tipo' => 'select',
      'name' => 'impuestos_incluidos',
      'opciones' => '{"1":"Si","0":"No"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-15 09:46:22',
    ),
    512 => 
    array (
      'id' => 518,
      'descripcion' => 'Encabezado descuento pp',
      'tipo' => 'select',
      'name' => 'encabezado_id',
      'opciones' => 'model_App\\Ventas\\DescuentoPpEncabezado',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-15 09:46:22',
    ),
    513 => 
    array (
      'id' => 519,
      'descripcion' => 'Días pronto pago',
      'tipo' => 'bsText',
      'name' => 'dias_pp',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-15 09:46:22',
    ),
    514 => 
    array (
      'id' => 520,
      'descripcion' => 'Porcentaje descuento',
      'tipo' => 'bsText',
      'name' => 'descuento_pp',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-15 09:46:22',
      'updated_at' => '2019-09-15 09:46:22',
    ),
    515 => 
    array (
      'id' => 521,
      'descripcion' => 'Vendedor',
      'tipo' => 'select',
      'name' => 'vendedor_id',
      'opciones' => 'model_App\\Ventas\\Vendedor',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-16 01:01:10',
      'updated_at' => '2019-09-16 01:01:10',
    ),
    516 => 
    array (
      'id' => 522,
      'descripcion' => 'Cliente',
      'tipo' => 'bsText',
      'name' => 'cliente_input',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '{"autocomplete":"off"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2019-09-17 10:29:40',
      'updated_at' => '2020-07-06 15:11:56',
    ),
    517 => 
    array (
      'id' => 523,
      'descripcion' => 'Forma de pago',
      'tipo' => 'select',
      'name' => 'forma_pago',
      'opciones' => '{"contado":"Contado","credito":"Crédito"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2019-09-17 10:31:15',
      'updated_at' => '2019-10-22 04:00:18',
    ),
    518 => 
    array (
      'id' => 524,
      'descripcion' => 'Orden de compras',
      'tipo' => 'bsText',
      'name' => 'orden_compras',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-17 10:35:24',
      'updated_at' => '2019-09-17 10:35:38',
    ),
    519 => 
    array (
      'id' => 525,
      'descripcion' => 'Tasa',
      'tipo' => 'bsText',
      'name' => 'tasa_impuesto',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-17 09:46:22',
      'updated_at' => '2019-09-17 09:46:22',
    ),
    520 => 
    array (
      'id' => 526,
      'descripcion' => 'Cta. ventas',
      'tipo' => 'select',
      'name' => 'cta_ventas_id',
      'opciones' => 'model_App\\Contabilidad\\ContabCuenta',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-17 09:46:22',
      'updated_at' => '2019-09-17 09:46:22',
    ),
    521 => 
    array (
      'id' => 527,
      'descripcion' => 'Cta. ventas devoluciones',
      'tipo' => 'select',
      'name' => 'cta_ventas_devol_id',
      'opciones' => 'model_App\\Contabilidad\\ContabCuenta',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-17 09:46:22',
      'updated_at' => '2019-09-17 09:46:22',
    ),
    522 => 
    array (
      'id' => 528,
      'descripcion' => 'Cta. compras',
      'tipo' => 'select',
      'name' => 'cta_compras_id',
      'opciones' => 'model_App\\Contabilidad\\ContabCuenta',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-17 09:46:22',
      'updated_at' => '2019-09-17 09:46:22',
    ),
    523 => 
    array (
      'id' => 529,
      'descripcion' => 'Cta. compras devoluciones',
      'tipo' => 'select',
      'name' => 'cta_compras_devol_id',
      'opciones' => 'model_App\\Contabilidad\\ContabCuenta',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-17 09:46:22',
      'updated_at' => '2019-09-17 09:46:22',
    ),
    524 => 
    array (
      'id' => 530,
      'descripcion' => 'Impuesto asociado',
      'tipo' => 'select',
      'name' => 'impuesto_id',
      'opciones' => 'model_App\\Contabilidad\\Impuesto',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-17 19:23:09',
      'updated_at' => '2019-09-17 19:24:07',
    ),
    525 => 
    array (
      'id' => 531,
      'descripcion' => 'Tercero',
      'tipo' => 'bsText',
      'name' => 'core_tercero_id',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '{"class":"form-control autocompletar","autocomplete":"off"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-26 09:24:20',
      'updated_at' => '2019-09-26 09:26:37',
    ),
    526 => 
    array (
      'id' => 532,
      'descripcion' => 'Pagina de inicio',
      'tipo' => 'select',
      'name' => 'pagina_inicio',
      'opciones' => '{"0":"No","1":"Si"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-09-27 04:22:54',
      'updated_at' => '2019-09-28 05:53:15',
    ),
    527 => 
    array (
      'id' => 533,
      'descripcion' => 'Comprador',
      'tipo' => 'select',
      'name' => 'comprador_id',
      'opciones' => 'model_App\\Compras\\Comprador',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-07 09:46:22',
      'updated_at' => '2019-10-07 09:46:22',
    ),
    528 => 
    array (
      'id' => 534,
      'descripcion' => 'Clase de proveedor',
      'tipo' => 'select',
      'name' => 'clase_proveedor_id',
      'opciones' => 'model_App\\Compras\\ClaseProveedor',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-07 09:46:22',
      'updated_at' => '2019-10-07 09:46:22',
    ),
    529 => 
    array (
      'id' => 535,
      'descripcion' => 'Cta x pagar default',
      'tipo' => 'select',
      'name' => 'cta_x_pagar_id',
      'opciones' => 'model_App\\Contabilidad\\ContabCuenta',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-07 09:46:22',
      'updated_at' => '2019-10-11 06:51:16',
    ),
    530 => 
    array (
      'id' => 536,
      'descripcion' => 'Proveedor',
      'tipo' => 'select',
      'name' => 'proveedor_id',
      'opciones' => 'model_App\\Compras\\Proveedor',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-07 09:46:22',
      'updated_at' => '2019-10-30 10:04:19',
    ),
    531 => 
    array (
      'id' => 537,
      'descripcion' => 'Condicion de pago',
      'tipo' => 'select',
      'name' => 'condicion_pago_id',
      'opciones' => 'model_App\\Compras\\CondicionPagoProv',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-07 10:56:11',
      'updated_at' => '2019-10-07 10:56:32',
    ),
    532 => 
    array (
      'id' => 538,
      'descripcion' => 'Proveedor',
      'tipo' => 'bsText',
      'name' => 'proveedor_input',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '{"autocomplete":"off"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-08 14:07:19',
      'updated_at' => '2020-07-09 14:14:15',
    ),
    533 => 
    array (
      'id' => 539,
      'descripcion' => 'Prefijo Doc. proveedor',
      'tipo' => 'bsText',
      'name' => 'doc_proveedor_prefijo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-08 15:33:34',
      'updated_at' => '2019-10-08 19:16:58',
    ),
    534 => 
    array (
      'id' => 540,
      'descripcion' => 'Consecutivo Doc. proveedor',
      'tipo' => 'bsText',
      'name' => 'doc_proveedor_consecutivo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-08 16:12:57',
      'updated_at' => '2019-10-08 18:38:17',
    ),
    535 => 
    array (
      'id' => 541,
      'descripcion' => 'Cuenta contrapartida',
      'tipo' => 'select',
      'name' => 'cta_contrapartida_id',
      'opciones' => 'model_App\\Contabilidad\\ContabCuenta',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-08 23:12:24',
      'updated_at' => '2019-10-08 23:12:40',
    ),
    536 => 
    array (
      'id' => 542,
      'descripcion' => 'Movimiento',
      'tipo' => 'select',
      'name' => 'movimiento',
      'opciones' => '{"entrada":"Entrada","salida":"Salida","transferencia":"Transferencia"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2019-10-08 23:14:48',
      'updated_at' => '2019-10-21 05:51:56',
    ),
    537 => 
    array (
      'id' => 543,
      'descripcion' => 'Cuenta de Inventarios/Gastos',
      'tipo' => 'select',
      'name' => 'cta_inventarios_id',
      'opciones' => 'model_App\\Contabilidad\\ContabCuenta',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-09 06:01:52',
      'updated_at' => '2020-02-22 10:21:13',
    ),
    538 => 
    array (
      'id' => 544,
      'descripcion' => 'Valor total documento',
      'tipo' => 'bsText',
      'name' => 'valor_documento',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-10 08:43:16',
      'updated_at' => '2019-10-10 08:46:44',
    ),
    539 => 
    array (
      'id' => 545,
      'descripcion' => 'Valor pagado',
      'tipo' => 'bsText',
      'name' => 'valor_pagado',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-10 08:43:43',
      'updated_at' => '2019-10-10 08:46:53',
    ),
    540 => 
    array (
      'id' => 546,
      'descripcion' => 'Saldo pendiente',
      'tipo' => 'bsText',
      'name' => 'saldo_pendiente',
      'opciones' => '{"class":"form-control","readonly":"readonly"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2019-10-10 08:44:12',
      'updated_at' => '2019-10-10 08:47:02',
    ),
    541 => 
    array (
      'id' => 547,
      'descripcion' => 'ID proveedor',
      'tipo' => 'hidden',
      'name' => 'proveedor_id',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-10 09:17:28',
      'updated_at' => '2019-10-10 09:17:53',
    ),
    542 => 
    array (
      'id' => 548,
      'descripcion' => 'ID Referencia Tercero',
      'tipo' => 'hidden',
      'name' => 'referencia_tercero_id',
      'opciones' => 'Depende del tipo de tercero, puede ser: proveedore_id, cliente_id, estudiante_id; inmueble_id, etc.',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2019-10-10 09:47:22',
      'updated_at' => '2019-10-10 09:48:44',
    ),
    543 => 
    array (
      'id' => 549,
      'descripcion' => 'Cuenta Ingresos',
      'tipo' => 'select',
      'name' => 'cta_ingresos_id',
      'opciones' => 'model_App\\Contabilidad\\ContabCuenta',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-11 05:25:42',
      'updated_at' => '2020-02-22 09:48:19',
    ),
    544 => 
    array (
      'id' => 550,
      'descripcion' => 'Mostrar en página web',
      'tipo' => 'select',
      'name' => 'mostrar_en_pagina_web',
      'opciones' => '{"1":"Si","0":"No"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-11 05:33:43',
      'updated_at' => '2019-10-11 05:34:08',
    ),
    545 => 
    array (
      'id' => 551,
      'descripcion' => 'Cta. contrapartida',
      'tipo' => 'select',
      'name' => 'cta_contrapartida_id',
      'opciones' => 'model_App\\Contabilidad\\ContabCuenta',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-11 06:55:24',
      'updated_at' => '2019-10-11 06:56:11',
    ),
    546 => 
    array (
      'id' => 552,
      'descripcion' => 'sucursal_id',
      'tipo' => 'select',
      'name' => 'sucursal_id',
      'opciones' => 'model_App\\Core\\Sucursal',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-15 09:46:22',
      'updated_at' => '2019-10-15 09:46:22',
    ),
    547 => 
    array (
      'id' => 553,
      'descripcion' => 'Tipo de documento',
      'tipo' => 'select',
      'name' => 'tipo_doc_app_id',
      'opciones' => 'model_App\\Core\\TipoDocApp',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-15 09:46:22',
      'updated_at' => '2019-10-15 09:46:22',
    ),
    548 => 
    array (
      'id' => 554,
      'descripcion' => 'Número Resolución',
      'tipo' => 'bsText',
      'name' => 'numero_resolucion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-15 09:46:22',
      'updated_at' => '2019-10-15 09:46:22',
    ),
    549 => 
    array (
      'id' => 555,
      'descripcion' => 'Número desde',
      'tipo' => 'bsText',
      'name' => 'numero_fact_inicial',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-15 09:46:22',
      'updated_at' => '2019-10-15 09:46:22',
    ),
    550 => 
    array (
      'id' => 556,
      'descripcion' => 'Número hasta',
      'tipo' => 'bsText',
      'name' => 'numero_fact_final',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-15 09:46:22',
      'updated_at' => '2019-10-15 09:46:22',
    ),
    551 => 
    array (
      'id' => 557,
      'descripcion' => 'Fecha expedición',
      'tipo' => 'fecha',
      'name' => 'fecha_expedicion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-15 09:46:22',
      'updated_at' => '2019-10-15 09:46:22',
    ),
    552 => 
    array (
      'id' => 558,
      'descripcion' => 'Fecha expiración',
      'tipo' => 'fecha',
      'name' => 'fecha_expiracion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-15 09:46:22',
      'updated_at' => '2019-10-15 09:46:22',
    ),
    553 => 
    array (
      'id' => 559,
      'descripcion' => 'Modalidad',
      'tipo' => 'select',
      'name' => 'modalidad',
      'opciones' => '{"Computador":"Computador","Electrónica":"Electrónica"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-15 09:46:22',
      'updated_at' => '2019-10-15 09:46:22',
    ),
    554 => 
    array (
      'id' => 560,
      'descripcion' => 'Tipo solicitud',
      'tipo' => 'select',
      'name' => 'tipo_solicitud',
      'opciones' => '{"Autorizada":"Autorizada","Habilitada":"Habilitada"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-15 09:46:22',
      'updated_at' => '2019-10-15 09:46:22',
    ),
    555 => 
    array (
      'id' => 561,
      'descripcion' => 'Cliente',
      'tipo' => 'select',
      'name' => 'cliente_id',
      'opciones' => 'model_App\\Ventas\\Cliente',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-20 23:24:57',
      'updated_at' => '2019-10-30 10:05:30',
    ),
    556 => 
    array (
      'id' => 562,
      'descripcion' => 'ID cliente',
      'tipo' => 'hidden',
      'name' => 'cliente_id',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-21 00:08:39',
      'updated_at' => '2019-10-21 00:08:51',
    ),
    557 => 
    array (
      'id' => 563,
      'descripcion' => 'Costo unitario',
      'tipo' => 'bsText',
      'name' => 'costo_unitario',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-21 04:45:37',
      'updated_at' => '2019-10-21 04:45:53',
    ),
    558 => 
    array (
      'id' => 564,
      'descripcion' => 'Costo total',
      'tipo' => 'bsText',
      'name' => 'costo_total',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-21 04:46:01',
      'updated_at' => '2019-10-21 04:46:20',
    ),
    559 => 
    array (
      'id' => 565,
      'descripcion' => 'Motivo de inventario',
      'tipo' => 'select',
      'name' => 'inv_motivo_id',
      'opciones' => 'model_App\\Inventarios\\InvMotivo',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-21 04:46:29',
      'updated_at' => '2019-10-21 04:49:13',
    ),
    560 => 
    array (
      'id' => 566,
      'descripcion' => 'Modelo CRUD',
      'tipo' => 'select',
      'name' => 'core_modelo_id',
      'opciones' => 'model_App\\Sistema\\Modelo',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-21 22:45:54',
      'updated_at' => '2019-10-21 22:46:40',
    ),
    561 => 
    array (
      'id' => 567,
      'descripcion' => '$100.000',
      'tipo' => 'bsText',
      'name' => 'billetes',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-29 00:04:42',
      'updated_at' => '2019-10-29 00:05:14',
    ),
    562 => 
    array (
      'id' => 568,
      'descripcion' => '$50.000',
      'tipo' => 'bsText',
      'name' => 'billetes',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-29 00:10:50',
      'updated_at' => '2019-10-29 00:10:59',
    ),
    563 => 
    array (
      'id' => 569,
      'descripcion' => '$20.000',
      'tipo' => 'bsText',
      'name' => 'billetes',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-29 00:11:02',
      'updated_at' => '2019-10-29 00:11:06',
    ),
    564 => 
    array (
      'id' => 570,
      'descripcion' => '$10.000',
      'tipo' => 'bsText',
      'name' => 'billetes',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-29 00:11:09',
      'updated_at' => '2019-10-29 00:11:13',
    ),
    565 => 
    array (
      'id' => 571,
      'descripcion' => '$5.000',
      'tipo' => 'bsText',
      'name' => 'billetes',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-29 00:11:16',
      'updated_at' => '2019-10-29 00:11:22',
    ),
    566 => 
    array (
      'id' => 572,
      'descripcion' => '$2.000',
      'tipo' => 'bsText',
      'name' => 'billetes',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-29 00:11:25',
      'updated_at' => '2019-10-29 00:11:30',
    ),
    567 => 
    array (
      'id' => 573,
      'descripcion' => '$1.000',
      'tipo' => 'bsText',
      'name' => 'billetes',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-29 00:11:33',
      'updated_at' => '2019-10-29 00:11:37',
    ),
    568 => 
    array (
      'id' => 574,
      'descripcion' => '$1.000',
      'tipo' => 'bsText',
      'name' => 'monedas',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-29 00:11:40',
      'updated_at' => '2019-10-29 00:11:53',
    ),
    569 => 
    array (
      'id' => 575,
      'descripcion' => '$100',
      'tipo' => 'bsText',
      'name' => 'monedas',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-29 00:11:55',
      'updated_at' => '2019-10-29 00:12:01',
    ),
    570 => 
    array (
      'id' => 576,
      'descripcion' => '$50',
      'tipo' => 'bsText',
      'name' => 'monedas',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-29 00:12:04',
      'updated_at' => '2019-10-29 00:12:15',
    ),
    571 => 
    array (
      'id' => 577,
      'descripcion' => '$500',
      'tipo' => 'bsText',
      'name' => 'monedas',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-29 00:12:18',
      'updated_at' => '2019-10-29 00:12:22',
    ),
    572 => 
    array (
      'id' => 578,
      'descripcion' => '$200',
      'tipo' => 'bsText',
      'name' => 'monedas',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-29 00:12:23',
      'updated_at' => '2019-10-29 00:12:28',
    ),
    573 => 
    array (
      'id' => 579,
      'descripcion' => 'Caja',
      'tipo' => 'select',
      'name' => 'teso_caja_id',
      'opciones' => 'model_App\\Tesoreria\\TesoCaja',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-29 00:24:35',
      'updated_at' => '2019-10-29 00:25:45',
    ),
    574 => 
    array (
      'id' => 580,
      'descripcion' => 'Observaciones',
      'tipo' => 'bsTextArea',
      'name' => 'observaciones',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-29 00:26:41',
      'updated_at' => '2019-10-29 00:26:58',
    ),
    575 => 
    array (
      'id' => 581,
      'descripcion' => 'Modenas',
      'tipo' => 'personalizado',
      'name' => 'lbl_monedas',
      'opciones' => '',
      'value' => '<div style="padding-left: 15px;">
    <h3>Monedas</h3> <hr>
</div>',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-29 00:28:34',
      'updated_at' => '2020-06-14 04:39:01',
    ),
    576 => 
    array (
      'id' => 582,
      'descripcion' => 'Billetes',
      'tipo' => 'personalizado',
      'name' => 'lbl_billetes',
      'opciones' => '',
      'value' => '<div style="padding-left: 15px;">
    <h3>Billetes</h3> <hr>
</div>',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-29 00:33:03',
      'updated_at' => '2020-06-14 04:38:44',
    ),
    577 => 
    array (
      'id' => 583,
      'descripcion' => 'Costo promedio',
      'tipo' => 'bsText',
      'name' => 'costo_promedio',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-10-31 07:12:01',
      'updated_at' => '2019-10-31 07:12:16',
    ),
    578 => 
    array (
      'id' => 584,
      'descripcion' => 'Estado Doc.',
      'tipo' => 'select',
      'name' => 'estado',
      'opciones' => '{"Pendiente":"Pendiente"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-11-01 23:52:55',
      'updated_at' => '2019-11-01 23:53:12',
    ),
    579 => 
    array (
      'id' => 585,
      'descripcion' => 'Empleado',
      'tipo' => 'select',
      'name' => 'core_tercero_id',
      'opciones' => 'model_App\\Nomina\\NomContrato',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-11-06 00:18:11',
      'updated_at' => '2019-11-06 00:18:42',
    ),
    580 => 
    array (
      'id' => 586,
      'descripcion' => 'Tercero',
      'tipo' => 'select',
      'name' => 'core_tercero_id',
      'opciones' => 'model_App\\Core\\TerceroNoCliente',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-11-19 22:43:53',
      'updated_at' => '2019-11-19 22:44:03',
    ),
    581 => 
    array (
      'id' => 587,
      'descripcion' => 'Tercero',
      'tipo' => 'select',
      'name' => 'core_tercero_id',
      'opciones' => 'model_App\\Core\\TerceroNoProveedor',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-11-19 22:44:06',
      'updated_at' => '2019-11-19 22:44:17',
    ),
    582 => 
    array (
      'id' => 588,
      'descripcion' => 'Tercero',
      'tipo' => 'select',
      'name' => 'core_tercero_id',
      'opciones' => 'model_App\\Core\\Tercero',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-11-25 03:34:56',
      'updated_at' => '2019-11-25 03:35:03',
    ),
    583 => 
    array (
      'id' => 589,
      'descripcion' => 'Periodicidad mensual',
      'tipo' => 'select',
      'name' => 'periodicidad_mensual',
      'opciones' => '{"1":"Una vez","2":"Dos veces","3":"Tres veces","4":"Cuatro veces"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-12-19 10:36:57',
      'updated_at' => '2019-12-19 10:36:57',
    ),
    584 => 
    array (
      'id' => 590,
      'descripcion' => 'Tiempo a liquidar',
      'tipo' => 'select',
      'name' => 'tiempo_a_liquidar',
      'opciones' => '{"240":"Un Mes","120":"Una Quincena","60":"Una Semana"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2019-12-20 10:36:57',
      'updated_at' => '2019-12-20 10:36:57',
    ),
    585 => 
    array (
      'id' => 591,
      'descripcion' => 'Perfil',
      'tipo' => 'select',
      'name' => 'role',
      'opciones' => '{"2":"Profesor","13":"Director de grupo"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-01-06 20:25:30',
      'updated_at' => '2020-01-06 20:26:42',
    ),
    586 => 
    array (
      'id' => 592,
      'descripcion' => 'Año lectivo',
      'tipo' => 'select',
      'name' => 'periodo_lectivo_id',
      'opciones' => 'model_App\\Matriculas\\PeriodoLectivo',
      'value' => 'null',
      'atributos' => '{"class":"form-control"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-01-07 01:00:05',
      'updated_at' => '2020-07-03 11:16:23',
    ),
    587 => 
    array (
      'id' => 593,
      'descripcion' => 'Escalas de valoración',
      'tipo' => 'escala_valoracion',
      'name' => 'escala_valoracion',
      'opciones' => 'model_App\\Calificaciones\\EscalaValoracion',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'Muestra todas un TextArea por cada escala de valoración del Periodo Lectivo Abierto (Cerrado = 0)',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-01-07 09:21:21',
      'updated_at' => '2020-01-07 09:22:44',
    ),
    588 => 
    array (
      'id' => 594,
      'descripcion' => 'Marco Ajax',
      'tipo' => 'frame_ajax',
      'name' => 'marco_ajax',
      'opciones' => 'null',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-01-08 08:50:42',
      'updated_at' => '2020-01-08 08:50:42',
    ),
    589 => 
    array (
      'id' => 595,
      'descripcion' => 'Escala de valoración',
      'tipo' => 'select',
      'name' => 'escala_valoracion_id',
      'opciones' => '{"":""}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2020-01-08 16:54:02',
      'updated_at' => '2020-01-08 19:00:39',
    ),
    590 => 
    array (
      'id' => 596,
      'descripcion' => 'Asignatura ',
      'tipo' => 'select',
      'name' => 'asignatura_id',
      'opciones' => 'model_App\\Calificaciones\\Asignatura',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2020-01-12 06:21:06',
      'updated_at' => '2020-01-12 06:21:13',
    ),
    591 => 
    array (
      'id' => 597,
      'descripcion' => 'Asistió?',
      'tipo' => 'bsRadioBtn',
      'name' => 'asistio',
      'opciones' => '{"Si":"Si","No":"No"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2020-01-12 06:22:35',
      'updated_at' => '2020-01-12 07:51:24',
    ),
    592 => 
    array (
      'id' => 598,
      'descripcion' => 'Anotación',
      'tipo' => 'bsTextArea',
      'name' => 'anotacion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-01-12 06:24:28',
      'updated_at' => '2020-01-12 06:29:17',
    ),
    593 => 
    array (
      'id' => 599,
      'descripcion' => 'Periodo',
      'tipo' => 'select',
      'name' => 'periodo_id',
      'opciones' => '{"":""}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2020-01-19 18:33:25',
      'updated_at' => '2020-01-19 18:33:40',
    ),
    594 => 
    array (
      'id' => 600,
      'descripcion' => 'Asignatura',
      'tipo' => 'select',
      'name' => 'asignatura_id',
      'opciones' => '{"":""}',
      'value' => 'null',
      'atributos' => '{"class":"select_dependientes_hijo"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2020-01-20 19:39:59',
      'updated_at' => '2020-01-20 19:40:10',
    ),
    595 => 
    array (
      'id' => 601,
      'descripcion' => 'Imagen horario',
      'tipo' => 'imagen',
      'name' => 'imagen',
      'opciones' => 'jpg,png,gif',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-01-21 07:48:22',
      'updated_at' => '2020-01-21 07:48:44',
    ),
    596 => 
    array (
      'id' => 602,
      'descripcion' => 'Plantilla plan de clases',
      'tipo' => 'select',
      'name' => 'plantilla_plan_clases_id',
      'opciones' => 'model_App\\AcademicoDocente\\PlanClaseEstrucPlantilla',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-01-24 09:46:22',
      'updated_at' => '2020-01-24 09:46:22',
    ),
    597 => 
    array (
      'id' => 603,
      'descripcion' => 'Semana académica',
      'tipo' => 'select',
      'name' => 'semana_calendario_id',
      'opciones' => 'model_App\\Core\\SemanasCalendario',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-01-24 09:46:22',
      'updated_at' => '2020-01-25 16:08:41',
    ),
    598 => 
    array (
      'id' => 604,
      'descripcion' => 'Profesor',
      'tipo' => 'select',
      'name' => 'user_id',
      'opciones' => 'model_App\\AcademicoDocente\\Profesor',
      'value' => 'null',
      'atributos' => '{"class":"form-control"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-01-24 09:46:22',
      'updated_at' => '2020-07-03 11:14:12',
    ),
    599 => 
    array (
      'id' => 605,
      'descripcion' => 'Plan de clases',
      'tipo' => 'select',
      'name' => 'plan_clase_encabezado_id',
      'opciones' => 'model_App\\AcademicoDocente\\PlanClaseEncabezado',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-01-24 09:46:22',
      'updated_at' => '2020-01-24 09:46:22',
    ),
    600 => 
    array (
      'id' => 606,
      'descripcion' => 'Elemento',
      'tipo' => 'select',
      'name' => 'plan_clase_estruc_elemento_id',
      'opciones' => 'model_App\\AcademicoDocente\\PlanClaseEstrucElemento',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-01-24 09:46:22',
      'updated_at' => '2020-01-24 09:46:22',
    ),
    601 => 
    array (
      'id' => 607,
      'descripcion' => 'Página destino',
      'tipo' => 'select',
      'name' => 'pagina_id',
      'opciones' => 'model_App\\PaginaWeb\\Pagina',
      'value' => 'null',
      'atributos' => '{"class":"select_dependientes_padre"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-01-25 17:32:53',
      'updated_at' => '2020-01-25 18:09:04',
    ),
    602 => 
    array (
      'id' => 608,
      'descripcion' => 'Título ventana',
      'tipo' => 'bsText',
      'name' => 'titulo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-01-25 17:40:17',
      'updated_at' => '2020-01-25 17:40:23',
    ),
    603 => 
    array (
      'id' => 609,
      'descripcion' => 'Sección destino',
      'tipo' => 'select',
      'name' => 'seccion_id',
      'opciones' => '{"":""}',
      'value' => 'null',
      'atributos' => '{"class":"select_dependientes_hijo"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-01-25 18:07:59',
      'updated_at' => '2020-01-25 18:30:36',
    ),
    604 => 
    array (
      'id' => 610,
      'descripcion' => 'Slug ID',
      'tipo' => 'hidden',
      'name' => 'slug_id',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-01-26 10:59:54',
      'updated_at' => '2020-01-26 13:53:48',
    ),
    605 => 
    array (
      'id' => 611,
      'descripcion' => 'URL externa',
      'tipo' => 'bsText',
      'name' => 'url_externa',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-01-26 12:58:26',
      'updated_at' => '2020-01-26 13:07:56',
    ),
    606 => 
    array (
      'id' => 612,
      'descripcion' => 'Es periodo de promedios?',
      'tipo' => 'select',
      'name' => 'periodo_de_promedios',
      'opciones' => '{"0":"No","1":"Si"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-01-29 17:31:11',
      'updated_at' => '2020-01-29 17:31:54',
    ),
    607 => 
    array (
      'id' => 613,
      'descripcion' => 'Estilo formato',
      'tipo' => 'select',
      'name' => 'estilo_formato',
      'opciones' => '{"certificado_notas_marca_agua":"Marca de agua","dos_escudos":"Dos Escudos"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-06 12:32:50',
      'updated_at' => '2020-02-06 12:35:23',
    ),
    608 => 
    array (
      'id' => 614,
      'descripcion' => 'Estudiante',
      'tipo' => 'select',
      'name' => 'estudiante_id',
      'opciones' => '{"":""}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2020-02-06 12:38:01',
      'updated_at' => '2020-02-06 12:39:05',
    ),
    609 => 
    array (
      'id' => 615,
      'descripcion' => 'Fecha expedición',
      'tipo' => 'date',
      'name' => 'fecha_expedicion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-06 13:23:43',
      'updated_at' => '2020-02-06 20:17:28',
    ),
    610 => 
    array (
      'id' => 616,
      'descripcion' => 'Firma autorizada #1',
      'tipo' => 'select',
      'name' => 'firma_autorizada_1',
      'opciones' => 'model_App\\Core\\FirmaAutorizada',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-06 20:11:13',
      'updated_at' => '2020-02-07 09:14:10',
    ),
    611 => 
    array (
      'id' => 617,
      'descripcion' => 'Firma autorizada #2',
      'tipo' => 'select',
      'name' => 'firma_autorizada_2',
      'opciones' => 'model_App\\Core\\FirmaAutorizada',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-06 20:12:44',
      'updated_at' => '2020-02-07 09:14:20',
    ),
    612 => 
    array (
      'id' => 618,
      'descripcion' => 'Observación adicional',
      'tipo' => 'textarea',
      'name' => 'observacion_adicional',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '{"cols":"25"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-06 21:06:18',
      'updated_at' => '2020-02-06 21:24:22',
    ),
    613 => 
    array (
      'id' => 619,
      'descripcion' => 'Detalla valores Matrícula/Pensión',
      'tipo' => 'select',
      'name' => 'detalla_valores_matricula_pension',
      'opciones' => '{"no":"No","ambos":"Ambos valores","solo_matricula":"Solo matrícula","solo_pension":"Solo pensión"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-07 07:43:04',
      'updated_at' => '2020-02-07 07:51:38',
    ),
    614 => 
    array (
      'id' => 620,
      'descripcion' => 'Nombre corto',
      'tipo' => 'bsText',
      'name' => 'nombre_corto',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-10 09:46:22',
      'updated_at' => '2020-02-10 09:46:22',
    ),
    615 => 
    array (
      'id' => 621,
      'descripcion' => 'Agrupación para cálculo',
      'tipo' => 'select',
      'name' => 'nom_agrupacion_id',
      'opciones' => 'model_App\\Nomina\\AgrupacionConcepto',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-10 12:29:15',
      'updated_at' => '2020-02-10 12:30:02',
    ),
    616 => 
    array (
      'id' => 622,
      'descripcion' => 'Grupo de inventarios',
      'tipo' => 'select',
      'name' => 'grupo_inventario_id',
      'opciones' => 'model_App\\Inventarios\\InvGrupo',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-17 05:02:02',
      'updated_at' => '2020-02-17 05:02:41',
    ),
    617 => 
    array (
      'id' => 623,
      'descripcion' => 'Número de columnas',
      'tipo' => 'select',
      'name' => 'numero_columnas',
      'opciones' => '{"1":"Una","2":"Dos","3":"Tres","4":"Cuatro","6":"Seis"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-17 05:06:55',
      'updated_at' => '2020-02-17 05:06:55',
    ),
    618 => 
    array (
      'id' => 624,
      'descripcion' => 'Grado',
      'tipo' => 'select',
      'name' => 'categoria_id',
      'opciones' => 'model_App\\Matriculas\\Grado',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'mockery: Usado en tabla inv_productos para modelo Items (188) tipo Libros de colegios. ',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-18 08:07:24',
      'updated_at' => '2020-02-18 08:10:43',
    ),
    619 => 
    array (
      'id' => 625,
      'descripcion' => 'Elemento biblioteca',
      'tipo' => 'select',
      'name' => 'precio_compra',
      'opciones' => '{"77.77":"Si"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2020-02-18 08:16:44',
      'updated_at' => '2020-02-18 08:17:22',
    ),
    620 => 
    array (
      'id' => 626,
      'descripcion' => 'Unidad medida 1',
      'tipo' => 'select',
      'name' => 'unidad_medida1',
      'opciones' => '{"UND":"UND"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2020-02-18 08:19:17',
      'updated_at' => '2020-02-18 08:19:37',
    ),
    621 => 
    array (
      'id' => 627,
      'descripcion' => 'Cantidad',
      'tipo' => 'bsText',
      'name' => 'referencia',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'mockery: Se almacena en inv_productos. Usado para generar un documento EA par cargue inicial, este campo se deja en cero al crear la EA.',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-18 08:20:37',
      'updated_at' => '2020-02-18 08:21:52',
    ),
    622 => 
    array (
      'id' => 628,
      'descripcion' => 'Editorial',
      'tipo' => 'bsText',
      'name' => 'unidad_medida2',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'mocking: se almacena en inv_productos. Usado en el modelo 188 para Elementos de biblioteca.',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-18 08:24:54',
      'updated_at' => '2020-02-18 19:01:40',
    ),
    623 => 
    array (
      'id' => 629,
      'descripcion' => 'Etiqueta a mostrar',
      'tipo' => 'text',
      'name' => 'etiqueta',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-18 08:55:56',
      'updated_at' => '2020-02-18 08:56:38',
    ),
    624 => 
    array (
      'id' => 630,
      'descripcion' => 'Fecha',
      'tipo' => 'date',
      'name' => 'fecha',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '{"class":"form-control"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-18 18:51:08',
      'updated_at' => '2020-02-18 18:51:23',
    ),
    625 => 
    array (
      'id' => 631,
      'descripcion' => 'Talla',
      'tipo' => 'bsText',
      'name' => 'unidad_medida2',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'mocking: se almacena en inv_productos. Usado en el modelo 189 para Productos.',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-18 19:02:03',
      'updated_at' => '2020-02-18 19:21:16',
    ),
    626 => 
    array (
      'id' => 632,
      'descripcion' => 'Talla',
      'tipo' => 'text',
      'name' => 'unidad_medida2',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => 'mocking: se almacena en inv_productos. Usado en el modelo 189 para Productos.',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-18 20:58:37',
      'updated_at' => '2020-02-18 20:58:43',
    ),
    627 => 
    array (
      'id' => 633,
      'descripcion' => 'Bodega',
      'tipo' => 'select',
      'name' => 'inv_bodega_id',
      'opciones' => 'model_App\\Inventarios\\InvBodega',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-18 21:05:30',
      'updated_at' => '2020-02-18 21:05:36',
    ),
    628 => 
    array (
      'id' => 634,
      'descripcion' => 'Fecha recepción mercancía',
      'tipo' => 'fecha',
      'name' => 'fecha_recepcion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-21 12:46:04',
      'updated_at' => '2020-02-21 12:46:49',
    ),
    629 => 
    array (
      'id' => 635,
      'descripcion' => 'Hora de entrega',
      'tipo' => 'hora',
      'name' => 'hora_entrega',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-23 13:18:33',
      'updated_at' => '2020-02-23 13:18:56',
    ),
    630 => 
    array (
      'id' => 636,
      'descripcion' => 'Detalla proveedores',
      'tipo' => 'select',
      'name' => 'detalla_proveedores',
      'opciones' => '{"0":"No","1":"Si"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-28 04:58:44',
      'updated_at' => '2020-02-28 04:59:06',
    ),
    631 => 
    array (
      'id' => 637,
      'descripcion' => 'IVA Incluido',
      'tipo' => 'select',
      'name' => 'iva_incluido',
      'opciones' => '{"0":"No","1":"Si"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-28 05:15:31',
      'updated_at' => '2020-02-28 05:16:06',
    ),
    632 => 
    array (
      'id' => 638,
      'descripcion' => '% proyección #1',
      'tipo' => 'text',
      'name' => 'porcentaje_proyeccion_1',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-28 10:10:15',
      'updated_at' => '2020-02-28 10:10:15',
    ),
    633 => 
    array (
      'id' => 639,
      'descripcion' => '% proyección #2',
      'tipo' => 'text',
      'name' => 'porcentaje_proyeccion_2',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-28 10:10:29',
      'updated_at' => '2020-02-28 10:10:36',
    ),
    634 => 
    array (
      'id' => 640,
      'descripcion' => '% proyección #3',
      'tipo' => 'text',
      'name' => 'porcentaje_proyeccion_3',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-28 10:10:43',
      'updated_at' => '2020-02-28 10:10:50',
    ),
    635 => 
    array (
      'id' => 641,
      'descripcion' => '% proyección #4',
      'tipo' => 'text',
      'name' => 'porcentaje_proyeccion_4',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-02-28 10:11:02',
      'updated_at' => '2020-02-28 10:11:09',
    ),
    636 => 
    array (
      'id' => 642,
      'descripcion' => 'Anotación',
      'tipo' => 'bsTextArea',
      'name' => 'anotacion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-01 09:46:22',
      'updated_at' => '2020-03-01 09:46:22',
    ),
    637 => 
    array (
      'id' => 643,
      'descripcion' => 'Asignatura',
      'tipo' => 'select',
      'name' => 'id_asignatura',
      'opciones' => '{"":""}',
      'value' => 'null',
      'atributos' => '{"class":"select_dependientes_hijo"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2020-03-01 05:58:03',
      'updated_at' => '2020-03-01 05:58:07',
    ),
    638 => 
    array (
      'id' => 644,
      'descripcion' => 'Subsidio de transporte',
      'tipo' => 'select',
      'name' => 'liquida_subsidio_transporte',
      'opciones' => '{"1":"Si, si < 2 SMMLV","2":"Si (siempre)","3":"No (nunca)"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-15 09:46:22',
      'updated_at' => '2020-03-15 09:46:22',
    ),
    639 => 
    array (
      'id' => 645,
      'descripcion' => 'Planilla PILA',
      'tipo' => 'select',
      'name' => 'planilla_pila_id',
      'opciones' => 'model_App\\Nomina\\PlanillaPila',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-15 09:46:22',
      'updated_at' => '2020-03-15 09:46:22',
    ),
    640 => 
    array (
      'id' => 646,
      'descripcion' => 'Es pasante SENA',
      'tipo' => 'select',
      'name' => 'es_pasante_sena',
      'opciones' => '{"0":"No","1":"Si"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-15 09:46:22',
      'updated_at' => '2020-03-15 09:46:22',
    ),
    641 => 
    array (
      'id' => 647,
      'descripcion' => 'Fondo cesantías',
      'tipo' => 'select',
      'name' => 'entidad_cesantias_id',
      'opciones' => 'model_App\\Nomina\\NomEntidad',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-15 09:46:22',
      'updated_at' => '2020-03-15 10:24:43',
    ),
    642 => 
    array (
      'id' => 648,
      'descripcion' => 'Caja compensación',
      'tipo' => 'select',
      'name' => 'entidad_caja_compensacion_id',
      'opciones' => 'model_App\\Nomina\\NomEntidad',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-15 09:46:22',
      'updated_at' => '2020-03-15 10:24:34',
    ),
    643 => 
    array (
      'id' => 649,
      'descripcion' => 'Tipo liquidación',
      'tipo' => 'select',
      'name' => 'tipo_liquidacion',
      'opciones' => '{"normal":"Normal","selectiva":"Selectiva","terminacion_contrato":"Terminación de contrato"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-15 09:46:22',
      'updated_at' => '2020-03-15 11:39:13',
    ),
    644 => 
    array (
      'id' => 650,
      'descripcion' => 'Tercero',
      'tipo' => 'select',
      'name' => 'core_tercero_id',
      'opciones' => 'model_App\\Core\\TerceroNoContrato',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2020-03-15 10:19:03',
      'updated_at' => '2020-03-15 10:21:44',
    ),
    645 => 
    array (
      'id' => 651,
      'descripcion' => 'Número interno',
      'tipo' => 'bsText',
      'name' => 'int',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    646 => 
    array (
      'id' => 652,
      'descripcion' => 'Placa',
      'tipo' => 'bsText',
      'name' => 'placa',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    647 => 
    array (
      'id' => 653,
      'descripcion' => 'Número vinculación',
      'tipo' => 'bsText',
      'name' => 'numero_vin',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    648 => 
    array (
      'id' => 654,
      'descripcion' => 'Número motor',
      'tipo' => 'bsText',
      'name' => 'numero_motor',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    649 => 
    array (
      'id' => 655,
      'descripcion' => 'Modelo',
      'tipo' => 'bsText',
      'name' => 'modelo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    650 => 
    array (
      'id' => 656,
      'descripcion' => 'Marca',
      'tipo' => 'bsText',
      'name' => 'marca',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    651 => 
    array (
      'id' => 657,
      'descripcion' => 'Clase',
      'tipo' => 'bsText',
      'name' => 'clase',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    652 => 
    array (
      'id' => 658,
      'descripcion' => 'Color',
      'tipo' => 'bsText',
      'name' => 'color',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    653 => 
    array (
      'id' => 659,
      'descripcion' => 'Cilindraje',
      'tipo' => 'bsText',
      'name' => 'cilindraje',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    654 => 
    array (
      'id' => 660,
      'descripcion' => 'Capacidad',
      'tipo' => 'bsText',
      'name' => 'capacidad',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    655 => 
    array (
      'id' => 661,
      'descripcion' => 'Fecha control kilometraje',
      'tipo' => 'fecha',
      'name' => 'fecha_control_kilometraje',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    656 => 
    array (
      'id' => 662,
      'descripcion' => 'Propietario',
      'tipo' => 'select',
      'name' => 'propietario_id',
      'opciones' => 'model_App\\Contratotransporte\\Propietario',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    657 => 
    array (
      'id' => 663,
      'descripcion' => 'genera_planilla',
      'tipo' => 'select',
      'name' => 'genera_planilla',
      'opciones' => '{"SI":"SI","NO":"NO"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-25 15:28:54',
    ),
    658 => 
    array (
      'id' => 664,
      'descripcion' => 'Tercero',
      'tipo' => 'select',
      'name' => 'tercero_id',
      'opciones' => 'model_App\\Core\\Tercero',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    659 => 
    array (
      'id' => 665,
      'descripcion' => 'Numeración',
      'tipo' => 'bsText',
      'name' => 'numeracion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    660 => 
    array (
      'id' => 666,
      'descripcion' => 'Texto',
      'tipo' => 'bsTextArea',
      'name' => 'texto',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    661 => 
    array (
      'id' => 667,
      'descripcion' => 'Plantilla artículo',
      'tipo' => 'select',
      'name' => 'plantillaarticulo_id',
      'opciones' => 'model_App\\Contratotransporte\\Plantillaarticulo',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    662 => 
    array (
      'id' => 668,
      'descripcion' => 'Teléfono',
      'tipo' => 'bsText',
      'name' => 'telefono',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    663 => 
    array (
      'id' => 669,
      'descripcion' => 'E-mail',
      'tipo' => 'bsText',
      'name' => 'correo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    664 => 
    array (
      'id' => 670,
      'descripcion' => 'Firma',
      'tipo' => 'bsText',
      'name' => 'firma',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    665 => 
    array (
      'id' => 671,
      'descripcion' => 'Pie de página',
      'tipo' => 'bsText',
      'name' => 'pie_pagina1',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    666 => 
    array (
      'id' => 672,
      'descripcion' => 'Tíitulo al respaldo',
      'tipo' => 'bsText',
      'name' => 'titulo_atras',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    667 => 
    array (
      'id' => 673,
      'descripcion' => 'Conductor',
      'tipo' => 'select',
      'name' => 'conductor_id',
      'opciones' => 'model_App\\Contratotransporte\\Conductor',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    668 => 
    array (
      'id' => 674,
      'descripcion' => 'Planilla',
      'tipo' => 'select',
      'name' => 'planillac_id',
      'opciones' => 'model_App\\Contratotransporte\\Planillac',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    669 => 
    array (
      'id' => 675,
      'descripcion' => 'Contrato',
      'tipo' => 'select',
      'name' => 'contrato_id',
      'opciones' => 'model_App\\Contratotransporte\\Contrato',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    670 => 
    array (
      'id' => 676,
      'descripcion' => 'Razón social',
      'tipo' => 'bsText',
      'name' => 'razon_social',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    671 => 
    array (
      'id' => 677,
      'descripcion' => 'NIT',
      'tipo' => 'bsText',
      'name' => 'nit',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    672 => 
    array (
      'id' => 678,
      'descripcion' => 'Convenio',
      'tipo' => 'bsText',
      'name' => 'convenio',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    673 => 
    array (
      'id' => 679,
      'descripcion' => 'Plantilla',
      'tipo' => 'select',
      'name' => 'plantilla_id',
      'opciones' => 'model_App\\Contratotransporte\\Plantilla',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    674 => 
    array (
      'id' => 680,
      'descripcion' => 'Plantilla artículo numeral',
      'tipo' => 'select',
      'name' => 'plantillaarticulonumeral_id',
      'opciones' => 'model_App\\Contratotransporte\\Plantillaarticulonumeral',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    675 => 
    array (
      'id' => 681,
      'descripcion' => 'Campo',
      'tipo' => 'bsText',
      'name' => 'campo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    676 => 
    array (
      'id' => 682,
      'descripcion' => 'Valor',
      'tipo' => 'bsText',
      'name' => 'valor',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    677 => 
    array (
      'id' => 683,
      'descripcion' => 'Mantenimiento',
      'tipo' => 'select',
      'name' => 'mantenimiento_id',
      'opciones' => 'model_App\\Contratotransporte\\Mantenimiento',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    678 => 
    array (
      'id' => 684,
      'descripcion' => 'Fecha suceso',
      'tipo' => 'fecha',
      'name' => 'fecha_suceso',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    679 => 
    array (
      'id' => 685,
      'descripcion' => 'Reporte',
      'tipo' => 'bsTextArea',
      'name' => 'reporte',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    680 => 
    array (
      'id' => 686,
      'descripcion' => 'Sede',
      'tipo' => 'bsText',
      'name' => 'sede',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    681 => 
    array (
      'id' => 687,
      'descripcion' => 'Revisado',
      'tipo' => 'select',
      'name' => 'revisado',
      'opciones' => '{"":"","1":"Si","0":"No"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    682 => 
    array (
      'id' => 688,
      'descripcion' => 'Anio/periodo',
      'tipo' => 'select',
      'name' => 'anioperiodo_id',
      'opciones' => 'model_App\\Contratotransporte\\Anioperiodo',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    683 => 
    array (
      'id' => 689,
      'descripcion' => 'Tarjeta operación',
      'tipo' => 'select',
      'name' => 'tarjeta_operacion',
      'opciones' => '{"SI":"SI","NO":"NO"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-25 15:28:17',
    ),
    684 => 
    array (
      'id' => 690,
      'descripcion' => 'Documento',
      'tipo' => 'bsText',
      'name' => 'documento',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    685 => 
    array (
      'id' => 691,
      'descripcion' => 'Recurso',
      'tipo' => 'file',
      'name' => 'recurso',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-25 15:29:42',
    ),
    686 => 
    array (
      'id' => 692,
      'descripcion' => 'No. documento',
      'tipo' => 'bsText',
      'name' => 'nro_documento',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    687 => 
    array (
      'id' => 693,
      'descripcion' => 'Vigencia inicio',
      'tipo' => 'fecha',
      'name' => 'vigencia_inicio',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-25 15:31:24',
    ),
    688 => 
    array (
      'id' => 694,
      'descripcion' => 'Vigencia fin',
      'tipo' => 'fecha',
      'name' => 'vigencia_fin',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-25 15:31:37',
    ),
    689 => 
    array (
      'id' => 695,
      'descripcion' => 'Vehículo',
      'tipo' => 'select',
      'name' => 'vehiculo_id',
      'opciones' => 'model_App\\Contratotransporte\\Vehiculo',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    690 => 
    array (
      'id' => 696,
      'descripcion' => 'Licencia',
      'tipo' => 'select',
      'name' => 'licencia',
      'opciones' => '{"SI":"SI","NO":"NO"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-25 15:30:36',
    ),
    691 => 
    array (
      'id' => 697,
      'descripcion' => 'Identificación',
      'tipo' => 'bsText',
      'name' => 'identificacion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    692 => 
    array (
      'id' => 698,
      'descripcion' => 'Persona',
      'tipo' => 'bsText',
      'name' => 'persona',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    693 => 
    array (
      'id' => 699,
      'descripcion' => 'Código',
      'tipo' => 'bsText',
      'name' => 'codigo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    694 => 
    array (
      'id' => 700,
      'descripcion' => 'Versión',
      'tipo' => 'bsText',
      'name' => 'version',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    695 => 
    array (
      'id' => 701,
      'descripcion' => 'Número contrato',
      'tipo' => 'bsText',
      'name' => 'numero_contrato',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    696 => 
    array (
      'id' => 702,
      'descripcion' => 'Objeto',
      'tipo' => 'bsText',
      'name' => 'objeto',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    697 => 
    array (
      'id' => 703,
      'descripcion' => 'Origen',
      'tipo' => 'bsText',
      'name' => 'origen',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    698 => 
    array (
      'id' => 704,
      'descripcion' => 'Destino',
      'tipo' => 'bsText',
      'name' => 'destino',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    699 => 
    array (
      'id' => 705,
      'descripcion' => 'Fecha fin',
      'tipo' => 'fecha',
      'name' => 'fecha_fin',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    700 => 
    array (
      'id' => 706,
      'descripcion' => 'Valor contrato',
      'tipo' => 'bsText',
      'name' => 'valor_contrato',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    701 => 
    array (
      'id' => 707,
      'descripcion' => 'Valor empresa',
      'tipo' => 'bsText',
      'name' => 'valor_empresa',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    702 => 
    array (
      'id' => 708,
      'descripcion' => 'Valor propietario',
      'tipo' => 'bsText',
      'name' => 'valor_propietario',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    703 => 
    array (
      'id' => 709,
      'descripcion' => 'Dirección notificación',
      'tipo' => 'bsText',
      'name' => 'direccion_notificacion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    704 => 
    array (
      'id' => 710,
      'descripcion' => 'Teléfono notificación',
      'tipo' => 'bsText',
      'name' => 'telefono_notificacion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    705 => 
    array (
      'id' => 711,
      'descripcion' => 'Día contrato',
      'tipo' => 'bsText',
      'name' => 'dia_contrato',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    706 => 
    array (
      'id' => 712,
      'descripcion' => 'Mes contrato',
      'tipo' => 'bsText',
      'name' => 'mes_contrato',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    707 => 
    array (
      'id' => 713,
      'descripcion' => 'Pie uno',
      'tipo' => 'bsText',
      'name' => 'pie_uno',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    708 => 
    array (
      'id' => 714,
      'descripcion' => 'Pie dos',
      'tipo' => 'bsText',
      'name' => 'pie_dos',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    709 => 
    array (
      'id' => 715,
      'descripcion' => 'Pie tres',
      'tipo' => 'bsText',
      'name' => 'pie_tres',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    710 => 
    array (
      'id' => 716,
      'descripcion' => 'Pie cuatro',
      'tipo' => 'bsText',
      'name' => 'pie_cuatro',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    711 => 
    array (
      'id' => 717,
      'descripcion' => 'Contratante',
      'tipo' => 'select',
      'name' => 'contratante_id',
      'opciones' => 'model_App\\Contratotransporte\\Contratante',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    712 => 
    array (
      'id' => 718,
      'descripcion' => 'Año',
      'tipo' => 'select',
      'name' => 'anio_id',
      'opciones' => 'model_App\\Contratotransporte\\Anio',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    713 => 
    array (
      'id' => 719,
      'descripcion' => 'Inicio',
      'tipo' => 'bsText',
      'name' => 'inicio',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    714 => 
    array (
      'id' => 720,
      'descripcion' => 'Fin',
      'tipo' => 'bsText',
      'name' => 'fin',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    715 => 
    array (
      'id' => 721,
      'descripcion' => 'Año',
      'tipo' => 'bsText',
      'name' => 'anio',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-24 09:46:22',
      'updated_at' => '2020-03-24 09:46:22',
    ),
    716 => 
    array (
      'id' => 722,
      'descripcion' => 'Estado',
      'tipo' => 'select',
      'name' => 'estado',
      'opciones' => '{"SI":"SI","NO":"NO"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-03-27 07:20:30',
      'updated_at' => '2020-03-27 07:20:51',
    ),
    717 => 
    array (
      'id' => 723,
      'descripcion' => 'Agrupar por',
      'tipo' => 'select',
      'name' => 'agrupar_por',
      'opciones' => '{"cliente_id":"Clientes","inv_producto_id":"Productos","tasa_impuesto":"Tasa de impuesto","clase_cliente_id":"Clase de clientes","core_tipo_transaccion_id":"Tipo de transacción"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-04-02 10:35:48',
      'updated_at' => '2020-04-02 10:41:37',
    ),
    718 => 
    array (
      'id' => 724,
      'descripcion' => 'Detalla productos',
      'tipo' => 'select',
      'name' => 'detalla_productos',
      'opciones' => '{"0":"No","1":"Si"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-04-02 10:42:32',
      'updated_at' => '2020-04-02 10:42:50',
    ),
    719 => 
    array (
      'id' => 725,
      'descripcion' => 'Detalla clientes',
      'tipo' => 'select',
      'name' => 'detalla_clientes',
      'opciones' => '{"0":"No","1":"Si"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-04-02 10:44:39',
      'updated_at' => '2020-04-02 10:44:53',
    ),
    720 => 
    array (
      'id' => 726,
      'descripcion' => 'Nombre',
      'tipo' => 'bsText',
      'name' => 'nombre',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-05-01 05:18:03',
      'updated_at' => '2020-05-01 05:18:33',
    ),
    721 => 
    array (
      'id' => 727,
      'descripcion' => 'Imagen previsualización',
      'tipo' => 'file',
      'name' => 'preview',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-05-01 05:19:19',
      'updated_at' => '2020-05-01 05:19:52',
    ),
    722 => 
    array (
      'id' => 728,
      'descripcion' => 'Widget',
      'tipo' => 'select',
      'name' => 'widget_id',
      'opciones' => 'model_App\\web\\Widget',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-05-02 09:14:33',
      'updated_at' => '2020-05-02 09:15:26',
    ),
    723 => 
    array (
      'id' => 729,
      'descripcion' => 'Links',
      'tipo' => 'bsTextArea',
      'name' => 'links',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-05-02 09:20:34',
      'updated_at' => '2020-05-02 09:20:34',
    ),
    724 => 
    array (
      'id' => 730,
      'descripcion' => 'Estilos',
      'tipo' => 'bsTextArea',
      'name' => 'estilos',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-05-02 09:20:37',
      'updated_at' => '2020-05-02 09:20:46',
    ),
    725 => 
    array (
      'id' => 731,
      'descripcion' => 'Scripts',
      'tipo' => 'bsTextArea',
      'name' => 'scripts',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-05-02 09:20:52',
      'updated_at' => '2020-05-02 09:21:00',
    ),
    726 => 
    array (
      'id' => 732,
      'descripcion' => 'Nombre o Razón social',
      'tipo' => 'bsText',
      'name' => 'nombre',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-05-04 08:35:53',
      'updated_at' => '2020-05-04 08:36:25',
    ),
    727 => 
    array (
      'id' => 733,
      'descripcion' => 'Tipo documento identidad',
      'tipo' => 'select',
      'name' => 'tipo_documento_identidad',
      'opciones' => '{"Cédula de ciudadanía":"Cédula de ciudadanía","NIT":"NIT","Cédula de extranjería":"Cédula de extranjería","Pasaporte":"Pasaporte"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-05-04 08:36:28',
      'updated_at' => '2020-05-04 08:52:11',
    ),
    728 => 
    array (
      'id' => 734,
      'descripcion' => 'Tipo de solicitud',
      'tipo' => 'select',
      'name' => 'tipo_solicitud',
      'opciones' => '{"Petición":"Petición","Queja":"Queja","Reclamo":"Reclamo","Felicitación":"Felicitación","Sugerencia":"Sugerencia"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-05-04 08:38:22',
      'updated_at' => '2020-05-04 09:01:13',
    ),
    729 => 
    array (
      'id' => 735,
      'descripcion' => 'Comentario',
      'tipo' => 'bsTextArea',
      'name' => 'comentario',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-05-04 09:01:44',
      'updated_at' => '2020-05-04 09:02:00',
    ),
    730 => 
    array (
      'id' => 736,
      'descripcion' => 'Apellido',
      'tipo' => 'bsText',
      'name' => 'apellido',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-05-09 10:20:41',
      'updated_at' => '2020-05-09 10:20:55',
    ),
    731 => 
    array (
      'id' => 737,
      'descripcion' => 'aceptar_terminos_y_condiciones',
      'tipo' => 'personalizado',
      'name' => 'aceptar_terminos_y_condiciones',
      'opciones' => '',
      'value' => '<div id="aceptar_terminos_y_condiciones">
<label for="aceptar_terminos_y_condiciones" class="col-sm-3 control-label"> </label>
<div class="checkbox">
<label>
<input type="checkbox" checked name="aceptar_terminos_y_condiciones" id="aceptar_terminos_y_condiciones" reuired="required">Acepto términos, condiciones y autorizo el tratamiento de mis datos personales según las siguientes condiciones:  
</label>
</div>
<div class="form-group">
                    <a href="#" target="_blank">Ver condiciones</a></div>
</div>',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2020-05-09 10:23:56',
      'updated_at' => '2020-05-09 10:29:45',
    ),
    732 => 
    array (
      'id' => 738,
      'descripcion' => 'Fuente del efectivo',
      'tipo' => 'select',
      'name' => 'fuente_efectivo',
      'opciones' => '{"Propia":"Propia","Prestamo financiero":"Prestamo financiero (CxP)"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-05-11 06:18:11',
      'updated_at' => '2020-05-11 06:20:16',
    ),
    733 => 
    array (
      'id' => 739,
      'descripcion' => 'Tipo de reporte',
      'tipo' => 'select',
      'name' => 'tipo_reporte',
      'opciones' => '{"0":"Resumen de recaudos","1":"Cartera Vencida"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-05-13 10:01:24',
      'updated_at' => '2020-05-13 10:01:24',
    ),
    734 => 
    array (
      'id' => 740,
      'descripcion' => 'Tercero',
      'tipo' => 'input_lista_sugerencias',
      'name' => 'core_tercero_id',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '{"class":"form-control text_input_sugerencias","data-url_busqueda":"core_consultar_terceros_v2","data-clase_modelo":"App\\\\Core\\\\Tercero"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-05-30 04:02:10',
      'updated_at' => '2020-05-30 04:27:18',
    ),
    735 => 
    array (
      'id' => 741,
      'descripcion' => 'Lbl Signos o síntomas',
      'tipo' => 'personalizado',
      'name' => 'lbl_signos_o_sintomas',
      'opciones' => '',
      'value' => '<div style="padding-left: 15px;">
    <h3>Signos o síntomas</h3> <hr>
</div>',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-06-01 11:47:37',
      'updated_at' => '2020-06-14 04:38:12',
    ),
    736 => 
    array (
      'id' => 742,
      'descripcion' => 'Separador5',
      'tipo' => 'personalizado',
      'name' => 'separador5',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-06-01 11:55:20',
      'updated_at' => '2020-06-01 11:55:29',
    ),
    737 => 
    array (
      'id' => 743,
      'descripcion' => 'Examen Médico',
      'tipo' => 'select',
      'name' => 'examen_id',
      'opciones' => 'model_App\\Salud\\ExamenMedicoConsulta',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2020-06-02 22:45:16',
      'updated_at' => '2020-06-03 00:53:26',
    ),
    738 => 
    array (
      'id' => 744,
      'descripcion' => 'Diagnóstico',
      'tipo' => 'bsCheckBox',
      'name' => 'diagnostico',
      'opciones' => '{"":"","Hipermetropía":"Hipermetropía","Astigmatismo":"Astigmatismo","Miopía":"Miopía","Presbicia":"Presbicia","Emétrope":"Emétrope"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-06-02 23:10:01',
      'updated_at' => '2020-06-03 10:57:29',
    ),
    739 => 
    array (
      'id' => 745,
      'descripcion' => 'Filtro',
      'tipo' => 'bsText',
      'name' => 'filtro',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-06-02 23:11:12',
      'updated_at' => '2020-06-02 23:11:35',
    ),
    740 => 
    array (
      'id' => 746,
      'descripcion' => 'Tercero',
      'tipo' => 'select',
      'name' => 'tercero_id',
      'opciones' => 'model_App\\Core\\TerceroNoPropietario',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2020-06-08 09:50:38',
      'updated_at' => '2020-06-08 10:57:35',
    ),
    741 => 
    array (
      'id' => 747,
      'descripcion' => 'Tercero',
      'tipo' => 'select',
      'name' => 'tercero_id',
      'opciones' => 'model_App\\Core\\TerceroNoConductor',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2020-06-08 10:05:20',
      'updated_at' => '2020-06-08 10:57:20',
    ),
    742 => 
    array (
      'id' => 748,
      'descripcion' => 'Email / Usuario',
      'tipo' => 'bsText',
      'name' => 'email',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '{"type":"email"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-06-08 10:14:25',
      'updated_at' => '2020-06-08 10:15:10',
    ),
    743 => 
    array (
      'id' => 749,
      'descripcion' => 'Visualizar preinforme',
      'tipo' => 'select',
      'name' => 'core_campo_id-ID',
      'opciones' => '{"0":"No","1":"Si"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-06-13 11:38:25',
      'updated_at' => '2020-06-13 11:39:19',
    ),
    744 => 
    array (
      'id' => 750,
      'descripcion' => 'Descripción',
      'tipo' => 'bsText',
      'name' => 'core_campo_id-ID',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-06-14 16:56:19',
      'updated_at' => '2020-06-14 20:53:43',
    ),
    745 => 
    array (
      'id' => 751,
      'descripcion' => 'Remitido por',
      'tipo' => 'select',
      'name' => 'remitido_por',
      'opciones' => 'model_App\\Salud\\EntidadRemisora',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-06-14 21:13:34',
      'updated_at' => '2020-06-14 21:13:34',
    ),
    746 => 
    array (
      'id' => 752,
      'descripcion' => 'ID User',
      'tipo' => 'constante',
      'name' => 'created_by',
      'opciones' => '',
      'value' => 'created_by',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2020-06-23 23:52:07',
      'updated_at' => '2020-06-23 23:53:23',
    ),
    747 => 
    array (
      'id' => 753,
      'descripcion' => 'Descripción',
      'tipo' => 'bsText',
      'name' => 'descripcion',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-06-25 12:45:53',
      'updated_at' => '2020-06-29 20:13:50',
    ),
    748 => 
    array (
      'id' => 754,
      'descripcion' => 'Valor fijo',
      'tipo' => 'bsText',
      'name' => 'valor_fijo',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-06-29 20:26:18',
      'updated_at' => '2020-06-29 20:26:18',
    ),
    749 => 
    array (
      'id' => 755,
      'descripcion' => 'Concepto',
      'tipo' => 'select',
      'name' => 'nom_concepto_id',
      'opciones' => 'model_App\\Nomina\\ConceptoCuota',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-06-30 19:10:13',
      'updated_at' => '2020-06-30 19:10:28',
    ),
    750 => 
    array (
      'id' => 756,
      'descripcion' => 'Cajero',
      'tipo' => 'bsLabel',
      'name' => 'cajero_id',
      'opciones' => 'model_App\\User',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-07-06 09:46:22',
      'updated_at' => '2020-07-06 09:46:22',
    ),
    751 => 
    array (
      'id' => 757,
      'descripcion' => 'PDV',
      'tipo' => 'bsLabel',
      'name' => 'pdv_id',
      'opciones' => 'model_App\\VentasPos\\Pdv',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-07-06 09:46:22',
      'updated_at' => '2020-07-06 09:46:22',
    ),
    752 => 
    array (
      'id' => 758,
      'descripcion' => 'Efectivo base',
      'tipo' => 'bsText',
      'name' => 'efectivo_base',
      'opciones' => '',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-07-06 09:46:22',
      'updated_at' => '2020-07-06 09:46:22',
    ),
    753 => 
    array (
      'id' => 759,
      'descripcion' => 'Bodega por defecto',
      'tipo' => 'select',
      'name' => 'bodega_default_id',
      'opciones' => 'model_App\\Inventarios\\InvBodega',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-07-06 09:46:22',
      'updated_at' => '2020-07-06 09:46:22',
    ),
    754 => 
    array (
      'id' => 760,
      'descripcion' => 'Caja por defecto',
      'tipo' => 'select',
      'name' => 'caja_default_id',
      'opciones' => 'model_App\\Tesoreria\\TesoCaja',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-07-06 09:46:22',
      'updated_at' => '2020-07-06 09:46:22',
    ),
    755 => 
    array (
      'id' => 761,
      'descripcion' => 'Cajero por defecto',
      'tipo' => 'select',
      'name' => 'cajero_default_id',
      'opciones' => 'model_App\\VentasPos\\Cajero',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-07-06 09:46:22',
      'updated_at' => '2020-07-06 14:53:29',
    ),
    756 => 
    array (
      'id' => 762,
      'descripcion' => 'Cliente por defecto',
      'tipo' => 'select',
      'name' => 'cliente_default_id',
      'opciones' => 'model_App\\Ventas\\Cliente',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-07-06 09:46:22',
      'updated_at' => '2020-07-06 09:46:22',
    ),
    757 => 
    array (
      'id' => 763,
      'descripcion' => 'Tipo Documento por defecto',
      'tipo' => 'select',
      'name' => 'tipo_doc_app_default_id',
      'opciones' => 'model_App\\Core\\TipoDocApp',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 1,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-07-06 09:46:22',
      'updated_at' => '2020-07-06 09:46:22',
    ),
    758 => 
    array (
      'id' => 764,
      'descripcion' => 'Grupo sanguíneo',
      'tipo' => 'select',
      'name' => 'grupo_sanguineo',
      'opciones' => 'model_App\\Salud\\GrupoSanguineo',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-07-10 05:02:27',
      'updated_at' => '2020-07-10 05:03:02',
    ),
    759 => 
    array (
      'id' => 765,
      'descripcion' => 'Tipo',
      'tipo' => 'constante',
      'name' => 'tipo',
      'opciones' => '',
      'value' => 'servicio',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 0,
      'unico' => 0,
      'created_at' => '2020-07-11 05:37:09',
      'updated_at' => '2020-07-11 05:37:09',
    ),
    760 => 
    array (
      'id' => 766,
      'descripcion' => 'Incluir saldo anterior',
      'tipo' => 'select',
      'name' => 'incluir_saldo_anterior',
      'opciones' => '{"0":"No","1":"Si"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-07-25 11:06:07',
      'updated_at' => '2020-07-25 11:06:07',
    ),
    761 => 
    array (
      'id' => 767,
      'descripcion' => 'Cuenta contable',
      'tipo' => 'select',
      'name' => 'contab_cuenta_id',
      'opciones' => 'model_App\\Contabilidad\\ContabCuenta',
      'value' => 'null',
      'atributos' => '{"class":"combobox"}',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-07-28 12:31:46',
      'updated_at' => '2020-07-28 12:31:54',
    ),
    762 => 
    array (
      'id' => 768,
      'descripcion' => 'Mostrar Items sin movimiento',
      'tipo' => 'select',
      'name' => 'mostrar_items_sin_movimiento',
      'opciones' => '{"0":"No","1":"Si"}',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-08-04 10:41:01',
      'updated_at' => '2020-08-04 10:41:34',
    ),
    763 => 
    array (
      'id' => 769,
      'descripcion' => 'Fecha de cumpleaños',
      'tipo' => 'fecha',
      'name' => 'direccion2',
      'opciones' => ' ',
      'value' => 'null',
      'atributos' => '',
      'definicion' => '',
      'requerido' => 0,
      'editable' => 1,
      'unico' => 0,
      'created_at' => '2020-08-09 08:25:29',
      'updated_at' => '2020-08-09 08:26:04',
    ),
  ),
  'sys_modelo_tiene_campos' => 
  array (
    0 => 
    array (
      'id' => 1,
      'orden' => 4,
      'core_modelo_id' => 1,
      'core_campo_id' => 1,
    ),
    1 => 
    array (
      'id' => 2,
      'orden' => 6,
      'core_modelo_id' => 1,
      'core_campo_id' => 2,
    ),
    2 => 
    array (
      'id' => 3,
      'orden' => 4,
      'core_modelo_id' => 2,
      'core_campo_id' => 2,
    ),
    3 => 
    array (
      'id' => 4,
      'orden' => 0,
      'core_modelo_id' => 3,
      'core_campo_id' => 2,
    ),
    4 => 
    array (
      'id' => 5,
      'orden' => 2,
      'core_modelo_id' => 4,
      'core_campo_id' => 2,
    ),
    5 => 
    array (
      'id' => 6,
      'orden' => 7,
      'core_modelo_id' => 6,
      'core_campo_id' => 2,
    ),
    6 => 
    array (
      'id' => 7,
      'orden' => 3,
      'core_modelo_id' => 11,
      'core_campo_id' => 2,
    ),
    7 => 
    array (
      'id' => 8,
      'orden' => 3,
      'core_modelo_id' => 12,
      'core_campo_id' => 2,
    ),
    8 => 
    array (
      'id' => 9,
      'orden' => 2,
      'core_modelo_id' => 14,
      'core_campo_id' => 2,
    ),
    9 => 
    array (
      'id' => 10,
      'orden' => 1,
      'core_modelo_id' => 15,
      'core_campo_id' => 2,
    ),
    10 => 
    array (
      'id' => 11,
      'orden' => 2,
      'core_modelo_id' => 16,
      'core_campo_id' => 2,
    ),
    11 => 
    array (
      'id' => 12,
      'orden' => 1,
      'core_modelo_id' => 20,
      'core_campo_id' => 2,
    ),
    12 => 
    array (
      'id' => 13,
      'orden' => 1,
      'core_modelo_id' => 21,
      'core_campo_id' => 2,
    ),
    13 => 
    array (
      'id' => 14,
      'orden' => 4,
      'core_modelo_id' => 22,
      'core_campo_id' => 2,
    ),
    14 => 
    array (
      'id' => 15,
      'orden' => 4,
      'core_modelo_id' => 24,
      'core_campo_id' => 2,
    ),
    15 => 
    array (
      'id' => 16,
      'orden' => 6,
      'core_modelo_id' => 26,
      'core_campo_id' => 2,
    ),
    16 => 
    array (
      'id' => 17,
      'orden' => 2,
      'core_modelo_id' => 27,
      'core_campo_id' => 2,
    ),
    17 => 
    array (
      'id' => 18,
      'orden' => 1,
      'core_modelo_id' => 30,
      'core_campo_id' => 2,
    ),
    18 => 
    array (
      'id' => 19,
      'orden' => 2,
      'core_modelo_id' => 32,
      'core_campo_id' => 2,
    ),
    19 => 
    array (
      'id' => 20,
      'orden' => 1,
      'core_modelo_id' => 34,
      'core_campo_id' => 2,
    ),
    20 => 
    array (
      'id' => 21,
      'orden' => 1,
      'core_modelo_id' => 35,
      'core_campo_id' => 2,
    ),
    21 => 
    array (
      'id' => 22,
      'orden' => 1,
      'core_modelo_id' => 37,
      'core_campo_id' => 2,
    ),
    22 => 
    array (
      'id' => 23,
      'orden' => 1,
      'core_modelo_id' => 38,
      'core_campo_id' => 2,
    ),
    23 => 
    array (
      'id' => 24,
      'orden' => 7,
      'core_modelo_id' => 41,
      'core_campo_id' => 2,
    ),
    24 => 
    array (
      'id' => 25,
      'orden' => 1,
      'core_modelo_id' => 42,
      'core_campo_id' => 2,
    ),
    25 => 
    array (
      'id' => 26,
      'orden' => 1,
      'core_modelo_id' => 44,
      'core_campo_id' => 2,
    ),
    26 => 
    array (
      'id' => 27,
      'orden' => 2,
      'core_modelo_id' => 45,
      'core_campo_id' => 2,
    ),
    27 => 
    array (
      'id' => 28,
      'orden' => 3,
      'core_modelo_id' => 48,
      'core_campo_id' => 2,
    ),
    28 => 
    array (
      'id' => 29,
      'orden' => 4,
      'core_modelo_id' => 49,
      'core_campo_id' => 2,
    ),
    29 => 
    array (
      'id' => 30,
      'orden' => 2,
      'core_modelo_id' => 50,
      'core_campo_id' => 2,
    ),
    30 => 
    array (
      'id' => 31,
      'orden' => 2,
      'core_modelo_id' => 53,
      'core_campo_id' => 2,
    ),
    31 => 
    array (
      'id' => 32,
      'orden' => 2,
      'core_modelo_id' => 60,
      'core_campo_id' => 2,
    ),
    32 => 
    array (
      'id' => 33,
      'orden' => 1,
      'core_modelo_id' => 61,
      'core_campo_id' => 2,
    ),
    33 => 
    array (
      'id' => 34,
      'orden' => 1,
      'core_modelo_id' => 62,
      'core_campo_id' => 2,
    ),
    34 => 
    array (
      'id' => 35,
      'orden' => 2,
      'core_modelo_id' => 65,
      'core_campo_id' => 2,
    ),
    35 => 
    array (
      'id' => 36,
      'orden' => 2,
      'core_modelo_id' => 68,
      'core_campo_id' => 2,
    ),
    36 => 
    array (
      'id' => 37,
      'orden' => 4,
      'core_modelo_id' => 69,
      'core_campo_id' => 2,
    ),
    37 => 
    array (
      'id' => 39,
      'orden' => 2,
      'core_modelo_id' => 81,
      'core_campo_id' => 2,
    ),
    38 => 
    array (
      'id' => 40,
      'orden' => 4,
      'core_modelo_id' => 84,
      'core_campo_id' => 2,
    ),
    39 => 
    array (
      'id' => 41,
      'orden' => 1,
      'core_modelo_id' => 92,
      'core_campo_id' => 2,
    ),
    40 => 
    array (
      'id' => 42,
      'orden' => 2,
      'core_modelo_id' => 94,
      'core_campo_id' => 2,
    ),
    41 => 
    array (
      'id' => 43,
      'orden' => 1,
      'core_modelo_id' => 97,
      'core_campo_id' => 2,
    ),
    42 => 
    array (
      'id' => 44,
      'orden' => 1,
      'core_modelo_id' => 98,
      'core_campo_id' => 2,
    ),
    43 => 
    array (
      'id' => 45,
      'orden' => 1,
      'core_modelo_id' => 99,
      'core_campo_id' => 2,
    ),
    44 => 
    array (
      'id' => 46,
      'orden' => 1,
      'core_modelo_id' => 106,
      'core_campo_id' => 2,
    ),
    45 => 
    array (
      'id' => 47,
      'orden' => 1,
      'core_modelo_id' => 107,
      'core_campo_id' => 2,
    ),
    46 => 
    array (
      'id' => 48,
      'orden' => 1,
      'core_modelo_id' => 108,
      'core_campo_id' => 2,
    ),
    47 => 
    array (
      'id' => 49,
      'orden' => 8,
      'core_modelo_id' => 109,
      'core_campo_id' => 2,
    ),
    48 => 
    array (
      'id' => 50,
      'orden' => 2,
      'core_modelo_id' => 114,
      'core_campo_id' => 2,
    ),
    49 => 
    array (
      'id' => 52,
      'orden' => 4,
      'core_modelo_id' => 118,
      'core_campo_id' => 2,
    ),
    50 => 
    array (
      'id' => 53,
      'orden' => 2,
      'core_modelo_id' => 119,
      'core_campo_id' => 2,
    ),
    51 => 
    array (
      'id' => 54,
      'orden' => 2,
      'core_modelo_id' => 120,
      'core_campo_id' => 2,
    ),
    52 => 
    array (
      'id' => 55,
      'orden' => 2,
      'core_modelo_id' => 121,
      'core_campo_id' => 2,
    ),
    53 => 
    array (
      'id' => 56,
      'orden' => 2,
      'core_modelo_id' => 122,
      'core_campo_id' => 2,
    ),
    54 => 
    array (
      'id' => 57,
      'orden' => 2,
      'core_modelo_id' => 123,
      'core_campo_id' => 2,
    ),
    55 => 
    array (
      'id' => 58,
      'orden' => 2,
      'core_modelo_id' => 124,
      'core_campo_id' => 2,
    ),
    56 => 
    array (
      'id' => 59,
      'orden' => 2,
      'core_modelo_id' => 126,
      'core_campo_id' => 2,
    ),
    57 => 
    array (
      'id' => 60,
      'orden' => 2,
      'core_modelo_id' => 128,
      'core_campo_id' => 2,
    ),
    58 => 
    array (
      'id' => 61,
      'orden' => 2,
      'core_modelo_id' => 130,
      'core_campo_id' => 2,
    ),
    59 => 
    array (
      'id' => 62,
      'orden' => 2,
      'core_modelo_id' => 132,
      'core_campo_id' => 2,
    ),
    60 => 
    array (
      'id' => 63,
      'orden' => 2,
      'core_modelo_id' => 133,
      'core_campo_id' => 2,
    ),
    61 => 
    array (
      'id' => 64,
      'orden' => 2,
      'core_modelo_id' => 135,
      'core_campo_id' => 2,
    ),
    62 => 
    array (
      'id' => 65,
      'orden' => 2,
      'core_modelo_id' => 136,
      'core_campo_id' => 2,
    ),
    63 => 
    array (
      'id' => 66,
      'orden' => 2,
      'core_modelo_id' => 137,
      'core_campo_id' => 2,
    ),
    64 => 
    array (
      'id' => 67,
      'orden' => 2,
      'core_modelo_id' => 140,
      'core_campo_id' => 2,
    ),
    65 => 
    array (
      'id' => 68,
      'orden' => 2,
      'core_modelo_id' => 143,
      'core_campo_id' => 2,
    ),
    66 => 
    array (
      'id' => 69,
      'orden' => 2,
      'core_modelo_id' => 145,
      'core_campo_id' => 2,
    ),
    67 => 
    array (
      'id' => 70,
      'orden' => 8,
      'core_modelo_id' => 1,
      'core_campo_id' => 3,
    ),
    68 => 
    array (
      'id' => 71,
      'orden' => 0,
      'core_modelo_id' => 19,
      'core_campo_id' => 3,
    ),
    69 => 
    array (
      'id' => 72,
      'orden' => 1,
      'core_modelo_id' => 39,
      'core_campo_id' => 3,
    ),
    70 => 
    array (
      'id' => 73,
      'orden' => 1,
      'core_modelo_id' => 49,
      'core_campo_id' => 3,
    ),
    71 => 
    array (
      'id' => 74,
      'orden' => 1,
      'core_modelo_id' => 94,
      'core_campo_id' => 3,
    ),
    72 => 
    array (
      'id' => 75,
      'orden' => 10,
      'core_modelo_id' => 1,
      'core_campo_id' => 4,
    ),
    73 => 
    array (
      'id' => 76,
      'orden' => 1,
      'core_modelo_id' => 2,
      'core_campo_id' => 6,
    ),
    74 => 
    array (
      'id' => 77,
      'orden' => 1,
      'core_modelo_id' => 16,
      'core_campo_id' => 6,
    ),
    75 => 
    array (
      'id' => 78,
      'orden' => 2,
      'core_modelo_id' => 26,
      'core_campo_id' => 6,
    ),
    76 => 
    array (
      'id' => 79,
      'orden' => 2,
      'core_modelo_id' => 118,
      'core_campo_id' => 6,
    ),
    77 => 
    array (
      'id' => 80,
      'orden' => 3,
      'core_modelo_id' => 2,
      'core_campo_id' => 7,
    ),
    78 => 
    array (
      'id' => 81,
      'orden' => 3,
      'core_modelo_id' => 4,
      'core_campo_id' => 7,
    ),
    79 => 
    array (
      'id' => 82,
      'orden' => 1,
      'core_modelo_id' => 5,
      'core_campo_id' => 7,
    ),
    80 => 
    array (
      'id' => 83,
      'orden' => 2,
      'core_modelo_id' => 9,
      'core_campo_id' => 7,
    ),
    81 => 
    array (
      'id' => 84,
      'orden' => 5,
      'core_modelo_id' => 17,
      'core_campo_id' => 8,
    ),
    82 => 
    array (
      'id' => 85,
      'orden' => 4,
      'core_modelo_id' => 18,
      'core_campo_id' => 8,
    ),
    83 => 
    array (
      'id' => 86,
      'orden' => 9,
      'core_modelo_id' => 25,
      'core_campo_id' => 8,
    ),
    84 => 
    array (
      'id' => 87,
      'orden' => 6,
      'core_modelo_id' => 46,
      'core_campo_id' => 8,
    ),
    85 => 
    array (
      'id' => 88,
      'orden' => 5,
      'core_modelo_id' => 47,
      'core_campo_id' => 8,
    ),
    86 => 
    array (
      'id' => 89,
      'orden' => 5,
      'core_modelo_id' => 51,
      'core_campo_id' => 8,
    ),
    87 => 
    array (
      'id' => 90,
      'orden' => 5,
      'core_modelo_id' => 52,
      'core_campo_id' => 8,
    ),
    88 => 
    array (
      'id' => 91,
      'orden' => 10,
      'core_modelo_id' => 54,
      'core_campo_id' => 8,
    ),
    89 => 
    array (
      'id' => 92,
      'orden' => 6,
      'core_modelo_id' => 59,
      'core_campo_id' => 8,
    ),
    90 => 
    array (
      'id' => 93,
      'orden' => 5,
      'core_modelo_id' => 90,
      'core_campo_id' => 8,
    ),
    91 => 
    array (
      'id' => 94,
      'orden' => 16,
      'core_modelo_id' => 139,
      'core_campo_id' => 8,
    ),
    92 => 
    array (
      'id' => 95,
      'orden' => 6,
      'core_modelo_id' => 2,
      'core_campo_id' => 9,
    ),
    93 => 
    array (
      'id' => 96,
      'orden' => 7,
      'core_modelo_id' => 2,
      'core_campo_id' => 10,
    ),
    94 => 
    array (
      'id' => 97,
      'orden' => 3,
      'core_modelo_id' => 14,
      'core_campo_id' => 10,
    ),
    95 => 
    array (
      'id' => 98,
      'orden' => 5,
      'core_modelo_id' => 20,
      'core_campo_id' => 10,
    ),
    96 => 
    array (
      'id' => 100,
      'orden' => 14,
      'core_modelo_id' => 26,
      'core_campo_id' => 10,
    ),
    97 => 
    array (
      'id' => 101,
      'orden' => 6,
      'core_modelo_id' => 30,
      'core_campo_id' => 10,
    ),
    98 => 
    array (
      'id' => 102,
      'orden' => 3,
      'core_modelo_id' => 98,
      'core_campo_id' => 10,
    ),
    99 => 
    array (
      'id' => 103,
      'orden' => 4,
      'core_modelo_id' => 102,
      'core_campo_id' => 10,
    ),
    100 => 
    array (
      'id' => 104,
      'orden' => 4,
      'core_modelo_id' => 104,
      'core_campo_id' => 10,
    ),
    101 => 
    array (
      'id' => 105,
      'orden' => 3,
      'core_modelo_id' => 105,
      'core_campo_id' => 10,
    ),
    102 => 
    array (
      'id' => 106,
      'orden' => 4,
      'core_modelo_id' => 109,
      'core_campo_id' => 10,
    ),
    103 => 
    array (
      'id' => 107,
      'orden' => 10,
      'core_modelo_id' => 123,
      'core_campo_id' => 10,
    ),
    104 => 
    array (
      'id' => 108,
      'orden' => 9,
      'core_modelo_id' => 2,
      'core_campo_id' => 11,
    ),
    105 => 
    array (
      'id' => 109,
      'orden' => 5,
      'core_modelo_id' => 2,
      'core_campo_id' => 12,
    ),
    106 => 
    array (
      'id' => 111,
      'orden' => 2,
      'core_modelo_id' => 2,
      'core_campo_id' => 14,
    ),
    107 => 
    array (
      'id' => 112,
      'orden' => 1,
      'core_modelo_id' => 12,
      'core_campo_id' => 17,
    ),
    108 => 
    array (
      'id' => 113,
      'orden' => 2,
      'core_modelo_id' => 5,
      'core_campo_id' => 18,
    ),
    109 => 
    array (
      'id' => 114,
      'orden' => 20,
      'core_modelo_id' => 6,
      'core_campo_id' => 18,
    ),
    110 => 
    array (
      'id' => 115,
      'orden' => 20,
      'core_modelo_id' => 7,
      'core_campo_id' => 18,
    ),
    111 => 
    array (
      'id' => 116,
      'orden' => 20,
      'core_modelo_id' => 41,
      'core_campo_id' => 18,
    ),
    112 => 
    array (
      'id' => 117,
      'orden' => 11,
      'core_modelo_id' => 66,
      'core_campo_id' => 18,
    ),
    113 => 
    array (
      'id' => 118,
      'orden' => 10,
      'core_modelo_id' => 93,
      'core_campo_id' => 18,
    ),
    114 => 
    array (
      'id' => 119,
      'orden' => 9,
      'core_modelo_id' => 95,
      'core_campo_id' => 18,
    ),
    115 => 
    array (
      'id' => 120,
      'orden' => 22,
      'core_modelo_id' => 138,
      'core_campo_id' => 18,
    ),
    116 => 
    array (
      'id' => 121,
      'orden' => 20,
      'core_modelo_id' => 146,
      'core_campo_id' => 18,
    ),
    117 => 
    array (
      'id' => 122,
      'orden' => 4,
      'core_modelo_id' => 5,
      'core_campo_id' => 19,
    ),
    118 => 
    array (
      'id' => 123,
      'orden' => 6,
      'core_modelo_id' => 5,
      'core_campo_id' => 20,
    ),
    119 => 
    array (
      'id' => 124,
      'orden' => 14,
      'core_modelo_id' => 1,
      'core_campo_id' => 22,
    ),
    120 => 
    array (
      'id' => 125,
      'orden' => 22,
      'core_modelo_id' => 6,
      'core_campo_id' => 22,
    ),
    121 => 
    array (
      'id' => 126,
      'orden' => 22,
      'core_modelo_id' => 7,
      'core_campo_id' => 22,
    ),
    122 => 
    array (
      'id' => 127,
      'orden' => 3,
      'core_modelo_id' => 8,
      'core_campo_id' => 22,
    ),
    123 => 
    array (
      'id' => 128,
      'orden' => 7,
      'core_modelo_id' => 11,
      'core_campo_id' => 22,
    ),
    124 => 
    array (
      'id' => 129,
      'orden' => 10,
      'core_modelo_id' => 12,
      'core_campo_id' => 22,
    ),
    125 => 
    array (
      'id' => 130,
      'orden' => 4,
      'core_modelo_id' => 14,
      'core_campo_id' => 22,
    ),
    126 => 
    array (
      'id' => 131,
      'orden' => 2,
      'core_modelo_id' => 15,
      'core_campo_id' => 22,
    ),
    127 => 
    array (
      'id' => 132,
      'orden' => 7,
      'core_modelo_id' => 20,
      'core_campo_id' => 22,
    ),
    128 => 
    array (
      'id' => 133,
      'orden' => 2,
      'core_modelo_id' => 21,
      'core_campo_id' => 22,
    ),
    129 => 
    array (
      'id' => 134,
      'orden' => 30,
      'core_modelo_id' => 22,
      'core_campo_id' => 22,
    ),
    130 => 
    array (
      'id' => 135,
      'orden' => 20,
      'core_modelo_id' => 24,
      'core_campo_id' => 22,
    ),
    131 => 
    array (
      'id' => 136,
      'orden' => 16,
      'core_modelo_id' => 26,
      'core_campo_id' => 22,
    ),
    132 => 
    array (
      'id' => 137,
      'orden' => 3,
      'core_modelo_id' => 27,
      'core_campo_id' => 22,
    ),
    133 => 
    array (
      'id' => 138,
      'orden' => 8,
      'core_modelo_id' => 30,
      'core_campo_id' => 22,
    ),
    134 => 
    array (
      'id' => 139,
      'orden' => 7,
      'core_modelo_id' => 31,
      'core_campo_id' => 22,
    ),
    135 => 
    array (
      'id' => 140,
      'orden' => 3,
      'core_modelo_id' => 32,
      'core_campo_id' => 22,
    ),
    136 => 
    array (
      'id' => 141,
      'orden' => 6,
      'core_modelo_id' => 33,
      'core_campo_id' => 22,
    ),
    137 => 
    array (
      'id' => 142,
      'orden' => 5,
      'core_modelo_id' => 36,
      'core_campo_id' => 22,
    ),
    138 => 
    array (
      'id' => 143,
      'orden' => 3,
      'core_modelo_id' => 37,
      'core_campo_id' => 22,
    ),
    139 => 
    array (
      'id' => 144,
      'orden' => 14,
      'core_modelo_id' => 38,
      'core_campo_id' => 22,
    ),
    140 => 
    array (
      'id' => 145,
      'orden' => 11,
      'core_modelo_id' => 39,
      'core_campo_id' => 22,
    ),
    141 => 
    array (
      'id' => 146,
      'orden' => 22,
      'core_modelo_id' => 41,
      'core_campo_id' => 22,
    ),
    142 => 
    array (
      'id' => 147,
      'orden' => 4,
      'core_modelo_id' => 42,
      'core_campo_id' => 22,
    ),
    143 => 
    array (
      'id' => 148,
      'orden' => 4,
      'core_modelo_id' => 45,
      'core_campo_id' => 22,
    ),
    144 => 
    array (
      'id' => 149,
      'orden' => 10,
      'core_modelo_id' => 49,
      'core_campo_id' => 22,
    ),
    145 => 
    array (
      'id' => 150,
      'orden' => 6,
      'core_modelo_id' => 50,
      'core_campo_id' => 22,
    ),
    146 => 
    array (
      'id' => 151,
      'orden' => 10,
      'core_modelo_id' => 55,
      'core_campo_id' => 22,
    ),
    147 => 
    array (
      'id' => 152,
      'orden' => 8,
      'core_modelo_id' => 60,
      'core_campo_id' => 22,
    ),
    148 => 
    array (
      'id' => 153,
      'orden' => 6,
      'core_modelo_id' => 65,
      'core_campo_id' => 22,
    ),
    149 => 
    array (
      'id' => 154,
      'orden' => 7,
      'core_modelo_id' => 67,
      'core_campo_id' => 22,
    ),
    150 => 
    array (
      'id' => 155,
      'orden' => 4,
      'core_modelo_id' => 68,
      'core_campo_id' => 22,
    ),
    151 => 
    array (
      'id' => 157,
      'orden' => 6,
      'core_modelo_id' => 71,
      'core_campo_id' => 22,
    ),
    152 => 
    array (
      'id' => 158,
      'orden' => 6,
      'core_modelo_id' => 75,
      'core_campo_id' => 22,
    ),
    153 => 
    array (
      'id' => 159,
      'orden' => 7,
      'core_modelo_id' => 76,
      'core_campo_id' => 22,
    ),
    154 => 
    array (
      'id' => 161,
      'orden' => 8,
      'core_modelo_id' => 81,
      'core_campo_id' => 22,
    ),
    155 => 
    array (
      'id' => 162,
      'orden' => 20,
      'core_modelo_id' => 83,
      'core_campo_id' => 22,
    ),
    156 => 
    array (
      'id' => 163,
      'orden' => 10,
      'core_modelo_id' => 84,
      'core_campo_id' => 22,
    ),
    157 => 
    array (
      'id' => 164,
      'orden' => 8,
      'core_modelo_id' => 87,
      'core_campo_id' => 22,
    ),
    158 => 
    array (
      'id' => 165,
      'orden' => 9,
      'core_modelo_id' => 88,
      'core_campo_id' => 22,
    ),
    159 => 
    array (
      'id' => 166,
      'orden' => 10,
      'core_modelo_id' => 90,
      'core_campo_id' => 22,
    ),
    160 => 
    array (
      'id' => 167,
      'orden' => 3,
      'core_modelo_id' => 92,
      'core_campo_id' => 22,
    ),
    161 => 
    array (
      'id' => 168,
      'orden' => 13,
      'core_modelo_id' => 93,
      'core_campo_id' => 22,
    ),
    162 => 
    array (
      'id' => 169,
      'orden' => 3,
      'core_modelo_id' => 94,
      'core_campo_id' => 22,
    ),
    163 => 
    array (
      'id' => 170,
      'orden' => 20,
      'core_modelo_id' => 95,
      'core_campo_id' => 22,
    ),
    164 => 
    array (
      'id' => 171,
      'orden' => 4,
      'core_modelo_id' => 98,
      'core_campo_id' => 22,
    ),
    165 => 
    array (
      'id' => 172,
      'orden' => 3,
      'core_modelo_id' => 99,
      'core_campo_id' => 22,
    ),
    166 => 
    array (
      'id' => 173,
      'orden' => 5,
      'core_modelo_id' => 102,
      'core_campo_id' => 22,
    ),
    167 => 
    array (
      'id' => 174,
      'orden' => 2,
      'core_modelo_id' => 106,
      'core_campo_id' => 22,
    ),
    168 => 
    array (
      'id' => 175,
      'orden' => 2,
      'core_modelo_id' => 107,
      'core_campo_id' => 22,
    ),
    169 => 
    array (
      'id' => 176,
      'orden' => 2,
      'core_modelo_id' => 108,
      'core_campo_id' => 22,
    ),
    170 => 
    array (
      'id' => 177,
      'orden' => 24,
      'core_modelo_id' => 109,
      'core_campo_id' => 22,
    ),
    171 => 
    array (
      'id' => 178,
      'orden' => 14,
      'core_modelo_id' => 114,
      'core_campo_id' => 22,
    ),
    172 => 
    array (
      'id' => 180,
      'orden' => 6,
      'core_modelo_id' => 118,
      'core_campo_id' => 22,
    ),
    173 => 
    array (
      'id' => 181,
      'orden' => 4,
      'core_modelo_id' => 119,
      'core_campo_id' => 22,
    ),
    174 => 
    array (
      'id' => 182,
      'orden' => 10,
      'core_modelo_id' => 120,
      'core_campo_id' => 22,
    ),
    175 => 
    array (
      'id' => 183,
      'orden' => 6,
      'core_modelo_id' => 121,
      'core_campo_id' => 22,
    ),
    176 => 
    array (
      'id' => 184,
      'orden' => 7,
      'core_modelo_id' => 122,
      'core_campo_id' => 22,
    ),
    177 => 
    array (
      'id' => 185,
      'orden' => 16,
      'core_modelo_id' => 123,
      'core_campo_id' => 22,
    ),
    178 => 
    array (
      'id' => 186,
      'orden' => 8,
      'core_modelo_id' => 124,
      'core_campo_id' => 22,
    ),
    179 => 
    array (
      'id' => 187,
      'orden' => 4,
      'core_modelo_id' => 126,
      'core_campo_id' => 22,
    ),
    180 => 
    array (
      'id' => 188,
      'orden' => 6,
      'core_modelo_id' => 128,
      'core_campo_id' => 22,
    ),
    181 => 
    array (
      'id' => 189,
      'orden' => 4,
      'core_modelo_id' => 130,
      'core_campo_id' => 22,
    ),
    182 => 
    array (
      'id' => 190,
      'orden' => 6,
      'core_modelo_id' => 132,
      'core_campo_id' => 22,
    ),
    183 => 
    array (
      'id' => 191,
      'orden' => 6,
      'core_modelo_id' => 133,
      'core_campo_id' => 22,
    ),
    184 => 
    array (
      'id' => 192,
      'orden' => 40,
      'core_modelo_id' => 134,
      'core_campo_id' => 22,
    ),
    185 => 
    array (
      'id' => 193,
      'orden' => 6,
      'core_modelo_id' => 135,
      'core_campo_id' => 22,
    ),
    186 => 
    array (
      'id' => 194,
      'orden' => 6,
      'core_modelo_id' => 136,
      'core_campo_id' => 22,
    ),
    187 => 
    array (
      'id' => 195,
      'orden' => 10,
      'core_modelo_id' => 137,
      'core_campo_id' => 22,
    ),
    188 => 
    array (
      'id' => 196,
      'orden' => 56,
      'core_modelo_id' => 138,
      'core_campo_id' => 22,
    ),
    189 => 
    array (
      'id' => 197,
      'orden' => 14,
      'core_modelo_id' => 140,
      'core_campo_id' => 22,
    ),
    190 => 
    array (
      'id' => 198,
      'orden' => 10,
      'core_modelo_id' => 143,
      'core_campo_id' => 22,
    ),
    191 => 
    array (
      'id' => 199,
      'orden' => 6,
      'core_modelo_id' => 145,
      'core_campo_id' => 22,
    ),
    192 => 
    array (
      'id' => 200,
      'orden' => 30,
      'core_modelo_id' => 146,
      'core_campo_id' => 22,
    ),
    193 => 
    array (
      'id' => 201,
      'orden' => 1,
      'core_modelo_id' => 4,
      'core_campo_id' => 24,
    ),
    194 => 
    array (
      'id' => 202,
      'orden' => 4,
      'core_modelo_id' => 4,
      'core_campo_id' => 25,
    ),
    195 => 
    array (
      'id' => 203,
      'orden' => 5,
      'core_modelo_id' => 4,
      'core_campo_id' => 26,
    ),
    196 => 
    array (
      'id' => 204,
      'orden' => 6,
      'core_modelo_id' => 4,
      'core_campo_id' => 27,
    ),
    197 => 
    array (
      'id' => 205,
      'orden' => 7,
      'core_modelo_id' => 4,
      'core_campo_id' => 28,
    ),
    198 => 
    array (
      'id' => 206,
      'orden' => 2,
      'core_modelo_id' => 20,
      'core_campo_id' => 28,
    ),
    199 => 
    array (
      'id' => 207,
      'orden' => 3,
      'core_modelo_id' => 30,
      'core_campo_id' => 28,
    ),
    200 => 
    array (
      'id' => 208,
      'orden' => 8,
      'core_modelo_id' => 4,
      'core_campo_id' => 29,
    ),
    201 => 
    array (
      'id' => 209,
      'orden' => 9,
      'core_modelo_id' => 4,
      'core_campo_id' => 30,
    ),
    202 => 
    array (
      'id' => 210,
      'orden' => 3,
      'core_modelo_id' => 34,
      'core_campo_id' => 31,
    ),
    203 => 
    array (
      'id' => 211,
      'orden' => 0,
      'core_modelo_id' => 3,
      'core_campo_id' => 32,
    ),
    204 => 
    array (
      'id' => 212,
      'orden' => 0,
      'core_modelo_id' => 3,
      'core_campo_id' => 33,
    ),
    205 => 
    array (
      'id' => 213,
      'orden' => 0,
      'core_modelo_id' => 3,
      'core_campo_id' => 34,
    ),
    206 => 
    array (
      'id' => 214,
      'orden' => 0,
      'core_modelo_id' => 3,
      'core_campo_id' => 35,
    ),
    207 => 
    array (
      'id' => 215,
      'orden' => 0,
      'core_modelo_id' => 3,
      'core_campo_id' => 36,
    ),
    208 => 
    array (
      'id' => 216,
      'orden' => 5,
      'core_modelo_id' => 6,
      'core_campo_id' => 37,
    ),
    209 => 
    array (
      'id' => 217,
      'orden' => 5,
      'core_modelo_id' => 7,
      'core_campo_id' => 37,
    ),
    210 => 
    array (
      'id' => 218,
      'orden' => 5,
      'core_modelo_id' => 41,
      'core_campo_id' => 37,
    ),
    211 => 
    array (
      'id' => 219,
      'orden' => 6,
      'core_modelo_id' => 93,
      'core_campo_id' => 37,
    ),
    212 => 
    array (
      'id' => 220,
      'orden' => 5,
      'core_modelo_id' => 95,
      'core_campo_id' => 37,
    ),
    213 => 
    array (
      'id' => 221,
      'orden' => 8,
      'core_modelo_id' => 2,
      'core_campo_id' => 38,
    ),
    214 => 
    array (
      'id' => 222,
      'orden' => 1,
      'core_modelo_id' => 8,
      'core_campo_id' => 39,
    ),
    215 => 
    array (
      'id' => 223,
      'orden' => 4,
      'core_modelo_id' => 25,
      'core_campo_id' => 39,
    ),
    216 => 
    array (
      'id' => 225,
      'orden' => 4,
      'core_modelo_id' => 51,
      'core_campo_id' => 39,
    ),
    217 => 
    array (
      'id' => 226,
      'orden' => 4,
      'core_modelo_id' => 52,
      'core_campo_id' => 39,
    ),
    218 => 
    array (
      'id' => 228,
      'orden' => 3,
      'core_modelo_id' => 59,
      'core_campo_id' => 39,
    ),
    219 => 
    array (
      'id' => 232,
      'orden' => 2,
      'core_modelo_id' => 8,
      'core_campo_id' => 40,
    ),
    220 => 
    array (
      'id' => 233,
      'orden' => 1,
      'core_modelo_id' => 6,
      'core_campo_id' => 41,
    ),
    221 => 
    array (
      'id' => 234,
      'orden' => 1,
      'core_modelo_id' => 7,
      'core_campo_id' => 41,
    ),
    222 => 
    array (
      'id' => 235,
      'orden' => 1,
      'core_modelo_id' => 41,
      'core_campo_id' => 41,
    ),
    223 => 
    array (
      'id' => 236,
      'orden' => 1,
      'core_modelo_id' => 138,
      'core_campo_id' => 41,
    ),
    224 => 
    array (
      'id' => 237,
      'orden' => 1,
      'core_modelo_id' => 146,
      'core_campo_id' => 41,
    ),
    225 => 
    array (
      'id' => 238,
      'orden' => 2,
      'core_modelo_id' => 6,
      'core_campo_id' => 42,
    ),
    226 => 
    array (
      'id' => 239,
      'orden' => 2,
      'core_modelo_id' => 7,
      'core_campo_id' => 42,
    ),
    227 => 
    array (
      'id' => 240,
      'orden' => 2,
      'core_modelo_id' => 41,
      'core_campo_id' => 42,
    ),
    228 => 
    array (
      'id' => 241,
      'orden' => 14,
      'core_modelo_id' => 138,
      'core_campo_id' => 42,
    ),
    229 => 
    array (
      'id' => 242,
      'orden' => 14,
      'core_modelo_id' => 146,
      'core_campo_id' => 42,
    ),
    230 => 
    array (
      'id' => 243,
      'orden' => 3,
      'core_modelo_id' => 6,
      'core_campo_id' => 43,
    ),
    231 => 
    array (
      'id' => 244,
      'orden' => 3,
      'core_modelo_id' => 7,
      'core_campo_id' => 43,
    ),
    232 => 
    array (
      'id' => 245,
      'orden' => 3,
      'core_modelo_id' => 41,
      'core_campo_id' => 43,
    ),
    233 => 
    array (
      'id' => 246,
      'orden' => 4,
      'core_modelo_id' => 93,
      'core_campo_id' => 43,
    ),
    234 => 
    array (
      'id' => 247,
      'orden' => 3,
      'core_modelo_id' => 95,
      'core_campo_id' => 43,
    ),
    235 => 
    array (
      'id' => 248,
      'orden' => 7,
      'core_modelo_id' => 146,
      'core_campo_id' => 43,
    ),
    236 => 
    array (
      'id' => 249,
      'orden' => 6,
      'core_modelo_id' => 6,
      'core_campo_id' => 44,
    ),
    237 => 
    array (
      'id' => 250,
      'orden' => 6,
      'core_modelo_id' => 7,
      'core_campo_id' => 44,
    ),
    238 => 
    array (
      'id' => 251,
      'orden' => 6,
      'core_modelo_id' => 41,
      'core_campo_id' => 44,
    ),
    239 => 
    array (
      'id' => 252,
      'orden' => 7,
      'core_modelo_id' => 93,
      'core_campo_id' => 44,
    ),
    240 => 
    array (
      'id' => 253,
      'orden' => 6,
      'core_modelo_id' => 95,
      'core_campo_id' => 44,
    ),
    241 => 
    array (
      'id' => 254,
      'orden' => 8,
      'core_modelo_id' => 6,
      'core_campo_id' => 45,
    ),
    242 => 
    array (
      'id' => 255,
      'orden' => 8,
      'core_modelo_id' => 7,
      'core_campo_id' => 45,
    ),
    243 => 
    array (
      'id' => 256,
      'orden' => 8,
      'core_modelo_id' => 41,
      'core_campo_id' => 45,
    ),
    244 => 
    array (
      'id' => 257,
      'orden' => 2,
      'core_modelo_id' => 66,
      'core_campo_id' => 45,
    ),
    245 => 
    array (
      'id' => 258,
      'orden' => 2,
      'core_modelo_id' => 93,
      'core_campo_id' => 45,
    ),
    246 => 
    array (
      'id' => 259,
      'orden' => 1,
      'core_modelo_id' => 95,
      'core_campo_id' => 45,
    ),
    247 => 
    array (
      'id' => 260,
      'orden' => 2,
      'core_modelo_id' => 134,
      'core_campo_id' => 45,
    ),
    248 => 
    array (
      'id' => 261,
      'orden' => 2,
      'core_modelo_id' => 138,
      'core_campo_id' => 45,
    ),
    249 => 
    array (
      'id' => 262,
      'orden' => 2,
      'core_modelo_id' => 146,
      'core_campo_id' => 45,
    ),
    250 => 
    array (
      'id' => 263,
      'orden' => 9,
      'core_modelo_id' => 6,
      'core_campo_id' => 46,
    ),
    251 => 
    array (
      'id' => 264,
      'orden' => 9,
      'core_modelo_id' => 7,
      'core_campo_id' => 46,
    ),
    252 => 
    array (
      'id' => 265,
      'orden' => 9,
      'core_modelo_id' => 41,
      'core_campo_id' => 46,
    ),
    253 => 
    array (
      'id' => 266,
      'orden' => 3,
      'core_modelo_id' => 66,
      'core_campo_id' => 46,
    ),
    254 => 
    array (
      'id' => 267,
      'orden' => 3,
      'core_modelo_id' => 93,
      'core_campo_id' => 46,
    ),
    255 => 
    array (
      'id' => 268,
      'orden' => 2,
      'core_modelo_id' => 95,
      'core_campo_id' => 46,
    ),
    256 => 
    array (
      'id' => 269,
      'orden' => 4,
      'core_modelo_id' => 134,
      'core_campo_id' => 46,
    ),
    257 => 
    array (
      'id' => 270,
      'orden' => 4,
      'core_modelo_id' => 138,
      'core_campo_id' => 46,
    ),
    258 => 
    array (
      'id' => 271,
      'orden' => 4,
      'core_modelo_id' => 146,
      'core_campo_id' => 46,
    ),
    259 => 
    array (
      'id' => 272,
      'orden' => 10,
      'core_modelo_id' => 6,
      'core_campo_id' => 47,
    ),
    260 => 
    array (
      'id' => 273,
      'orden' => 10,
      'core_modelo_id' => 7,
      'core_campo_id' => 47,
    ),
    261 => 
    array (
      'id' => 274,
      'orden' => 10,
      'core_modelo_id' => 41,
      'core_campo_id' => 47,
    ),
    262 => 
    array (
      'id' => 275,
      'orden' => 11,
      'core_modelo_id' => 6,
      'core_campo_id' => 48,
    ),
    263 => 
    array (
      'id' => 276,
      'orden' => 11,
      'core_modelo_id' => 41,
      'core_campo_id' => 48,
    ),
    264 => 
    array (
      'id' => 277,
      'orden' => 13,
      'core_modelo_id' => 6,
      'core_campo_id' => 50,
    ),
    265 => 
    array (
      'id' => 278,
      'orden' => 13,
      'core_modelo_id' => 7,
      'core_campo_id' => 50,
    ),
    266 => 
    array (
      'id' => 279,
      'orden' => 18,
      'core_modelo_id' => 29,
      'core_campo_id' => 50,
    ),
    267 => 
    array (
      'id' => 280,
      'orden' => 13,
      'core_modelo_id' => 41,
      'core_campo_id' => 50,
    ),
    268 => 
    array (
      'id' => 281,
      'orden' => 9,
      'core_modelo_id' => 66,
      'core_campo_id' => 50,
    ),
    269 => 
    array (
      'id' => 282,
      'orden' => 8,
      'core_modelo_id' => 93,
      'core_campo_id' => 50,
    ),
    270 => 
    array (
      'id' => 283,
      'orden' => 7,
      'core_modelo_id' => 95,
      'core_campo_id' => 50,
    ),
    271 => 
    array (
      'id' => 284,
      'orden' => 10,
      'core_modelo_id' => 134,
      'core_campo_id' => 50,
    ),
    272 => 
    array (
      'id' => 285,
      'orden' => 16,
      'core_modelo_id' => 138,
      'core_campo_id' => 50,
    ),
    273 => 
    array (
      'id' => 286,
      'orden' => 16,
      'core_modelo_id' => 146,
      'core_campo_id' => 50,
    ),
    274 => 
    array (
      'id' => 287,
      'orden' => 14,
      'core_modelo_id' => 6,
      'core_campo_id' => 51,
    ),
    275 => 
    array (
      'id' => 288,
      'orden' => 14,
      'core_modelo_id' => 7,
      'core_campo_id' => 51,
    ),
    276 => 
    array (
      'id' => 289,
      'orden' => 14,
      'core_modelo_id' => 41,
      'core_campo_id' => 51,
    ),
    277 => 
    array (
      'id' => 290,
      'orden' => 15,
      'core_modelo_id' => 6,
      'core_campo_id' => 52,
    ),
    278 => 
    array (
      'id' => 291,
      'orden' => 15,
      'core_modelo_id' => 7,
      'core_campo_id' => 52,
    ),
    279 => 
    array (
      'id' => 292,
      'orden' => 20,
      'core_modelo_id' => 29,
      'core_campo_id' => 52,
    ),
    280 => 
    array (
      'id' => 293,
      'orden' => 15,
      'core_modelo_id' => 41,
      'core_campo_id' => 52,
    ),
    281 => 
    array (
      'id' => 294,
      'orden' => 16,
      'core_modelo_id' => 6,
      'core_campo_id' => 53,
    ),
    282 => 
    array (
      'id' => 295,
      'orden' => 16,
      'core_modelo_id' => 7,
      'core_campo_id' => 53,
    ),
    283 => 
    array (
      'id' => 296,
      'orden' => 16,
      'core_modelo_id' => 41,
      'core_campo_id' => 53,
    ),
    284 => 
    array (
      'id' => 297,
      'orden' => 14,
      'core_modelo_id' => 134,
      'core_campo_id' => 53,
    ),
    285 => 
    array (
      'id' => 298,
      'orden' => 24,
      'core_modelo_id' => 138,
      'core_campo_id' => 53,
    ),
    286 => 
    array (
      'id' => 299,
      'orden' => 22,
      'core_modelo_id' => 146,
      'core_campo_id' => 53,
    ),
    287 => 
    array (
      'id' => 300,
      'orden' => 17,
      'core_modelo_id' => 6,
      'core_campo_id' => 54,
    ),
    288 => 
    array (
      'id' => 301,
      'orden' => 17,
      'core_modelo_id' => 7,
      'core_campo_id' => 54,
    ),
    289 => 
    array (
      'id' => 302,
      'orden' => 17,
      'core_modelo_id' => 41,
      'core_campo_id' => 54,
    ),
    290 => 
    array (
      'id' => 303,
      'orden' => 18,
      'core_modelo_id' => 6,
      'core_campo_id' => 55,
    ),
    291 => 
    array (
      'id' => 304,
      'orden' => 18,
      'core_modelo_id' => 7,
      'core_campo_id' => 55,
    ),
    292 => 
    array (
      'id' => 305,
      'orden' => 11,
      'core_modelo_id' => 29,
      'core_campo_id' => 55,
    ),
    293 => 
    array (
      'id' => 306,
      'orden' => 18,
      'core_modelo_id' => 41,
      'core_campo_id' => 55,
    ),
    294 => 
    array (
      'id' => 307,
      'orden' => 9,
      'core_modelo_id' => 93,
      'core_campo_id' => 55,
    ),
    295 => 
    array (
      'id' => 308,
      'orden' => 8,
      'core_modelo_id' => 95,
      'core_campo_id' => 55,
    ),
    296 => 
    array (
      'id' => 309,
      'orden' => 12,
      'core_modelo_id' => 134,
      'core_campo_id' => 55,
    ),
    297 => 
    array (
      'id' => 310,
      'orden' => 20,
      'core_modelo_id' => 138,
      'core_campo_id' => 55,
    ),
    298 => 
    array (
      'id' => 311,
      'orden' => 18,
      'core_modelo_id' => 146,
      'core_campo_id' => 55,
    ),
    299 => 
    array (
      'id' => 312,
      'orden' => 19,
      'core_modelo_id' => 6,
      'core_campo_id' => 56,
    ),
    300 => 
    array (
      'id' => 313,
      'orden' => 19,
      'core_modelo_id' => 7,
      'core_campo_id' => 56,
    ),
    301 => 
    array (
      'id' => 314,
      'orden' => 19,
      'core_modelo_id' => 41,
      'core_campo_id' => 56,
    ),
    302 => 
    array (
      'id' => 315,
      'orden' => 21,
      'core_modelo_id' => 6,
      'core_campo_id' => 57,
    ),
    303 => 
    array (
      'id' => 316,
      'orden' => 21,
      'core_modelo_id' => 7,
      'core_campo_id' => 57,
    ),
    304 => 
    array (
      'id' => 317,
      'orden' => 21,
      'core_modelo_id' => 41,
      'core_campo_id' => 57,
    ),
    305 => 
    array (
      'id' => 318,
      'orden' => 4,
      'core_modelo_id' => 6,
      'core_campo_id' => 58,
    ),
    306 => 
    array (
      'id' => 319,
      'orden' => 4,
      'core_modelo_id' => 7,
      'core_campo_id' => 58,
    ),
    307 => 
    array (
      'id' => 320,
      'orden' => 4,
      'core_modelo_id' => 41,
      'core_campo_id' => 58,
    ),
    308 => 
    array (
      'id' => 321,
      'orden' => 5,
      'core_modelo_id' => 66,
      'core_campo_id' => 58,
    ),
    309 => 
    array (
      'id' => 322,
      'orden' => 5,
      'core_modelo_id' => 93,
      'core_campo_id' => 58,
    ),
    310 => 
    array (
      'id' => 323,
      'orden' => 4,
      'core_modelo_id' => 95,
      'core_campo_id' => 58,
    ),
    311 => 
    array (
      'id' => 324,
      'orden' => 6,
      'core_modelo_id' => 134,
      'core_campo_id' => 58,
    ),
    312 => 
    array (
      'id' => 325,
      'orden' => 18,
      'core_modelo_id' => 138,
      'core_campo_id' => 58,
    ),
    313 => 
    array (
      'id' => 326,
      'orden' => 8,
      'core_modelo_id' => 146,
      'core_campo_id' => 58,
    ),
    314 => 
    array (
      'id' => 327,
      'orden' => 4,
      'core_modelo_id' => 12,
      'core_campo_id' => 61,
    ),
    315 => 
    array (
      'id' => 328,
      'orden' => 1,
      'core_modelo_id' => 64,
      'core_campo_id' => 61,
    ),
    316 => 
    array (
      'id' => 329,
      'orden' => 4,
      'core_modelo_id' => 120,
      'core_campo_id' => 61,
    ),
    317 => 
    array (
      'id' => 330,
      'orden' => 6,
      'core_modelo_id' => 12,
      'core_campo_id' => 62,
    ),
    318 => 
    array (
      'id' => 331,
      'orden' => 4,
      'core_modelo_id' => 55,
      'core_campo_id' => 62,
    ),
    319 => 
    array (
      'id' => 332,
      'orden' => 3,
      'core_modelo_id' => 65,
      'core_campo_id' => 62,
    ),
    320 => 
    array (
      'id' => 333,
      'orden' => 8,
      'core_modelo_id' => 12,
      'core_campo_id' => 63,
    ),
    321 => 
    array (
      'id' => 334,
      'orden' => 5,
      'core_modelo_id' => 55,
      'core_campo_id' => 63,
    ),
    322 => 
    array (
      'id' => 335,
      'orden' => 4,
      'core_modelo_id' => 65,
      'core_campo_id' => 63,
    ),
    323 => 
    array (
      'id' => 336,
      'orden' => 12,
      'core_modelo_id' => 12,
      'core_campo_id' => 64,
    ),
    324 => 
    array (
      'id' => 337,
      'orden' => 5,
      'core_modelo_id' => 65,
      'core_campo_id' => 64,
    ),
    325 => 
    array (
      'id' => 338,
      'orden' => 1,
      'core_modelo_id' => 13,
      'core_campo_id' => 65,
    ),
    326 => 
    array (
      'id' => 339,
      'orden' => 2,
      'core_modelo_id' => 13,
      'core_campo_id' => 66,
    ),
    327 => 
    array (
      'id' => 340,
      'orden' => 3,
      'core_modelo_id' => 13,
      'core_campo_id' => 67,
    ),
    328 => 
    array (
      'id' => 341,
      'orden' => 4,
      'core_modelo_id' => 13,
      'core_campo_id' => 68,
    ),
    329 => 
    array (
      'id' => 342,
      'orden' => 5,
      'core_modelo_id' => 13,
      'core_campo_id' => 69,
    ),
    330 => 
    array (
      'id' => 343,
      'orden' => 1,
      'core_modelo_id' => 14,
      'core_campo_id' => 70,
    ),
    331 => 
    array (
      'id' => 344,
      'orden' => 1,
      'core_modelo_id' => 17,
      'core_campo_id' => 71,
    ),
    332 => 
    array (
      'id' => 345,
      'orden' => 1,
      'core_modelo_id' => 18,
      'core_campo_id' => 71,
    ),
    333 => 
    array (
      'id' => 346,
      'orden' => 2,
      'core_modelo_id' => 17,
      'core_campo_id' => 72,
    ),
    334 => 
    array (
      'id' => 347,
      'orden' => 3,
      'core_modelo_id' => 17,
      'core_campo_id' => 73,
    ),
    335 => 
    array (
      'id' => 348,
      'orden' => 2,
      'core_modelo_id' => 18,
      'core_campo_id' => 73,
    ),
    336 => 
    array (
      'id' => 349,
      'orden' => 4,
      'core_modelo_id' => 17,
      'core_campo_id' => 74,
    ),
    337 => 
    array (
      'id' => 350,
      'orden' => 3,
      'core_modelo_id' => 18,
      'core_campo_id' => 75,
    ),
    338 => 
    array (
      'id' => 351,
      'orden' => 3,
      'core_modelo_id' => 20,
      'core_campo_id' => 76,
    ),
    339 => 
    array (
      'id' => 352,
      'orden' => 4,
      'core_modelo_id' => 30,
      'core_campo_id' => 76,
    ),
    340 => 
    array (
      'id' => 353,
      'orden' => 4,
      'core_modelo_id' => 20,
      'core_campo_id' => 77,
    ),
    341 => 
    array (
      'id' => 354,
      'orden' => 5,
      'core_modelo_id' => 30,
      'core_campo_id' => 77,
    ),
    342 => 
    array (
      'id' => 355,
      'orden' => 6,
      'core_modelo_id' => 20,
      'core_campo_id' => 78,
    ),
    343 => 
    array (
      'id' => 356,
      'orden' => 7,
      'core_modelo_id' => 30,
      'core_campo_id' => 78,
    ),
    344 => 
    array (
      'id' => 357,
      'orden' => 8,
      'core_modelo_id' => 22,
      'core_campo_id' => 79,
    ),
    345 => 
    array (
      'id' => 358,
      'orden' => 8,
      'core_modelo_id' => 24,
      'core_campo_id' => 82,
    ),
    346 => 
    array (
      'id' => 359,
      'orden' => 6,
      'core_modelo_id' => 24,
      'core_campo_id' => 83,
    ),
    347 => 
    array (
      'id' => 360,
      'orden' => 10,
      'core_modelo_id' => 22,
      'core_campo_id' => 84,
    ),
    348 => 
    array (
      'id' => 361,
      'orden' => 14,
      'core_modelo_id' => 22,
      'core_campo_id' => 85,
    ),
    349 => 
    array (
      'id' => 362,
      'orden' => 16,
      'core_modelo_id' => 22,
      'core_campo_id' => 86,
    ),
    350 => 
    array (
      'id' => 363,
      'orden' => 2,
      'core_modelo_id' => 42,
      'core_campo_id' => 86,
    ),
    351 => 
    array (
      'id' => 364,
      'orden' => 99,
      'core_modelo_id' => 25,
      'core_campo_id' => 87,
    ),
    352 => 
    array (
      'id' => 365,
      'orden' => 99,
      'core_modelo_id' => 43,
      'core_campo_id' => 87,
    ),
    353 => 
    array (
      'id' => 366,
      'orden' => 99,
      'core_modelo_id' => 46,
      'core_campo_id' => 87,
    ),
    354 => 
    array (
      'id' => 367,
      'orden' => 99,
      'core_modelo_id' => 47,
      'core_campo_id' => 87,
    ),
    355 => 
    array (
      'id' => 368,
      'orden' => 99,
      'core_modelo_id' => 51,
      'core_campo_id' => 87,
    ),
    356 => 
    array (
      'id' => 369,
      'orden' => 99,
      'core_modelo_id' => 52,
      'core_campo_id' => 87,
    ),
    357 => 
    array (
      'id' => 370,
      'orden' => 99,
      'core_modelo_id' => 54,
      'core_campo_id' => 87,
    ),
    358 => 
    array (
      'id' => 371,
      'orden' => 99,
      'core_modelo_id' => 59,
      'core_campo_id' => 87,
    ),
    359 => 
    array (
      'id' => 372,
      'orden' => 99,
      'core_modelo_id' => 90,
      'core_campo_id' => 87,
    ),
    360 => 
    array (
      'id' => 373,
      'orden' => 99,
      'core_modelo_id' => 139,
      'core_campo_id' => 87,
    ),
    361 => 
    array (
      'id' => 374,
      'orden' => 2,
      'core_modelo_id' => 25,
      'core_campo_id' => 88,
    ),
    362 => 
    array (
      'id' => 375,
      'orden' => 1,
      'core_modelo_id' => 43,
      'core_campo_id' => 88,
    ),
    363 => 
    array (
      'id' => 376,
      'orden' => 2,
      'core_modelo_id' => 46,
      'core_campo_id' => 88,
    ),
    364 => 
    array (
      'id' => 377,
      'orden' => 1,
      'core_modelo_id' => 47,
      'core_campo_id' => 88,
    ),
    365 => 
    array (
      'id' => 378,
      'orden' => 2,
      'core_modelo_id' => 51,
      'core_campo_id' => 88,
    ),
    366 => 
    array (
      'id' => 379,
      'orden' => 2,
      'core_modelo_id' => 52,
      'core_campo_id' => 88,
    ),
    367 => 
    array (
      'id' => 380,
      'orden' => 2,
      'core_modelo_id' => 54,
      'core_campo_id' => 88,
    ),
    368 => 
    array (
      'id' => 381,
      'orden' => 1,
      'core_modelo_id' => 59,
      'core_campo_id' => 88,
    ),
    369 => 
    array (
      'id' => 382,
      'orden' => 2,
      'core_modelo_id' => 90,
      'core_campo_id' => 88,
    ),
    370 => 
    array (
      'id' => 383,
      'orden' => 3,
      'core_modelo_id' => 139,
      'core_campo_id' => 88,
    ),
    371 => 
    array (
      'id' => 384,
      'orden' => 2,
      'core_modelo_id' => 20,
      'core_campo_id' => 89,
    ),
    372 => 
    array (
      'id' => 385,
      'orden' => 2,
      'core_modelo_id' => 30,
      'core_campo_id' => 89,
    ),
    373 => 
    array (
      'id' => 386,
      'orden' => 11,
      'core_modelo_id' => 25,
      'core_campo_id' => 90,
    ),
    374 => 
    array (
      'id' => 387,
      'orden' => 13,
      'core_modelo_id' => 139,
      'core_campo_id' => 90,
    ),
    375 => 
    array (
      'id' => 388,
      'orden' => 10,
      'core_modelo_id' => 25,
      'core_campo_id' => 92,
    ),
    376 => 
    array (
      'id' => 389,
      'orden' => 5,
      'core_modelo_id' => 43,
      'core_campo_id' => 92,
    ),
    377 => 
    array (
      'id' => 390,
      'orden' => 5,
      'core_modelo_id' => 46,
      'core_campo_id' => 92,
    ),
    378 => 
    array (
      'id' => 391,
      'orden' => 4,
      'core_modelo_id' => 47,
      'core_campo_id' => 92,
    ),
    379 => 
    array (
      'id' => 392,
      'orden' => 5,
      'core_modelo_id' => 51,
      'core_campo_id' => 92,
    ),
    380 => 
    array (
      'id' => 393,
      'orden' => 6,
      'core_modelo_id' => 52,
      'core_campo_id' => 92,
    ),
    381 => 
    array (
      'id' => 394,
      'orden' => 9,
      'core_modelo_id' => 54,
      'core_campo_id' => 92,
    ),
    382 => 
    array (
      'id' => 395,
      'orden' => 3,
      'core_modelo_id' => 25,
      'core_campo_id' => 93,
    ),
    383 => 
    array (
      'id' => 396,
      'orden' => 2,
      'core_modelo_id' => 43,
      'core_campo_id' => 93,
    ),
    384 => 
    array (
      'id' => 397,
      'orden' => 3,
      'core_modelo_id' => 46,
      'core_campo_id' => 93,
    ),
    385 => 
    array (
      'id' => 398,
      'orden' => 2,
      'core_modelo_id' => 47,
      'core_campo_id' => 93,
    ),
    386 => 
    array (
      'id' => 399,
      'orden' => 3,
      'core_modelo_id' => 51,
      'core_campo_id' => 93,
    ),
    387 => 
    array (
      'id' => 400,
      'orden' => 3,
      'core_modelo_id' => 52,
      'core_campo_id' => 93,
    ),
    388 => 
    array (
      'id' => 401,
      'orden' => 3,
      'core_modelo_id' => 54,
      'core_campo_id' => 93,
    ),
    389 => 
    array (
      'id' => 402,
      'orden' => 2,
      'core_modelo_id' => 59,
      'core_campo_id' => 93,
    ),
    390 => 
    array (
      'id' => 403,
      'orden' => 1,
      'core_modelo_id' => 66,
      'core_campo_id' => 93,
    ),
    391 => 
    array (
      'id' => 404,
      'orden' => 4,
      'core_modelo_id' => 90,
      'core_campo_id' => 93,
    ),
    392 => 
    array (
      'id' => 405,
      'orden' => 3,
      'core_modelo_id' => 96,
      'core_campo_id' => 93,
    ),
    393 => 
    array (
      'id' => 406,
      'orden' => 4,
      'core_modelo_id' => 139,
      'core_campo_id' => 93,
    ),
    394 => 
    array (
      'id' => 407,
      'orden' => 99,
      'core_modelo_id' => 17,
      'core_campo_id' => 94,
    ),
    395 => 
    array (
      'id' => 408,
      'orden' => 99,
      'core_modelo_id' => 25,
      'core_campo_id' => 94,
    ),
    396 => 
    array (
      'id' => 409,
      'orden' => 99,
      'core_modelo_id' => 31,
      'core_campo_id' => 94,
    ),
    397 => 
    array (
      'id' => 410,
      'orden' => 99,
      'core_modelo_id' => 43,
      'core_campo_id' => 94,
    ),
    398 => 
    array (
      'id' => 411,
      'orden' => 99,
      'core_modelo_id' => 46,
      'core_campo_id' => 94,
    ),
    399 => 
    array (
      'id' => 412,
      'orden' => 99,
      'core_modelo_id' => 47,
      'core_campo_id' => 94,
    ),
    400 => 
    array (
      'id' => 413,
      'orden' => 99,
      'core_modelo_id' => 48,
      'core_campo_id' => 94,
    ),
    401 => 
    array (
      'id' => 414,
      'orden' => 99,
      'core_modelo_id' => 49,
      'core_campo_id' => 94,
    ),
    402 => 
    array (
      'id' => 415,
      'orden' => 99,
      'core_modelo_id' => 51,
      'core_campo_id' => 94,
    ),
    403 => 
    array (
      'id' => 416,
      'orden' => 99,
      'core_modelo_id' => 52,
      'core_campo_id' => 94,
    ),
    404 => 
    array (
      'id' => 417,
      'orden' => 99,
      'core_modelo_id' => 54,
      'core_campo_id' => 94,
    ),
    405 => 
    array (
      'id' => 418,
      'orden' => 99,
      'core_modelo_id' => 56,
      'core_campo_id' => 94,
    ),
    406 => 
    array (
      'id' => 419,
      'orden' => 99,
      'core_modelo_id' => 59,
      'core_campo_id' => 94,
    ),
    407 => 
    array (
      'id' => 420,
      'orden' => 99,
      'core_modelo_id' => 63,
      'core_campo_id' => 94,
    ),
    408 => 
    array (
      'id' => 421,
      'orden' => 99,
      'core_modelo_id' => 64,
      'core_campo_id' => 94,
    ),
    409 => 
    array (
      'id' => 422,
      'orden' => 99,
      'core_modelo_id' => 66,
      'core_campo_id' => 94,
    ),
    410 => 
    array (
      'id' => 423,
      'orden' => 99,
      'core_modelo_id' => 76,
      'core_campo_id' => 94,
    ),
    411 => 
    array (
      'id' => 425,
      'orden' => 99,
      'core_modelo_id' => 90,
      'core_campo_id' => 94,
    ),
    412 => 
    array (
      'id' => 426,
      'orden' => 99,
      'core_modelo_id' => 95,
      'core_campo_id' => 94,
    ),
    413 => 
    array (
      'id' => 427,
      'orden' => 99,
      'core_modelo_id' => 17,
      'core_campo_id' => 95,
    ),
    414 => 
    array (
      'id' => 428,
      'orden' => 99,
      'core_modelo_id' => 25,
      'core_campo_id' => 95,
    ),
    415 => 
    array (
      'id' => 429,
      'orden' => 99,
      'core_modelo_id' => 31,
      'core_campo_id' => 95,
    ),
    416 => 
    array (
      'id' => 430,
      'orden' => 99,
      'core_modelo_id' => 43,
      'core_campo_id' => 95,
    ),
    417 => 
    array (
      'id' => 431,
      'orden' => 99,
      'core_modelo_id' => 46,
      'core_campo_id' => 95,
    ),
    418 => 
    array (
      'id' => 432,
      'orden' => 99,
      'core_modelo_id' => 47,
      'core_campo_id' => 95,
    ),
    419 => 
    array (
      'id' => 433,
      'orden' => 99,
      'core_modelo_id' => 48,
      'core_campo_id' => 95,
    ),
    420 => 
    array (
      'id' => 434,
      'orden' => 99,
      'core_modelo_id' => 49,
      'core_campo_id' => 95,
    ),
    421 => 
    array (
      'id' => 435,
      'orden' => 99,
      'core_modelo_id' => 51,
      'core_campo_id' => 95,
    ),
    422 => 
    array (
      'id' => 436,
      'orden' => 99,
      'core_modelo_id' => 52,
      'core_campo_id' => 95,
    ),
    423 => 
    array (
      'id' => 437,
      'orden' => 99,
      'core_modelo_id' => 54,
      'core_campo_id' => 95,
    ),
    424 => 
    array (
      'id' => 438,
      'orden' => 99,
      'core_modelo_id' => 56,
      'core_campo_id' => 95,
    ),
    425 => 
    array (
      'id' => 439,
      'orden' => 99,
      'core_modelo_id' => 59,
      'core_campo_id' => 95,
    ),
    426 => 
    array (
      'id' => 440,
      'orden' => 99,
      'core_modelo_id' => 63,
      'core_campo_id' => 95,
    ),
    427 => 
    array (
      'id' => 441,
      'orden' => 99,
      'core_modelo_id' => 64,
      'core_campo_id' => 95,
    ),
    428 => 
    array (
      'id' => 442,
      'orden' => 99,
      'core_modelo_id' => 66,
      'core_campo_id' => 95,
    ),
    429 => 
    array (
      'id' => 443,
      'orden' => 99,
      'core_modelo_id' => 76,
      'core_campo_id' => 95,
    ),
    430 => 
    array (
      'id' => 445,
      'orden' => 99,
      'core_modelo_id' => 90,
      'core_campo_id' => 95,
    ),
    431 => 
    array (
      'id' => 446,
      'orden' => 99,
      'core_modelo_id' => 95,
      'core_campo_id' => 95,
    ),
    432 => 
    array (
      'id' => 447,
      'orden' => 1,
      'core_modelo_id' => 27,
      'core_campo_id' => 96,
    ),
    433 => 
    array (
      'id' => 449,
      'orden' => 4,
      'core_modelo_id' => 29,
      'core_campo_id' => 97,
    ),
    434 => 
    array (
      'id' => 450,
      'orden' => 16,
      'core_modelo_id' => 38,
      'core_campo_id' => 97,
    ),
    435 => 
    array (
      'id' => 451,
      'orden' => 1,
      'core_modelo_id' => 93,
      'core_campo_id' => 97,
    ),
    436 => 
    array (
      'id' => 452,
      'orden' => 2,
      'core_modelo_id' => 110,
      'core_campo_id' => 97,
    ),
    437 => 
    array (
      'id' => 453,
      'orden' => 7,
      'core_modelo_id' => 43,
      'core_campo_id' => 98,
    ),
    438 => 
    array (
      'id' => 454,
      'orden' => 99,
      'core_modelo_id' => 46,
      'core_campo_id' => 98,
    ),
    439 => 
    array (
      'id' => 455,
      'orden' => 99,
      'core_modelo_id' => 47,
      'core_campo_id' => 98,
    ),
    440 => 
    array (
      'id' => 456,
      'orden' => 99,
      'core_modelo_id' => 51,
      'core_campo_id' => 98,
    ),
    441 => 
    array (
      'id' => 457,
      'orden' => 99,
      'core_modelo_id' => 52,
      'core_campo_id' => 98,
    ),
    442 => 
    array (
      'id' => 458,
      'orden' => 99,
      'core_modelo_id' => 54,
      'core_campo_id' => 98,
    ),
    443 => 
    array (
      'id' => 459,
      'orden' => 99,
      'core_modelo_id' => 56,
      'core_campo_id' => 98,
    ),
    444 => 
    array (
      'id' => 460,
      'orden' => 99,
      'core_modelo_id' => 59,
      'core_campo_id' => 98,
    ),
    445 => 
    array (
      'id' => 461,
      'orden' => 99,
      'core_modelo_id' => 25,
      'core_campo_id' => 99,
    ),
    446 => 
    array (
      'id' => 462,
      'orden' => 99,
      'core_modelo_id' => 25,
      'core_campo_id' => 100,
    ),
    447 => 
    array (
      'id' => 463,
      'orden' => 99,
      'core_modelo_id' => 43,
      'core_campo_id' => 100,
    ),
    448 => 
    array (
      'id' => 464,
      'orden' => 99,
      'core_modelo_id' => 46,
      'core_campo_id' => 100,
    ),
    449 => 
    array (
      'id' => 465,
      'orden' => 99,
      'core_modelo_id' => 47,
      'core_campo_id' => 100,
    ),
    450 => 
    array (
      'id' => 466,
      'orden' => 99,
      'core_modelo_id' => 51,
      'core_campo_id' => 100,
    ),
    451 => 
    array (
      'id' => 467,
      'orden' => 99,
      'core_modelo_id' => 52,
      'core_campo_id' => 100,
    ),
    452 => 
    array (
      'id' => 468,
      'orden' => 99,
      'core_modelo_id' => 54,
      'core_campo_id' => 100,
    ),
    453 => 
    array (
      'id' => 469,
      'orden' => 99,
      'core_modelo_id' => 59,
      'core_campo_id' => 100,
    ),
    454 => 
    array (
      'id' => 470,
      'orden' => 3,
      'core_modelo_id' => 90,
      'core_campo_id' => 100,
    ),
    455 => 
    array (
      'id' => 471,
      'orden' => 99,
      'core_modelo_id' => 139,
      'core_campo_id' => 100,
    ),
    456 => 
    array (
      'id' => 472,
      'orden' => 99,
      'core_modelo_id' => 25,
      'core_campo_id' => 101,
    ),
    457 => 
    array (
      'id' => 476,
      'orden' => 16,
      'core_modelo_id' => 29,
      'core_campo_id' => 105,
    ),
    458 => 
    array (
      'id' => 477,
      'orden' => 8,
      'core_modelo_id' => 66,
      'core_campo_id' => 105,
    ),
    459 => 
    array (
      'id' => 478,
      'orden' => 11,
      'core_modelo_id' => 95,
      'core_campo_id' => 105,
    ),
    460 => 
    array (
      'id' => 479,
      'orden' => 12,
      'core_modelo_id' => 29,
      'core_campo_id' => 106,
    ),
    461 => 
    array (
      'id' => 480,
      'orden' => 11,
      'core_modelo_id' => 66,
      'core_campo_id' => 106,
    ),
    462 => 
    array (
      'id' => 481,
      'orden' => 10,
      'core_modelo_id' => 95,
      'core_campo_id' => 106,
    ),
    463 => 
    array (
      'id' => 482,
      'orden' => 13,
      'core_modelo_id' => 29,
      'core_campo_id' => 107,
    ),
    464 => 
    array (
      'id' => 483,
      'orden' => 11,
      'core_modelo_id' => 66,
      'core_campo_id' => 107,
    ),
    465 => 
    array (
      'id' => 484,
      'orden' => 14,
      'core_modelo_id' => 29,
      'core_campo_id' => 108,
    ),
    466 => 
    array (
      'id' => 485,
      'orden' => 15,
      'core_modelo_id' => 29,
      'core_campo_id' => 109,
    ),
    467 => 
    array (
      'id' => 486,
      'orden' => 16,
      'core_modelo_id' => 29,
      'core_campo_id' => 110,
    ),
    468 => 
    array (
      'id' => 487,
      'orden' => 17,
      'core_modelo_id' => 29,
      'core_campo_id' => 111,
    ),
    469 => 
    array (
      'id' => 488,
      'orden' => 18,
      'core_modelo_id' => 29,
      'core_campo_id' => 112,
    ),
    470 => 
    array (
      'id' => 489,
      'orden' => 19,
      'core_modelo_id' => 29,
      'core_campo_id' => 113,
    ),
    471 => 
    array (
      'id' => 490,
      'orden' => 20,
      'core_modelo_id' => 29,
      'core_campo_id' => 114,
    ),
    472 => 
    array (
      'id' => 491,
      'orden' => 21,
      'core_modelo_id' => 29,
      'core_campo_id' => 115,
    ),
    473 => 
    array (
      'id' => 492,
      'orden' => 10,
      'core_modelo_id' => 4,
      'core_campo_id' => 116,
    ),
    474 => 
    array (
      'id' => 493,
      'orden' => 99,
      'core_modelo_id' => 1,
      'core_campo_id' => 117,
    ),
    475 => 
    array (
      'id' => 494,
      'orden' => 99,
      'core_modelo_id' => 11,
      'core_campo_id' => 117,
    ),
    476 => 
    array (
      'id' => 495,
      'orden' => 99,
      'core_modelo_id' => 29,
      'core_campo_id' => 117,
    ),
    477 => 
    array (
      'id' => 496,
      'orden' => 99,
      'core_modelo_id' => 67,
      'core_campo_id' => 117,
    ),
    478 => 
    array (
      'id' => 497,
      'orden' => 99,
      'core_modelo_id' => 68,
      'core_campo_id' => 117,
    ),
    479 => 
    array (
      'id' => 498,
      'orden' => 14,
      'core_modelo_id' => 70,
      'core_campo_id' => 117,
    ),
    480 => 
    array (
      'id' => 499,
      'orden' => 2,
      'core_modelo_id' => 7,
      'core_campo_id' => 118,
    ),
    481 => 
    array (
      'id' => 500,
      'orden' => 6,
      'core_modelo_id' => 22,
      'core_campo_id' => 119,
    ),
    482 => 
    array (
      'id' => 501,
      'orden' => 20,
      'core_modelo_id' => 22,
      'core_campo_id' => 120,
    ),
    483 => 
    array (
      'id' => 502,
      'orden' => 20,
      'core_modelo_id' => 22,
      'core_campo_id' => 121,
    ),
    484 => 
    array (
      'id' => 503,
      'orden' => 12,
      'core_modelo_id' => 25,
      'core_campo_id' => 122,
    ),
    485 => 
    array (
      'id' => 505,
      'orden' => 6,
      'core_modelo_id' => 19,
      'core_campo_id' => 123,
    ),
    486 => 
    array (
      'id' => 506,
      'orden' => 0,
      'core_modelo_id' => 53,
      'core_campo_id' => 123,
    ),
    487 => 
    array (
      'id' => 507,
      'orden' => 2,
      'core_modelo_id' => 69,
      'core_campo_id' => 123,
    ),
    488 => 
    array (
      'id' => 508,
      'orden' => 8,
      'core_modelo_id' => 76,
      'core_campo_id' => 123,
    ),
    489 => 
    array (
      'id' => 510,
      'orden' => 2,
      'core_modelo_id' => 31,
      'core_campo_id' => 125,
    ),
    490 => 
    array (
      'id' => 511,
      'orden' => 2,
      'core_modelo_id' => 35,
      'core_campo_id' => 125,
    ),
    491 => 
    array (
      'id' => 512,
      'orden' => 3,
      'core_modelo_id' => 87,
      'core_campo_id' => 125,
    ),
    492 => 
    array (
      'id' => 513,
      'orden' => 3,
      'core_modelo_id' => 88,
      'core_campo_id' => 125,
    ),
    493 => 
    array (
      'id' => 514,
      'orden' => 6,
      'core_modelo_id' => 120,
      'core_campo_id' => 125,
    ),
    494 => 
    array (
      'id' => 515,
      'orden' => 3,
      'core_modelo_id' => 31,
      'core_campo_id' => 126,
    ),
    495 => 
    array (
      'id' => 516,
      'orden' => 4,
      'core_modelo_id' => 31,
      'core_campo_id' => 127,
    ),
    496 => 
    array (
      'id' => 517,
      'orden' => 5,
      'core_modelo_id' => 31,
      'core_campo_id' => 128,
    ),
    497 => 
    array (
      'id' => 518,
      'orden' => 6,
      'core_modelo_id' => 31,
      'core_campo_id' => 129,
    ),
    498 => 
    array (
      'id' => 519,
      'orden' => 7,
      'core_modelo_id' => 3,
      'core_campo_id' => 130,
    ),
    499 => 
    array (
      'id' => 520,
      'orden' => 8,
      'core_modelo_id' => 3,
      'core_campo_id' => 131,
    ),
    500 => 
    array (
      'id' => 521,
      'orden' => 7,
      'core_modelo_id' => 3,
      'core_campo_id' => 132,
    ),
    501 => 
    array (
      'id' => 522,
      'orden' => 9,
      'core_modelo_id' => 3,
      'core_campo_id' => 133,
    ),
    502 => 
    array (
      'id' => 523,
      'orden' => 10,
      'core_modelo_id' => 3,
      'core_campo_id' => 134,
    ),
    503 => 
    array (
      'id' => 524,
      'orden' => 11,
      'core_modelo_id' => 3,
      'core_campo_id' => 135,
    ),
    504 => 
    array (
      'id' => 525,
      'orden' => 1,
      'core_modelo_id' => 9,
      'core_campo_id' => 136,
    ),
    505 => 
    array (
      'id' => 526,
      'orden' => 1,
      'core_modelo_id' => 32,
      'core_campo_id' => 136,
    ),
    506 => 
    array (
      'id' => 527,
      'orden' => 1,
      'core_modelo_id' => 33,
      'core_campo_id' => 137,
    ),
    507 => 
    array (
      'id' => 528,
      'orden' => 2,
      'core_modelo_id' => 33,
      'core_campo_id' => 138,
    ),
    508 => 
    array (
      'id' => 529,
      'orden' => 3,
      'core_modelo_id' => 33,
      'core_campo_id' => 139,
    ),
    509 => 
    array (
      'id' => 530,
      'orden' => 0,
      'core_modelo_id' => 6,
      'core_campo_id' => 140,
    ),
    510 => 
    array (
      'id' => 531,
      'orden' => 0,
      'core_modelo_id' => 7,
      'core_campo_id' => 140,
    ),
    511 => 
    array (
      'id' => 532,
      'orden' => 4,
      'core_modelo_id' => 8,
      'core_campo_id' => 140,
    ),
    512 => 
    array (
      'id' => 533,
      'orden' => 2,
      'core_modelo_id' => 29,
      'core_campo_id' => 140,
    ),
    513 => 
    array (
      'id' => 534,
      'orden' => 0,
      'core_modelo_id' => 41,
      'core_campo_id' => 140,
    ),
    514 => 
    array (
      'id' => 535,
      'orden' => 0,
      'core_modelo_id' => 53,
      'core_campo_id' => 140,
    ),
    515 => 
    array (
      'id' => 536,
      'orden' => 0,
      'core_modelo_id' => 55,
      'core_campo_id' => 140,
    ),
    516 => 
    array (
      'id' => 537,
      'orden' => 1,
      'core_modelo_id' => 69,
      'core_campo_id' => 140,
    ),
    517 => 
    array (
      'id' => 538,
      'orden' => 4,
      'core_modelo_id' => 76,
      'core_campo_id' => 140,
    ),
    518 => 
    array (
      'id' => 539,
      'orden' => 1,
      'core_modelo_id' => 93,
      'core_campo_id' => 140,
    ),
    519 => 
    array (
      'id' => 540,
      'orden' => 0,
      'core_modelo_id' => 95,
      'core_campo_id' => 140,
    ),
    520 => 
    array (
      'id' => 541,
      'orden' => 13,
      'core_modelo_id' => 3,
      'core_campo_id' => 141,
    ),
    521 => 
    array (
      'id' => 542,
      'orden' => 3,
      'core_modelo_id' => 16,
      'core_campo_id' => 142,
    ),
    522 => 
    array (
      'id' => 543,
      'orden' => 4,
      'core_modelo_id' => 16,
      'core_campo_id' => 143,
    ),
    523 => 
    array (
      'id' => 544,
      'orden' => 5,
      'core_modelo_id' => 16,
      'core_campo_id' => 144,
    ),
    524 => 
    array (
      'id' => 545,
      'orden' => 6,
      'core_modelo_id' => 16,
      'core_campo_id' => 145,
    ),
    525 => 
    array (
      'id' => 546,
      'orden' => 7,
      'core_modelo_id' => 16,
      'core_campo_id' => 146,
    ),
    526 => 
    array (
      'id' => 547,
      'orden' => 8,
      'core_modelo_id' => 16,
      'core_campo_id' => 147,
    ),
    527 => 
    array (
      'id' => 548,
      'orden' => 9,
      'core_modelo_id' => 16,
      'core_campo_id' => 148,
    ),
    528 => 
    array (
      'id' => 549,
      'orden' => 10,
      'core_modelo_id' => 16,
      'core_campo_id' => 149,
    ),
    529 => 
    array (
      'id' => 550,
      'orden' => 11,
      'core_modelo_id' => 16,
      'core_campo_id' => 150,
    ),
    530 => 
    array (
      'id' => 551,
      'orden' => 4,
      'core_modelo_id' => 34,
      'core_campo_id' => 151,
    ),
    531 => 
    array (
      'id' => 552,
      'orden' => 5,
      'core_modelo_id' => 34,
      'core_campo_id' => 152,
    ),
    532 => 
    array (
      'id' => 553,
      'orden' => 6,
      'core_modelo_id' => 34,
      'core_campo_id' => 153,
    ),
    533 => 
    array (
      'id' => 555,
      'orden' => 7,
      'core_modelo_id' => 34,
      'core_campo_id' => 154,
    ),
    534 => 
    array (
      'id' => 556,
      'orden' => 8,
      'core_modelo_id' => 34,
      'core_campo_id' => 155,
    ),
    535 => 
    array (
      'id' => 557,
      'orden' => 2,
      'core_modelo_id' => 34,
      'core_campo_id' => 156,
    ),
    536 => 
    array (
      'id' => 558,
      'orden' => 12,
      'core_modelo_id' => 123,
      'core_campo_id' => 156,
    ),
    537 => 
    array (
      'id' => 559,
      'orden' => 9,
      'core_modelo_id' => 34,
      'core_campo_id' => 157,
    ),
    538 => 
    array (
      'id' => 560,
      'orden' => 10,
      'core_modelo_id' => 34,
      'core_campo_id' => 158,
    ),
    539 => 
    array (
      'id' => 561,
      'orden' => 12,
      'core_modelo_id' => 16,
      'core_campo_id' => 159,
    ),
    540 => 
    array (
      'id' => 562,
      'orden' => 9,
      'core_modelo_id' => 30,
      'core_campo_id' => 160,
    ),
    541 => 
    array (
      'id' => 564,
      'orden' => 4,
      'core_modelo_id' => 35,
      'core_campo_id' => 162,
    ),
    542 => 
    array (
      'id' => 565,
      'orden' => 8,
      'core_modelo_id' => 120,
      'core_campo_id' => 162,
    ),
    543 => 
    array (
      'id' => 566,
      'orden' => 3,
      'core_modelo_id' => 35,
      'core_campo_id' => 163,
    ),
    544 => 
    array (
      'id' => 567,
      'orden' => 5,
      'core_modelo_id' => 35,
      'core_campo_id' => 164,
    ),
    545 => 
    array (
      'id' => 568,
      'orden' => 6,
      'core_modelo_id' => 35,
      'core_campo_id' => 165,
    ),
    546 => 
    array (
      'id' => 569,
      'orden' => 7,
      'core_modelo_id' => 35,
      'core_campo_id' => 166,
    ),
    547 => 
    array (
      'id' => 570,
      'orden' => 0,
      'core_modelo_id' => 36,
      'core_campo_id' => 167,
    ),
    548 => 
    array (
      'id' => 571,
      'orden' => 4,
      'core_modelo_id' => 36,
      'core_campo_id' => 168,
    ),
    549 => 
    array (
      'id' => 572,
      'orden' => 1,
      'core_modelo_id' => 36,
      'core_campo_id' => 169,
    ),
    550 => 
    array (
      'id' => 574,
      'orden' => 5,
      'core_modelo_id' => 71,
      'core_campo_id' => 169,
    ),
    551 => 
    array (
      'id' => 575,
      'orden' => 5,
      'core_modelo_id' => 75,
      'core_campo_id' => 169,
    ),
    552 => 
    array (
      'id' => 576,
      'orden' => 2,
      'core_modelo_id' => 38,
      'core_campo_id' => 170,
    ),
    553 => 
    array (
      'id' => 577,
      'orden' => 20,
      'core_modelo_id' => 38,
      'core_campo_id' => 171,
    ),
    554 => 
    array (
      'id' => 578,
      'orden' => 3,
      'core_modelo_id' => 38,
      'core_campo_id' => 172,
    ),
    555 => 
    array (
      'id' => 579,
      'orden' => 4,
      'core_modelo_id' => 38,
      'core_campo_id' => 173,
    ),
    556 => 
    array (
      'id' => 580,
      'orden' => 5,
      'core_modelo_id' => 38,
      'core_campo_id' => 174,
    ),
    557 => 
    array (
      'id' => 581,
      'orden' => 7,
      'core_modelo_id' => 38,
      'core_campo_id' => 175,
    ),
    558 => 
    array (
      'id' => 582,
      'orden' => 8,
      'core_modelo_id' => 38,
      'core_campo_id' => 176,
    ),
    559 => 
    array (
      'id' => 583,
      'orden' => 11,
      'core_modelo_id' => 38,
      'core_campo_id' => 177,
    ),
    560 => 
    array (
      'id' => 584,
      'orden' => 0,
      'core_modelo_id' => 70,
      'core_campo_id' => 177,
    ),
    561 => 
    array (
      'id' => 585,
      'orden' => 2,
      'core_modelo_id' => 71,
      'core_campo_id' => 177,
    ),
    562 => 
    array (
      'id' => 586,
      'orden' => 2,
      'core_modelo_id' => 75,
      'core_campo_id' => 177,
    ),
    563 => 
    array (
      'id' => 587,
      'orden' => 3,
      'core_modelo_id' => 39,
      'core_campo_id' => 183,
    ),
    564 => 
    array (
      'id' => 588,
      'orden' => 4,
      'core_modelo_id' => 39,
      'core_campo_id' => 184,
    ),
    565 => 
    array (
      'id' => 589,
      'orden' => 8,
      'core_modelo_id' => 39,
      'core_campo_id' => 185,
    ),
    566 => 
    array (
      'id' => 590,
      'orden' => 7,
      'core_modelo_id' => 39,
      'core_campo_id' => 186,
    ),
    567 => 
    array (
      'id' => 591,
      'orden' => 9,
      'core_modelo_id' => 39,
      'core_campo_id' => 187,
    ),
    568 => 
    array (
      'id' => 592,
      'orden' => 10,
      'core_modelo_id' => 39,
      'core_campo_id' => 188,
    ),
    569 => 
    array (
      'id' => 593,
      'orden' => 5,
      'core_modelo_id' => 39,
      'core_campo_id' => 189,
    ),
    570 => 
    array (
      'id' => 594,
      'orden' => 1,
      'core_modelo_id' => 25,
      'core_campo_id' => 192,
    ),
    571 => 
    array (
      'id' => 595,
      'orden' => 1,
      'core_modelo_id' => 60,
      'core_campo_id' => 192,
    ),
    572 => 
    array (
      'id' => 596,
      'orden' => 3,
      'core_modelo_id' => 43,
      'core_campo_id' => 194,
    ),
    573 => 
    array (
      'id' => 597,
      'orden' => 3,
      'core_modelo_id' => 51,
      'core_campo_id' => 194,
    ),
    574 => 
    array (
      'id' => 598,
      'orden' => 12,
      'core_modelo_id' => 139,
      'core_campo_id' => 194,
    ),
    575 => 
    array (
      'id' => 599,
      'orden' => 99,
      'core_modelo_id' => 5,
      'core_campo_id' => 195,
    ),
    576 => 
    array (
      'id' => 600,
      'orden' => 3,
      'core_modelo_id' => 69,
      'core_campo_id' => 195,
    ),
    577 => 
    array (
      'id' => 601,
      'orden' => 4,
      'core_modelo_id' => 33,
      'core_campo_id' => 196,
    ),
    578 => 
    array (
      'id' => 602,
      'orden' => 3,
      'core_modelo_id' => 44,
      'core_campo_id' => 196,
    ),
    579 => 
    array (
      'id' => 603,
      'orden' => 2,
      'core_modelo_id' => 44,
      'core_campo_id' => 197,
    ),
    580 => 
    array (
      'id' => 604,
      'orden' => 4,
      'core_modelo_id' => 44,
      'core_campo_id' => 198,
    ),
    581 => 
    array (
      'id' => 605,
      'orden' => 3,
      'core_modelo_id' => 45,
      'core_campo_id' => 199,
    ),
    582 => 
    array (
      'id' => 606,
      'orden' => 5,
      'core_modelo_id' => 46,
      'core_campo_id' => 200,
    ),
    583 => 
    array (
      'id' => 607,
      'orden' => 0,
      'core_modelo_id' => 7,
      'core_campo_id' => 201,
    ),
    584 => 
    array (
      'id' => 608,
      'orden' => 0,
      'core_modelo_id' => 21,
      'core_campo_id' => 201,
    ),
    585 => 
    array (
      'id' => 609,
      'orden' => 0,
      'core_modelo_id' => 22,
      'core_campo_id' => 201,
    ),
    586 => 
    array (
      'id' => 610,
      'orden' => 0,
      'core_modelo_id' => 24,
      'core_campo_id' => 201,
    ),
    587 => 
    array (
      'id' => 611,
      'orden' => 0,
      'core_modelo_id' => 33,
      'core_campo_id' => 201,
    ),
    588 => 
    array (
      'id' => 612,
      'orden' => 0,
      'core_modelo_id' => 43,
      'core_campo_id' => 201,
    ),
    589 => 
    array (
      'id' => 613,
      'orden' => 1,
      'core_modelo_id' => 45,
      'core_campo_id' => 201,
    ),
    590 => 
    array (
      'id' => 614,
      'orden' => 1,
      'core_modelo_id' => 46,
      'core_campo_id' => 201,
    ),
    591 => 
    array (
      'id' => 615,
      'orden' => 0,
      'core_modelo_id' => 47,
      'core_campo_id' => 201,
    ),
    592 => 
    array (
      'id' => 616,
      'orden' => 0,
      'core_modelo_id' => 48,
      'core_campo_id' => 201,
    ),
    593 => 
    array (
      'id' => 617,
      'orden' => 0,
      'core_modelo_id' => 49,
      'core_campo_id' => 201,
    ),
    594 => 
    array (
      'id' => 618,
      'orden' => 0,
      'core_modelo_id' => 50,
      'core_campo_id' => 201,
    ),
    595 => 
    array (
      'id' => 619,
      'orden' => 1,
      'core_modelo_id' => 51,
      'core_campo_id' => 201,
    ),
    596 => 
    array (
      'id' => 620,
      'orden' => 1,
      'core_modelo_id' => 52,
      'core_campo_id' => 201,
    ),
    597 => 
    array (
      'id' => 621,
      'orden' => 1,
      'core_modelo_id' => 54,
      'core_campo_id' => 201,
    ),
    598 => 
    array (
      'id' => 622,
      'orden' => 1,
      'core_modelo_id' => 55,
      'core_campo_id' => 201,
    ),
    599 => 
    array (
      'id' => 623,
      'orden' => 1,
      'core_modelo_id' => 56,
      'core_campo_id' => 201,
    ),
    600 => 
    array (
      'id' => 624,
      'orden' => 0,
      'core_modelo_id' => 59,
      'core_campo_id' => 201,
    ),
    601 => 
    array (
      'id' => 625,
      'orden' => 1,
      'core_modelo_id' => 65,
      'core_campo_id' => 201,
    ),
    602 => 
    array (
      'id' => 626,
      'orden' => 2,
      'core_modelo_id' => 139,
      'core_campo_id' => 201,
    ),
    603 => 
    array (
      'id' => 627,
      'orden' => 99,
      'core_modelo_id' => 46,
      'core_campo_id' => 202,
    ),
    604 => 
    array (
      'id' => 629,
      'orden' => 99,
      'core_modelo_id' => 52,
      'core_campo_id' => 203,
    ),
    605 => 
    array (
      'id' => 630,
      'orden' => 99,
      'core_modelo_id' => 59,
      'core_campo_id' => 203,
    ),
    606 => 
    array (
      'id' => 631,
      'orden' => 6,
      'core_modelo_id' => 43,
      'core_campo_id' => 204,
    ),
    607 => 
    array (
      'id' => 632,
      'orden' => 2,
      'core_modelo_id' => 64,
      'core_campo_id' => 204,
    ),
    608 => 
    array (
      'id' => 633,
      'orden' => 2,
      'core_modelo_id' => 39,
      'core_campo_id' => 205,
    ),
    609 => 
    array (
      'id' => 634,
      'orden' => 1,
      'core_modelo_id' => 39,
      'core_campo_id' => 206,
    ),
    610 => 
    array (
      'id' => 635,
      'orden' => 2,
      'core_modelo_id' => 48,
      'core_campo_id' => 208,
    ),
    611 => 
    array (
      'id' => 636,
      'orden' => 4,
      'core_modelo_id' => 48,
      'core_campo_id' => 209,
    ),
    612 => 
    array (
      'id' => 637,
      'orden' => 1,
      'core_modelo_id' => 48,
      'core_campo_id' => 210,
    ),
    613 => 
    array (
      'id' => 638,
      'orden' => 2,
      'core_modelo_id' => 49,
      'core_campo_id' => 210,
    ),
    614 => 
    array (
      'id' => 639,
      'orden' => 5,
      'core_modelo_id' => 33,
      'core_campo_id' => 211,
    ),
    615 => 
    array (
      'id' => 640,
      'orden' => 3,
      'core_modelo_id' => 45,
      'core_campo_id' => 211,
    ),
    616 => 
    array (
      'id' => 641,
      'orden' => 2,
      'core_modelo_id' => 113,
      'core_campo_id' => 215,
    ),
    617 => 
    array (
      'id' => 642,
      'orden' => 4,
      'core_modelo_id' => 129,
      'core_campo_id' => 215,
    ),
    618 => 
    array (
      'id' => 643,
      'orden' => 4,
      'core_modelo_id' => 131,
      'core_campo_id' => 215,
    ),
    619 => 
    array (
      'id' => 644,
      'orden' => 10,
      'core_modelo_id' => 3,
      'core_campo_id' => 221,
    ),
    620 => 
    array (
      'id' => 645,
      'orden' => 99,
      'core_modelo_id' => 47,
      'core_campo_id' => 222,
    ),
    621 => 
    array (
      'id' => 646,
      'orden' => 0,
      'core_modelo_id' => 30,
      'core_campo_id' => 223,
    ),
    622 => 
    array (
      'id' => 647,
      'orden' => 3,
      'core_modelo_id' => 49,
      'core_campo_id' => 224,
    ),
    623 => 
    array (
      'id' => 648,
      'orden' => 99,
      'core_modelo_id' => 47,
      'core_campo_id' => 225,
    ),
    624 => 
    array (
      'id' => 649,
      'orden' => 99,
      'core_modelo_id' => 51,
      'core_campo_id' => 225,
    ),
    625 => 
    array (
      'id' => 650,
      'orden' => 99,
      'core_modelo_id' => 54,
      'core_campo_id' => 225,
    ),
    626 => 
    array (
      'id' => 654,
      'orden' => 3,
      'core_modelo_id' => 50,
      'core_campo_id' => 229,
    ),
    627 => 
    array (
      'id' => 655,
      'orden' => 4,
      'core_modelo_id' => 50,
      'core_campo_id' => 230,
    ),
    628 => 
    array (
      'id' => 656,
      'orden' => 5,
      'core_modelo_id' => 50,
      'core_campo_id' => 231,
    ),
    629 => 
    array (
      'id' => 657,
      'orden' => 3,
      'core_modelo_id' => 42,
      'core_campo_id' => 232,
    ),
    630 => 
    array (
      'id' => 658,
      'orden' => 6,
      'core_modelo_id' => 39,
      'core_campo_id' => 233,
    ),
    631 => 
    array (
      'id' => 659,
      'orden' => 3,
      'core_modelo_id' => 53,
      'core_campo_id' => 234,
    ),
    632 => 
    array (
      'id' => 660,
      'orden' => 5,
      'core_modelo_id' => 69,
      'core_campo_id' => 234,
    ),
    633 => 
    array (
      'id' => 661,
      'orden' => 4,
      'core_modelo_id' => 53,
      'core_campo_id' => 235,
    ),
    634 => 
    array (
      'id' => 662,
      'orden' => 6,
      'core_modelo_id' => 69,
      'core_campo_id' => 235,
    ),
    635 => 
    array (
      'id' => 663,
      'orden' => 5,
      'core_modelo_id' => 53,
      'core_campo_id' => 236,
    ),
    636 => 
    array (
      'id' => 664,
      'orden' => 7,
      'core_modelo_id' => 69,
      'core_campo_id' => 236,
    ),
    637 => 
    array (
      'id' => 665,
      'orden' => 6,
      'core_modelo_id' => 53,
      'core_campo_id' => 237,
    ),
    638 => 
    array (
      'id' => 666,
      'orden' => 8,
      'core_modelo_id' => 69,
      'core_campo_id' => 237,
    ),
    639 => 
    array (
      'id' => 667,
      'orden' => 7,
      'core_modelo_id' => 53,
      'core_campo_id' => 238,
    ),
    640 => 
    array (
      'id' => 668,
      'orden' => 9,
      'core_modelo_id' => 69,
      'core_campo_id' => 238,
    ),
    641 => 
    array (
      'id' => 669,
      'orden' => 8,
      'core_modelo_id' => 53,
      'core_campo_id' => 239,
    ),
    642 => 
    array (
      'id' => 670,
      'orden' => 10,
      'core_modelo_id' => 69,
      'core_campo_id' => 239,
    ),
    643 => 
    array (
      'id' => 671,
      'orden' => 9,
      'core_modelo_id' => 53,
      'core_campo_id' => 240,
    ),
    644 => 
    array (
      'id' => 672,
      'orden' => 11,
      'core_modelo_id' => 69,
      'core_campo_id' => 240,
    ),
    645 => 
    array (
      'id' => 673,
      'orden' => 10,
      'core_modelo_id' => 53,
      'core_campo_id' => 241,
    ),
    646 => 
    array (
      'id' => 674,
      'orden' => 12,
      'core_modelo_id' => 69,
      'core_campo_id' => 241,
    ),
    647 => 
    array (
      'id' => 675,
      'orden' => 0,
      'core_modelo_id' => 37,
      'core_campo_id' => 242,
    ),
    648 => 
    array (
      'id' => 676,
      'orden' => 1,
      'core_modelo_id' => 53,
      'core_campo_id' => 243,
    ),
    649 => 
    array (
      'id' => 677,
      'orden' => 10,
      'core_modelo_id' => 29,
      'core_campo_id' => 244,
    ),
    650 => 
    array (
      'id' => 678,
      'orden' => 6,
      'core_modelo_id' => 66,
      'core_campo_id' => 244,
    ),
    651 => 
    array (
      'id' => 679,
      'orden' => 7,
      'core_modelo_id' => 134,
      'core_campo_id' => 244,
    ),
    652 => 
    array (
      'id' => 682,
      'orden' => 10,
      'core_modelo_id' => 29,
      'core_campo_id' => 245,
    ),
    653 => 
    array (
      'id' => 683,
      'orden' => 8,
      'core_modelo_id' => 134,
      'core_campo_id' => 245,
    ),
    654 => 
    array (
      'id' => 686,
      'orden' => 5,
      'core_modelo_id' => 54,
      'core_campo_id' => 247,
    ),
    655 => 
    array (
      'id' => 687,
      'orden' => 11,
      'core_modelo_id' => 54,
      'core_campo_id' => 248,
    ),
    656 => 
    array (
      'id' => 688,
      'orden' => 12,
      'core_modelo_id' => 54,
      'core_campo_id' => 249,
    ),
    657 => 
    array (
      'id' => 689,
      'orden' => 14,
      'core_modelo_id' => 54,
      'core_campo_id' => 250,
    ),
    658 => 
    array (
      'id' => 690,
      'orden' => 15,
      'core_modelo_id' => 54,
      'core_campo_id' => 251,
    ),
    659 => 
    array (
      'id' => 691,
      'orden' => 6,
      'core_modelo_id' => 55,
      'core_campo_id' => 252,
    ),
    660 => 
    array (
      'id' => 692,
      'orden' => 7,
      'core_modelo_id' => 55,
      'core_campo_id' => 253,
    ),
    661 => 
    array (
      'id' => 693,
      'orden' => 8,
      'core_modelo_id' => 55,
      'core_campo_id' => 254,
    ),
    662 => 
    array (
      'id' => 694,
      'orden' => 9,
      'core_modelo_id' => 55,
      'core_campo_id' => 255,
    ),
    663 => 
    array (
      'id' => 695,
      'orden' => 2,
      'core_modelo_id' => 55,
      'core_campo_id' => 256,
    ),
    664 => 
    array (
      'id' => 696,
      'orden' => 2,
      'core_modelo_id' => 37,
      'core_campo_id' => 257,
    ),
    665 => 
    array (
      'id' => 697,
      'orden' => 3,
      'core_modelo_id' => 55,
      'core_campo_id' => 257,
    ),
    666 => 
    array (
      'id' => 698,
      'orden' => 3,
      'core_modelo_id' => 56,
      'core_campo_id' => 257,
    ),
    667 => 
    array (
      'id' => 699,
      'orden' => 7,
      'core_modelo_id' => 87,
      'core_campo_id' => 257,
    ),
    668 => 
    array (
      'id' => 700,
      'orden' => 8,
      'core_modelo_id' => 88,
      'core_campo_id' => 257,
    ),
    669 => 
    array (
      'id' => 701,
      'orden' => 2,
      'core_modelo_id' => 97,
      'core_campo_id' => 257,
    ),
    670 => 
    array (
      'id' => 702,
      'orden' => 2,
      'core_modelo_id' => 99,
      'core_campo_id' => 257,
    ),
    671 => 
    array (
      'id' => 704,
      'orden' => 4,
      'core_modelo_id' => 123,
      'core_campo_id' => 257,
    ),
    672 => 
    array (
      'id' => 705,
      'orden' => 4,
      'core_modelo_id' => 124,
      'core_campo_id' => 257,
    ),
    673 => 
    array (
      'id' => 706,
      'orden' => 2,
      'core_modelo_id' => 56,
      'core_campo_id' => 258,
    ),
    674 => 
    array (
      'id' => 707,
      'orden' => 5,
      'core_modelo_id' => 56,
      'core_campo_id' => 259,
    ),
    675 => 
    array (
      'id' => 708,
      'orden' => 4,
      'core_modelo_id' => 56,
      'core_campo_id' => 261,
    ),
    676 => 
    array (
      'id' => 709,
      'orden' => 8,
      'core_modelo_id' => 26,
      'core_campo_id' => 262,
    ),
    677 => 
    array (
      'id' => 710,
      'orden' => 12,
      'core_modelo_id' => 26,
      'core_campo_id' => 263,
    ),
    678 => 
    array (
      'id' => 711,
      'orden' => 10,
      'core_modelo_id' => 26,
      'core_campo_id' => 264,
    ),
    679 => 
    array (
      'id' => 712,
      'orden' => 4,
      'core_modelo_id' => 59,
      'core_campo_id' => 265,
    ),
    680 => 
    array (
      'id' => 713,
      'orden' => 5,
      'core_modelo_id' => 59,
      'core_campo_id' => 266,
    ),
    681 => 
    array (
      'id' => 714,
      'orden' => 3,
      'core_modelo_id' => 60,
      'core_campo_id' => 267,
    ),
    682 => 
    array (
      'id' => 715,
      'orden' => 6,
      'core_modelo_id' => 61,
      'core_campo_id' => 268,
    ),
    683 => 
    array (
      'id' => 716,
      'orden' => 3,
      'core_modelo_id' => 64,
      'core_campo_id' => 270,
    ),
    684 => 
    array (
      'id' => 717,
      'orden' => 1,
      'core_modelo_id' => 63,
      'core_campo_id' => 271,
    ),
    685 => 
    array (
      'id' => 718,
      'orden' => 2,
      'core_modelo_id' => 63,
      'core_campo_id' => 272,
    ),
    686 => 
    array (
      'id' => 719,
      'orden' => 3,
      'core_modelo_id' => 63,
      'core_campo_id' => 273,
    ),
    687 => 
    array (
      'id' => 720,
      'orden' => 4,
      'core_modelo_id' => 66,
      'core_campo_id' => 274,
    ),
    688 => 
    array (
      'id' => 721,
      'orden' => 5,
      'core_modelo_id' => 134,
      'core_campo_id' => 274,
    ),
    689 => 
    array (
      'id' => 723,
      'orden' => 7,
      'core_modelo_id' => 66,
      'core_campo_id' => 277,
    ),
    690 => 
    array (
      'id' => 724,
      'orden' => 10,
      'core_modelo_id' => 66,
      'core_campo_id' => 279,
    ),
    691 => 
    array (
      'id' => 725,
      'orden' => 13,
      'core_modelo_id' => 66,
      'core_campo_id' => 280,
    ),
    692 => 
    array (
      'id' => 726,
      'orden' => 2,
      'core_modelo_id' => 67,
      'core_campo_id' => 281,
    ),
    693 => 
    array (
      'id' => 727,
      'orden' => 3,
      'core_modelo_id' => 67,
      'core_campo_id' => 282,
    ),
    694 => 
    array (
      'id' => 728,
      'orden' => 4,
      'core_modelo_id' => 67,
      'core_campo_id' => 283,
    ),
    695 => 
    array (
      'id' => 729,
      'orden' => 6,
      'core_modelo_id' => 67,
      'core_campo_id' => 284,
    ),
    696 => 
    array (
      'id' => 730,
      'orden' => 12,
      'core_modelo_id' => 66,
      'core_campo_id' => 285,
    ),
    697 => 
    array (
      'id' => 731,
      'orden' => 2,
      'core_modelo_id' => 62,
      'core_campo_id' => 286,
    ),
    698 => 
    array (
      'id' => 733,
      'orden' => 4,
      'core_modelo_id' => 62,
      'core_campo_id' => 288,
    ),
    699 => 
    array (
      'id' => 734,
      'orden' => 2,
      'core_modelo_id' => 1,
      'core_campo_id' => 289,
    ),
    700 => 
    array (
      'id' => 735,
      'orden' => 8,
      'core_modelo_id' => 66,
      'core_campo_id' => 289,
    ),
    701 => 
    array (
      'id' => 736,
      'orden' => 1,
      'core_modelo_id' => 19,
      'core_campo_id' => 290,
    ),
    702 => 
    array (
      'id' => 737,
      'orden' => 3,
      'core_modelo_id' => 19,
      'core_campo_id' => 291,
    ),
    703 => 
    array (
      'id' => 738,
      'orden' => 4,
      'core_modelo_id' => 19,
      'core_campo_id' => 292,
    ),
    704 => 
    array (
      'id' => 740,
      'orden' => 7,
      'core_modelo_id' => 19,
      'core_campo_id' => 294,
    ),
    705 => 
    array (
      'id' => 741,
      'orden' => 13,
      'core_modelo_id' => 66,
      'core_campo_id' => 294,
    ),
    706 => 
    array (
      'id' => 742,
      'orden' => 8,
      'core_modelo_id' => 19,
      'core_campo_id' => 295,
    ),
    707 => 
    array (
      'id' => 743,
      'orden' => 9,
      'core_modelo_id' => 19,
      'core_campo_id' => 296,
    ),
    708 => 
    array (
      'id' => 744,
      'orden' => 10,
      'core_modelo_id' => 19,
      'core_campo_id' => 297,
    ),
    709 => 
    array (
      'id' => 745,
      'orden' => 15,
      'core_modelo_id' => 29,
      'core_campo_id' => 298,
    ),
    710 => 
    array (
      'id' => 746,
      'orden' => 19,
      'core_modelo_id' => 29,
      'core_campo_id' => 299,
    ),
    711 => 
    array (
      'id' => 747,
      'orden' => 22,
      'core_modelo_id' => 29,
      'core_campo_id' => 300,
    ),
    712 => 
    array (
      'id' => 749,
      'orden' => 23,
      'core_modelo_id' => 29,
      'core_campo_id' => 301,
    ),
    713 => 
    array (
      'id' => 750,
      'orden' => 24,
      'core_modelo_id' => 29,
      'core_campo_id' => 302,
    ),
    714 => 
    array (
      'id' => 751,
      'orden' => 25,
      'core_modelo_id' => 29,
      'core_campo_id' => 303,
    ),
    715 => 
    array (
      'id' => 752,
      'orden' => 3,
      'core_modelo_id' => 68,
      'core_campo_id' => 304,
    ),
    716 => 
    array (
      'id' => 753,
      'orden' => 13,
      'core_modelo_id' => 66,
      'core_campo_id' => 305,
    ),
    717 => 
    array (
      'id' => 754,
      'orden' => 1,
      'core_modelo_id' => 31,
      'core_campo_id' => 306,
    ),
    718 => 
    array (
      'id' => 755,
      'orden' => 2,
      'core_modelo_id' => 11,
      'core_campo_id' => 307,
    ),
    719 => 
    array (
      'id' => 757,
      'orden' => 10,
      'core_modelo_id' => 3,
      'core_campo_id' => 309,
    ),
    720 => 
    array (
      'id' => 758,
      'orden' => 12,
      'core_modelo_id' => 38,
      'core_campo_id' => 311,
    ),
    721 => 
    array (
      'id' => 759,
      'orden' => 1,
      'core_modelo_id' => 70,
      'core_campo_id' => 311,
    ),
    722 => 
    array (
      'id' => 760,
      'orden' => 3,
      'core_modelo_id' => 71,
      'core_campo_id' => 311,
    ),
    723 => 
    array (
      'id' => 761,
      'orden' => 3,
      'core_modelo_id' => 75,
      'core_campo_id' => 311,
    ),
    724 => 
    array (
      'id' => 763,
      'orden' => 2,
      'core_modelo_id' => 70,
      'core_campo_id' => 312,
    ),
    725 => 
    array (
      'id' => 764,
      'orden' => 4,
      'core_modelo_id' => 71,
      'core_campo_id' => 312,
    ),
    726 => 
    array (
      'id' => 765,
      'orden' => 4,
      'core_modelo_id' => 75,
      'core_campo_id' => 312,
    ),
    727 => 
    array (
      'id' => 767,
      'orden' => 99,
      'core_modelo_id' => 71,
      'core_campo_id' => 314,
    ),
    728 => 
    array (
      'id' => 768,
      'orden' => 99,
      'core_modelo_id' => 75,
      'core_campo_id' => 314,
    ),
    729 => 
    array (
      'id' => 769,
      'orden' => 99,
      'core_modelo_id' => 119,
      'core_campo_id' => 314,
    ),
    730 => 
    array (
      'id' => 770,
      'orden' => 99,
      'core_modelo_id' => 121,
      'core_campo_id' => 314,
    ),
    731 => 
    array (
      'id' => 771,
      'orden' => 99,
      'core_modelo_id' => 122,
      'core_campo_id' => 314,
    ),
    732 => 
    array (
      'id' => 772,
      'orden' => 1,
      'core_modelo_id' => 76,
      'core_campo_id' => 316,
    ),
    733 => 
    array (
      'id' => 773,
      'orden' => 2,
      'core_modelo_id' => 76,
      'core_campo_id' => 317,
    ),
    734 => 
    array (
      'id' => 774,
      'orden' => 3,
      'core_modelo_id' => 76,
      'core_campo_id' => 318,
    ),
    735 => 
    array (
      'id' => 775,
      'orden' => 99,
      'core_modelo_id' => 76,
      'core_campo_id' => 319,
    ),
    736 => 
    array (
      'id' => 776,
      'orden' => 5,
      'core_modelo_id' => 76,
      'core_campo_id' => 320,
    ),
    737 => 
    array (
      'id' => 777,
      'orden' => 9,
      'core_modelo_id' => 81,
      'core_campo_id' => 342,
    ),
    738 => 
    array (
      'id' => 778,
      'orden' => 12,
      'core_modelo_id' => 81,
      'core_campo_id' => 343,
    ),
    739 => 
    array (
      'id' => 782,
      'orden' => 9,
      'core_modelo_id' => 76,
      'core_campo_id' => 353,
    ),
    740 => 
    array (
      'id' => 784,
      'orden' => 7,
      'core_modelo_id' => 76,
      'core_campo_id' => 355,
    ),
    741 => 
    array (
      'id' => 786,
      'orden' => 8,
      'core_modelo_id' => 123,
      'core_campo_id' => 355,
    ),
    742 => 
    array (
      'id' => 788,
      'orden' => 3,
      'core_modelo_id' => 83,
      'core_campo_id' => 357,
    ),
    743 => 
    array (
      'id' => 790,
      'orden' => 5,
      'core_modelo_id' => 83,
      'core_campo_id' => 359,
    ),
    744 => 
    array (
      'id' => 791,
      'orden' => 7,
      'core_modelo_id' => 83,
      'core_campo_id' => 360,
    ),
    745 => 
    array (
      'id' => 792,
      'orden' => 8,
      'core_modelo_id' => 83,
      'core_campo_id' => 361,
    ),
    746 => 
    array (
      'id' => 793,
      'orden' => 8,
      'core_modelo_id' => 83,
      'core_campo_id' => 362,
    ),
    747 => 
    array (
      'id' => 794,
      'orden' => 9,
      'core_modelo_id' => 83,
      'core_campo_id' => 363,
    ),
    748 => 
    array (
      'id' => 795,
      'orden' => 12,
      'core_modelo_id' => 83,
      'core_campo_id' => 364,
    ),
    749 => 
    array (
      'id' => 796,
      'orden' => 1,
      'core_modelo_id' => 84,
      'core_campo_id' => 365,
    ),
    750 => 
    array (
      'id' => 798,
      'orden' => 2,
      'core_modelo_id' => 88,
      'core_campo_id' => 368,
    ),
    751 => 
    array (
      'id' => 799,
      'orden' => 4,
      'core_modelo_id' => 87,
      'core_campo_id' => 369,
    ),
    752 => 
    array (
      'id' => 800,
      'orden' => 6,
      'core_modelo_id' => 88,
      'core_campo_id' => 369,
    ),
    753 => 
    array (
      'id' => 801,
      'orden' => 5,
      'core_modelo_id' => 87,
      'core_campo_id' => 370,
    ),
    754 => 
    array (
      'id' => 802,
      'orden' => 6,
      'core_modelo_id' => 84,
      'core_campo_id' => 371,
    ),
    755 => 
    array (
      'id' => 803,
      'orden' => 6,
      'core_modelo_id' => 87,
      'core_campo_id' => 372,
    ),
    756 => 
    array (
      'id' => 804,
      'orden' => 7,
      'core_modelo_id' => 88,
      'core_campo_id' => 372,
    ),
    757 => 
    array (
      'id' => 805,
      'orden' => 4,
      'core_modelo_id' => 88,
      'core_campo_id' => 373,
    ),
    758 => 
    array (
      'id' => 806,
      'orden' => 5,
      'core_modelo_id' => 88,
      'core_campo_id' => 374,
    ),
    759 => 
    array (
      'id' => 807,
      'orden' => 3,
      'core_modelo_id' => 84,
      'core_campo_id' => 379,
    ),
    760 => 
    array (
      'id' => 808,
      'orden' => 1,
      'core_modelo_id' => 91,
      'core_campo_id' => 381,
    ),
    761 => 
    array (
      'id' => 809,
      'orden' => 2,
      'core_modelo_id' => 91,
      'core_campo_id' => 384,
    ),
    762 => 
    array (
      'id' => 810,
      'orden' => 3,
      'core_modelo_id' => 91,
      'core_campo_id' => 385,
    ),
    763 => 
    array (
      'id' => 811,
      'orden' => 0,
      'core_modelo_id' => 8,
      'core_campo_id' => 386,
    ),
    764 => 
    array (
      'id' => 812,
      'orden' => 99,
      'core_modelo_id' => 90,
      'core_campo_id' => 386,
    ),
    765 => 
    array (
      'id' => 813,
      'orden' => 4,
      'core_modelo_id' => 11,
      'core_campo_id' => 387,
    ),
    766 => 
    array (
      'id' => 814,
      'orden' => 8,
      'core_modelo_id' => 84,
      'core_campo_id' => 387,
    ),
    767 => 
    array (
      'id' => 815,
      'orden' => 2,
      'core_modelo_id' => 98,
      'core_campo_id' => 387,
    ),
    768 => 
    array (
      'id' => 816,
      'orden' => 3,
      'core_modelo_id' => 122,
      'core_campo_id' => 387,
    ),
    769 => 
    array (
      'id' => 817,
      'orden' => 2,
      'core_modelo_id' => 92,
      'core_campo_id' => 388,
    ),
    770 => 
    array (
      'id' => 818,
      'orden' => 11,
      'core_modelo_id' => 93,
      'core_campo_id' => 389,
    ),
    771 => 
    array (
      'id' => 819,
      'orden' => 12,
      'core_modelo_id' => 93,
      'core_campo_id' => 390,
    ),
    772 => 
    array (
      'id' => 820,
      'orden' => 99,
      'core_modelo_id' => 95,
      'core_campo_id' => 391,
    ),
    773 => 
    array (
      'id' => 821,
      'orden' => 12,
      'core_modelo_id' => 95,
      'core_campo_id' => 392,
    ),
    774 => 
    array (
      'id' => 822,
      'orden' => 13,
      'core_modelo_id' => 95,
      'core_campo_id' => 393,
    ),
    775 => 
    array (
      'id' => 824,
      'orden' => 14,
      'core_modelo_id' => 95,
      'core_campo_id' => 395,
    ),
    776 => 
    array (
      'id' => 825,
      'orden' => 99,
      'core_modelo_id' => 96,
      'core_campo_id' => 396,
    ),
    777 => 
    array (
      'id' => 826,
      'orden' => 99,
      'core_modelo_id' => 100,
      'core_campo_id' => 396,
    ),
    778 => 
    array (
      'id' => 827,
      'orden' => 99,
      'core_modelo_id' => 101,
      'core_campo_id' => 396,
    ),
    779 => 
    array (
      'id' => 828,
      'orden' => 2,
      'core_modelo_id' => 96,
      'core_campo_id' => 397,
    ),
    780 => 
    array (
      'id' => 829,
      'orden' => 99,
      'core_modelo_id' => 96,
      'core_campo_id' => 399,
    ),
    781 => 
    array (
      'id' => 830,
      'orden' => 5,
      'core_modelo_id' => 96,
      'core_campo_id' => 400,
    ),
    782 => 
    array (
      'id' => 831,
      'orden' => 6,
      'core_modelo_id' => 96,
      'core_campo_id' => 401,
    ),
    783 => 
    array (
      'id' => 832,
      'orden' => 7,
      'core_modelo_id' => 96,
      'core_campo_id' => 402,
    ),
    784 => 
    array (
      'id' => 833,
      'orden' => 1,
      'core_modelo_id' => 111,
      'core_campo_id' => 404,
    ),
    785 => 
    array (
      'id' => 834,
      'orden' => 3,
      'core_modelo_id' => 97,
      'core_campo_id' => 406,
    ),
    786 => 
    array (
      'id' => 835,
      'orden' => 4,
      'core_modelo_id' => 101,
      'core_campo_id' => 408,
    ),
    787 => 
    array (
      'id' => 836,
      'orden' => 12,
      'core_modelo_id' => 101,
      'core_campo_id' => 411,
    ),
    788 => 
    array (
      'id' => 837,
      'orden' => 1,
      'core_modelo_id' => 102,
      'core_campo_id' => 412,
    ),
    789 => 
    array (
      'id' => 838,
      'orden' => 99,
      'core_modelo_id' => 110,
      'core_campo_id' => 412,
    ),
    790 => 
    array (
      'id' => 839,
      'orden' => 99,
      'core_modelo_id' => 111,
      'core_campo_id' => 412,
    ),
    791 => 
    array (
      'id' => 840,
      'orden' => 99,
      'core_modelo_id' => 115,
      'core_campo_id' => 412,
    ),
    792 => 
    array (
      'id' => 841,
      'orden' => 2,
      'core_modelo_id' => 102,
      'core_campo_id' => 413,
    ),
    793 => 
    array (
      'id' => 842,
      'orden' => 99,
      'core_modelo_id' => 110,
      'core_campo_id' => 413,
    ),
    794 => 
    array (
      'id' => 843,
      'orden' => 99,
      'core_modelo_id' => 111,
      'core_campo_id' => 413,
    ),
    795 => 
    array (
      'id' => 844,
      'orden' => 99,
      'core_modelo_id' => 115,
      'core_campo_id' => 413,
    ),
    796 => 
    array (
      'id' => 845,
      'orden' => 3,
      'core_modelo_id' => 102,
      'core_campo_id' => 414,
    ),
    797 => 
    array (
      'id' => 846,
      'orden' => 1,
      'core_modelo_id' => 104,
      'core_campo_id' => 415,
    ),
    798 => 
    array (
      'id' => 847,
      'orden' => 1,
      'core_modelo_id' => 105,
      'core_campo_id' => 415,
    ),
    799 => 
    array (
      'id' => 848,
      'orden' => 2,
      'core_modelo_id' => 105,
      'core_campo_id' => 416,
    ),
    800 => 
    array (
      'id' => 849,
      'orden' => 2,
      'core_modelo_id' => 104,
      'core_campo_id' => 417,
    ),
    801 => 
    array (
      'id' => 850,
      'orden' => 3,
      'core_modelo_id' => 104,
      'core_campo_id' => 418,
    ),
    802 => 
    array (
      'id' => 851,
      'orden' => 99,
      'core_modelo_id' => 100,
      'core_campo_id' => 419,
    ),
    803 => 
    array (
      'id' => 852,
      'orden' => 99,
      'core_modelo_id' => 101,
      'core_campo_id' => 419,
    ),
    804 => 
    array (
      'id' => 853,
      'orden' => 99,
      'core_modelo_id' => 100,
      'core_campo_id' => 420,
    ),
    805 => 
    array (
      'id' => 854,
      'orden' => 8,
      'core_modelo_id' => 96,
      'core_campo_id' => 421,
    ),
    806 => 
    array (
      'id' => 855,
      'orden' => 5,
      'core_modelo_id' => 101,
      'core_campo_id' => 422,
    ),
    807 => 
    array (
      'id' => 856,
      'orden' => 6,
      'core_modelo_id' => 101,
      'core_campo_id' => 423,
    ),
    808 => 
    array (
      'id' => 857,
      'orden' => 10,
      'core_modelo_id' => 101,
      'core_campo_id' => 424,
    ),
    809 => 
    array (
      'id' => 858,
      'orden' => 2,
      'core_modelo_id' => 109,
      'core_campo_id' => 425,
    ),
    810 => 
    array (
      'id' => 859,
      'orden' => 6,
      'core_modelo_id' => 109,
      'core_campo_id' => 426,
    ),
    811 => 
    array (
      'id' => 861,
      'orden' => 22,
      'core_modelo_id' => 109,
      'core_campo_id' => 428,
    ),
    812 => 
    array (
      'id' => 862,
      'orden' => 6,
      'core_modelo_id' => 110,
      'core_campo_id' => 429,
    ),
    813 => 
    array (
      'id' => 863,
      'orden' => 5,
      'core_modelo_id' => 110,
      'core_campo_id' => 430,
    ),
    814 => 
    array (
      'id' => 864,
      'orden' => 3,
      'core_modelo_id' => 110,
      'core_campo_id' => 431,
    ),
    815 => 
    array (
      'id' => 865,
      'orden' => 99,
      'core_modelo_id' => 110,
      'core_campo_id' => 432,
    ),
    816 => 
    array (
      'id' => 866,
      'orden' => 99,
      'core_modelo_id' => 111,
      'core_campo_id' => 432,
    ),
    817 => 
    array (
      'id' => 867,
      'orden' => 99,
      'core_modelo_id' => 115,
      'core_campo_id' => 432,
    ),
    818 => 
    array (
      'id' => 868,
      'orden' => 2,
      'core_modelo_id' => 111,
      'core_campo_id' => 433,
    ),
    819 => 
    array (
      'id' => 869,
      'orden' => 4,
      'core_modelo_id' => 111,
      'core_campo_id' => 434,
    ),
    820 => 
    array (
      'id' => 870,
      'orden' => 6,
      'core_modelo_id' => 111,
      'core_campo_id' => 435,
    ),
    821 => 
    array (
      'id' => 871,
      'orden' => 8,
      'core_modelo_id' => 111,
      'core_campo_id' => 436,
    ),
    822 => 
    array (
      'id' => 872,
      'orden' => 6,
      'core_modelo_id' => 110,
      'core_campo_id' => 437,
    ),
    823 => 
    array (
      'id' => 873,
      'orden' => 6,
      'core_modelo_id' => 110,
      'core_campo_id' => 438,
    ),
    824 => 
    array (
      'id' => 874,
      'orden' => 8,
      'core_modelo_id' => 110,
      'core_campo_id' => 439,
    ),
    825 => 
    array (
      'id' => 875,
      'orden' => 10,
      'core_modelo_id' => 110,
      'core_campo_id' => 440,
    ),
    826 => 
    array (
      'id' => 876,
      'orden' => 14,
      'core_modelo_id' => 110,
      'core_campo_id' => 441,
    ),
    827 => 
    array (
      'id' => 880,
      'orden' => 27,
      'core_modelo_id' => 110,
      'core_campo_id' => 447,
    ),
    828 => 
    array (
      'id' => 881,
      'orden' => 1,
      'core_modelo_id' => 113,
      'core_campo_id' => 448,
    ),
    829 => 
    array (
      'id' => 882,
      'orden' => 32,
      'core_modelo_id' => 138,
      'core_campo_id' => 448,
    ),
    830 => 
    array (
      'id' => 883,
      'orden' => 25,
      'core_modelo_id' => 146,
      'core_campo_id' => 448,
    ),
    831 => 
    array (
      'id' => 884,
      'orden' => 3,
      'core_modelo_id' => 113,
      'core_campo_id' => 449,
    ),
    832 => 
    array (
      'id' => 885,
      'orden' => 4,
      'core_modelo_id' => 114,
      'core_campo_id' => 459,
    ),
    833 => 
    array (
      'id' => 886,
      'orden' => 10,
      'core_modelo_id' => 114,
      'core_campo_id' => 461,
    ),
    834 => 
    array (
      'id' => 887,
      'orden' => 2,
      'core_modelo_id' => 115,
      'core_campo_id' => 464,
    ),
    835 => 
    array (
      'id' => 888,
      'orden' => 4,
      'core_modelo_id' => 115,
      'core_campo_id' => 465,
    ),
    836 => 
    array (
      'id' => 889,
      'orden' => 6,
      'core_modelo_id' => 115,
      'core_campo_id' => 466,
    ),
    837 => 
    array (
      'id' => 890,
      'orden' => 10,
      'core_modelo_id' => 109,
      'core_campo_id' => 469,
    ),
    838 => 
    array (
      'id' => 893,
      'orden' => 16,
      'core_modelo_id' => 114,
      'core_campo_id' => 472,
    ),
    839 => 
    array (
      'id' => 894,
      'orden' => 5,
      'core_modelo_id' => 118,
      'core_campo_id' => 475,
    ),
    840 => 
    array (
      'id' => 895,
      'orden' => 3,
      'core_modelo_id' => 36,
      'core_campo_id' => 478,
    ),
    841 => 
    array (
      'id' => 896,
      'orden' => 4,
      'core_modelo_id' => 37,
      'core_campo_id' => 479,
    ),
    842 => 
    array (
      'id' => 897,
      'orden' => 4,
      'core_modelo_id' => 121,
      'core_campo_id' => 480,
    ),
    843 => 
    array (
      'id' => 898,
      'orden' => 5,
      'core_modelo_id' => 122,
      'core_campo_id' => 481,
    ),
    844 => 
    array (
      'id' => 900,
      'orden' => 6,
      'core_modelo_id' => 123,
      'core_campo_id' => 484,
    ),
    845 => 
    array (
      'id' => 901,
      'orden' => 99,
      'core_modelo_id' => 123,
      'core_campo_id' => 486,
    ),
    846 => 
    array (
      'id' => 902,
      'orden' => 99,
      'core_modelo_id' => 123,
      'core_campo_id' => 487,
    ),
    847 => 
    array (
      'id' => 903,
      'orden' => 6,
      'core_modelo_id' => 124,
      'core_campo_id' => 488,
    ),
    848 => 
    array (
      'id' => 904,
      'orden' => 6,
      'core_modelo_id' => 49,
      'core_campo_id' => 494,
    ),
    849 => 
    array (
      'id' => 905,
      'orden' => 38,
      'core_modelo_id' => 138,
      'core_campo_id' => 495,
    ),
    850 => 
    array (
      'id' => 906,
      'orden' => 26,
      'core_modelo_id' => 138,
      'core_campo_id' => 496,
    ),
    851 => 
    array (
      'id' => 907,
      'orden' => 28,
      'core_modelo_id' => 138,
      'core_campo_id' => 497,
    ),
    852 => 
    array (
      'id' => 908,
      'orden' => 40,
      'core_modelo_id' => 138,
      'core_campo_id' => 498,
    ),
    853 => 
    array (
      'id' => 909,
      'orden' => 27,
      'core_modelo_id' => 146,
      'core_campo_id' => 498,
    ),
    854 => 
    array (
      'id' => 910,
      'orden' => 42,
      'core_modelo_id' => 138,
      'core_campo_id' => 499,
    ),
    855 => 
    array (
      'id' => 911,
      'orden' => 44,
      'core_modelo_id' => 138,
      'core_campo_id' => 500,
    ),
    856 => 
    array (
      'id' => 912,
      'orden' => 46,
      'core_modelo_id' => 138,
      'core_campo_id' => 501,
    ),
    857 => 
    array (
      'id' => 913,
      'orden' => 48,
      'core_modelo_id' => 138,
      'core_campo_id' => 502,
    ),
    858 => 
    array (
      'id' => 914,
      'orden' => 4,
      'core_modelo_id' => 137,
      'core_campo_id' => 503,
    ),
    859 => 
    array (
      'id' => 915,
      'orden' => 6,
      'core_modelo_id' => 137,
      'core_campo_id' => 504,
    ),
    860 => 
    array (
      'id' => 916,
      'orden' => 6,
      'core_modelo_id' => 143,
      'core_campo_id' => 504,
    ),
    861 => 
    array (
      'id' => 917,
      'orden' => 4,
      'core_modelo_id' => 132,
      'core_campo_id' => 505,
    ),
    862 => 
    array (
      'id' => 918,
      'orden' => 8,
      'core_modelo_id' => 137,
      'core_campo_id' => 505,
    ),
    863 => 
    array (
      'id' => 919,
      'orden' => 8,
      'core_modelo_id' => 143,
      'core_campo_id' => 505,
    ),
    864 => 
    array (
      'id' => 920,
      'orden' => 4,
      'core_modelo_id' => 136,
      'core_campo_id' => 506,
    ),
    865 => 
    array (
      'id' => 921,
      'orden' => 4,
      'core_modelo_id' => 145,
      'core_campo_id' => 506,
    ),
    866 => 
    array (
      'id' => 922,
      'orden' => 4,
      'core_modelo_id' => 135,
      'core_campo_id' => 507,
    ),
    867 => 
    array (
      'id' => 923,
      'orden' => 20,
      'core_modelo_id' => 134,
      'core_campo_id' => 508,
    ),
    868 => 
    array (
      'id' => 924,
      'orden' => 22,
      'core_modelo_id' => 134,
      'core_campo_id' => 509,
    ),
    869 => 
    array (
      'id' => 925,
      'orden' => 4,
      'core_modelo_id' => 133,
      'core_campo_id' => 510,
    ),
    870 => 
    array (
      'id' => 926,
      'orden' => 2,
      'core_modelo_id' => 131,
      'core_campo_id' => 511,
    ),
    871 => 
    array (
      'id' => 927,
      'orden' => 36,
      'core_modelo_id' => 138,
      'core_campo_id' => 511,
    ),
    872 => 
    array (
      'id' => 928,
      'orden' => 6,
      'core_modelo_id' => 129,
      'core_campo_id' => 512,
    ),
    873 => 
    array (
      'id' => 929,
      'orden' => 6,
      'core_modelo_id' => 131,
      'core_campo_id' => 512,
    ),
    874 => 
    array (
      'id' => 930,
      'orden' => 8,
      'core_modelo_id' => 131,
      'core_campo_id' => 513,
    ),
    875 => 
    array (
      'id' => 931,
      'orden' => 10,
      'core_modelo_id' => 131,
      'core_campo_id' => 514,
    ),
    876 => 
    array (
      'id' => 932,
      'orden' => 2,
      'core_modelo_id' => 129,
      'core_campo_id' => 515,
    ),
    877 => 
    array (
      'id' => 933,
      'orden' => 34,
      'core_modelo_id' => 138,
      'core_campo_id' => 515,
    ),
    878 => 
    array (
      'id' => 934,
      'orden' => 8,
      'core_modelo_id' => 129,
      'core_campo_id' => 516,
    ),
    879 => 
    array (
      'id' => 935,
      'orden' => 4,
      'core_modelo_id' => 128,
      'core_campo_id' => 517,
    ),
    880 => 
    array (
      'id' => 936,
      'orden' => 2,
      'core_modelo_id' => 127,
      'core_campo_id' => 518,
    ),
    881 => 
    array (
      'id' => 937,
      'orden' => 4,
      'core_modelo_id' => 127,
      'core_campo_id' => 519,
    ),
    882 => 
    array (
      'id' => 938,
      'orden' => 6,
      'core_modelo_id' => 127,
      'core_campo_id' => 520,
    ),
    883 => 
    array (
      'id' => 939,
      'orden' => 30,
      'core_modelo_id' => 138,
      'core_campo_id' => 521,
    ),
    884 => 
    array (
      'id' => 940,
      'orden' => 8,
      'core_modelo_id' => 139,
      'core_campo_id' => 521,
    ),
    885 => 
    array (
      'id' => 941,
      'orden' => 6,
      'core_modelo_id' => 139,
      'core_campo_id' => 522,
    ),
    886 => 
    array (
      'id' => 942,
      'orden' => 10,
      'core_modelo_id' => 139,
      'core_campo_id' => 523,
    ),
    887 => 
    array (
      'id' => 943,
      'orden' => 14,
      'core_modelo_id' => 139,
      'core_campo_id' => 524,
    ),
    888 => 
    array (
      'id' => 944,
      'orden' => 4,
      'core_modelo_id' => 140,
      'core_campo_id' => 525,
    ),
    889 => 
    array (
      'id' => 945,
      'orden' => 6,
      'core_modelo_id' => 140,
      'core_campo_id' => 526,
    ),
    890 => 
    array (
      'id' => 946,
      'orden' => 8,
      'core_modelo_id' => 140,
      'core_campo_id' => 527,
    ),
    891 => 
    array (
      'id' => 947,
      'orden' => 10,
      'core_modelo_id' => 140,
      'core_campo_id' => 528,
    ),
    892 => 
    array (
      'id' => 948,
      'orden' => 12,
      'core_modelo_id' => 140,
      'core_campo_id' => 529,
    ),
    893 => 
    array (
      'id' => 949,
      'orden' => 12,
      'core_modelo_id' => 22,
      'core_campo_id' => 530,
    ),
    894 => 
    array (
      'id' => 951,
      'orden' => 4,
      'core_modelo_id' => 81,
      'core_campo_id' => 532,
    ),
    895 => 
    array (
      'id' => 952,
      'orden' => 24,
      'core_modelo_id' => 146,
      'core_campo_id' => 534,
    ),
    896 => 
    array (
      'id' => 953,
      'orden' => 4,
      'core_modelo_id' => 143,
      'core_campo_id' => 535,
    ),
    897 => 
    array (
      'id' => 954,
      'orden' => 28,
      'core_modelo_id' => 146,
      'core_campo_id' => 537,
    ),
    898 => 
    array (
      'id' => 1006,
      'orden' => 20,
      'core_modelo_id' => 142,
      'core_campo_id' => 18,
    ),
    899 => 
    array (
      'id' => 1007,
      'orden' => 30,
      'core_modelo_id' => 142,
      'core_campo_id' => 22,
    ),
    900 => 
    array (
      'id' => 1008,
      'orden' => 1,
      'core_modelo_id' => 142,
      'core_campo_id' => 41,
    ),
    901 => 
    array (
      'id' => 1009,
      'orden' => 14,
      'core_modelo_id' => 142,
      'core_campo_id' => 42,
    ),
    902 => 
    array (
      'id' => 1010,
      'orden' => 6,
      'core_modelo_id' => 142,
      'core_campo_id' => 43,
    ),
    903 => 
    array (
      'id' => 1011,
      'orden' => 2,
      'core_modelo_id' => 142,
      'core_campo_id' => 45,
    ),
    904 => 
    array (
      'id' => 1012,
      'orden' => 4,
      'core_modelo_id' => 142,
      'core_campo_id' => 46,
    ),
    905 => 
    array (
      'id' => 1013,
      'orden' => 16,
      'core_modelo_id' => 142,
      'core_campo_id' => 50,
    ),
    906 => 
    array (
      'id' => 1014,
      'orden' => 22,
      'core_modelo_id' => 142,
      'core_campo_id' => 53,
    ),
    907 => 
    array (
      'id' => 1015,
      'orden' => 18,
      'core_modelo_id' => 142,
      'core_campo_id' => 55,
    ),
    908 => 
    array (
      'id' => 1016,
      'orden' => 8,
      'core_modelo_id' => 142,
      'core_campo_id' => 58,
    ),
    909 => 
    array (
      'id' => 1017,
      'orden' => 10,
      'core_modelo_id' => 142,
      'core_campo_id' => 244,
    ),
    910 => 
    array (
      'id' => 1018,
      'orden' => 12,
      'core_modelo_id' => 142,
      'core_campo_id' => 245,
    ),
    911 => 
    array (
      'id' => 1023,
      'orden' => 16,
      'core_modelo_id' => 147,
      'core_campo_id' => 8,
    ),
    912 => 
    array (
      'id' => 1024,
      'orden' => 99,
      'core_modelo_id' => 147,
      'core_campo_id' => 87,
    ),
    913 => 
    array (
      'id' => 1025,
      'orden' => 3,
      'core_modelo_id' => 147,
      'core_campo_id' => 88,
    ),
    914 => 
    array (
      'id' => 1026,
      'orden' => 13,
      'core_modelo_id' => 147,
      'core_campo_id' => 90,
    ),
    915 => 
    array (
      'id' => 1027,
      'orden' => 4,
      'core_modelo_id' => 147,
      'core_campo_id' => 93,
    ),
    916 => 
    array (
      'id' => 1028,
      'orden' => 99,
      'core_modelo_id' => 147,
      'core_campo_id' => 100,
    ),
    917 => 
    array (
      'id' => 1029,
      'orden' => 12,
      'core_modelo_id' => 147,
      'core_campo_id' => 194,
    ),
    918 => 
    array (
      'id' => 1030,
      'orden' => 2,
      'core_modelo_id' => 147,
      'core_campo_id' => 201,
    ),
    919 => 
    array (
      'id' => 1033,
      'orden' => 10,
      'core_modelo_id' => 147,
      'core_campo_id' => 523,
    ),
    920 => 
    array (
      'id' => 1035,
      'orden' => 6,
      'core_modelo_id' => 147,
      'core_campo_id' => 538,
    ),
    921 => 
    array (
      'id' => 1036,
      'orden' => 18,
      'core_modelo_id' => 147,
      'core_campo_id' => 539,
    ),
    922 => 
    array (
      'id' => 1037,
      'orden' => 20,
      'core_modelo_id' => 147,
      'core_campo_id' => 540,
    ),
    923 => 
    array (
      'id' => 1038,
      'orden' => 99,
      'core_modelo_id' => 147,
      'core_campo_id' => 98,
    ),
    924 => 
    array (
      'id' => 1039,
      'orden' => 99,
      'core_modelo_id' => 147,
      'core_campo_id' => 94,
    ),
    925 => 
    array (
      'id' => 1040,
      'orden' => 99,
      'core_modelo_id' => 147,
      'core_campo_id' => 95,
    ),
    926 => 
    array (
      'id' => 1041,
      'orden' => 2,
      'core_modelo_id' => 24,
      'core_campo_id' => 140,
    ),
    927 => 
    array (
      'id' => 1042,
      'orden' => 2,
      'core_modelo_id' => 22,
      'core_campo_id' => 140,
    ),
    928 => 
    array (
      'id' => 1044,
      'orden' => 4,
      'core_modelo_id' => 28,
      'core_campo_id' => 193,
    ),
    929 => 
    array (
      'id' => 1045,
      'orden' => 6,
      'core_modelo_id' => 28,
      'core_campo_id' => 2,
    ),
    930 => 
    array (
      'id' => 1047,
      'orden' => 8,
      'core_modelo_id' => 28,
      'core_campo_id' => 542,
    ),
    931 => 
    array (
      'id' => 1048,
      'orden' => 10,
      'core_modelo_id' => 28,
      'core_campo_id' => 541,
    ),
    932 => 
    array (
      'id' => 1049,
      'orden' => 12,
      'core_modelo_id' => 28,
      'core_campo_id' => 22,
    ),
    933 => 
    array (
      'id' => 1050,
      'orden' => 11,
      'core_modelo_id' => 24,
      'core_campo_id' => 543,
    ),
    934 => 
    array (
      'id' => 1051,
      'orden' => 4,
      'core_modelo_id' => 61,
      'core_campo_id' => 201,
    ),
    935 => 
    array (
      'id' => 1057,
      'orden' => 10,
      'core_modelo_id' => 149,
      'core_campo_id' => 539,
    ),
    936 => 
    array (
      'id' => 1058,
      'orden' => 12,
      'core_modelo_id' => 149,
      'core_campo_id' => 540,
    ),
    937 => 
    array (
      'id' => 1059,
      'orden' => 14,
      'core_modelo_id' => 149,
      'core_campo_id' => 93,
    ),
    938 => 
    array (
      'id' => 1060,
      'orden' => 16,
      'core_modelo_id' => 149,
      'core_campo_id' => 194,
    ),
    939 => 
    array (
      'id' => 1061,
      'orden' => 99,
      'core_modelo_id' => 149,
      'core_campo_id' => 94,
    ),
    940 => 
    array (
      'id' => 1062,
      'orden' => 99,
      'core_modelo_id' => 149,
      'core_campo_id' => 95,
    ),
    941 => 
    array (
      'id' => 1063,
      'orden' => 18,
      'core_modelo_id' => 149,
      'core_campo_id' => 544,
    ),
    942 => 
    array (
      'id' => 1064,
      'orden' => 20,
      'core_modelo_id' => 149,
      'core_campo_id' => 545,
    ),
    943 => 
    array (
      'id' => 1065,
      'orden' => 24,
      'core_modelo_id' => 149,
      'core_campo_id' => 546,
    ),
    944 => 
    array (
      'id' => 1066,
      'orden' => 0,
      'core_modelo_id' => 149,
      'core_campo_id' => 201,
    ),
    945 => 
    array (
      'id' => 1070,
      'orden' => 10,
      'core_modelo_id' => 150,
      'core_campo_id' => 8,
    ),
    946 => 
    array (
      'id' => 1072,
      'orden' => 99,
      'core_modelo_id' => 150,
      'core_campo_id' => 87,
    ),
    947 => 
    array (
      'id' => 1073,
      'orden' => 2,
      'core_modelo_id' => 150,
      'core_campo_id' => 88,
    ),
    948 => 
    array (
      'id' => 1074,
      'orden' => 9,
      'core_modelo_id' => 150,
      'core_campo_id' => 92,
    ),
    949 => 
    array (
      'id' => 1075,
      'orden' => 3,
      'core_modelo_id' => 150,
      'core_campo_id' => 93,
    ),
    950 => 
    array (
      'id' => 1076,
      'orden' => 99,
      'core_modelo_id' => 150,
      'core_campo_id' => 94,
    ),
    951 => 
    array (
      'id' => 1077,
      'orden' => 99,
      'core_modelo_id' => 150,
      'core_campo_id' => 95,
    ),
    952 => 
    array (
      'id' => 1078,
      'orden' => 99,
      'core_modelo_id' => 150,
      'core_campo_id' => 98,
    ),
    953 => 
    array (
      'id' => 1079,
      'orden' => 99,
      'core_modelo_id' => 150,
      'core_campo_id' => 100,
    ),
    954 => 
    array (
      'id' => 1080,
      'orden' => 1,
      'core_modelo_id' => 150,
      'core_campo_id' => 201,
    ),
    955 => 
    array (
      'id' => 1083,
      'orden' => 11,
      'core_modelo_id' => 150,
      'core_campo_id' => 248,
    ),
    956 => 
    array (
      'id' => 1084,
      'orden' => 12,
      'core_modelo_id' => 150,
      'core_campo_id' => 249,
    ),
    957 => 
    array (
      'id' => 1085,
      'orden' => 14,
      'core_modelo_id' => 150,
      'core_campo_id' => 250,
    ),
    958 => 
    array (
      'id' => 1087,
      'orden' => 4,
      'core_modelo_id' => 150,
      'core_campo_id' => 538,
    ),
    959 => 
    array (
      'id' => 1088,
      'orden' => 99,
      'core_modelo_id' => 150,
      'core_campo_id' => 334,
    ),
    960 => 
    array (
      'id' => 1089,
      'orden' => 13,
      'core_modelo_id' => 24,
      'core_campo_id' => 549,
    ),
    961 => 
    array (
      'id' => 1090,
      'orden' => 24,
      'core_modelo_id' => 22,
      'core_campo_id' => 550,
    ),
    962 => 
    array (
      'id' => 1091,
      'orden' => 14,
      'core_modelo_id' => 24,
      'core_campo_id' => 550,
    ),
    963 => 
    array (
      'id' => 1092,
      'orden' => 10,
      'core_modelo_id' => 146,
      'core_campo_id' => 37,
    ),
    964 => 
    array (
      'id' => 1093,
      'orden' => 12,
      'core_modelo_id' => 146,
      'core_campo_id' => 44,
    ),
    965 => 
    array (
      'id' => 1094,
      'orden' => 12,
      'core_modelo_id' => 138,
      'core_campo_id' => 43,
    ),
    966 => 
    array (
      'id' => 1095,
      'orden' => 8,
      'core_modelo_id' => 138,
      'core_campo_id' => 37,
    ),
    967 => 
    array (
      'id' => 1096,
      'orden' => 10,
      'core_modelo_id' => 138,
      'core_campo_id' => 44,
    ),
    968 => 
    array (
      'id' => 1097,
      'orden' => 99,
      'core_modelo_id' => 138,
      'core_campo_id' => 386,
    ),
    969 => 
    array (
      'id' => 1098,
      'orden' => 99,
      'core_modelo_id' => 146,
      'core_campo_id' => 386,
    ),
    970 => 
    array (
      'id' => 1099,
      'orden' => 99,
      'core_modelo_id' => 146,
      'core_campo_id' => 94,
    ),
    971 => 
    array (
      'id' => 1100,
      'orden' => 99,
      'core_modelo_id' => 146,
      'core_campo_id' => 95,
    ),
    972 => 
    array (
      'id' => 1101,
      'orden' => 99,
      'core_modelo_id' => 138,
      'core_campo_id' => 94,
    ),
    973 => 
    array (
      'id' => 1102,
      'orden' => 99,
      'core_modelo_id' => 138,
      'core_campo_id' => 95,
    ),
    974 => 
    array (
      'id' => 1103,
      'orden' => 22,
      'core_modelo_id' => 149,
      'core_campo_id' => 551,
    ),
    975 => 
    array (
      'id' => 1105,
      'orden' => 99,
      'core_modelo_id' => 149,
      'core_campo_id' => 98,
    ),
    976 => 
    array (
      'id' => 1106,
      'orden' => 99,
      'core_modelo_id' => 150,
      'core_campo_id' => 547,
    ),
    977 => 
    array (
      'id' => 1107,
      'orden' => 99,
      'core_modelo_id' => 150,
      'core_campo_id' => 548,
    ),
    978 => 
    array (
      'id' => 1108,
      'orden' => 99,
      'core_modelo_id' => 28,
      'core_campo_id' => 386,
    ),
    979 => 
    array (
      'id' => 1109,
      'orden' => 9,
      'core_modelo_id' => 151,
      'core_campo_id' => 8,
    ),
    980 => 
    array (
      'id' => 1110,
      'orden' => 4,
      'core_modelo_id' => 151,
      'core_campo_id' => 39,
    ),
    981 => 
    array (
      'id' => 1111,
      'orden' => 99,
      'core_modelo_id' => 151,
      'core_campo_id' => 87,
    ),
    982 => 
    array (
      'id' => 1112,
      'orden' => 2,
      'core_modelo_id' => 151,
      'core_campo_id' => 88,
    ),
    983 => 
    array (
      'id' => 1113,
      'orden' => 11,
      'core_modelo_id' => 151,
      'core_campo_id' => 90,
    ),
    984 => 
    array (
      'id' => 1115,
      'orden' => 3,
      'core_modelo_id' => 151,
      'core_campo_id' => 93,
    ),
    985 => 
    array (
      'id' => 1116,
      'orden' => 99,
      'core_modelo_id' => 151,
      'core_campo_id' => 94,
    ),
    986 => 
    array (
      'id' => 1117,
      'orden' => 99,
      'core_modelo_id' => 151,
      'core_campo_id' => 95,
    ),
    987 => 
    array (
      'id' => 1118,
      'orden' => 99,
      'core_modelo_id' => 151,
      'core_campo_id' => 99,
    ),
    988 => 
    array (
      'id' => 1119,
      'orden' => 99,
      'core_modelo_id' => 151,
      'core_campo_id' => 100,
    ),
    989 => 
    array (
      'id' => 1120,
      'orden' => 99,
      'core_modelo_id' => 151,
      'core_campo_id' => 101,
    ),
    990 => 
    array (
      'id' => 1122,
      'orden' => 1,
      'core_modelo_id' => 151,
      'core_campo_id' => 192,
    ),
    991 => 
    array (
      'id' => 1123,
      'orden' => 99,
      'core_modelo_id' => 151,
      'core_campo_id' => 98,
    ),
    992 => 
    array (
      'id' => 1124,
      'orden' => 0,
      'core_modelo_id' => 152,
      'core_campo_id' => 192,
    ),
    993 => 
    array (
      'id' => 1125,
      'orden' => 2,
      'core_modelo_id' => 152,
      'core_campo_id' => 553,
    ),
    994 => 
    array (
      'id' => 1126,
      'orden' => 4,
      'core_modelo_id' => 152,
      'core_campo_id' => 554,
    ),
    995 => 
    array (
      'id' => 1127,
      'orden' => 6,
      'core_modelo_id' => 152,
      'core_campo_id' => 555,
    ),
    996 => 
    array (
      'id' => 1128,
      'orden' => 8,
      'core_modelo_id' => 152,
      'core_campo_id' => 556,
    ),
    997 => 
    array (
      'id' => 1129,
      'orden' => 10,
      'core_modelo_id' => 152,
      'core_campo_id' => 557,
    ),
    998 => 
    array (
      'id' => 1130,
      'orden' => 12,
      'core_modelo_id' => 152,
      'core_campo_id' => 558,
    ),
    999 => 
    array (
      'id' => 1131,
      'orden' => 16,
      'core_modelo_id' => 152,
      'core_campo_id' => 559,
    ),
    1000 => 
    array (
      'id' => 1132,
      'orden' => 18,
      'core_modelo_id' => 152,
      'core_campo_id' => 96,
    ),
    1001 => 
    array (
      'id' => 1133,
      'orden' => 20,
      'core_modelo_id' => 152,
      'core_campo_id' => 560,
    ),
    1002 => 
    array (
      'id' => 1134,
      'orden' => 22,
      'core_modelo_id' => 152,
      'core_campo_id' => 22,
    ),
    1003 => 
    array (
      'id' => 1135,
      'orden' => 5,
      'core_modelo_id' => 146,
      'core_campo_id' => 47,
    ),
    1004 => 
    array (
      'id' => 1136,
      'orden' => 19,
      'core_modelo_id' => 146,
      'core_campo_id' => 56,
    ),
    1005 => 
    array (
      'id' => 1138,
      'orden' => 2,
      'core_modelo_id' => 80,
      'core_campo_id' => 201,
    ),
    1006 => 
    array (
      'id' => 1139,
      'orden' => 4,
      'core_modelo_id' => 80,
      'core_campo_id' => 339,
    ),
    1007 => 
    array (
      'id' => 1140,
      'orden' => 6,
      'core_modelo_id' => 80,
      'core_campo_id' => 337,
    ),
    1008 => 
    array (
      'id' => 1141,
      'orden' => 99,
      'core_modelo_id' => 149,
      'core_campo_id' => 87,
    ),
    1009 => 
    array (
      'id' => 1142,
      'orden' => 1,
      'core_modelo_id' => 149,
      'core_campo_id' => 88,
    ),
    1010 => 
    array (
      'id' => 1143,
      'orden' => 99,
      'core_modelo_id' => 149,
      'core_campo_id' => 100,
    ),
    1011 => 
    array (
      'id' => 1144,
      'orden' => 99,
      'core_modelo_id' => 28,
      'core_campo_id' => 94,
    ),
    1012 => 
    array (
      'id' => 1145,
      'orden' => 99,
      'core_modelo_id' => 28,
      'core_campo_id' => 95,
    ),
    1013 => 
    array (
      'id' => 1146,
      'orden' => 10,
      'core_modelo_id' => 153,
      'core_campo_id' => 8,
    ),
    1014 => 
    array (
      'id' => 1147,
      'orden' => 99,
      'core_modelo_id' => 153,
      'core_campo_id' => 87,
    ),
    1015 => 
    array (
      'id' => 1148,
      'orden' => 2,
      'core_modelo_id' => 153,
      'core_campo_id' => 88,
    ),
    1016 => 
    array (
      'id' => 1149,
      'orden' => 9,
      'core_modelo_id' => 153,
      'core_campo_id' => 92,
    ),
    1017 => 
    array (
      'id' => 1150,
      'orden' => 3,
      'core_modelo_id' => 153,
      'core_campo_id' => 93,
    ),
    1018 => 
    array (
      'id' => 1151,
      'orden' => 99,
      'core_modelo_id' => 153,
      'core_campo_id' => 94,
    ),
    1019 => 
    array (
      'id' => 1152,
      'orden' => 99,
      'core_modelo_id' => 153,
      'core_campo_id' => 95,
    ),
    1020 => 
    array (
      'id' => 1153,
      'orden' => 99,
      'core_modelo_id' => 153,
      'core_campo_id' => 98,
    ),
    1021 => 
    array (
      'id' => 1154,
      'orden' => 99,
      'core_modelo_id' => 153,
      'core_campo_id' => 100,
    ),
    1022 => 
    array (
      'id' => 1155,
      'orden' => 1,
      'core_modelo_id' => 153,
      'core_campo_id' => 201,
    ),
    1023 => 
    array (
      'id' => 1156,
      'orden' => 11,
      'core_modelo_id' => 153,
      'core_campo_id' => 248,
    ),
    1024 => 
    array (
      'id' => 1157,
      'orden' => 12,
      'core_modelo_id' => 153,
      'core_campo_id' => 249,
    ),
    1025 => 
    array (
      'id' => 1158,
      'orden' => 14,
      'core_modelo_id' => 153,
      'core_campo_id' => 250,
    ),
    1026 => 
    array (
      'id' => 1159,
      'orden' => 99,
      'core_modelo_id' => 153,
      'core_campo_id' => 334,
    ),
    1027 => 
    array (
      'id' => 1162,
      'orden' => 99,
      'core_modelo_id' => 153,
      'core_campo_id' => 548,
    ),
    1028 => 
    array (
      'id' => 1163,
      'orden' => 4,
      'core_modelo_id' => 153,
      'core_campo_id' => 522,
    ),
    1029 => 
    array (
      'id' => 1164,
      'orden' => 99,
      'core_modelo_id' => 153,
      'core_campo_id' => 562,
    ),
    1030 => 
    array (
      'id' => 1165,
      'orden' => 99,
      'core_modelo_id' => 22,
      'core_campo_id' => 94,
    ),
    1031 => 
    array (
      'id' => 1166,
      'orden' => 99,
      'core_modelo_id' => 22,
      'core_campo_id' => 95,
    ),
    1032 => 
    array (
      'id' => 1167,
      'orden' => 1,
      'core_modelo_id' => 154,
      'core_campo_id' => 386,
    ),
    1033 => 
    array (
      'id' => 1168,
      'orden' => 99,
      'core_modelo_id' => 154,
      'core_campo_id' => 336,
    ),
    1034 => 
    array (
      'id' => 1169,
      'orden' => 2,
      'core_modelo_id' => 154,
      'core_campo_id' => 39,
    ),
    1035 => 
    array (
      'id' => 1170,
      'orden' => 3,
      'core_modelo_id' => 154,
      'core_campo_id' => 193,
    ),
    1036 => 
    array (
      'id' => 1171,
      'orden' => 6,
      'core_modelo_id' => 154,
      'core_campo_id' => 88,
    ),
    1037 => 
    array (
      'id' => 1172,
      'orden' => 8,
      'core_modelo_id' => 154,
      'core_campo_id' => 283,
    ),
    1038 => 
    array (
      'id' => 1173,
      'orden' => 10,
      'core_modelo_id' => 154,
      'core_campo_id' => 93,
    ),
    1039 => 
    array (
      'id' => 1174,
      'orden' => 12,
      'core_modelo_id' => 154,
      'core_campo_id' => 448,
    ),
    1040 => 
    array (
      'id' => 1175,
      'orden' => 14,
      'core_modelo_id' => 154,
      'core_campo_id' => 215,
    ),
    1041 => 
    array (
      'id' => 1176,
      'orden' => 17,
      'core_modelo_id' => 154,
      'core_campo_id' => 216,
    ),
    1042 => 
    array (
      'id' => 1177,
      'orden' => 11,
      'core_modelo_id' => 154,
      'core_campo_id' => 565,
    ),
    1043 => 
    array (
      'id' => 1178,
      'orden' => 15,
      'core_modelo_id' => 154,
      'core_campo_id' => 563,
    ),
    1044 => 
    array (
      'id' => 1179,
      'orden' => 19,
      'core_modelo_id' => 154,
      'core_campo_id' => 564,
    ),
    1045 => 
    array (
      'id' => 1181,
      'orden' => 16,
      'core_modelo_id' => 155,
      'core_campo_id' => 8,
    ),
    1046 => 
    array (
      'id' => 1182,
      'orden' => 99,
      'core_modelo_id' => 155,
      'core_campo_id' => 87,
    ),
    1047 => 
    array (
      'id' => 1183,
      'orden' => 3,
      'core_modelo_id' => 155,
      'core_campo_id' => 88,
    ),
    1048 => 
    array (
      'id' => 1184,
      'orden' => 13,
      'core_modelo_id' => 155,
      'core_campo_id' => 90,
    ),
    1049 => 
    array (
      'id' => 1185,
      'orden' => 4,
      'core_modelo_id' => 155,
      'core_campo_id' => 93,
    ),
    1050 => 
    array (
      'id' => 1186,
      'orden' => 99,
      'core_modelo_id' => 155,
      'core_campo_id' => 100,
    ),
    1051 => 
    array (
      'id' => 1187,
      'orden' => 12,
      'core_modelo_id' => 155,
      'core_campo_id' => 194,
    ),
    1052 => 
    array (
      'id' => 1188,
      'orden' => 2,
      'core_modelo_id' => 155,
      'core_campo_id' => 201,
    ),
    1053 => 
    array (
      'id' => 1189,
      'orden' => 8,
      'core_modelo_id' => 155,
      'core_campo_id' => 521,
    ),
    1054 => 
    array (
      'id' => 1190,
      'orden' => 6,
      'core_modelo_id' => 155,
      'core_campo_id' => 522,
    ),
    1055 => 
    array (
      'id' => 1191,
      'orden' => 10,
      'core_modelo_id' => 155,
      'core_campo_id' => 523,
    ),
    1056 => 
    array (
      'id' => 1192,
      'orden' => 14,
      'core_modelo_id' => 155,
      'core_campo_id' => 524,
    ),
    1057 => 
    array (
      'id' => 1193,
      'orden' => 4,
      'core_modelo_id' => 26,
      'core_campo_id' => 566,
    ),
    1058 => 
    array (
      'id' => 1196,
      'orden' => 30,
      'core_modelo_id' => 156,
      'core_campo_id' => 22,
    ),
    1059 => 
    array (
      'id' => 1210,
      'orden' => 99,
      'core_modelo_id' => 156,
      'core_campo_id' => 94,
    ),
    1060 => 
    array (
      'id' => 1211,
      'orden' => 99,
      'core_modelo_id' => 156,
      'core_campo_id' => 95,
    ),
    1061 => 
    array (
      'id' => 1212,
      'orden' => 99,
      'core_modelo_id' => 156,
      'core_campo_id' => 386,
    ),
    1062 => 
    array (
      'id' => 1213,
      'orden' => 25,
      'core_modelo_id' => 156,
      'core_campo_id' => 448,
    ),
    1063 => 
    array (
      'id' => 1214,
      'orden' => 27,
      'core_modelo_id' => 156,
      'core_campo_id' => 498,
    ),
    1064 => 
    array (
      'id' => 1215,
      'orden' => 24,
      'core_modelo_id' => 156,
      'core_campo_id' => 534,
    ),
    1065 => 
    array (
      'id' => 1216,
      'orden' => 28,
      'core_modelo_id' => 156,
      'core_campo_id' => 537,
    ),
    1066 => 
    array (
      'id' => 1218,
      'orden' => 6,
      'core_modelo_id' => 138,
      'core_campo_id' => 47,
    ),
    1067 => 
    array (
      'id' => 1220,
      'orden' => 44,
      'core_modelo_id' => 157,
      'core_campo_id' => 22,
    ),
    1068 => 
    array (
      'id' => 1233,
      'orden' => 99,
      'core_modelo_id' => 157,
      'core_campo_id' => 94,
    ),
    1069 => 
    array (
      'id' => 1234,
      'orden' => 99,
      'core_modelo_id' => 157,
      'core_campo_id' => 95,
    ),
    1070 => 
    array (
      'id' => 1235,
      'orden' => 99,
      'core_modelo_id' => 157,
      'core_campo_id' => 386,
    ),
    1071 => 
    array (
      'id' => 1236,
      'orden' => 27,
      'core_modelo_id' => 157,
      'core_campo_id' => 448,
    ),
    1072 => 
    array (
      'id' => 1237,
      'orden' => 32,
      'core_modelo_id' => 157,
      'core_campo_id' => 495,
    ),
    1073 => 
    array (
      'id' => 1238,
      'orden' => 20,
      'core_modelo_id' => 157,
      'core_campo_id' => 496,
    ),
    1074 => 
    array (
      'id' => 1239,
      'orden' => 22,
      'core_modelo_id' => 157,
      'core_campo_id' => 497,
    ),
    1075 => 
    array (
      'id' => 1240,
      'orden' => 34,
      'core_modelo_id' => 157,
      'core_campo_id' => 498,
    ),
    1076 => 
    array (
      'id' => 1241,
      'orden' => 36,
      'core_modelo_id' => 157,
      'core_campo_id' => 499,
    ),
    1077 => 
    array (
      'id' => 1242,
      'orden' => 38,
      'core_modelo_id' => 157,
      'core_campo_id' => 500,
    ),
    1078 => 
    array (
      'id' => 1243,
      'orden' => 40,
      'core_modelo_id' => 157,
      'core_campo_id' => 501,
    ),
    1079 => 
    array (
      'id' => 1246,
      'orden' => 29,
      'core_modelo_id' => 157,
      'core_campo_id' => 521,
    ),
    1080 => 
    array (
      'id' => 1247,
      'orden' => 30,
      'core_modelo_id' => 157,
      'core_campo_id' => 515,
    ),
    1081 => 
    array (
      'id' => 1248,
      'orden' => 31,
      'core_modelo_id' => 157,
      'core_campo_id' => 511,
    ),
    1082 => 
    array (
      'id' => 1249,
      'orden' => 42,
      'core_modelo_id' => 157,
      'core_campo_id' => 502,
    ),
    1083 => 
    array (
      'id' => 1250,
      'orden' => 1,
      'core_modelo_id' => 158,
      'core_campo_id' => 201,
    ),
    1084 => 
    array (
      'id' => 1253,
      'orden' => 6,
      'core_modelo_id' => 158,
      'core_campo_id' => 93,
    ),
    1085 => 
    array (
      'id' => 1258,
      'orden' => 8,
      'core_modelo_id' => 158,
      'core_campo_id' => 579,
    ),
    1086 => 
    array (
      'id' => 1259,
      'orden' => 10,
      'core_modelo_id' => 158,
      'core_campo_id' => 580,
    ),
    1087 => 
    array (
      'id' => 1275,
      'orden' => 16,
      'core_modelo_id' => 159,
      'core_campo_id' => 8,
    ),
    1088 => 
    array (
      'id' => 1276,
      'orden' => 99,
      'core_modelo_id' => 159,
      'core_campo_id' => 87,
    ),
    1089 => 
    array (
      'id' => 1277,
      'orden' => 3,
      'core_modelo_id' => 159,
      'core_campo_id' => 88,
    ),
    1090 => 
    array (
      'id' => 1278,
      'orden' => 13,
      'core_modelo_id' => 159,
      'core_campo_id' => 90,
    ),
    1091 => 
    array (
      'id' => 1279,
      'orden' => 4,
      'core_modelo_id' => 159,
      'core_campo_id' => 93,
    ),
    1092 => 
    array (
      'id' => 1280,
      'orden' => 99,
      'core_modelo_id' => 159,
      'core_campo_id' => 94,
    ),
    1093 => 
    array (
      'id' => 1281,
      'orden' => 99,
      'core_modelo_id' => 159,
      'core_campo_id' => 95,
    ),
    1094 => 
    array (
      'id' => 1282,
      'orden' => 99,
      'core_modelo_id' => 159,
      'core_campo_id' => 98,
    ),
    1095 => 
    array (
      'id' => 1283,
      'orden' => 99,
      'core_modelo_id' => 159,
      'core_campo_id' => 100,
    ),
    1096 => 
    array (
      'id' => 1284,
      'orden' => 12,
      'core_modelo_id' => 159,
      'core_campo_id' => 194,
    ),
    1097 => 
    array (
      'id' => 1285,
      'orden' => 2,
      'core_modelo_id' => 159,
      'core_campo_id' => 201,
    ),
    1098 => 
    array (
      'id' => 1286,
      'orden' => 10,
      'core_modelo_id' => 159,
      'core_campo_id' => 523,
    ),
    1099 => 
    array (
      'id' => 1287,
      'orden' => 6,
      'core_modelo_id' => 159,
      'core_campo_id' => 538,
    ),
    1100 => 
    array (
      'id' => 1288,
      'orden' => 18,
      'core_modelo_id' => 159,
      'core_campo_id' => 539,
    ),
    1101 => 
    array (
      'id' => 1289,
      'orden' => 20,
      'core_modelo_id' => 159,
      'core_campo_id' => 540,
    ),
    1102 => 
    array (
      'id' => 1290,
      'orden' => 16,
      'core_modelo_id' => 160,
      'core_campo_id' => 8,
    ),
    1103 => 
    array (
      'id' => 1291,
      'orden' => 99,
      'core_modelo_id' => 160,
      'core_campo_id' => 87,
    ),
    1104 => 
    array (
      'id' => 1292,
      'orden' => 3,
      'core_modelo_id' => 160,
      'core_campo_id' => 88,
    ),
    1105 => 
    array (
      'id' => 1293,
      'orden' => 13,
      'core_modelo_id' => 160,
      'core_campo_id' => 90,
    ),
    1106 => 
    array (
      'id' => 1294,
      'orden' => 4,
      'core_modelo_id' => 160,
      'core_campo_id' => 93,
    ),
    1107 => 
    array (
      'id' => 1295,
      'orden' => 99,
      'core_modelo_id' => 160,
      'core_campo_id' => 94,
    ),
    1108 => 
    array (
      'id' => 1296,
      'orden' => 99,
      'core_modelo_id' => 160,
      'core_campo_id' => 95,
    ),
    1109 => 
    array (
      'id' => 1297,
      'orden' => 99,
      'core_modelo_id' => 160,
      'core_campo_id' => 98,
    ),
    1110 => 
    array (
      'id' => 1298,
      'orden' => 99,
      'core_modelo_id' => 160,
      'core_campo_id' => 100,
    ),
    1111 => 
    array (
      'id' => 1299,
      'orden' => 12,
      'core_modelo_id' => 160,
      'core_campo_id' => 194,
    ),
    1112 => 
    array (
      'id' => 1300,
      'orden' => 2,
      'core_modelo_id' => 160,
      'core_campo_id' => 201,
    ),
    1113 => 
    array (
      'id' => 1301,
      'orden' => 10,
      'core_modelo_id' => 160,
      'core_campo_id' => 523,
    ),
    1114 => 
    array (
      'id' => 1302,
      'orden' => 6,
      'core_modelo_id' => 160,
      'core_campo_id' => 538,
    ),
    1115 => 
    array (
      'id' => 1303,
      'orden' => 18,
      'core_modelo_id' => 160,
      'core_campo_id' => 539,
    ),
    1116 => 
    array (
      'id' => 1304,
      'orden' => 20,
      'core_modelo_id' => 160,
      'core_campo_id' => 540,
    ),
    1117 => 
    array (
      'id' => 1305,
      'orden' => 8,
      'core_modelo_id' => 161,
      'core_campo_id' => 583,
    ),
    1118 => 
    array (
      'id' => 1306,
      'orden' => 9,
      'core_modelo_id' => 164,
      'core_campo_id' => 8,
    ),
    1119 => 
    array (
      'id' => 1307,
      'orden' => 4,
      'core_modelo_id' => 164,
      'core_campo_id' => 39,
    ),
    1120 => 
    array (
      'id' => 1308,
      'orden' => 99,
      'core_modelo_id' => 164,
      'core_campo_id' => 87,
    ),
    1121 => 
    array (
      'id' => 1309,
      'orden' => 2,
      'core_modelo_id' => 164,
      'core_campo_id' => 88,
    ),
    1122 => 
    array (
      'id' => 1310,
      'orden' => 11,
      'core_modelo_id' => 164,
      'core_campo_id' => 90,
    ),
    1123 => 
    array (
      'id' => 1311,
      'orden' => 10,
      'core_modelo_id' => 164,
      'core_campo_id' => 92,
    ),
    1124 => 
    array (
      'id' => 1312,
      'orden' => 3,
      'core_modelo_id' => 164,
      'core_campo_id' => 93,
    ),
    1125 => 
    array (
      'id' => 1313,
      'orden' => 99,
      'core_modelo_id' => 164,
      'core_campo_id' => 94,
    ),
    1126 => 
    array (
      'id' => 1314,
      'orden' => 99,
      'core_modelo_id' => 164,
      'core_campo_id' => 95,
    ),
    1127 => 
    array (
      'id' => 1315,
      'orden' => 99,
      'core_modelo_id' => 164,
      'core_campo_id' => 99,
    ),
    1128 => 
    array (
      'id' => 1316,
      'orden' => 99,
      'core_modelo_id' => 164,
      'core_campo_id' => 100,
    ),
    1129 => 
    array (
      'id' => 1317,
      'orden' => 99,
      'core_modelo_id' => 164,
      'core_campo_id' => 101,
    ),
    1130 => 
    array (
      'id' => 1319,
      'orden' => 1,
      'core_modelo_id' => 164,
      'core_campo_id' => 192,
    ),
    1131 => 
    array (
      'id' => 1320,
      'orden' => 9,
      'core_modelo_id' => 165,
      'core_campo_id' => 8,
    ),
    1132 => 
    array (
      'id' => 1321,
      'orden' => 4,
      'core_modelo_id' => 165,
      'core_campo_id' => 39,
    ),
    1133 => 
    array (
      'id' => 1322,
      'orden' => 99,
      'core_modelo_id' => 165,
      'core_campo_id' => 87,
    ),
    1134 => 
    array (
      'id' => 1323,
      'orden' => 2,
      'core_modelo_id' => 165,
      'core_campo_id' => 88,
    ),
    1135 => 
    array (
      'id' => 1324,
      'orden' => 11,
      'core_modelo_id' => 165,
      'core_campo_id' => 90,
    ),
    1136 => 
    array (
      'id' => 1325,
      'orden' => 10,
      'core_modelo_id' => 165,
      'core_campo_id' => 92,
    ),
    1137 => 
    array (
      'id' => 1326,
      'orden' => 3,
      'core_modelo_id' => 165,
      'core_campo_id' => 93,
    ),
    1138 => 
    array (
      'id' => 1327,
      'orden' => 99,
      'core_modelo_id' => 165,
      'core_campo_id' => 94,
    ),
    1139 => 
    array (
      'id' => 1328,
      'orden' => 99,
      'core_modelo_id' => 165,
      'core_campo_id' => 95,
    ),
    1140 => 
    array (
      'id' => 1329,
      'orden' => 99,
      'core_modelo_id' => 165,
      'core_campo_id' => 99,
    ),
    1141 => 
    array (
      'id' => 1330,
      'orden' => 99,
      'core_modelo_id' => 165,
      'core_campo_id' => 100,
    ),
    1142 => 
    array (
      'id' => 1331,
      'orden' => 99,
      'core_modelo_id' => 165,
      'core_campo_id' => 101,
    ),
    1143 => 
    array (
      'id' => 1333,
      'orden' => 1,
      'core_modelo_id' => 165,
      'core_campo_id' => 192,
    ),
    1144 => 
    array (
      'id' => 1334,
      'orden' => 12,
      'core_modelo_id' => 165,
      'core_campo_id' => 584,
    ),
    1145 => 
    array (
      'id' => 1335,
      'orden' => 12,
      'core_modelo_id' => 164,
      'core_campo_id' => 584,
    ),
    1146 => 
    array (
      'id' => 1336,
      'orden' => 2,
      'core_modelo_id' => 86,
      'core_campo_id' => 2,
    ),
    1147 => 
    array (
      'id' => 1337,
      'orden' => 4,
      'core_modelo_id' => 86,
      'core_campo_id' => 22,
    ),
    1148 => 
    array (
      'id' => 1338,
      'orden' => 2,
      'core_modelo_id' => 89,
      'core_campo_id' => 39,
    ),
    1149 => 
    array (
      'id' => 1339,
      'orden' => 4,
      'core_modelo_id' => 89,
      'core_campo_id' => 2,
    ),
    1150 => 
    array (
      'id' => 1340,
      'orden' => 6,
      'core_modelo_id' => 89,
      'core_campo_id' => 375,
    ),
    1151 => 
    array (
      'id' => 1341,
      'orden' => 8,
      'core_modelo_id' => 89,
      'core_campo_id' => 376,
    ),
    1152 => 
    array (
      'id' => 1342,
      'orden' => 10,
      'core_modelo_id' => 89,
      'core_campo_id' => 22,
    ),
    1153 => 
    array (
      'id' => 1343,
      'orden' => 16,
      'core_modelo_id' => 166,
      'core_campo_id' => 8,
    ),
    1154 => 
    array (
      'id' => 1344,
      'orden' => 99,
      'core_modelo_id' => 166,
      'core_campo_id' => 87,
    ),
    1155 => 
    array (
      'id' => 1345,
      'orden' => 3,
      'core_modelo_id' => 166,
      'core_campo_id' => 88,
    ),
    1156 => 
    array (
      'id' => 1347,
      'orden' => 4,
      'core_modelo_id' => 166,
      'core_campo_id' => 93,
    ),
    1157 => 
    array (
      'id' => 1348,
      'orden' => 99,
      'core_modelo_id' => 166,
      'core_campo_id' => 94,
    ),
    1158 => 
    array (
      'id' => 1349,
      'orden' => 99,
      'core_modelo_id' => 166,
      'core_campo_id' => 95,
    ),
    1159 => 
    array (
      'id' => 1350,
      'orden' => 99,
      'core_modelo_id' => 166,
      'core_campo_id' => 98,
    ),
    1160 => 
    array (
      'id' => 1351,
      'orden' => 99,
      'core_modelo_id' => 166,
      'core_campo_id' => 100,
    ),
    1161 => 
    array (
      'id' => 1353,
      'orden' => 2,
      'core_modelo_id' => 166,
      'core_campo_id' => 201,
    ),
    1162 => 
    array (
      'id' => 1358,
      'orden' => 2,
      'core_modelo_id' => 161,
      'core_campo_id' => 448,
    ),
    1163 => 
    array (
      'id' => 1359,
      'orden' => 4,
      'core_modelo_id' => 161,
      'core_campo_id' => 215,
    ),
    1164 => 
    array (
      'id' => 1362,
      'orden' => 10,
      'core_modelo_id' => 156,
      'core_campo_id' => 587,
    ),
    1165 => 
    array (
      'id' => 1363,
      'orden' => 10,
      'core_modelo_id' => 157,
      'core_campo_id' => 586,
    ),
    1166 => 
    array (
      'id' => 1364,
      'orden' => 5,
      'core_modelo_id' => 170,
      'core_campo_id' => 8,
    ),
    1167 => 
    array (
      'id' => 1365,
      'orden' => 4,
      'core_modelo_id' => 170,
      'core_campo_id' => 39,
    ),
    1168 => 
    array (
      'id' => 1366,
      'orden' => 99,
      'core_modelo_id' => 170,
      'core_campo_id' => 87,
    ),
    1169 => 
    array (
      'id' => 1367,
      'orden' => 2,
      'core_modelo_id' => 170,
      'core_campo_id' => 88,
    ),
    1170 => 
    array (
      'id' => 1368,
      'orden' => 6,
      'core_modelo_id' => 170,
      'core_campo_id' => 92,
    ),
    1171 => 
    array (
      'id' => 1369,
      'orden' => 3,
      'core_modelo_id' => 170,
      'core_campo_id' => 93,
    ),
    1172 => 
    array (
      'id' => 1370,
      'orden' => 99,
      'core_modelo_id' => 170,
      'core_campo_id' => 94,
    ),
    1173 => 
    array (
      'id' => 1371,
      'orden' => 99,
      'core_modelo_id' => 170,
      'core_campo_id' => 95,
    ),
    1174 => 
    array (
      'id' => 1372,
      'orden' => 99,
      'core_modelo_id' => 170,
      'core_campo_id' => 98,
    ),
    1175 => 
    array (
      'id' => 1373,
      'orden' => 99,
      'core_modelo_id' => 170,
      'core_campo_id' => 100,
    ),
    1176 => 
    array (
      'id' => 1374,
      'orden' => 1,
      'core_modelo_id' => 170,
      'core_campo_id' => 201,
    ),
    1177 => 
    array (
      'id' => 1375,
      'orden' => 99,
      'core_modelo_id' => 170,
      'core_campo_id' => 203,
    ),
    1178 => 
    array (
      'id' => 1376,
      'orden' => 26,
      'core_modelo_id' => 149,
      'core_campo_id' => 584,
    ),
    1179 => 
    array (
      'id' => 1377,
      'orden' => 9,
      'core_modelo_id' => 171,
      'core_campo_id' => 8,
    ),
    1180 => 
    array (
      'id' => 1378,
      'orden' => 4,
      'core_modelo_id' => 171,
      'core_campo_id' => 39,
    ),
    1181 => 
    array (
      'id' => 1379,
      'orden' => 99,
      'core_modelo_id' => 171,
      'core_campo_id' => 87,
    ),
    1182 => 
    array (
      'id' => 1380,
      'orden' => 2,
      'core_modelo_id' => 171,
      'core_campo_id' => 88,
    ),
    1183 => 
    array (
      'id' => 1381,
      'orden' => 11,
      'core_modelo_id' => 171,
      'core_campo_id' => 90,
    ),
    1184 => 
    array (
      'id' => 1382,
      'orden' => 10,
      'core_modelo_id' => 171,
      'core_campo_id' => 92,
    ),
    1185 => 
    array (
      'id' => 1383,
      'orden' => 3,
      'core_modelo_id' => 171,
      'core_campo_id' => 93,
    ),
    1186 => 
    array (
      'id' => 1384,
      'orden' => 99,
      'core_modelo_id' => 171,
      'core_campo_id' => 94,
    ),
    1187 => 
    array (
      'id' => 1385,
      'orden' => 99,
      'core_modelo_id' => 171,
      'core_campo_id' => 95,
    ),
    1188 => 
    array (
      'id' => 1386,
      'orden' => 99,
      'core_modelo_id' => 171,
      'core_campo_id' => 99,
    ),
    1189 => 
    array (
      'id' => 1387,
      'orden' => 99,
      'core_modelo_id' => 171,
      'core_campo_id' => 100,
    ),
    1190 => 
    array (
      'id' => 1388,
      'orden' => 99,
      'core_modelo_id' => 171,
      'core_campo_id' => 101,
    ),
    1191 => 
    array (
      'id' => 1390,
      'orden' => 1,
      'core_modelo_id' => 171,
      'core_campo_id' => 192,
    ),
    1192 => 
    array (
      'id' => 1400,
      'orden' => 16,
      'core_modelo_id' => 172,
      'core_campo_id' => 8,
    ),
    1193 => 
    array (
      'id' => 1401,
      'orden' => 99,
      'core_modelo_id' => 172,
      'core_campo_id' => 87,
    ),
    1194 => 
    array (
      'id' => 1402,
      'orden' => 3,
      'core_modelo_id' => 172,
      'core_campo_id' => 88,
    ),
    1195 => 
    array (
      'id' => 1403,
      'orden' => 13,
      'core_modelo_id' => 172,
      'core_campo_id' => 90,
    ),
    1196 => 
    array (
      'id' => 1404,
      'orden' => 4,
      'core_modelo_id' => 172,
      'core_campo_id' => 93,
    ),
    1197 => 
    array (
      'id' => 1405,
      'orden' => 99,
      'core_modelo_id' => 172,
      'core_campo_id' => 94,
    ),
    1198 => 
    array (
      'id' => 1406,
      'orden' => 99,
      'core_modelo_id' => 172,
      'core_campo_id' => 95,
    ),
    1199 => 
    array (
      'id' => 1407,
      'orden' => 99,
      'core_modelo_id' => 172,
      'core_campo_id' => 98,
    ),
    1200 => 
    array (
      'id' => 1408,
      'orden' => 99,
      'core_modelo_id' => 172,
      'core_campo_id' => 100,
    ),
    1201 => 
    array (
      'id' => 1409,
      'orden' => 12,
      'core_modelo_id' => 172,
      'core_campo_id' => 194,
    ),
    1202 => 
    array (
      'id' => 1410,
      'orden' => 2,
      'core_modelo_id' => 172,
      'core_campo_id' => 201,
    ),
    1203 => 
    array (
      'id' => 1411,
      'orden' => 10,
      'core_modelo_id' => 172,
      'core_campo_id' => 523,
    ),
    1204 => 
    array (
      'id' => 1412,
      'orden' => 6,
      'core_modelo_id' => 172,
      'core_campo_id' => 538,
    ),
    1205 => 
    array (
      'id' => 1413,
      'orden' => 18,
      'core_modelo_id' => 172,
      'core_campo_id' => 539,
    ),
    1206 => 
    array (
      'id' => 1414,
      'orden' => 20,
      'core_modelo_id' => 172,
      'core_campo_id' => 540,
    ),
    1207 => 
    array (
      'id' => 1415,
      'orden' => 16,
      'core_modelo_id' => 167,
      'core_campo_id' => 8,
    ),
    1208 => 
    array (
      'id' => 1416,
      'orden' => 99,
      'core_modelo_id' => 167,
      'core_campo_id' => 87,
    ),
    1209 => 
    array (
      'id' => 1417,
      'orden' => 3,
      'core_modelo_id' => 167,
      'core_campo_id' => 88,
    ),
    1210 => 
    array (
      'id' => 1418,
      'orden' => 4,
      'core_modelo_id' => 167,
      'core_campo_id' => 93,
    ),
    1211 => 
    array (
      'id' => 1419,
      'orden' => 99,
      'core_modelo_id' => 167,
      'core_campo_id' => 94,
    ),
    1212 => 
    array (
      'id' => 1420,
      'orden' => 99,
      'core_modelo_id' => 167,
      'core_campo_id' => 95,
    ),
    1213 => 
    array (
      'id' => 1421,
      'orden' => 99,
      'core_modelo_id' => 167,
      'core_campo_id' => 98,
    ),
    1214 => 
    array (
      'id' => 1422,
      'orden' => 99,
      'core_modelo_id' => 167,
      'core_campo_id' => 100,
    ),
    1215 => 
    array (
      'id' => 1423,
      'orden' => 2,
      'core_modelo_id' => 167,
      'core_campo_id' => 201,
    ),
    1216 => 
    array (
      'id' => 1424,
      'orden' => 16,
      'core_modelo_id' => 173,
      'core_campo_id' => 8,
    ),
    1217 => 
    array (
      'id' => 1425,
      'orden' => 99,
      'core_modelo_id' => 173,
      'core_campo_id' => 87,
    ),
    1218 => 
    array (
      'id' => 1426,
      'orden' => 3,
      'core_modelo_id' => 173,
      'core_campo_id' => 88,
    ),
    1219 => 
    array (
      'id' => 1427,
      'orden' => 13,
      'core_modelo_id' => 173,
      'core_campo_id' => 90,
    ),
    1220 => 
    array (
      'id' => 1428,
      'orden' => 4,
      'core_modelo_id' => 173,
      'core_campo_id' => 93,
    ),
    1221 => 
    array (
      'id' => 1429,
      'orden' => 99,
      'core_modelo_id' => 173,
      'core_campo_id' => 100,
    ),
    1222 => 
    array (
      'id' => 1430,
      'orden' => 12,
      'core_modelo_id' => 173,
      'core_campo_id' => 194,
    ),
    1223 => 
    array (
      'id' => 1431,
      'orden' => 2,
      'core_modelo_id' => 173,
      'core_campo_id' => 201,
    ),
    1224 => 
    array (
      'id' => 1432,
      'orden' => 8,
      'core_modelo_id' => 173,
      'core_campo_id' => 521,
    ),
    1225 => 
    array (
      'id' => 1433,
      'orden' => 6,
      'core_modelo_id' => 173,
      'core_campo_id' => 522,
    ),
    1226 => 
    array (
      'id' => 1434,
      'orden' => 10,
      'core_modelo_id' => 173,
      'core_campo_id' => 523,
    ),
    1227 => 
    array (
      'id' => 1435,
      'orden' => 14,
      'core_modelo_id' => 173,
      'core_campo_id' => 524,
    ),
    1228 => 
    array (
      'id' => 1436,
      'orden' => 9,
      'core_modelo_id' => 174,
      'core_campo_id' => 8,
    ),
    1229 => 
    array (
      'id' => 1437,
      'orden' => 4,
      'core_modelo_id' => 174,
      'core_campo_id' => 39,
    ),
    1230 => 
    array (
      'id' => 1438,
      'orden' => 99,
      'core_modelo_id' => 174,
      'core_campo_id' => 87,
    ),
    1231 => 
    array (
      'id' => 1439,
      'orden' => 2,
      'core_modelo_id' => 174,
      'core_campo_id' => 88,
    ),
    1232 => 
    array (
      'id' => 1440,
      'orden' => 11,
      'core_modelo_id' => 174,
      'core_campo_id' => 90,
    ),
    1233 => 
    array (
      'id' => 1441,
      'orden' => 10,
      'core_modelo_id' => 174,
      'core_campo_id' => 92,
    ),
    1234 => 
    array (
      'id' => 1442,
      'orden' => 3,
      'core_modelo_id' => 174,
      'core_campo_id' => 93,
    ),
    1235 => 
    array (
      'id' => 1443,
      'orden' => 99,
      'core_modelo_id' => 174,
      'core_campo_id' => 94,
    ),
    1236 => 
    array (
      'id' => 1444,
      'orden' => 99,
      'core_modelo_id' => 174,
      'core_campo_id' => 95,
    ),
    1237 => 
    array (
      'id' => 1445,
      'orden' => 99,
      'core_modelo_id' => 174,
      'core_campo_id' => 99,
    ),
    1238 => 
    array (
      'id' => 1446,
      'orden' => 99,
      'core_modelo_id' => 174,
      'core_campo_id' => 100,
    ),
    1239 => 
    array (
      'id' => 1447,
      'orden' => 99,
      'core_modelo_id' => 174,
      'core_campo_id' => 101,
    ),
    1240 => 
    array (
      'id' => 1449,
      'orden' => 1,
      'core_modelo_id' => 174,
      'core_campo_id' => 192,
    ),
    1241 => 
    array (
      'id' => 1450,
      'orden' => 12,
      'core_modelo_id' => 171,
      'core_campo_id' => 584,
    ),
    1242 => 
    array (
      'id' => 1451,
      'orden' => 12,
      'core_modelo_id' => 174,
      'core_campo_id' => 584,
    ),
    1243 => 
    array (
      'id' => 1452,
      'orden' => 16,
      'core_modelo_id' => 175,
      'core_campo_id' => 8,
    ),
    1244 => 
    array (
      'id' => 1453,
      'orden' => 99,
      'core_modelo_id' => 175,
      'core_campo_id' => 87,
    ),
    1245 => 
    array (
      'id' => 1454,
      'orden' => 3,
      'core_modelo_id' => 175,
      'core_campo_id' => 88,
    ),
    1246 => 
    array (
      'id' => 1456,
      'orden' => 4,
      'core_modelo_id' => 175,
      'core_campo_id' => 93,
    ),
    1247 => 
    array (
      'id' => 1457,
      'orden' => 99,
      'core_modelo_id' => 175,
      'core_campo_id' => 100,
    ),
    1248 => 
    array (
      'id' => 1459,
      'orden' => 2,
      'core_modelo_id' => 175,
      'core_campo_id' => 201,
    ),
    1249 => 
    array (
      'id' => 1461,
      'orden' => 6,
      'core_modelo_id' => 175,
      'core_campo_id' => 522,
    ),
    1250 => 
    array (
      'id' => 1464,
      'orden' => 99,
      'core_modelo_id' => 158,
      'core_campo_id' => 95,
    ),
    1251 => 
    array (
      'id' => 1465,
      'orden' => 99,
      'core_modelo_id' => 158,
      'core_campo_id' => 94,
    ),
    1252 => 
    array (
      'id' => 1466,
      'orden' => 3,
      'core_modelo_id' => 87,
      'core_campo_id' => 589,
    ),
    1253 => 
    array (
      'id' => 1467,
      'orden' => 3,
      'core_modelo_id' => 88,
      'core_campo_id' => 589,
    ),
    1254 => 
    array (
      'id' => 1468,
      'orden' => 8,
      'core_modelo_id' => 90,
      'core_campo_id' => 590,
    ),
    1255 => 
    array (
      'id' => 1470,
      'orden' => 6,
      'core_modelo_id' => 85,
      'core_campo_id' => 22,
    ),
    1256 => 
    array (
      'id' => 1471,
      'orden' => 16,
      'core_modelo_id' => 177,
      'core_campo_id' => 8,
    ),
    1257 => 
    array (
      'id' => 1472,
      'orden' => 99,
      'core_modelo_id' => 177,
      'core_campo_id' => 87,
    ),
    1258 => 
    array (
      'id' => 1473,
      'orden' => 3,
      'core_modelo_id' => 177,
      'core_campo_id' => 88,
    ),
    1259 => 
    array (
      'id' => 1474,
      'orden' => 13,
      'core_modelo_id' => 177,
      'core_campo_id' => 90,
    ),
    1260 => 
    array (
      'id' => 1475,
      'orden' => 4,
      'core_modelo_id' => 177,
      'core_campo_id' => 93,
    ),
    1261 => 
    array (
      'id' => 1476,
      'orden' => 99,
      'core_modelo_id' => 177,
      'core_campo_id' => 94,
    ),
    1262 => 
    array (
      'id' => 1477,
      'orden' => 99,
      'core_modelo_id' => 177,
      'core_campo_id' => 95,
    ),
    1263 => 
    array (
      'id' => 1478,
      'orden' => 99,
      'core_modelo_id' => 177,
      'core_campo_id' => 98,
    ),
    1264 => 
    array (
      'id' => 1479,
      'orden' => 99,
      'core_modelo_id' => 177,
      'core_campo_id' => 100,
    ),
    1265 => 
    array (
      'id' => 1480,
      'orden' => 12,
      'core_modelo_id' => 177,
      'core_campo_id' => 194,
    ),
    1266 => 
    array (
      'id' => 1481,
      'orden' => 2,
      'core_modelo_id' => 177,
      'core_campo_id' => 201,
    ),
    1267 => 
    array (
      'id' => 1482,
      'orden' => 10,
      'core_modelo_id' => 177,
      'core_campo_id' => 523,
    ),
    1268 => 
    array (
      'id' => 1483,
      'orden' => 6,
      'core_modelo_id' => 177,
      'core_campo_id' => 538,
    ),
    1269 => 
    array (
      'id' => 1486,
      'orden' => 4,
      'core_modelo_id' => 178,
      'core_campo_id' => 7,
    ),
    1270 => 
    array (
      'id' => 1487,
      'orden' => 2,
      'core_modelo_id' => 178,
      'core_campo_id' => 591,
    ),
    1271 => 
    array (
      'id' => 1488,
      'orden' => 6,
      'core_modelo_id' => 178,
      'core_campo_id' => 18,
    ),
    1272 => 
    array (
      'id' => 1489,
      'orden' => 8,
      'core_modelo_id' => 178,
      'core_campo_id' => 19,
    ),
    1273 => 
    array (
      'id' => 1490,
      'orden' => 10,
      'core_modelo_id' => 178,
      'core_campo_id' => 20,
    ),
    1274 => 
    array (
      'id' => 1491,
      'orden' => 3,
      'core_modelo_id' => 179,
      'core_campo_id' => 2,
    ),
    1275 => 
    array (
      'id' => 1492,
      'orden' => 1,
      'core_modelo_id' => 179,
      'core_campo_id' => 17,
    ),
    1276 => 
    array (
      'id' => 1493,
      'orden' => 6,
      'core_modelo_id' => 179,
      'core_campo_id' => 22,
    ),
    1277 => 
    array (
      'id' => 1495,
      'orden' => 4,
      'core_modelo_id' => 179,
      'core_campo_id' => 62,
    ),
    1278 => 
    array (
      'id' => 1496,
      'orden' => 5,
      'core_modelo_id' => 179,
      'core_campo_id' => 63,
    ),
    1279 => 
    array (
      'id' => 1497,
      'orden' => 7,
      'core_modelo_id' => 179,
      'core_campo_id' => 64,
    ),
    1280 => 
    array (
      'id' => 1498,
      'orden' => 0,
      'core_modelo_id' => 13,
      'core_campo_id' => 592,
    ),
    1281 => 
    array (
      'id' => 1502,
      'orden' => 8,
      'core_modelo_id' => 70,
      'core_campo_id' => 22,
    ),
    1282 => 
    array (
      'id' => 1503,
      'orden' => 4,
      'core_modelo_id' => 70,
      'core_campo_id' => 595,
    ),
    1283 => 
    array (
      'id' => 1504,
      'orden' => 10,
      'core_modelo_id' => 70,
      'core_campo_id' => 313,
    ),
    1284 => 
    array (
      'id' => 1506,
      'orden' => 6,
      'core_modelo_id' => 70,
      'core_campo_id' => 169,
    ),
    1285 => 
    array (
      'id' => 1507,
      'orden' => 10,
      'core_modelo_id' => 66,
      'core_campo_id' => 53,
    ),
    1286 => 
    array (
      'id' => 1509,
      'orden' => 6,
      'core_modelo_id' => 29,
      'core_campo_id' => 58,
    ),
    1287 => 
    array (
      'id' => 1510,
      'orden' => 5,
      'core_modelo_id' => 29,
      'core_campo_id' => 274,
    ),
    1288 => 
    array (
      'id' => 1511,
      'orden' => 12,
      'core_modelo_id' => 29,
      'core_campo_id' => 45,
    ),
    1289 => 
    array (
      'id' => 1512,
      'orden' => 12,
      'core_modelo_id' => 29,
      'core_campo_id' => 46,
    ),
    1290 => 
    array (
      'id' => 1513,
      'orden' => 34,
      'core_modelo_id' => 29,
      'core_campo_id' => 18,
    ),
    1291 => 
    array (
      'id' => 1514,
      'orden' => 2,
      'core_modelo_id' => 116,
      'core_campo_id' => 463,
    ),
    1292 => 
    array (
      'id' => 1515,
      'orden' => 4,
      'core_modelo_id' => 116,
      'core_campo_id' => 93,
    ),
    1293 => 
    array (
      'id' => 1516,
      'orden' => 6,
      'core_modelo_id' => 116,
      'core_campo_id' => 177,
    ),
    1294 => 
    array (
      'id' => 1519,
      'orden' => 8,
      'core_modelo_id' => 116,
      'core_campo_id' => 311,
    ),
    1295 => 
    array (
      'id' => 1520,
      'orden' => 10,
      'core_modelo_id' => 116,
      'core_campo_id' => 312,
    ),
    1296 => 
    array (
      'id' => 1523,
      'orden' => 12,
      'core_modelo_id' => 116,
      'core_campo_id' => 204,
    ),
    1297 => 
    array (
      'id' => 1526,
      'orden' => 2,
      'core_modelo_id' => 181,
      'core_campo_id' => 311,
    ),
    1298 => 
    array (
      'id' => 1527,
      'orden' => 4,
      'core_modelo_id' => 181,
      'core_campo_id' => 596,
    ),
    1299 => 
    array (
      'id' => 1528,
      'orden' => 6,
      'core_modelo_id' => 181,
      'core_campo_id' => 93,
    ),
    1300 => 
    array (
      'id' => 1529,
      'orden' => 8,
      'core_modelo_id' => 181,
      'core_campo_id' => 597,
    ),
    1301 => 
    array (
      'id' => 1531,
      'orden' => 10,
      'core_modelo_id' => 181,
      'core_campo_id' => 598,
    ),
    1302 => 
    array (
      'id' => 1532,
      'orden' => 5,
      'core_modelo_id' => 19,
      'core_campo_id' => 592,
    ),
    1303 => 
    array (
      'id' => 1533,
      'orden' => 13,
      'core_modelo_id' => 38,
      'core_campo_id' => 600,
    ),
    1304 => 
    array (
      'id' => 1534,
      'orden' => 12,
      'core_modelo_id' => 1,
      'core_campo_id' => 601,
    ),
    1305 => 
    array (
      'id' => 1535,
      'orden' => 0,
      'core_modelo_id' => 5,
      'core_campo_id' => 124,
    ),
    1306 => 
    array (
      'id' => 1537,
      'orden' => 2,
      'core_modelo_id' => 182,
      'core_campo_id' => 592,
    ),
    1307 => 
    array (
      'id' => 1538,
      'orden' => 4,
      'core_modelo_id' => 182,
      'core_campo_id' => 2,
    ),
    1308 => 
    array (
      'id' => 1539,
      'orden' => 6,
      'core_modelo_id' => 182,
      'core_campo_id' => 257,
    ),
    1309 => 
    array (
      'id' => 1540,
      'orden' => 8,
      'core_modelo_id' => 182,
      'core_campo_id' => 22,
    ),
    1310 => 
    array (
      'id' => 1541,
      'orden' => 2,
      'core_modelo_id' => 183,
      'core_campo_id' => 602,
    ),
    1311 => 
    array (
      'id' => 1542,
      'orden' => 4,
      'core_modelo_id' => 183,
      'core_campo_id' => 2,
    ),
    1312 => 
    array (
      'id' => 1543,
      'orden' => 6,
      'core_modelo_id' => 183,
      'core_campo_id' => 10,
    ),
    1313 => 
    array (
      'id' => 1544,
      'orden' => 8,
      'core_modelo_id' => 183,
      'core_campo_id' => 22,
    ),
    1314 => 
    array (
      'id' => 1546,
      'orden' => 2,
      'core_modelo_id' => 184,
      'core_campo_id' => 93,
    ),
    1315 => 
    array (
      'id' => 1547,
      'orden' => 6,
      'core_modelo_id' => 184,
      'core_campo_id' => 603,
    ),
    1316 => 
    array (
      'id' => 1548,
      'orden' => 8,
      'core_modelo_id' => 184,
      'core_campo_id' => 177,
    ),
    1317 => 
    array (
      'id' => 1550,
      'orden' => 10,
      'core_modelo_id' => 184,
      'core_campo_id' => 311,
    ),
    1318 => 
    array (
      'id' => 1551,
      'orden' => 12,
      'core_modelo_id' => 184,
      'core_campo_id' => 312,
    ),
    1319 => 
    array (
      'id' => 1553,
      'orden' => 14,
      'core_modelo_id' => 184,
      'core_campo_id' => 22,
    ),
    1320 => 
    array (
      'id' => 1554,
      'orden' => 12,
      'core_modelo_id' => 109,
      'core_campo_id' => 607,
    ),
    1321 => 
    array (
      'id' => 1556,
      'orden' => 3,
      'core_modelo_id' => 81,
      'core_campo_id' => 608,
    ),
    1322 => 
    array (
      'id' => 1557,
      'orden' => 14,
      'core_modelo_id' => 109,
      'core_campo_id' => 609,
    ),
    1323 => 
    array (
      'id' => 1558,
      'orden' => 2,
      'core_modelo_id' => 76,
      'core_campo_id' => 354,
    ),
    1324 => 
    array (
      'id' => 1561,
      'orden' => 18,
      'core_modelo_id' => 109,
      'core_campo_id' => 354,
    ),
    1325 => 
    array (
      'id' => 1562,
      'orden' => 99,
      'core_modelo_id' => 109,
      'core_campo_id' => 610,
    ),
    1326 => 
    array (
      'id' => 1563,
      'orden' => 99,
      'core_modelo_id' => 109,
      'core_campo_id' => 611,
    ),
    1327 => 
    array (
      'id' => 1564,
      'orden' => 16,
      'core_modelo_id' => 109,
      'core_campo_id' => 470,
    ),
    1328 => 
    array (
      'id' => 1565,
      'orden' => 2,
      'core_modelo_id' => 12,
      'core_campo_id' => 293,
    ),
    1329 => 
    array (
      'id' => 1566,
      'orden' => 11,
      'core_modelo_id' => 12,
      'core_campo_id' => 612,
    ),
    1330 => 
    array (
      'id' => 1568,
      'orden' => 2,
      'core_modelo_id' => 186,
      'core_campo_id' => 2,
    ),
    1331 => 
    array (
      'id' => 1569,
      'orden' => 4,
      'core_modelo_id' => 186,
      'core_campo_id' => 620,
    ),
    1332 => 
    array (
      'id' => 1570,
      'orden' => 6,
      'core_modelo_id' => 186,
      'core_campo_id' => 22,
    ),
    1333 => 
    array (
      'id' => 1571,
      'orden' => 0,
      'core_modelo_id' => 186,
      'core_campo_id' => 201,
    ),
    1334 => 
    array (
      'id' => 1572,
      'orden' => 9,
      'core_modelo_id' => 84,
      'core_campo_id' => 621,
    ),
    1335 => 
    array (
      'id' => 1573,
      'orden' => 4,
      'core_modelo_id' => 188,
      'core_campo_id' => 2,
    ),
    1336 => 
    array (
      'id' => 1574,
      'orden' => 30,
      'core_modelo_id' => 188,
      'core_campo_id' => 22,
    ),
    1337 => 
    array (
      'id' => 1576,
      'orden' => 6,
      'core_modelo_id' => 188,
      'core_campo_id' => 84,
    ),
    1338 => 
    array (
      'id' => 1579,
      'orden' => 99,
      'core_modelo_id' => 188,
      'core_campo_id' => 94,
    ),
    1339 => 
    array (
      'id' => 1580,
      'orden' => 99,
      'core_modelo_id' => 188,
      'core_campo_id' => 95,
    ),
    1340 => 
    array (
      'id' => 1581,
      'orden' => 99,
      'core_modelo_id' => 188,
      'core_campo_id' => 119,
    ),
    1341 => 
    array (
      'id' => 1583,
      'orden' => 12,
      'core_modelo_id' => 188,
      'core_campo_id' => 121,
    ),
    1342 => 
    array (
      'id' => 1584,
      'orden' => 99,
      'core_modelo_id' => 188,
      'core_campo_id' => 140,
    ),
    1343 => 
    array (
      'id' => 1585,
      'orden' => 99,
      'core_modelo_id' => 188,
      'core_campo_id' => 201,
    ),
    1344 => 
    array (
      'id' => 1588,
      'orden' => 8,
      'core_modelo_id' => 188,
      'core_campo_id' => 624,
    ),
    1345 => 
    array (
      'id' => 1590,
      'orden' => 99,
      'core_modelo_id' => 188,
      'core_campo_id' => 626,
    ),
    1346 => 
    array (
      'id' => 1591,
      'orden' => 14,
      'core_modelo_id' => 188,
      'core_campo_id' => 627,
    ),
    1347 => 
    array (
      'id' => 1592,
      'orden' => 99,
      'core_modelo_id' => 188,
      'core_campo_id' => 625,
    ),
    1348 => 
    array (
      'id' => 1593,
      'orden' => 10,
      'core_modelo_id' => 188,
      'core_campo_id' => 628,
    ),
    1349 => 
    array (
      'id' => 1594,
      'orden' => 4,
      'core_modelo_id' => 189,
      'core_campo_id' => 2,
    ),
    1350 => 
    array (
      'id' => 1595,
      'orden' => 30,
      'core_modelo_id' => 189,
      'core_campo_id' => 22,
    ),
    1351 => 
    array (
      'id' => 1596,
      'orden' => 6,
      'core_modelo_id' => 189,
      'core_campo_id' => 84,
    ),
    1352 => 
    array (
      'id' => 1597,
      'orden' => 99,
      'core_modelo_id' => 189,
      'core_campo_id' => 94,
    ),
    1353 => 
    array (
      'id' => 1598,
      'orden' => 99,
      'core_modelo_id' => 189,
      'core_campo_id' => 95,
    ),
    1354 => 
    array (
      'id' => 1599,
      'orden' => 99,
      'core_modelo_id' => 189,
      'core_campo_id' => 119,
    ),
    1355 => 
    array (
      'id' => 1601,
      'orden' => 99,
      'core_modelo_id' => 189,
      'core_campo_id' => 140,
    ),
    1356 => 
    array (
      'id' => 1602,
      'orden' => 99,
      'core_modelo_id' => 189,
      'core_campo_id' => 201,
    ),
    1357 => 
    array (
      'id' => 1605,
      'orden' => 99,
      'core_modelo_id' => 189,
      'core_campo_id' => 626,
    ),
    1358 => 
    array (
      'id' => 1608,
      'orden' => 12,
      'core_modelo_id' => 189,
      'core_campo_id' => 120,
    ),
    1359 => 
    array (
      'id' => 1609,
      'orden' => 8,
      'core_modelo_id' => 189,
      'core_campo_id' => 631,
    ),
    1360 => 
    array (
      'id' => 1611,
      'orden' => 22,
      'core_modelo_id' => 189,
      'core_campo_id' => 530,
    ),
    1361 => 
    array (
      'id' => 1612,
      'orden' => 25,
      'core_modelo_id' => 189,
      'core_campo_id' => 550,
    ),
    1362 => 
    array (
      'id' => 1613,
      'orden' => 19,
      'core_modelo_id' => 189,
      'core_campo_id' => 85,
    ),
    1363 => 
    array (
      'id' => 1614,
      'orden' => 22,
      'core_modelo_id' => 189,
      'core_campo_id' => 86,
    ),
    1364 => 
    array (
      'id' => 1615,
      'orden' => 18,
      'core_modelo_id' => 177,
      'core_campo_id' => 634,
    ),
    1365 => 
    array (
      'id' => 1616,
      'orden' => 99,
      'core_modelo_id' => 187,
      'core_campo_id' => 100,
    ),
    1366 => 
    array (
      'id' => 1617,
      'orden' => 99,
      'core_modelo_id' => 187,
      'core_campo_id' => 98,
    ),
    1367 => 
    array (
      'id' => 1618,
      'orden' => 99,
      'core_modelo_id' => 187,
      'core_campo_id' => 95,
    ),
    1368 => 
    array (
      'id' => 1619,
      'orden' => 99,
      'core_modelo_id' => 187,
      'core_campo_id' => 94,
    ),
    1369 => 
    array (
      'id' => 1620,
      'orden' => 2,
      'core_modelo_id' => 187,
      'core_campo_id' => 201,
    ),
    1370 => 
    array (
      'id' => 1621,
      'orden' => 4,
      'core_modelo_id' => 187,
      'core_campo_id' => 88,
    ),
    1371 => 
    array (
      'id' => 1622,
      'orden' => 6,
      'core_modelo_id' => 187,
      'core_campo_id' => 93,
    ),
    1372 => 
    array (
      'id' => 1623,
      'orden' => 8,
      'core_modelo_id' => 187,
      'core_campo_id' => 39,
    ),
    1373 => 
    array (
      'id' => 1624,
      'orden' => 10,
      'core_modelo_id' => 187,
      'core_campo_id' => 92,
    ),
    1374 => 
    array (
      'id' => 1625,
      'orden' => 99,
      'core_modelo_id' => 187,
      'core_campo_id' => 87,
    ),
    1375 => 
    array (
      'id' => 1626,
      'orden' => 12,
      'core_modelo_id' => 187,
      'core_campo_id' => 8,
    ),
    1376 => 
    array (
      'id' => 1627,
      'orden' => 18,
      'core_modelo_id' => 175,
      'core_campo_id' => 176,
    ),
    1377 => 
    array (
      'id' => 1629,
      'orden' => 2,
      'core_modelo_id' => 192,
      'core_campo_id' => 72,
    ),
    1378 => 
    array (
      'id' => 1631,
      'orden' => 4,
      'core_modelo_id' => 192,
      'core_campo_id' => 311,
    ),
    1379 => 
    array (
      'id' => 1633,
      'orden' => 6,
      'core_modelo_id' => 192,
      'core_campo_id' => 643,
    ),
    1380 => 
    array (
      'id' => 1634,
      'orden' => 6,
      'core_modelo_id' => 90,
      'core_campo_id' => 649,
    ),
    1381 => 
    array (
      'id' => 1635,
      'orden' => 10,
      'core_modelo_id' => 83,
      'core_campo_id' => 647,
    ),
    1382 => 
    array (
      'id' => 1636,
      'orden' => 14,
      'core_modelo_id' => 83,
      'core_campo_id' => 648,
    ),
    1383 => 
    array (
      'id' => 1637,
      'orden' => 16,
      'core_modelo_id' => 83,
      'core_campo_id' => 646,
    ),
    1384 => 
    array (
      'id' => 1638,
      'orden' => 6,
      'core_modelo_id' => 83,
      'core_campo_id' => 644,
    ),
    1385 => 
    array (
      'id' => 1639,
      'orden' => 2,
      'core_modelo_id' => 83,
      'core_campo_id' => 356,
    ),
    1386 => 
    array (
      'id' => 1640,
      'orden' => 0,
      'core_modelo_id' => 83,
      'core_campo_id' => 650,
    ),
    1387 => 
    array (
      'id' => 1642,
      'orden' => 2,
      'core_modelo_id' => 213,
      'core_campo_id' => 667,
    ),
    1388 => 
    array (
      'id' => 1643,
      'orden' => 4,
      'core_modelo_id' => 213,
      'core_campo_id' => 665,
    ),
    1389 => 
    array (
      'id' => 1644,
      'orden' => 4,
      'core_modelo_id' => 213,
      'core_campo_id' => 666,
    ),
    1390 => 
    array (
      'id' => 1645,
      'orden' => 2,
      'core_modelo_id' => 212,
      'core_campo_id' => 679,
    ),
    1391 => 
    array (
      'id' => 1646,
      'orden' => 4,
      'core_modelo_id' => 212,
      'core_campo_id' => 316,
    ),
    1392 => 
    array (
      'id' => 1647,
      'orden' => 6,
      'core_modelo_id' => 212,
      'core_campo_id' => 666,
    ),
    1393 => 
    array (
      'id' => 1648,
      'orden' => 2,
      'core_modelo_id' => 211,
      'core_campo_id' => 673,
    ),
    1394 => 
    array (
      'id' => 1649,
      'orden' => 4,
      'core_modelo_id' => 211,
      'core_campo_id' => 674,
    ),
    1395 => 
    array (
      'id' => 1650,
      'orden' => 2,
      'core_modelo_id' => 210,
      'core_campo_id' => 675,
    ),
    1396 => 
    array (
      'id' => 1651,
      'orden' => 4,
      'core_modelo_id' => 210,
      'core_campo_id' => 676,
    ),
    1397 => 
    array (
      'id' => 1652,
      'orden' => 6,
      'core_modelo_id' => 210,
      'core_campo_id' => 677,
    ),
    1398 => 
    array (
      'id' => 1653,
      'orden' => 8,
      'core_modelo_id' => 210,
      'core_campo_id' => 678,
    ),
    1399 => 
    array (
      'id' => 1654,
      'orden' => 10,
      'core_modelo_id' => 210,
      'core_campo_id' => 679,
    ),
    1400 => 
    array (
      'id' => 1655,
      'orden' => 2,
      'core_modelo_id' => 209,
      'core_campo_id' => 680,
    ),
    1401 => 
    array (
      'id' => 1656,
      'orden' => 4,
      'core_modelo_id' => 209,
      'core_campo_id' => 681,
    ),
    1402 => 
    array (
      'id' => 1657,
      'orden' => 6,
      'core_modelo_id' => 209,
      'core_campo_id' => 682,
    ),
    1403 => 
    array (
      'id' => 1658,
      'orden' => 2,
      'core_modelo_id' => 208,
      'core_campo_id' => 683,
    ),
    1404 => 
    array (
      'id' => 1659,
      'orden' => 4,
      'core_modelo_id' => 208,
      'core_campo_id' => 684,
    ),
    1405 => 
    array (
      'id' => 1660,
      'orden' => 6,
      'core_modelo_id' => 208,
      'core_campo_id' => 685,
    ),
    1406 => 
    array (
      'id' => 1661,
      'orden' => 2,
      'core_modelo_id' => 207,
      'core_campo_id' => 683,
    ),
    1407 => 
    array (
      'id' => 1662,
      'orden' => 4,
      'core_modelo_id' => 207,
      'core_campo_id' => 684,
    ),
    1408 => 
    array (
      'id' => 1663,
      'orden' => 6,
      'core_modelo_id' => 207,
      'core_campo_id' => 280,
    ),
    1409 => 
    array (
      'id' => 1664,
      'orden' => 2,
      'core_modelo_id' => 206,
      'core_campo_id' => 688,
    ),
    1410 => 
    array (
      'id' => 1665,
      'orden' => 4,
      'core_modelo_id' => 206,
      'core_campo_id' => 93,
    ),
    1411 => 
    array (
      'id' => 1666,
      'orden' => 6,
      'core_modelo_id' => 206,
      'core_campo_id' => 686,
    ),
    1412 => 
    array (
      'id' => 1667,
      'orden' => 8,
      'core_modelo_id' => 206,
      'core_campo_id' => 687,
    ),
    1413 => 
    array (
      'id' => 1668,
      'orden' => 2,
      'core_modelo_id' => 205,
      'core_campo_id' => 675,
    ),
    1414 => 
    array (
      'id' => 1669,
      'orden' => 4,
      'core_modelo_id' => 205,
      'core_campo_id' => 697,
    ),
    1415 => 
    array (
      'id' => 1670,
      'orden' => 6,
      'core_modelo_id' => 205,
      'core_campo_id' => 698,
    ),
    1416 => 
    array (
      'id' => 1671,
      'orden' => 2,
      'core_modelo_id' => 204,
      'core_campo_id' => 718,
    ),
    1417 => 
    array (
      'id' => 1672,
      'orden' => 4,
      'core_modelo_id' => 204,
      'core_campo_id' => 719,
    ),
    1418 => 
    array (
      'id' => 1673,
      'orden' => 6,
      'core_modelo_id' => 204,
      'core_campo_id' => 720,
    ),
    1419 => 
    array (
      'id' => 1674,
      'orden' => 2,
      'core_modelo_id' => 203,
      'core_campo_id' => 721,
    ),
    1420 => 
    array (
      'id' => 1675,
      'orden' => 2,
      'core_modelo_id' => 202,
      'core_campo_id' => 662,
    ),
    1421 => 
    array (
      'id' => 1676,
      'orden' => 4,
      'core_modelo_id' => 202,
      'core_campo_id' => 651,
    ),
    1422 => 
    array (
      'id' => 1677,
      'orden' => 6,
      'core_modelo_id' => 202,
      'core_campo_id' => 652,
    ),
    1423 => 
    array (
      'id' => 1678,
      'orden' => 8,
      'core_modelo_id' => 202,
      'core_campo_id' => 653,
    ),
    1424 => 
    array (
      'id' => 1679,
      'orden' => 10,
      'core_modelo_id' => 202,
      'core_campo_id' => 654,
    ),
    1425 => 
    array (
      'id' => 1681,
      'orden' => 12,
      'core_modelo_id' => 202,
      'core_campo_id' => 655,
    ),
    1426 => 
    array (
      'id' => 1682,
      'orden' => 14,
      'core_modelo_id' => 202,
      'core_campo_id' => 656,
    ),
    1427 => 
    array (
      'id' => 1683,
      'orden' => 16,
      'core_modelo_id' => 202,
      'core_campo_id' => 657,
    ),
    1428 => 
    array (
      'id' => 1684,
      'orden' => 18,
      'core_modelo_id' => 202,
      'core_campo_id' => 658,
    ),
    1429 => 
    array (
      'id' => 1685,
      'orden' => 20,
      'core_modelo_id' => 202,
      'core_campo_id' => 659,
    ),
    1430 => 
    array (
      'id' => 1686,
      'orden' => 22,
      'core_modelo_id' => 202,
      'core_campo_id' => 660,
    ),
    1431 => 
    array (
      'id' => 1687,
      'orden' => 24,
      'core_modelo_id' => 202,
      'core_campo_id' => 661,
    ),
    1432 => 
    array (
      'id' => 1689,
      'orden' => 4,
      'core_modelo_id' => 201,
      'core_campo_id' => 663,
    ),
    1433 => 
    array (
      'id' => 1690,
      'orden' => 2,
      'core_modelo_id' => 200,
      'core_campo_id' => 316,
    ),
    1434 => 
    array (
      'id' => 1691,
      'orden' => 4,
      'core_modelo_id' => 200,
      'core_campo_id' => 236,
    ),
    1435 => 
    array (
      'id' => 1692,
      'orden' => 6,
      'core_modelo_id' => 200,
      'core_campo_id' => 668,
    ),
    1436 => 
    array (
      'id' => 1693,
      'orden' => 8,
      'core_modelo_id' => 200,
      'core_campo_id' => 669,
    ),
    1437 => 
    array (
      'id' => 1694,
      'orden' => 10,
      'core_modelo_id' => 200,
      'core_campo_id' => 670,
    ),
    1438 => 
    array (
      'id' => 1695,
      'orden' => 12,
      'core_modelo_id' => 200,
      'core_campo_id' => 671,
    ),
    1439 => 
    array (
      'id' => 1696,
      'orden' => 14,
      'core_modelo_id' => 200,
      'core_campo_id' => 672,
    ),
    1440 => 
    array (
      'id' => 1698,
      'orden' => 2,
      'core_modelo_id' => 199,
      'core_campo_id' => 695,
    ),
    1441 => 
    array (
      'id' => 1699,
      'orden' => 4,
      'core_modelo_id' => 199,
      'core_campo_id' => 689,
    ),
    1442 => 
    array (
      'id' => 1700,
      'orden' => 6,
      'core_modelo_id' => 199,
      'core_campo_id' => 690,
    ),
    1443 => 
    array (
      'id' => 1701,
      'orden' => 8,
      'core_modelo_id' => 199,
      'core_campo_id' => 691,
    ),
    1444 => 
    array (
      'id' => 1702,
      'orden' => 10,
      'core_modelo_id' => 199,
      'core_campo_id' => 692,
    ),
    1445 => 
    array (
      'id' => 1703,
      'orden' => 12,
      'core_modelo_id' => 199,
      'core_campo_id' => 693,
    ),
    1446 => 
    array (
      'id' => 1704,
      'orden' => 14,
      'core_modelo_id' => 199,
      'core_campo_id' => 694,
    ),
    1447 => 
    array (
      'id' => 1705,
      'orden' => 2,
      'core_modelo_id' => 198,
      'core_campo_id' => 673,
    ),
    1448 => 
    array (
      'id' => 1706,
      'orden' => 4,
      'core_modelo_id' => 198,
      'core_campo_id' => 696,
    ),
    1449 => 
    array (
      'id' => 1707,
      'orden' => 6,
      'core_modelo_id' => 198,
      'core_campo_id' => 690,
    ),
    1450 => 
    array (
      'id' => 1708,
      'orden' => 8,
      'core_modelo_id' => 198,
      'core_campo_id' => 691,
    ),
    1451 => 
    array (
      'id' => 1709,
      'orden' => 10,
      'core_modelo_id' => 198,
      'core_campo_id' => 692,
    ),
    1452 => 
    array (
      'id' => 1710,
      'orden' => 12,
      'core_modelo_id' => 198,
      'core_campo_id' => 693,
    ),
    1453 => 
    array (
      'id' => 1711,
      'orden' => 14,
      'core_modelo_id' => 198,
      'core_campo_id' => 694,
    ),
    1454 => 
    array (
      'id' => 1712,
      'orden' => 2,
      'core_modelo_id' => 197,
      'core_campo_id' => 3,
    ),
    1455 => 
    array (
      'id' => 1713,
      'orden' => 4,
      'core_modelo_id' => 197,
      'core_campo_id' => 700,
    ),
    1456 => 
    array (
      'id' => 1714,
      'orden' => 6,
      'core_modelo_id' => 197,
      'core_campo_id' => 93,
    ),
    1457 => 
    array (
      'id' => 1715,
      'orden' => 8,
      'core_modelo_id' => 197,
      'core_campo_id' => 701,
    ),
    1458 => 
    array (
      'id' => 1716,
      'orden' => 10,
      'core_modelo_id' => 197,
      'core_campo_id' => 702,
    ),
    1459 => 
    array (
      'id' => 1717,
      'orden' => 12,
      'core_modelo_id' => 197,
      'core_campo_id' => 703,
    ),
    1460 => 
    array (
      'id' => 1718,
      'orden' => 14,
      'core_modelo_id' => 197,
      'core_campo_id' => 704,
    ),
    1461 => 
    array (
      'id' => 1719,
      'orden' => 16,
      'core_modelo_id' => 197,
      'core_campo_id' => 125,
    ),
    1462 => 
    array (
      'id' => 1720,
      'orden' => 18,
      'core_modelo_id' => 197,
      'core_campo_id' => 705,
    ),
    1463 => 
    array (
      'id' => 1721,
      'orden' => 20,
      'core_modelo_id' => 197,
      'core_campo_id' => 706,
    ),
    1464 => 
    array (
      'id' => 1722,
      'orden' => 22,
      'core_modelo_id' => 197,
      'core_campo_id' => 707,
    ),
    1465 => 
    array (
      'id' => 1723,
      'orden' => 24,
      'core_modelo_id' => 197,
      'core_campo_id' => 708,
    ),
    1466 => 
    array (
      'id' => 1724,
      'orden' => 26,
      'core_modelo_id' => 197,
      'core_campo_id' => 709,
    ),
    1467 => 
    array (
      'id' => 1725,
      'orden' => 28,
      'core_modelo_id' => 197,
      'core_campo_id' => 710,
    ),
    1468 => 
    array (
      'id' => 1726,
      'orden' => 30,
      'core_modelo_id' => 197,
      'core_campo_id' => 711,
    ),
    1469 => 
    array (
      'id' => 1727,
      'orden' => 32,
      'core_modelo_id' => 197,
      'core_campo_id' => 712,
    ),
    1470 => 
    array (
      'id' => 1728,
      'orden' => 34,
      'core_modelo_id' => 197,
      'core_campo_id' => 713,
    ),
    1471 => 
    array (
      'id' => 1730,
      'orden' => 36,
      'core_modelo_id' => 197,
      'core_campo_id' => 714,
    ),
    1472 => 
    array (
      'id' => 1731,
      'orden' => 38,
      'core_modelo_id' => 197,
      'core_campo_id' => 715,
    ),
    1473 => 
    array (
      'id' => 1732,
      'orden' => 40,
      'core_modelo_id' => 197,
      'core_campo_id' => 716,
    ),
    1474 => 
    array (
      'id' => 1733,
      'orden' => 42,
      'core_modelo_id' => 197,
      'core_campo_id' => 717,
    ),
    1475 => 
    array (
      'id' => 1734,
      'orden' => 44,
      'core_modelo_id' => 197,
      'core_campo_id' => 695,
    ),
    1476 => 
    array (
      'id' => 1735,
      'orden' => 2,
      'core_modelo_id' => 196,
      'core_campo_id' => 664,
    ),
    1477 => 
    array (
      'id' => 1736,
      'orden' => 4,
      'core_modelo_id' => 196,
      'core_campo_id' => 22,
    ),
    1478 => 
    array (
      'id' => 1738,
      'orden' => 4,
      'core_modelo_id' => 195,
      'core_campo_id' => 22,
    ),
    1479 => 
    array (
      'id' => 1739,
      'orden' => 16,
      'core_modelo_id' => 200,
      'core_campo_id' => 722,
    ),
    1480 => 
    array (
      'id' => 1740,
      'orden' => 0,
      'core_modelo_id' => 215,
      'core_campo_id' => 39,
    ),
    1481 => 
    array (
      'id' => 1741,
      'orden' => 2,
      'core_modelo_id' => 215,
      'core_campo_id' => 508,
    ),
    1482 => 
    array (
      'id' => 1742,
      'orden' => 4,
      'core_modelo_id' => 215,
      'core_campo_id' => 509,
    ),
    1483 => 
    array (
      'id' => 1743,
      'orden' => 6,
      'core_modelo_id' => 215,
      'core_campo_id' => 22,
    ),
    1484 => 
    array (
      'id' => 1744,
      'orden' => 26,
      'core_modelo_id' => 134,
      'core_campo_id' => 161,
    ),
    1485 => 
    array (
      'id' => 1745,
      'orden' => 24,
      'core_modelo_id' => 134,
      'core_campo_id' => 561,
    ),
    1486 => 
    array (
      'id' => 1746,
      'orden' => 13,
      'core_modelo_id' => 216,
      'core_campo_id' => 18,
    ),
    1487 => 
    array (
      'id' => 1747,
      'orden' => 44,
      'core_modelo_id' => 216,
      'core_campo_id' => 22,
    ),
    1488 => 
    array (
      'id' => 1748,
      'orden' => 7,
      'core_modelo_id' => 216,
      'core_campo_id' => 37,
    ),
    1489 => 
    array (
      'id' => 1749,
      'orden' => 1,
      'core_modelo_id' => 216,
      'core_campo_id' => 41,
    ),
    1490 => 
    array (
      'id' => 1750,
      'orden' => 9,
      'core_modelo_id' => 216,
      'core_campo_id' => 42,
    ),
    1491 => 
    array (
      'id' => 1751,
      'orden' => 5,
      'core_modelo_id' => 216,
      'core_campo_id' => 43,
    ),
    1492 => 
    array (
      'id' => 1752,
      'orden' => 8,
      'core_modelo_id' => 216,
      'core_campo_id' => 44,
    ),
    1493 => 
    array (
      'id' => 1753,
      'orden' => 2,
      'core_modelo_id' => 216,
      'core_campo_id' => 45,
    ),
    1494 => 
    array (
      'id' => 1754,
      'orden' => 4,
      'core_modelo_id' => 216,
      'core_campo_id' => 46,
    ),
    1495 => 
    array (
      'id' => 1755,
      'orden' => 5,
      'core_modelo_id' => 216,
      'core_campo_id' => 47,
    ),
    1496 => 
    array (
      'id' => 1756,
      'orden' => 10,
      'core_modelo_id' => 216,
      'core_campo_id' => 50,
    ),
    1497 => 
    array (
      'id' => 1757,
      'orden' => 14,
      'core_modelo_id' => 216,
      'core_campo_id' => 53,
    ),
    1498 => 
    array (
      'id' => 1758,
      'orden' => 12,
      'core_modelo_id' => 216,
      'core_campo_id' => 55,
    ),
    1499 => 
    array (
      'id' => 1759,
      'orden' => 6,
      'core_modelo_id' => 216,
      'core_campo_id' => 58,
    ),
    1500 => 
    array (
      'id' => 1760,
      'orden' => 99,
      'core_modelo_id' => 216,
      'core_campo_id' => 94,
    ),
    1501 => 
    array (
      'id' => 1761,
      'orden' => 99,
      'core_modelo_id' => 216,
      'core_campo_id' => 95,
    ),
    1502 => 
    array (
      'id' => 1762,
      'orden' => 99,
      'core_modelo_id' => 216,
      'core_campo_id' => 386,
    ),
    1503 => 
    array (
      'id' => 1763,
      'orden' => 27,
      'core_modelo_id' => 216,
      'core_campo_id' => 448,
    ),
    1504 => 
    array (
      'id' => 1764,
      'orden' => 32,
      'core_modelo_id' => 216,
      'core_campo_id' => 495,
    ),
    1505 => 
    array (
      'id' => 1765,
      'orden' => 20,
      'core_modelo_id' => 216,
      'core_campo_id' => 496,
    ),
    1506 => 
    array (
      'id' => 1766,
      'orden' => 22,
      'core_modelo_id' => 216,
      'core_campo_id' => 497,
    ),
    1507 => 
    array (
      'id' => 1767,
      'orden' => 34,
      'core_modelo_id' => 216,
      'core_campo_id' => 498,
    ),
    1508 => 
    array (
      'id' => 1768,
      'orden' => 36,
      'core_modelo_id' => 216,
      'core_campo_id' => 499,
    ),
    1509 => 
    array (
      'id' => 1769,
      'orden' => 38,
      'core_modelo_id' => 216,
      'core_campo_id' => 500,
    ),
    1510 => 
    array (
      'id' => 1770,
      'orden' => 40,
      'core_modelo_id' => 216,
      'core_campo_id' => 501,
    ),
    1511 => 
    array (
      'id' => 1771,
      'orden' => 5,
      'core_modelo_id' => 216,
      'core_campo_id' => 118,
    ),
    1512 => 
    array (
      'id' => 1774,
      'orden' => 0,
      'core_modelo_id' => 138,
      'core_campo_id' => 140,
    ),
    1513 => 
    array (
      'id' => 1775,
      'orden' => 0,
      'core_modelo_id' => 216,
      'core_campo_id' => 140,
    ),
    1514 => 
    array (
      'id' => 1776,
      'orden' => 2,
      'core_modelo_id' => 117,
      'core_campo_id' => 726,
    ),
    1515 => 
    array (
      'id' => 1778,
      'orden' => 7,
      'core_modelo_id' => 117,
      'core_campo_id' => 727,
    ),
    1516 => 
    array (
      'id' => 1779,
      'orden' => 5,
      'core_modelo_id' => 117,
      'core_campo_id' => 169,
    ),
    1517 => 
    array (
      'id' => 1780,
      'orden' => 2,
      'core_modelo_id' => 217,
      'core_campo_id' => 728,
    ),
    1518 => 
    array (
      'id' => 1781,
      'orden' => 4,
      'core_modelo_id' => 217,
      'core_campo_id' => 729,
    ),
    1519 => 
    array (
      'id' => 1782,
      'orden' => 6,
      'core_modelo_id' => 217,
      'core_campo_id' => 730,
    ),
    1520 => 
    array (
      'id' => 1783,
      'orden' => 8,
      'core_modelo_id' => 217,
      'core_campo_id' => 731,
    ),
    1521 => 
    array (
      'id' => 1784,
      'orden' => 8,
      'core_modelo_id' => 149,
      'core_campo_id' => 39,
    ),
    1522 => 
    array (
      'id' => 1785,
      'orden' => 2,
      'core_modelo_id' => 218,
      'core_campo_id' => 726,
    ),
    1523 => 
    array (
      'id' => 1786,
      'orden' => 4,
      'core_modelo_id' => 218,
      'core_campo_id' => 736,
    ),
    1524 => 
    array (
      'id' => 1789,
      'orden' => 10,
      'core_modelo_id' => 218,
      'core_campo_id' => 18,
    ),
    1525 => 
    array (
      'id' => 1790,
      'orden' => 16,
      'core_modelo_id' => 218,
      'core_campo_id' => 19,
    ),
    1526 => 
    array (
      'id' => 1791,
      'orden' => 18,
      'core_modelo_id' => 218,
      'core_campo_id' => 20,
    ),
    1527 => 
    array (
      'id' => 1792,
      'orden' => 20,
      'core_modelo_id' => 218,
      'core_campo_id' => 737,
    ),
    1528 => 
    array (
      'id' => 1793,
      'orden' => 6,
      'core_modelo_id' => 218,
      'core_campo_id' => 103,
    ),
    1529 => 
    array (
      'id' => 1794,
      'orden' => 8,
      'core_modelo_id' => 218,
      'core_campo_id' => 46,
    ),
    1530 => 
    array (
      'id' => 1795,
      'orden' => 12,
      'core_modelo_id' => 218,
      'core_campo_id' => 236,
    ),
    1531 => 
    array (
      'id' => 1796,
      'orden' => 14,
      'core_modelo_id' => 218,
      'core_campo_id' => 668,
    ),
    1532 => 
    array (
      'id' => 1798,
      'orden' => 14,
      'core_modelo_id' => 219,
      'core_campo_id' => 22,
    ),
    1533 => 
    array (
      'id' => 1799,
      'orden' => 2,
      'core_modelo_id' => 219,
      'core_campo_id' => 93,
    ),
    1534 => 
    array (
      'id' => 1800,
      'orden' => 13,
      'core_modelo_id' => 219,
      'core_campo_id' => 174,
    ),
    1535 => 
    array (
      'id' => 1801,
      'orden' => 8,
      'core_modelo_id' => 219,
      'core_campo_id' => 177,
    ),
    1536 => 
    array (
      'id' => 1802,
      'orden' => 10,
      'core_modelo_id' => 219,
      'core_campo_id' => 311,
    ),
    1537 => 
    array (
      'id' => 1803,
      'orden' => 12,
      'core_modelo_id' => 219,
      'core_campo_id' => 312,
    ),
    1538 => 
    array (
      'id' => 1804,
      'orden' => 6,
      'core_modelo_id' => 219,
      'core_campo_id' => 603,
    ),
    1539 => 
    array (
      'id' => 1805,
      'orden' => 3,
      'core_modelo_id' => 47,
      'core_campo_id' => 740,
    ),
    1540 => 
    array (
      'id' => 1806,
      'orden' => 4,
      'core_modelo_id' => 54,
      'core_campo_id' => 740,
    ),
    1541 => 
    array (
      'id' => 1807,
      'orden' => 4,
      'core_modelo_id' => 46,
      'core_campo_id' => 740,
    ),
    1542 => 
    array (
      'id' => 1808,
      'orden' => 4,
      'core_modelo_id' => 110,
      'core_campo_id' => 123,
    ),
    1543 => 
    array (
      'id' => 1809,
      'orden' => 12,
      'core_modelo_id' => 110,
      'core_campo_id' => 329,
    ),
    1544 => 
    array (
      'id' => 1810,
      'orden' => 1,
      'core_modelo_id' => 110,
      'core_campo_id' => 442,
    ),
    1545 => 
    array (
      'id' => 1811,
      'orden' => 11,
      'core_modelo_id' => 110,
      'core_campo_id' => 443,
    ),
    1546 => 
    array (
      'id' => 1812,
      'orden' => 17,
      'core_modelo_id' => 110,
      'core_campo_id' => 444,
    ),
    1547 => 
    array (
      'id' => 1813,
      'orden' => 18,
      'core_modelo_id' => 110,
      'core_campo_id' => 445,
    ),
    1548 => 
    array (
      'id' => 1814,
      'orden' => 22,
      'core_modelo_id' => 110,
      'core_campo_id' => 446,
    ),
    1549 => 
    array (
      'id' => 1815,
      'orden' => 23,
      'core_modelo_id' => 110,
      'core_campo_id' => 741,
    ),
    1550 => 
    array (
      'id' => 1816,
      'orden' => 24,
      'core_modelo_id' => 110,
      'core_campo_id' => 742,
    ),
    1551 => 
    array (
      'id' => 1817,
      'orden' => 16,
      'core_modelo_id' => 221,
      'core_campo_id' => 8,
    ),
    1552 => 
    array (
      'id' => 1818,
      'orden' => 99,
      'core_modelo_id' => 221,
      'core_campo_id' => 87,
    ),
    1553 => 
    array (
      'id' => 1819,
      'orden' => 3,
      'core_modelo_id' => 221,
      'core_campo_id' => 88,
    ),
    1554 => 
    array (
      'id' => 1820,
      'orden' => 13,
      'core_modelo_id' => 221,
      'core_campo_id' => 90,
    ),
    1555 => 
    array (
      'id' => 1821,
      'orden' => 4,
      'core_modelo_id' => 221,
      'core_campo_id' => 93,
    ),
    1556 => 
    array (
      'id' => 1822,
      'orden' => 99,
      'core_modelo_id' => 221,
      'core_campo_id' => 100,
    ),
    1557 => 
    array (
      'id' => 1823,
      'orden' => 12,
      'core_modelo_id' => 221,
      'core_campo_id' => 194,
    ),
    1558 => 
    array (
      'id' => 1824,
      'orden' => 2,
      'core_modelo_id' => 221,
      'core_campo_id' => 201,
    ),
    1559 => 
    array (
      'id' => 1825,
      'orden' => 8,
      'core_modelo_id' => 221,
      'core_campo_id' => 521,
    ),
    1560 => 
    array (
      'id' => 1826,
      'orden' => 6,
      'core_modelo_id' => 221,
      'core_campo_id' => 522,
    ),
    1561 => 
    array (
      'id' => 1827,
      'orden' => 10,
      'core_modelo_id' => 221,
      'core_campo_id' => 523,
    ),
    1562 => 
    array (
      'id' => 1828,
      'orden' => 14,
      'core_modelo_id' => 221,
      'core_campo_id' => 524,
    ),
    1563 => 
    array (
      'id' => 1829,
      'orden' => 2,
      'core_modelo_id' => 101,
      'core_campo_id' => 743,
    ),
    1564 => 
    array (
      'id' => 1830,
      'orden' => 3,
      'core_modelo_id' => 101,
      'core_campo_id' => 744,
    ),
    1565 => 
    array (
      'id' => 1831,
      'orden' => 8,
      'core_modelo_id' => 101,
      'core_campo_id' => 745,
    ),
    1566 => 
    array (
      'id' => 1832,
      'orden' => 2,
      'core_modelo_id' => 201,
      'core_campo_id' => 746,
    ),
    1567 => 
    array (
      'id' => 1833,
      'orden' => 2,
      'core_modelo_id' => 195,
      'core_campo_id' => 747,
    ),
    1568 => 
    array (
      'id' => 1834,
      'orden' => 6,
      'core_modelo_id' => 195,
      'core_campo_id' => 748,
    ),
    1569 => 
    array (
      'id' => 1835,
      'orden' => 6,
      'core_modelo_id' => 201,
      'core_campo_id' => 748,
    ),
    1570 => 
    array (
      'id' => 1836,
      'orden' => 7,
      'core_modelo_id' => 138,
      'core_campo_id' => 118,
    ),
    1571 => 
    array (
      'id' => 1837,
      'orden' => 14,
      'core_modelo_id' => 12,
      'core_campo_id' => 749,
    ),
    1572 => 
    array (
      'id' => 1839,
      'orden' => 6,
      'core_modelo_id' => 146,
      'core_campo_id' => 118,
    ),
    1573 => 
    array (
      'id' => 1840,
      'orden' => 2,
      'core_modelo_id' => 223,
      'core_campo_id' => 750,
    ),
    1574 => 
    array (
      'id' => 1841,
      'orden' => 2,
      'core_modelo_id' => 224,
      'core_campo_id' => 750,
    ),
    1575 => 
    array (
      'id' => 1842,
      'orden' => 99,
      'core_modelo_id' => 223,
      'core_campo_id' => 412,
    ),
    1576 => 
    array (
      'id' => 1844,
      'orden' => 16,
      'core_modelo_id' => 95,
      'core_campo_id' => 751,
    ),
    1577 => 
    array (
      'id' => 1845,
      'orden' => 0,
      'core_modelo_id' => 225,
      'core_campo_id' => 117,
    ),
    1578 => 
    array (
      'id' => 1847,
      'orden' => 4,
      'core_modelo_id' => 225,
      'core_campo_id' => 311,
    ),
    1579 => 
    array (
      'id' => 1848,
      'orden' => 6,
      'core_modelo_id' => 225,
      'core_campo_id' => 312,
    ),
    1580 => 
    array (
      'id' => 1849,
      'orden' => 8,
      'core_modelo_id' => 225,
      'core_campo_id' => 169,
    ),
    1581 => 
    array (
      'id' => 1850,
      'orden' => 10,
      'core_modelo_id' => 225,
      'core_campo_id' => 22,
    ),
    1582 => 
    array (
      'id' => 1851,
      'orden' => 12,
      'core_modelo_id' => 225,
      'core_campo_id' => 313,
    ),
    1583 => 
    array (
      'id' => 1852,
      'orden' => 2,
      'core_modelo_id' => 225,
      'core_campo_id' => 177,
    ),
    1584 => 
    array (
      'id' => 1853,
      'orden' => 99,
      'core_modelo_id' => 37,
      'core_campo_id' => 752,
    ),
    1585 => 
    array (
      'id' => 1854,
      'orden' => 99,
      'core_modelo_id' => 38,
      'core_campo_id' => 752,
    ),
    1586 => 
    array (
      'id' => 1855,
      'orden' => 99,
      'core_modelo_id' => 36,
      'core_campo_id' => 752,
    ),
    1587 => 
    array (
      'id' => 1856,
      'orden' => 2,
      'core_modelo_id' => 77,
      'core_campo_id' => 316,
    ),
    1588 => 
    array (
      'id' => 1857,
      'orden' => 4,
      'core_modelo_id' => 77,
      'core_campo_id' => 169,
    ),
    1589 => 
    array (
      'id' => 1858,
      'orden' => 2,
      'core_modelo_id' => 85,
      'core_campo_id' => 753,
    ),
    1590 => 
    array (
      'id' => 1859,
      'orden' => 4,
      'core_modelo_id' => 85,
      'core_campo_id' => 257,
    ),
    1591 => 
    array (
      'id' => 1860,
      'orden' => 7,
      'core_modelo_id' => 84,
      'core_campo_id' => 754,
    ),
    1592 => 
    array (
      'id' => 1861,
      'orden' => 1,
      'core_modelo_id' => 87,
      'core_campo_id' => 585,
    ),
    1593 => 
    array (
      'id' => 1862,
      'orden' => 2,
      'core_modelo_id' => 87,
      'core_campo_id' => 755,
    ),
    1594 => 
    array (
      'id' => 1863,
      'orden' => 1,
      'core_modelo_id' => 88,
      'core_campo_id' => 585,
    ),
    1595 => 
    array (
      'id' => 1865,
      'orden' => 2,
      'core_modelo_id' => 227,
      'core_campo_id' => 2,
    ),
    1596 => 
    array (
      'id' => 1866,
      'orden' => 4,
      'core_modelo_id' => 227,
      'core_campo_id' => 759,
    ),
    1597 => 
    array (
      'id' => 1867,
      'orden' => 6,
      'core_modelo_id' => 227,
      'core_campo_id' => 760,
    ),
    1598 => 
    array (
      'id' => 1868,
      'orden' => 8,
      'core_modelo_id' => 227,
      'core_campo_id' => 761,
    ),
    1599 => 
    array (
      'id' => 1869,
      'orden' => 10,
      'core_modelo_id' => 227,
      'core_campo_id' => 762,
    ),
    1600 => 
    array (
      'id' => 1870,
      'orden' => 12,
      'core_modelo_id' => 227,
      'core_campo_id' => 763,
    ),
    1601 => 
    array (
      'id' => 1872,
      'orden' => 99,
      'core_modelo_id' => 227,
      'core_campo_id' => 94,
    ),
    1602 => 
    array (
      'id' => 1873,
      'orden' => 99,
      'core_modelo_id' => 227,
      'core_campo_id' => 95,
    ),
    1603 => 
    array (
      'id' => 1875,
      'orden' => 2,
      'core_modelo_id' => 226,
      'core_campo_id' => 124,
    ),
    1604 => 
    array (
      'id' => 1876,
      'orden' => 4,
      'core_modelo_id' => 226,
      'core_campo_id' => 7,
    ),
    1605 => 
    array (
      'id' => 1877,
      'orden' => 6,
      'core_modelo_id' => 226,
      'core_campo_id' => 18,
    ),
    1606 => 
    array (
      'id' => 1878,
      'orden' => 8,
      'core_modelo_id' => 226,
      'core_campo_id' => 19,
    ),
    1607 => 
    array (
      'id' => 1879,
      'orden' => 10,
      'core_modelo_id' => 226,
      'core_campo_id' => 20,
    ),
    1608 => 
    array (
      'id' => 1880,
      'orden' => 99,
      'core_modelo_id' => 226,
      'core_campo_id' => 195,
    ),
    1609 => 
    array (
      'id' => 1881,
      'orden' => 99,
      'core_modelo_id' => 227,
      'core_campo_id' => 386,
    ),
    1610 => 
    array (
      'id' => 1882,
      'orden' => 14,
      'core_modelo_id' => 227,
      'core_campo_id' => 257,
    ),
    1611 => 
    array (
      'id' => 1883,
      'orden' => 99,
      'core_modelo_id' => 228,
      'core_campo_id' => 87,
    ),
    1612 => 
    array (
      'id' => 1884,
      'orden' => 2,
      'core_modelo_id' => 228,
      'core_campo_id' => 88,
    ),
    1613 => 
    array (
      'id' => 1885,
      'orden' => 99,
      'core_modelo_id' => 228,
      'core_campo_id' => 100,
    ),
    1614 => 
    array (
      'id' => 1886,
      'orden' => 4,
      'core_modelo_id' => 228,
      'core_campo_id' => 93,
    ),
    1615 => 
    array (
      'id' => 1887,
      'orden' => 0,
      'core_modelo_id' => 228,
      'core_campo_id' => 201,
    ),
    1616 => 
    array (
      'id' => 1890,
      'orden' => 6,
      'core_modelo_id' => 228,
      'core_campo_id' => 758,
    ),
    1617 => 
    array (
      'id' => 1891,
      'orden' => 8,
      'core_modelo_id' => 228,
      'core_campo_id' => 8,
    ),
    1618 => 
    array (
      'id' => 1892,
      'orden' => 99,
      'core_modelo_id' => 228,
      'core_campo_id' => 94,
    ),
    1619 => 
    array (
      'id' => 1893,
      'orden' => 99,
      'core_modelo_id' => 228,
      'core_campo_id' => 95,
    ),
    1620 => 
    array (
      'id' => 1894,
      'orden' => 99,
      'core_modelo_id' => 229,
      'core_campo_id' => 87,
    ),
    1621 => 
    array (
      'id' => 1895,
      'orden' => 99,
      'core_modelo_id' => 229,
      'core_campo_id' => 94,
    ),
    1622 => 
    array (
      'id' => 1896,
      'orden' => 99,
      'core_modelo_id' => 229,
      'core_campo_id' => 95,
    ),
    1623 => 
    array (
      'id' => 1897,
      'orden' => 99,
      'core_modelo_id' => 229,
      'core_campo_id' => 100,
    ),
    1624 => 
    array (
      'id' => 1898,
      'orden' => 8,
      'core_modelo_id' => 229,
      'core_campo_id' => 8,
    ),
    1625 => 
    array (
      'id' => 1899,
      'orden' => 6,
      'core_modelo_id' => 229,
      'core_campo_id' => 93,
    ),
    1626 => 
    array (
      'id' => 1900,
      'orden' => 4,
      'core_modelo_id' => 229,
      'core_campo_id' => 88,
    ),
    1627 => 
    array (
      'id' => 1901,
      'orden' => 2,
      'core_modelo_id' => 229,
      'core_campo_id' => 201,
    ),
    1628 => 
    array (
      'id' => 1902,
      'orden' => 16,
      'core_modelo_id' => 230,
      'core_campo_id' => 8,
    ),
    1629 => 
    array (
      'id' => 1903,
      'orden' => 99,
      'core_modelo_id' => 230,
      'core_campo_id' => 87,
    ),
    1630 => 
    array (
      'id' => 1904,
      'orden' => 3,
      'core_modelo_id' => 230,
      'core_campo_id' => 88,
    ),
    1631 => 
    array (
      'id' => 1905,
      'orden' => 13,
      'core_modelo_id' => 230,
      'core_campo_id' => 90,
    ),
    1632 => 
    array (
      'id' => 1906,
      'orden' => 4,
      'core_modelo_id' => 230,
      'core_campo_id' => 93,
    ),
    1633 => 
    array (
      'id' => 1907,
      'orden' => 99,
      'core_modelo_id' => 230,
      'core_campo_id' => 100,
    ),
    1634 => 
    array (
      'id' => 1908,
      'orden' => 12,
      'core_modelo_id' => 230,
      'core_campo_id' => 194,
    ),
    1635 => 
    array (
      'id' => 1909,
      'orden' => 2,
      'core_modelo_id' => 230,
      'core_campo_id' => 201,
    ),
    1636 => 
    array (
      'id' => 1910,
      'orden' => 20,
      'core_modelo_id' => 230,
      'core_campo_id' => 521,
    ),
    1637 => 
    array (
      'id' => 1911,
      'orden' => 18,
      'core_modelo_id' => 230,
      'core_campo_id' => 522,
    ),
    1638 => 
    array (
      'id' => 1912,
      'orden' => 10,
      'core_modelo_id' => 230,
      'core_campo_id' => 523,
    ),
    1639 => 
    array (
      'id' => 1914,
      'orden' => 99,
      'core_modelo_id' => 224,
      'core_campo_id' => 412,
    ),
    1640 => 
    array (
      'id' => 1915,
      'orden' => 15,
      'core_modelo_id' => 95,
      'core_campo_id' => 764,
    ),
    1641 => 
    array (
      'id' => 1916,
      'orden' => 1,
      'core_modelo_id' => 220,
      'core_campo_id' => 201,
    ),
    1642 => 
    array (
      'id' => 1917,
      'orden' => 3,
      'core_modelo_id' => 220,
      'core_campo_id' => 97,
    ),
    1643 => 
    array (
      'id' => 1918,
      'orden' => 5,
      'core_modelo_id' => 220,
      'core_campo_id' => 2,
    ),
    1644 => 
    array (
      'id' => 1919,
      'orden' => 7,
      'core_modelo_id' => 220,
      'core_campo_id' => 79,
    ),
    1645 => 
    array (
      'id' => 1920,
      'orden' => 9,
      'core_modelo_id' => 220,
      'core_campo_id' => 84,
    ),
    1646 => 
    array (
      'id' => 1921,
      'orden' => 11,
      'core_modelo_id' => 220,
      'core_campo_id' => 530,
    ),
    1647 => 
    array (
      'id' => 1922,
      'orden' => 13,
      'core_modelo_id' => 220,
      'core_campo_id' => 85,
    ),
    1648 => 
    array (
      'id' => 1923,
      'orden' => 15,
      'core_modelo_id' => 220,
      'core_campo_id' => 86,
    ),
    1649 => 
    array (
      'id' => 1924,
      'orden' => 17,
      'core_modelo_id' => 220,
      'core_campo_id' => 120,
    ),
    1650 => 
    array (
      'id' => 1925,
      'orden' => 99,
      'core_modelo_id' => 220,
      'core_campo_id' => 765,
    ),
    1651 => 
    array (
      'id' => 1926,
      'orden' => 17,
      'core_modelo_id' => 230,
      'core_campo_id' => 97,
    ),
    1652 => 
    array (
      'id' => 1927,
      'orden' => 50,
      'core_modelo_id' => 138,
      'core_campo_id' => 769,
    ),
  ),
) ;