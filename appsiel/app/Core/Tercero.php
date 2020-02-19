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
}
