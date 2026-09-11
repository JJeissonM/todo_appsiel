<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RepairChequeraPaymentDocumentReferences extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('teso_control_cheques') ||
            !Schema::hasTable('teso_doc_encabezados') ||
            !Schema::hasTable('teso_movimientos') ||
            !Schema::hasTable('contab_movimientos') ||
            !Schema::hasColumn('teso_control_cheques', 'teso_doc_encabezado_id')) {
            return;
        }

        $cheques = DB::table('teso_control_cheques AS cheque')
            ->join('teso_doc_encabezados AS documento', 'documento.id', '=', 'cheque.teso_doc_encabezado_id')
            ->where('cheque.modalidad', 'usar_chequera')
            ->whereNotNull('cheque.teso_doc_encabezado_id')
            ->select(
                'documento.core_tipo_transaccion_id',
                'documento.core_tipo_doc_app_id',
                'documento.consecutivo',
                'documento.core_empresa_id',
                'documento.core_tercero_id',
                'documento.fecha',
                'cheque.numero_cheque',
                'cheque.teso_cuenta_bancaria_id'
            )
            ->get();

        foreach ($cheques as $cheque) {
            $referencias = [
                'core_tipo_transaccion_id' => (int)$cheque->core_tipo_transaccion_id,
                'core_tipo_doc_app_id' => (int)$cheque->core_tipo_doc_app_id
            ];

            DB::table('teso_movimientos')
                ->where('core_tipo_transaccion_id', 0)
                ->where('core_tipo_doc_app_id', 0)
                ->where('consecutivo', $cheque->consecutivo)
                ->where('core_empresa_id', $cheque->core_empresa_id)
                ->where('core_tercero_id', $cheque->core_tercero_id)
                ->where('fecha', $cheque->fecha)
                ->where('teso_cuenta_bancaria_id', $cheque->teso_cuenta_bancaria_id)
                ->where('documento_soporte', 'Cheque número ' . $cheque->numero_cheque)
                ->update($referencias);

            DB::table('contab_movimientos')
                ->where('core_tipo_transaccion_id', 0)
                ->where('core_tipo_doc_app_id', 0)
                ->where('consecutivo', $cheque->consecutivo)
                ->where('core_empresa_id', $cheque->core_empresa_id)
                ->where('core_tercero_id', $cheque->core_tercero_id)
                ->where('fecha', $cheque->fecha)
                ->where('teso_cuenta_bancaria_id', $cheque->teso_cuenta_bancaria_id)
                ->where('documento_soporte', 'Cheque número ' . $cheque->numero_cheque)
                ->update($referencias);
        }
    }

    public function down()
    {
        // La reparación restablece referencias documentales; no debe revertirse.
    }
}
