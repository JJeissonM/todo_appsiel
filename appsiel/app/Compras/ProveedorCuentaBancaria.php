<?php

namespace App\Compras;

use Illuminate\Database\Eloquent\Model;

use App\Core\Ciudad;
use App\Core\Tercero;
use App\Tesoreria\TesoEntidadFinanciera;

class ProveedorCuentaBancaria extends Model
{
    protected $table = 'compras_proveedores_cuentas_bancarias';

    protected $fillable = [
        'tercero_id',
        'entidad_financiera_id',
        'tipo_cuenta',
        'numero_cuenta',
        'codigo_ciudad',
        'estado'
    ];

    public function tercero()
    {
        return $this->belongsTo(Tercero::class, 'tercero_id');
    }

    public function entidad_financiera()
    {
        return $this->belongsTo(TesoEntidadFinanciera::class, 'entidad_financiera_id');
    }

    public function ciudad()
    {
        return $this->belongsTo(Ciudad::class, 'codigo_ciudad');
    }
}

