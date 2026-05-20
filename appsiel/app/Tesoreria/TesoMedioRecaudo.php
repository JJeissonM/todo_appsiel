<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

class TesoMedioRecaudo extends Model
{
    protected $table = 'teso_medios_recaudo';
    const ESTADO_ACTIVO = 'Activo';

    public $vistas = '{"show":"tesoreria.medios_recaudo.show"}';

    /*
        comportamiento: { Efectivo | Tarjeta bancaria | Otro }
    */
    protected $fillable = ['descripcion','comportamiento','por_defecto','maneja_puntos','estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Comportamiento', 'Por defecto', 'Maneja puntos', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = TesoMedioRecaudo::select(
            'teso_medios_recaudo.descripcion AS campo1',
            'teso_medios_recaudo.comportamiento AS campo2',
            'teso_medios_recaudo.por_defecto AS campo3',
            'teso_medios_recaudo.maneja_puntos AS campo4',
            'teso_medios_recaudo.estado AS campo5',
            'teso_medios_recaudo.id AS campo6'
        )

            ->where("teso_medios_recaudo.descripcion", "LIKE", "%$search%")
            ->orWhere("teso_medios_recaudo.comportamiento", "LIKE", "%$search%")
            ->orWhere("teso_medios_recaudo.por_defecto", "LIKE", "%$search%")
            ->orWhere("teso_medios_recaudo.maneja_puntos", "LIKE", "%$search%")
            ->orWhere("teso_medios_recaudo.estado", "LIKE", "%$search%")
            ->orderBy('teso_medios_recaudo.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = TesoMedioRecaudo::select(
            'teso_medios_recaudo.descripcion AS DESCRIPCIÓN',
            'teso_medios_recaudo.comportamiento AS COMPORTAMIENTO',
            'teso_medios_recaudo.por_defecto AS POR_DEFECTO',
            'teso_medios_recaudo.maneja_puntos AS MANEJA_PUNTOS',
            'teso_medios_recaudo.estado AS ESTADO'
        )
            ->where("teso_medios_recaudo.descripcion", "LIKE", "%$search%")
            ->orWhere("teso_medios_recaudo.comportamiento", "LIKE", "%$search%")
            ->orWhere("teso_medios_recaudo.por_defecto", "LIKE", "%$search%")
            ->orWhere("teso_medios_recaudo.maneja_puntos", "LIKE", "%$search%")
            ->orWhere("teso_medios_recaudo.estado", "LIKE", "%$search%")
            ->orderBy('teso_medios_recaudo.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE MEDIOS DE RECAUDO";
    }

    public static function opciones_campo_select()
    {
        $opciones = TesoMedioRecaudo::where('estado', self::ESTADO_ACTIVO)->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id . '-' . $opcion->comportamiento] = $opcion->descripcion;
        }

        return $vec;
    }

    public static function get_id_por_tipo_registro($tipo_registro)
    {
        $descripciones = self::get_descripciones_por_tipo_registro($tipo_registro);

        if (!empty($descripciones)) {
            $medios_recaudo = TesoMedioRecaudo::where('estado', self::ESTADO_ACTIVO)
                ->whereIn('descripcion', $descripciones)
                ->get();

            foreach ($descripciones as $descripcion) {
                $medio_recaudo = $medios_recaudo->where('descripcion', $descripcion)->first();
                if (!is_null($medio_recaudo)) {
                    return (int)$medio_recaudo->id;
                }
            }
        }

        $medio_recaudo = self::buscar_por_palabras_clave($tipo_registro);
        if (!is_null($medio_recaudo)) {
            return (int)$medio_recaudo->id;
        }

        throw new \InvalidArgumentException('No existe un medio de recaudo activo para el tipo de registro "' . $tipo_registro . '". Revise config/tesoreria.php en medios_recaudo_por_tipo_registro o el catálogo de medios de recaudo.');
    }

    protected static function get_descripciones_por_tipo_registro($tipo_registro)
    {
        $descripciones_por_defecto = [
            'efectivo' => ['Efectivo'],
            'transferencia_consignacion' => ['QR/Transf', 'Banco (Consignación)', 'Transferencia', 'Consignación'],
            'tarjeta_debito' => ['Tarjeta débito', 'Tarj. Deb/Cre'],
            'tarjeta_credito' => ['Tarjeta crédito', 'Tarj. Deb/Cre'],
            'cheque_propio' => ['Cheque propio', 'Cheque'],
            'cheque_tercero' => ['Cheque de tercero', 'Cheque'],
        ];

        $descripciones_config = config('tesoreria.medios_recaudo_por_tipo_registro.' . $tipo_registro, []);
        if (!is_array($descripciones_config)) {
            $descripciones_config = [$descripciones_config];
        }

        $descripciones = array_merge($descripciones_config, isset($descripciones_por_defecto[$tipo_registro]) ? $descripciones_por_defecto[$tipo_registro] : []);

        return array_values(array_unique(array_filter($descripciones)));
    }

    protected static function buscar_por_palabras_clave($tipo_registro)
    {
        $medios_recaudo = TesoMedioRecaudo::where('estado', self::ESTADO_ACTIVO)->get();

        foreach ($medios_recaudo as $medio_recaudo) {
            $descripcion = strtolower($medio_recaudo->descripcion);
            $comportamiento = strtolower($medio_recaudo->comportamiento);

            if ($tipo_registro == 'efectivo' && $comportamiento == 'efectivo') {
                return $medio_recaudo;
            }

            if ($tipo_registro == 'transferencia_consignacion' && (stripos($descripcion, 'transf') !== false || stripos($descripcion, 'consign') !== false || stripos($descripcion, 'qr') !== false)) {
                return $medio_recaudo;
            }

            if ($tipo_registro == 'tarjeta_debito' && stripos($descripcion, 'deb') !== false) {
                return $medio_recaudo;
            }

            if ($tipo_registro == 'tarjeta_credito' && (stripos($descripcion, 'cred') !== false || stripos($descripcion, 'cré') !== false || stripos($descripcion, 'cre') !== false)) {
                return $medio_recaudo;
            }

            if (($tipo_registro == 'cheque_propio' || $tipo_registro == 'cheque_tercero') && ($comportamiento == 'cheque' || stripos($descripcion, 'cheque') !== false)) {
                return $medio_recaudo;
            }
        }

        return null;
    }

    public function destinos()
    {
        return $this->hasMany(TesoMedioRecaudoDestino::class, 'teso_medio_recaudo_id');
    }
}
