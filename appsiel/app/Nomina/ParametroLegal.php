<?php

namespace App\Nomina;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ParametroLegal extends Model
{
    protected $table = 'nom_parametros_legales';

    protected $fillable = [
        'fecha_inicio',
        'fecha_fin',
        'smmlv',
        'auxilio_transporte',
        'uvt',
        'horas_laborales',
        'horas_dia_laboral',
        'normatividad',
        'estado',
    ];

    public $encabezado_tabla = [
        '<i style="font-size: 20px;" class="fa fa-check-square-o"></i>',
        'Fecha inicial',
        'Fecha final',
        'SMMLV',
        'Auxilio transporte',
        'UVT',
        'Horas laborales',
        'Horas día laboral',
        'Normatividad',
        'Estado',
    ];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';

    public static function consultar_registros($nro_registros, $search)
    {
        return ParametroLegal::select(
            'nom_parametros_legales.fecha_inicio AS campo1',
            'nom_parametros_legales.fecha_fin AS campo2',
            'nom_parametros_legales.smmlv AS campo3',
            'nom_parametros_legales.auxilio_transporte AS campo4',
            'nom_parametros_legales.uvt AS campo5',
            'nom_parametros_legales.horas_laborales AS campo6',
            'nom_parametros_legales.horas_dia_laboral AS campo7',
            'nom_parametros_legales.normatividad AS campo8',
            'nom_parametros_legales.estado AS campo9',
            'nom_parametros_legales.id AS campo10'
        )
            ->where("nom_parametros_legales.fecha_inicio", "LIKE", "%$search%")
            ->orWhere("nom_parametros_legales.fecha_fin", "LIKE", "%$search%")
            ->orWhere("nom_parametros_legales.smmlv", "LIKE", "%$search%")
            ->orWhere("nom_parametros_legales.normatividad", "LIKE", "%$search%")
            ->orWhere("nom_parametros_legales.estado", "LIKE", "%$search%")
            ->orderBy('nom_parametros_legales.fecha_inicio', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = ParametroLegal::select(
            'nom_parametros_legales.fecha_inicio AS FECHA_INICIAL',
            'nom_parametros_legales.fecha_fin AS FECHA_FINAL',
            'nom_parametros_legales.smmlv AS SMMLV',
            'nom_parametros_legales.auxilio_transporte AS AUXILIO_TRANSPORTE',
            'nom_parametros_legales.uvt AS UVT',
            'nom_parametros_legales.horas_laborales AS HORAS_LABORALES',
            'nom_parametros_legales.horas_dia_laboral AS HORAS_DIA_LABORAL',
            'nom_parametros_legales.normatividad AS NORMATIVIDAD',
            'nom_parametros_legales.estado AS ESTADO'
        )
            ->where("nom_parametros_legales.fecha_inicio", "LIKE", "%$search%")
            ->orWhere("nom_parametros_legales.fecha_fin", "LIKE", "%$search%")
            ->orWhere("nom_parametros_legales.smmlv", "LIKE", "%$search%")
            ->orWhere("nom_parametros_legales.normatividad", "LIKE", "%$search%")
            ->orWhere("nom_parametros_legales.estado", "LIKE", "%$search%")
            ->orderBy('nom_parametros_legales.fecha_inicio', 'DESC')
            ->toSql();

        return str_replace('?', '"%' . $search . '%"', $string);
    }

    public static function tituloExport()
    {
        return "LISTADO DE PARAMETROS LEGALES DE NOMINA";
    }

    public static function opciones_campo_select()
    {
        $opciones = ParametroLegal::where('estado', 'Activo')
            ->orderBy('fecha_inicio', 'DESC')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->fecha_inicio . ' - ' . number_format($opcion->smmlv, 0, ',', '.');
        }

        return $vec;
    }

    public static function vigente_para_fecha($fecha)
    {
        $fecha_periodo = Carbon::parse($fecha)->format('Y-m-d');

        return ParametroLegal::where('estado', 'Activo')
            ->where('fecha_inicio', '<=', $fecha_periodo)
            ->where(function ($query) use ($fecha_periodo) {
                $query->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>=', $fecha_periodo);
            })
            ->orderBy('fecha_inicio', 'DESC')
            ->first();
    }

    public static function horas_laborales_para_fecha($fecha = null, $valor_defecto = 240)
    {
        $parametro = self::vigente_para_fecha(self::normalizar_fecha_parametro($fecha));

        if (!is_null($parametro) && (float)$parametro->horas_laborales > 0) {
            return (float)$parametro->horas_laborales;
        }

        return (float)$valor_defecto;
    }

    public static function smmlv_para_fecha($fecha = null, $valor_defecto = 0)
    {
        $parametro = self::vigente_para_fecha(self::normalizar_fecha_parametro($fecha));

        if (!is_null($parametro) && (float)$parametro->smmlv > 0) {
            return (float)$parametro->smmlv;
        }

        return (float)$valor_defecto;
    }

    public static function auxilio_transporte_para_fecha($fecha = null, $valor_defecto = 0)
    {
        $parametro = self::vigente_para_fecha(self::normalizar_fecha_parametro($fecha));

        if (!is_null($parametro) && (float)$parametro->auxilio_transporte > 0) {
            return (float)$parametro->auxilio_transporte;
        }

        return (float)$valor_defecto;
    }

    public static function uvt_para_fecha($fecha = null, $valor_defecto = 0)
    {
        $parametro = self::vigente_para_fecha(self::normalizar_fecha_parametro($fecha));

        if (!is_null($parametro) && (float)$parametro->uvt > 0) {
            return (float)$parametro->uvt;
        }

        return (float)$valor_defecto;
    }

    public static function horas_dia_laboral_para_fecha($fecha = null, $valor_defecto = 8)
    {
        $parametro = self::vigente_para_fecha(self::normalizar_fecha_parametro($fecha));

        if (!is_null($parametro) && (float)$parametro->horas_dia_laboral > 0) {
            return (float)$parametro->horas_dia_laboral;
        }

        return (float)$valor_defecto;
    }

    protected static function normalizar_fecha_parametro($fecha)
    {
        if (is_null($fecha) || $fecha == '') {
            return date('Y-m-d');
        }

        return $fecha;
    }
}
