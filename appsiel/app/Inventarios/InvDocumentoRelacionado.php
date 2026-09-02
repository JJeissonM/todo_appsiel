<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

class InvDocumentoRelacionado extends Model
{
    protected $table = 'inv_documentos_relacionados';

    const TIPO_IF_AJUSTE = 'inventario_fisico_ajuste';
    const TIPO_TRANSACCION_INVENTARIO_FISICO = 27;

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

    /**
     * Obtiene únicamente una relación cuyo origen todavía existe y es un
     * Inventario Físico. Esto evita que relaciones huérfanas antiguas coincidan
     * accidentalmente con IDs reutilizados de documentos nuevos.
     */
    public static function ajusteValidoParaDocumento($documentoRelacionadoId, $empresaId = null)
    {
        $query = self::join(
                'inv_doc_encabezados AS inventario_fisico_origen_valido',
                'inventario_fisico_origen_valido.id',
                '=',
                'inv_documentos_relacionados.inv_doc_encabezado_origen_id'
            )
            ->where('inv_documentos_relacionados.inv_doc_encabezado_relacionado_id', (int)$documentoRelacionadoId)
            ->where('inv_documentos_relacionados.tipo_relacion', self::TIPO_IF_AJUSTE)
            ->where(
                'inventario_fisico_origen_valido.core_tipo_transaccion_id',
                self::TIPO_TRANSACCION_INVENTARIO_FISICO
            );

        if ((int)$empresaId > 0) {
            $query->where('inventario_fisico_origen_valido.core_empresa_id', (int)$empresaId);
        }

        return $query
            ->select('inv_documentos_relacionados.*')
            ->with('documento_origen.tipo_documento_app')
            ->orderBy('inv_documentos_relacionados.id', 'DESC')
            ->first();
    }
}
