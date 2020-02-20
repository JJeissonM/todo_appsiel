<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;
use Storage;

use App\Matriculas\Estudiante;

class Tercero extends Model
{
    protected $table = 'core_terceros'; 

    protected $fillable = ['core_empresa_id','tipo','razon_social','nombre1','otros_nombres','apellido1','apellido2', 'descripcion','id_tipo_documento_id','numero_identificacion', 'digito_verificacion','ciudad_expedicion','direccion1','direccion2','barrio','codigo_ciudad','codigo_postal','telefono1','telefono2','email','pagina_web','estado','user_id','contab_anticipo_cta_id','contab_cartera_cta_id','contab_cxp_cta_id','creado_por','modificado_por'];

    public $encabezado_tabla = ['ID','Nombre/Razón Social','Identificación','Establecimiento','Dirección','Teléfono','Acción'];

    public static function consultar_registros()
    {
        $select_raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo2';

        $registros = Tercero::where('core_terceros.core_empresa_id',Auth::user()->empresa_id)
                    ->select('core_terceros.id AS campo1',DB::raw($select_raw),'core_terceros.numero_identificacion AS campo3','core_terceros.descripcion AS campo4','core_terceros.direccion1 AS campo5','core_terceros.telefono1 AS campo6','core_terceros.id AS campo7')
                    ->get()
                    ->toArray();

        return $registros;
    }

    public static function datos_completos($core_tercero_id)
    {
        //$select_raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS nombre_completo';

        $registro = Tercero::leftJoin('core_tipos_docs_id','core_tipos_docs_id.id','=','core_terceros.id_tipo_documento_id')
                    ->leftJoin('core_ciudades','core_ciudades.id','=','core_terceros.codigo_ciudad')
                    ->where('core_terceros.id',$core_tercero_id)->select('core_tipos_docs_id.abreviatura AS tipo_doc_identidad','core_terceros.descripcion AS nombre_completo','core_terceros.numero_identificacion AS numero_identificacion','core_terceros.descripcion AS descripcion','core_terceros.direccion1 AS direccion1','core_terceros.telefono1 AS telefono1','core_ciudades.descripcion AS ciudad')
                    ->get()[0];

        return $registro;
    }

    public function cuenta_anticipos()
    {
        return $this->belongsTo('App\Contabilidad\ContabCuenta','contab_anticipo_cta_id');
    }

    public function cuenta_cartera()
    {
        return $this->belongsTo('App\Contabilidad\ContabCuenta','contab_cartera_cta_id');
    }

    public function cuenta_cxp()
    {
        return $this->belongsTo('App\Contabilidad\ContabCuenta','contab_cxp_cta_id');
    }

    public static function crear_nuevo_tercero($modelo_controller, $request)
    {
        
        // OJO!!!!! Datos manuales
        $codigo_ciudad = '16920001'; // Valledupar
        $tipo = 'Persona natural';

        $tercero = Tercero::create( array_merge($request->all(),
                                    ['codigo_ciudad' => $codigo_ciudad, 
                                        'core_empresa_id' => Auth::user()->empresa_id, 
                                        'descripcion' => $request->nombre1." ".$request->otros_nombres." ".$request->apellido1." ".$request->apellido2, 
                                        'tipo' => $tipo] ) );

        // Si se envía archivos tipo file (imagenes, adjuntos)
        $modelo_tercero = Modelo::where('modelo','terceros')->first();

        // Esto vas a cambiar!!!!! Se va a llamar a ImagenController
        $modelo_controller->almacenar_imagenes( $request, $modelo_tercero, $tercero );

        return $tercero;
    }

    public static function opciones_campo_select()
    {
        $opciones = Tercero::where('core_terceros.core_empresa_id',Auth::user()->empresa_id)
                    ->select('core_terceros.id','core_terceros.descripcion','core_terceros.numero_identificacion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->numero_identificacion.' '.$opcion->descripcion;
        }

        return $vec;
    }

    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"compras_compradores",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Compradores de compras."
                                },
                            "1":{
                                    "tabla":"compras_doc_encabezados",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Documento de compras."
                                },
                            "2":{
                                    "tabla":"compras_movimientos",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Movimiento de compras."
                                },
                            "3":{
                                    "tabla":"compras_proveedores",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en tabla de Proveedores."
                                },
                            "4":{
                                    "tabla":"contab_doc_encabezados",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Documentos de Contabilidad."
                                },
                            "5":{
                                    "tabla":"contab_doc_registros",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Registros de documentos de Contabilidad."
                                },
                            "6":{
                                    "tabla":"contab_movimientos",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Movimiento de Contabilidad."
                                },
                            "7":{
                                    "tabla":"core_firmas_autorizadas",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Firmas autorizadas."
                                },
                            "8":{
                                    "tabla":"cxc_abonos",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Abonos de CxC."
                                },
                            "9":{
                                    "tabla":"cxc_doc_encabezados",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Documentos de CxC."
                                },
                            "10":{
                                    "tabla":"cxc_intereses_mora",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Intereses de mora de CxC."
                                },
                            "11":{
                                    "tabla":"cxc_movimientos",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Movimientos de CxC."
                                },
                            "12":{
                                    "tabla":"cxp_abonos",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Abonos de CxP."
                                },
                            "13":{
                                    "tabla":"cxp_doc_encabezados",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Documentos de CxP."
                                },
                            "14":{
                                    "tabla":"cxp_movimientos",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Movimientos de CxP."
                                },
                            "15":{
                                    "tabla":"inv_doc_encabezados",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Documentos de Inventarios."
                                },
                            "16":{
                                    "tabla":"inv_doc_registros",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Registros de Documentos de Inventarios."
                                },
                            "17":{
                                    "tabla":"inv_movimientos",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Movimientos de Inventarios."
                                },
                            "18":{
                                    "tabla":"nom_contratos",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Contratos de Nómina."
                                },
                            "19":{
                                    "tabla":"nom_cuotas",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Movimientos de Cuotas de Nómina."
                                },
                            "20":{
                                    "tabla":"nom_doc_registros",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Registros de Documentos de Liquidación de Nómina."
                                },
                            "21":{
                                    "tabla":"nom_entidades",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Entidades de Nómina."
                                },
                            "22":{
                                    "tabla":"nom_equivalencias_contables",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Equivalencias Contables de Nómina."
                                },
                            "23":{
                                    "tabla":"nom_movimientos",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Movimientos de Nómina."
                                },
                            "24":{
                                    "tabla":"nom_prestamos",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Movimientos de Préstamos de Nómina."
                                },
                            "25":{
                                    "tabla":"ph_propiedades",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Inmuebles de Propiedad Horizontal."
                                },
                            "26":{
                                    "tabla":"salud_pacientes",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en tablas de Pacientes."
                                },
                            "27":{
                                    "tabla":"salud_profesionales",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en tabla de Profesionales de la Salus."
                                },
                            "28":{
                                    "tabla":"sga_estudiantes",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en tabla de Estudiantes."
                                },
                            "29":{
                                    "tabla":"sga_inscripciones",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en tabla de Inscripciones de Estudiantes."
                                },
                            "30":{
                                    "tabla":"teso_doc_encabezados",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Documentos de Tesorería."
                                },
                            "31":{
                                    "tabla":"teso_doc_registros",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Registros de Documentos de Tesorería."
                                },
                            "32":{
                                    "tabla":"teso_movimientos",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Movimientos de Tesorería."
                                },
                            "33":{
                                    "tabla":"vtas_clientes",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en tabla de Clientes."
                                },
                            "34":{
                                    "tabla":"vtas_doc_encabezados",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Documentos de Ventas."
                                },
                            "35":{
                                    "tabla":"vtas_movimientos",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en Movimientos de Ventas."
                                },
                            "36":{
                                    "tabla":"vtas_vendedores",
                                    "llave_foranea":"core_tercero_id",
                                    "mensaje":"Está relacionado en tabla de Vendedores."
                                }
                        }';
        $tablas = json_decode( $tablas_relacionadas );
        foreach($tablas AS $una_tabla)
        { 
            $registro = DB::table( $una_tabla->tabla )->where( $una_tabla->llave_foranea, $id )->get();

            if ( !empty($registro) )
            {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }
}
