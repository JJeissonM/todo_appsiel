<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FuecAdicional extends Model
{
    protected $table = 'cte_fuec_adicionales';

    protected $fillable = [
        'id',
        'contrato_id',
        'vehiculo_id',
        'conductor1_id',
        'conductor2_id',
        'conductor3_id',
        'estado',
        'codigo',
        'version',
        'fecha',
        'numero_fuec',
        'origen',
        'destino',
        'fecha_inicio',
        'fecha_fin',
        'valor_fuec',
        'valor_empresa',
        'valor_propietario',
        'direccion_notificacion',
        'telefono_notificacion',
        'dia_fuec',
        'mes_fuec',
        'anio_fuec',
        'tipo_servicio',
        'nro_personas',
        'disponibilidad',
        'pie_uno',
        'pie_dos',
        'pie_tres',
        'pie_cuatro',
        'created_at',
        'updated_at',
        'descripcion_recorrido',
        'anulacion_motivo',
        'anulado_por',
        'anulado_el',
    ];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Nro.', 'Contrato padre', 'Objeto', 'Fecha Celebrado', 'Origen - Destino', 'Vigencia', 'Contratante', 'Vehículo', 'Propietario', 'Estado'];

    public $vistas = '{"index":"layouts.index3"}';

    public $urls_acciones = '{"create":"no",
        "edit":"no",
        "show":"no",
        "eliminar":"no",
        "imprimir":"cte_fuec_adicionales/id_fila/imprimir"}';

    public static function opciones_campo_select()
    {
        $opciones = FuecAdicional::leftJoin('cte_contratantes', 'cte_contratantes.id', '=', 'cte_fuec_adicionales.contratante_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_contratantes.tercero_id')
            ->select('cte_fuec_adicionales.id', 'cte_fuec_adicionales.codigo AS contrato_codigo', 'core_terceros.descripcion AS tercero_descripcion', 'core_terceros.numero_identificacion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->contrato_codigo . ' > ' . $opcion->tercero_descripcion;
        }

        return $vec;
    }

    public static function consultar_registros2($nro_registros, $search)
    {
        //t1 es contratante, t2 es propietario
        $collection =  FuecAdicional::leftJoin('cte_contratos', 'cte_contratos.id', '=', 'cte_fuec_adicionales.contrato_id')
            ->leftJoin('cte_contratantes', 'cte_contratantes.id', '=', 'cte_contratos.contratante_id')
            ->leftJoin('core_terceros as t1', 't1.id', '=', 'cte_contratantes.tercero_id')
            ->leftJoin('cte_vehiculos', 'cte_vehiculos.id', '=', 'cte_fuec_adicionales.vehiculo_id')
            ->leftJoin('cte_propietarios', 'cte_propietarios.id', '=', 'cte_vehiculos.propietario_id')
            ->leftJoin('core_terceros as t2', 't2.id', '=', 'cte_propietarios.tercero_id')
            ->select(
                'cte_fuec_adicionales.numero_fuec AS campo1',
                'cte_contratos.numero_contrato AS campo2',
                'cte_contratos.objeto AS campo3',
                DB::raw('CONCAT("DÍA: ",cte_fuec_adicionales.dia_fuec," - MES: ",cte_fuec_adicionales.mes_fuec) AS campo4'),
                DB::raw('CONCAT(cte_fuec_adicionales.origen," - ",cte_fuec_adicionales.destino) AS campo5'),
                DB::raw('CONCAT("INICIO: ",cte_fuec_adicionales.fecha_inicio," - FINAL VIGENCIA: ",cte_fuec_adicionales.fecha_fin) AS campo6'),
                DB::raw('COALESCE(t1.descripcion, cte_contratos.contratanteText) AS campo7'),
                DB::raw('CONCAT("INTERNO: ",cte_vehiculos.int," - PLACA: ",cte_vehiculos.placa," - MODELO: ",cte_vehiculos.modelo," - MARCA: ",cte_vehiculos.marca," - CLASE: ",cte_vehiculos.clase) AS campo8'),
                't2.descripcion AS campo9',
                'cte_fuec_adicionales.estado AS campo10',
                'cte_fuec_adicionales.id AS campo11'
            )->where("cte_fuec_adicionales.numero_fuec", "LIKE", "%$search%")
            ->orWhere("cte_contratos.numero_contrato", "LIKE", "%$search%")
            ->orWhere("cte_contratos.objeto", "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT('DÍA: ',cte_fuec_adicionales.dia_fuec,' - MES: ',cte_fuec_adicionales.mes_fuec)"), "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT(cte_fuec_adicionales.origen,' - ',cte_fuec_adicionales.destino)"), "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT('INICIO: ',cte_fuec_adicionales.fecha_inicio,' - FINAL VIGENCIA: ',cte_fuec_adicionales.fecha_fin)"), "LIKE", "%$search%")
            ->orWhere('t1.descripcion', "LIKE", "%$search%")
            ->orWhere('cte_contratos.contratanteText', "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT('INTERNO: ',cte_vehiculos.int,' - PLACA: ',cte_vehiculos.placa,' - MODELO: ',cte_vehiculos.modelo,' - MARCA: ',cte_vehiculos.marca,' - CLASE: ',cte_vehiculos.clase)"), "LIKE", "%$search%")
            ->orWhere('t2.descripcion', "LIKE", "%$search%")
            ->orWhere("cte_fuec_adicionales.estado", "LIKE", "%$search%")
            ->orderBy('cte_fuec_adicionales.created_at', 'DESC')
            ->paginate($nro_registros);
        
        return $collection;
    }

    public static function sqlString($search)
    {
        $string = FuecAdicional::leftJoin('cte_contratos', 'cte_contratos.id', '=', 'cte_fuec_adicionales.contrato_id')
            ->leftJoin('cte_contratantes', 'cte_contratantes.id', '=', 'cte_contratos.contratante_id')
            ->leftJoin('core_terceros as t1', 't1.id', '=', 'cte_contratantes.tercero_id')
            ->leftJoin('cte_vehiculos', 'cte_vehiculos.id', '=', 'cte_fuec_adicionales.vehiculo_id')
            ->leftJoin('cte_propietarios', 'cte_propietarios.id', '=', 'cte_vehiculos.propietario_id')
            ->leftJoin('core_terceros as t2', 't2.id', '=', 'cte_propietarios.tercero_id')
            ->select(
                'cte_fuec_adicionales.numero_fuec AS NÚMERO FUEC',
                'cte_contratos.numero_contrato AS CONTRATO PADRE',
                'cte_contratos.objeto AS OBJETO',
                DB::raw('CONCAT("DÍA: ",cte_fuec_adicionales.dia_fuec," - MES: ",cte_fuec_adicionales.mes_fuec) AS FECHA'),
                DB::raw('CONCAT(cte_fuec_adicionales.origen," - ",cte_fuec_adicionales.destino) AS ORIGEN_DESTINO'),
                DB::raw('CONCAT("INICIO: ",cte_fuec_adicionales.fecha_inicio," - FINAL VIGENCIA: ",cte_fuec_adicionales.fecha_fin) AS VIGENCIA'),
                DB::raw('COALESCE(t1.descripcion, cte_contratos.contratanteText) AS CONTRATANTE'),
                DB::raw('CONCAT("INTERNO: ",cte_vehiculos.int," - PLACA: ",cte_vehiculos.placa," - MODELO: ",cte_vehiculos.modelo," - MARCA: ",cte_vehiculos.marca," - CLASE: ",cte_vehiculos.clase) AS VEHÍCULO'),
                't2.descripcion AS PROPIETARIO',
                'cte_fuec_adicionales.estado AS ESTADO'
            )->where("cte_fuec_adicionales.numero_fuec", "LIKE", "%$search%")
            ->orWhere("cte_contratos.numero_contrato", "LIKE", "%$search%")
            ->orWhere("cte_contratos.objeto", "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT('DÍA: ',cte_fuec_adicionales.dia_fuec,' - MES: ',cte_fuec_adicionales.mes_fuec)"), "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT(cte_fuec_adicionales.origen,' - ',cte_fuec_adicionales.destino)"), "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT('INICIO: ',cte_fuec_adicionales.fecha_inicio,' - FINAL VIGENCIA: ',cte_fuec_adicionales.fecha_fin)"), "LIKE", "%$search%")
            ->orWhere('t1.descripcion', "LIKE", "%$search%")
            ->orWhere('cte_contratos.contratanteText', "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT('INTERNO: ',cte_vehiculos.int,' - PLACA: ',cte_vehiculos.placa,' - MODELO: ',cte_vehiculos.modelo,' - MARCA: ',cte_vehiculos.marca,' - CLASE: ',cte_vehiculos.clase)"), "LIKE", "%$search%")
            ->orWhere('t2.descripcion', "LIKE", "%$search%")
            ->orWhere("cte_fuec_adicionales.estado", "LIKE", "%$search%")
            ->orderBy('cte_fuec_adicionales.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CONTRATOS DE TRANSPORTE";
    }


    public function contrato()
    {
        return $this->belongsTo(Contrato::class);
    }

    public function contratante()
    {
        return $this->belongsTo(Contratante::class);
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function conductor1()
    {
        return $this->belongsTo(Conductor::class,'conductor1_id');
    }

    public function conductor2()
    {
        return $this->belongsTo(Conductor::class,'conductor2_id');
    }

    public function conductor3()
    {
        return $this->belongsTo(Conductor::class,'conductor3_id');
    }

    public function contratogrupous()
    {
        return $this->hasMany(Contratogrupou::class);
    }

    public function planillacs()
    {
        return $this->hasMany(Planillac::class);
    }
}
