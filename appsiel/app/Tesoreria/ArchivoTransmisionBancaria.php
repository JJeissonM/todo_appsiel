<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

class ArchivoTransmisionBancaria extends Model
{
    protected $table = 'teso_archivos_transmision_bancaria';

    protected $fillable = [
        'core_empresa_id', 'teso_doc_encabezado_id', 'formato', 'nombre_archivo',
        'hash_sha256', 'cantidad_registros', 'cantidad_omitidos', 'valor_total',
        'generado_por'
    ];
}
