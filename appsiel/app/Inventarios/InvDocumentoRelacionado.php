<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

class InvDocumentoRelacionado extends Model
{
    protected $table = 'inv_documentos_relacionados';

    const TIPO_IF_AJUSTE = 'inventario_fisico_ajuste';

    protected $fillable = [
        'inv_doc_encabezado_origen_id',
        'inv_doc_encabezado_relacionado_id',
        'tipo_relacion',
        'creado_por',
        'modificado_por'
    ];

    public function documento_origen()
    {
        return $this->belongsTo(InvDocEncabezado::class, 'inv_doc_encabezado_origen_id');
    }

    public function documento_relacionado()
    {
        return $this->belongsTo(InvDocEncabezado::class, 'inv_doc_encabezado_relacionado_id');
    }

    public static function existe_ajuste_para_inventario_fisico($inv_fisico_id)
    {
        return self::where('inv_doc_encabezado_origen_id', (int)$inv_fisico_id)
            ->where('tipo_relacion', self::TIPO_IF_AJUSTE)
            ->exists();
    }
}
