<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    protected $table = 'cte_contratos';
    protected $fillable = ['id', 'codigo', 'version', 'fecha', 'numero_contrato', 'objeto', 'origen', 'destino', 'fecha_inicio', 'fecha_fin', 'valor_contrato', 'valor_empresa', 'valor_propietario', 'direccion_notificacion', 'telefono_notificacion', 'dia_contrato', 'mes_contrato', 'pie_uno', 'pie_dos', 'pie_tres', 'pie_cuatro', 'contratante_id', 'vehiculo_id', 'created_at', 'updated_at'];

    public static function opciones_campo_select()
    {
        $opciones = Contrato::leftJoin('cte_contratantes', 'cte_contratantes.id', '=', 'cte_contratos.contratante_id')
                            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_contratantes.tercero_id')
                            ->select('cte_contratos.id','cte_contratos.codigo AS contrato_codigo','core_terceros.descripcion AS tercero_descripcion','core_terceros.numero_identificacion')
                            ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->contrato_codigo.' > '.$opcion->tercero_descripcion;
        }

        return $vec;
    }
}
