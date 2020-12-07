<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Contrato extends Model
{
    protected $table = 'cte_contratos';
    protected $fillable = ['id', 'codigo', 'version', 'fecha', 'numero_contrato', 'rep_legal', 'representacion_de', 'objeto', 'origen', 'destino', 'fecha_inicio', 'fecha_fin', 'valor_contrato', 'valor_empresa', 'valor_propietario', 'direccion_notificacion', 'telefono_notificacion', 'dia_contrato', 'mes_contrato', 'pie_uno', 'pie_dos', 'pie_tres', 'pie_cuatro', 'contratanteText', 'contratante_id', 'vehiculo_id', 'conductor_id', 'created_at', 'updated_at'];

    public $encabezado_tabla = ['Nro.', 'Objeto', 'Fecha Celebrado', 'Origen - Destino', 'Vigencia', 'Contratante', 'Vehículo', 'Propietario', 'Estado', 'Acción'];

    public $vistas = '{"index":"layouts.index3"}';

    public $urls_acciones = '{"create":"cte_contratos/create","show":"cte_contratos/id_fila/show","imprimir":"cte_contratos/id_fila/imprimir","eliminar":"cte_contratos/id_fila/eliminar"}';

    public static function opciones_campo_select()
    {
        $opciones = Contrato::leftJoin('cte_contratantes', 'cte_contratantes.id', '=', 'cte_contratos.contratante_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_contratantes.tercero_id')
            ->select('cte_contratos.id', 'cte_contratos.codigo AS contrato_codigo', 'core_terceros.descripcion AS tercero_descripcion', 'core_terceros.numero_identificacion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->contrato_codigo . ' > ' . $opcion->tercero_descripcion;
        }

        return $vec;
    }

    public static function consultar_registros2()
    {
        //t1 es contratante, t2 es propietario
        $collection =  Contrato::leftJoin('cte_contratantes', 'cte_contratantes.id', '=', 'cte_contratos.contratante_id')
            ->leftJoin('core_terceros as t1', 't1.id', '=', 'cte_contratantes.tercero_id')
            ->leftJoin('cte_vehiculos', 'cte_vehiculos.id', '=', 'cte_contratos.vehiculo_id')
            ->leftJoin('cte_propietarios', 'cte_propietarios.id', '=', 'cte_vehiculos.propietario_id')
            ->leftJoin('core_terceros as t2', 't2.id', '=', 'cte_propietarios.tercero_id')
            ->select(
                'cte_contratos.numero_contrato AS campo1',
                'cte_contratos.objeto AS campo2',
                DB::raw('CONCAT("DÍA: ",cte_contratos.dia_contrato," - MES: ",cte_contratos.mes_contrato) AS campo3'),
                DB::raw('CONCAT(cte_contratos.origen," - ",cte_contratos.destino) AS campo4'),
                DB::raw('CONCAT("INICIO: ",cte_contratos.fecha_inicio," - FINAL VIGENCIA: ",cte_contratos.fecha_fin) AS campo5'),
                DB::raw('CONCAT(t1.nombre1," ",t1.otros_nombres," ",t1.apellido1," ",t1.apellido2," ",t1.razon_social) AS campo6'),
                DB::raw('CONCAT("INTERNO: ",cte_vehiculos.int," - PLACA: ",cte_vehiculos.placa," - MODELO: ",cte_vehiculos.modelo," - MARCA: ",cte_vehiculos.marca," - CLASE: ",cte_vehiculos.clase) AS campo7'),
                DB::raw('CONCAT(t2.numero_identificacion," - ",t2.nombre1," ",t2.otros_nombres," ",t2.apellido1," ",t2.apellido2," ",t2.razon_social) AS campo8'),
                'cte_contratos.estado AS campo9',
                'cte_contratos.id AS campo10'
            )
            ->orderBy('cte_contratos.created_at', 'DESC')
            ->paginate(100);
        if (count($collection) > 0) {
            foreach ($collection as $c) {
                if ($c->campo6 == null || $c->campo6 == 'null') {
                    $c->campo6 = Contrato::find($c->campo10)->contratanteText;
                }
            }
        }
        return $collection;
    }


    public function contratante()
    {
        return $this->belongsTo(Contratante::class);
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function conductor()
    {
        return $this->belongsTo(Conductor::class);
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
