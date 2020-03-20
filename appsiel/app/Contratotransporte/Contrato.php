<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    protected $table = 'cte_contratos';
    protected $fillable = ['id', 'codigo', 'version', 'fecha', 'numero_contrato', 'objeto', 'origen', 'destino', 'fecha_inicio', 'fecha_fin', 'valor_contrato', 'valor_empresa', 'valor_propietario', 'direccion_notificacion', 'telefono_notificacion', 'dia_contrato', 'mes_contrato', 'pie_uno', 'pie_dos', 'pie_tres', 'pie_cuatro', 'contratante_id', 'vehiculo_id', 'created_at', 'updated_at'];
}
