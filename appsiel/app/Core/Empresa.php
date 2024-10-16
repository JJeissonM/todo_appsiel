<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

use App\Core\Tercero;
use Illuminate\Support\Facades\DB;

class Empresa extends Model
{
    protected $table = 'core_empresas';

    protected $fillable = [ 'tipo', 'razon_social', 'nombre1', 'otros_nombres', 'apellido1', 'apellido2', 'descripcion', 'id_tipo_documento_id', 'numero_identificacion', 'digito_verificacion', 'ciudad_expedicion', 'direccion1', 'direccion2', 'barrio', 'codigo_ciudad', 'codigo_postal', 'telefono1', 'telefono2', 'email', 'pagina_web', 'estado' ];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Nombre/Razón Social', 'Establecimiento', 'Identificación', 'Dirección', 'Teléfono', 'Estado'];


    public function tipo_doc_identidad()
    {
        return $this->belongsTo('App\Core\TipoDocumentoId', 'id_tipo_documento_id');
    }
    
    public function colegio()
    {
        return $this->hasOne('App\Core\Colegio');
    }

    public function ciudad()
    {
        return $this->belongsTo('App\Core\Ciudad', 'codigo_ciudad');
    }

    public function representante_legal()
    {
        $tercero_empresa = Tercero::where('numero_identificacion',$this->numero_identificacion)->get()->first();

        if ( !is_null($tercero_empresa) )
        {
            $tercero_representante_legal_empresa = $tercero_empresa->representante_legal();
            if ( !is_null($tercero_representante_legal_empresa) )
            {
                return $tercero_representante_legal_empresa->descripcion;
            }
        }
        return '';
    }

    public function tercero()
    {
        return Tercero::where('numero_identificacion',$this->numero_identificacion)->get()->first();
    }

    public function tercero_representante_legal()
    {
        $tercero_empresa = Tercero::where('numero_identificacion',$this->numero_identificacion)->get()->first();

        if ( $tercero_empresa != null )
        {
            return $tercero_empresa->representante_legal();
        }

        return null;
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = Empresa::select(
            DB::raw('CONCAT(core_empresas.nombre1," ",core_empresas.otros_nombres," ",core_empresas.apellido1," ",core_empresas.apellido2," ",core_empresas.razon_social) AS campo1'),
            'core_empresas.descripcion AS campo2',
            'core_empresas.numero_identificacion AS campo3',
            'core_empresas.direccion1 AS campo4',
            'core_empresas.telefono1 AS campo5',
            'core_empresas.estado AS campo6',
            'core_empresas.id AS campo7'
        )->where(DB::raw('CONCAT(core_empresas.nombre1," ",core_empresas.otros_nombres," ",core_empresas.apellido1," ",core_empresas.apellido2," ",core_empresas.razon_social)'), "LIKE", "%$search%")
            ->orWhere("core_empresas.descripcion", "LIKE", "%$search%")
            ->orWhere("core_empresas.numero_identificacion", "LIKE", "%$search%")
            ->orWhere("core_empresas.estado", "LIKE", "%$search%")
            ->orWhere("core_empresas.direccion1", "LIKE", "%$search%")
            ->orWhere("core_empresas.telefono1", "LIKE", "%$search%")
            ->orderBy('core_empresas.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = Empresa::select(
            DB::raw('CONCAT(core_empresas.nombre1," ",core_empresas.otros_nombres," ",core_empresas.apellido1," ",core_empresas.apellido2," ",core_empresas.razon_social) AS EMPRESA'),
            'core_empresas.descripcion AS DESCRIPCIÓN',
            'core_empresas.numero_identificacion AS IDENTIFICACIÓN',
            'core_empresas.direccion1 AS DIRECCIÓN',
            'core_empresas.telefono1 AS TELÉFONO',
            'core_empresas.estado AS ESTADO'
        )->where(DB::raw('CONCAT(core_empresas.nombre1," ",core_empresas.otros_nombres," ",core_empresas.apellido1," ",core_empresas.apellido2," ",core_empresas.razon_social)'), "LIKE", "%$search%")
            ->orWhere("core_empresas.descripcion", "LIKE", "%$search%")
            ->orWhere("core_empresas.numero_identificacion", "LIKE", "%$search%")
            ->orWhere("core_empresas.estado", "LIKE", "%$search%")
            ->orWhere("core_empresas.direccion1", "LIKE", "%$search%")
            ->orWhere("core_empresas.telefono1", "LIKE", "%$search%")
            ->orderBy('core_empresas.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE EMPRESAS";
    }
}
