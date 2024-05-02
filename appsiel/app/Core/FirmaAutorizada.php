<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FirmaAutorizada extends Model
{
    protected $table = 'core_firmas_autorizadas';

    protected $fillable = ['core_empresa_id', 'core_tercero_id', 'titulo_tercero', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Tercero', 'Título/Cargo', 'Estado'];

    public function tercero()
    {
        return $this->belongsTo(Tercero::class, 'core_tercero_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $select_raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.apellido1," ",core_terceros.apellido2) AS campo1';

        $registros = FirmaAutorizada::leftJoin('core_terceros', 'core_terceros.id', '=', 'core_firmas_autorizadas.core_tercero_id')
            ->where('core_firmas_autorizadas.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                DB::raw($select_raw),
                'core_firmas_autorizadas.titulo_tercero AS campo2',
                'core_firmas_autorizadas.estado AS campo3',
                'core_firmas_autorizadas.id AS campo4'
            )
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.apellido1," ",core_terceros.apellido2)'), "LIKE", "%$search%")
            ->orWhere("core_firmas_autorizadas.titulo_tercero", "LIKE", "%$search%")
            ->orWhere("core_firmas_autorizadas.estado", "LIKE", "%$search%")
            ->orderBy('core_firmas_autorizadas.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $select_raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.apellido1," ",core_terceros.apellido2) AS TERCERO';

        $string = FirmaAutorizada::leftJoin('core_terceros', 'core_terceros.id', '=', 'core_firmas_autorizadas.core_tercero_id')
            ->where('core_firmas_autorizadas.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                DB::raw($select_raw),
                'core_firmas_autorizadas.titulo_tercero AS TÍTULO_CARGO',
                'core_firmas_autorizadas.estado AS ESTADO'
            )
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.apellido1," ",core_terceros.apellido2)'), "LIKE", "%$search%")
            ->orWhere("core_firmas_autorizadas.titulo_tercero", "LIKE", "%$search%")
            ->orWhere("core_firmas_autorizadas.estado", "LIKE", "%$search%")
            ->orderBy('core_firmas_autorizadas.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE FIRMAS AUTORIZADAS";
    }

    public static function opciones_campo_select()
    {
        $opciones = FirmaAutorizada::leftJoin('core_terceros', 'core_terceros.id', '=', 'core_firmas_autorizadas.core_tercero_id')
            ->where('core_firmas_autorizadas.estado', '=', 'Activo')
            ->select(
                'core_terceros.descripcion AS tercero_nombre',
                'core_firmas_autorizadas.titulo_tercero',
                'core_firmas_autorizadas.id'
            )
            ->orderBy('descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->tercero_nombre . ' (' . $opcion->titulo_tercero . ')';
        }

        return $vec;
    }

    public static function get_datos($id)
    {
        return FirmaAutorizada::leftJoin('core_terceros', 'core_terceros.id', '=', 'core_firmas_autorizadas.core_tercero_id')
            ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
            ->where('core_firmas_autorizadas.id', $id)
            ->select(
                'core_terceros.descripcion AS tercero_nombre',
                'core_firmas_autorizadas.titulo_tercero AS tercero_titulo',
                'core_terceros.numero_identificacion AS tercero_numero_identificacion',
                'core_tipos_docs_id.abreviatura AS tercero_tipo_doc_identidad'
            )
            ->get()
            ->first();
    }

    public static function get_firma_tercero($core_tercero_id)
    {
        return FirmaAutorizada::where('core_tercero_id', $core_tercero_id)->get()->first();
    }
}
