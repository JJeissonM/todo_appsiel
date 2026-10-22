<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddWarehouseGroupingToPosSalesReport extends Migration
{
    public function up()
    {
        $this->updateOptions(true);
    }

    public function down()
    {
        $this->updateOptions(false);
    }

    protected function updateOptions($add)
    {
        $fields = DB::table('sys_campos')
            ->join('sys_reporte_tiene_campos', 'sys_reporte_tiene_campos.core_campo_id', '=', 'sys_campos.id')
            ->join('sys_reportes', 'sys_reportes.id', '=', 'sys_reporte_tiene_campos.core_reporte_id')
            ->where('sys_reportes.url_form_action', 'pos_movimientos_ventas')
            ->where('sys_campos.name', 'agrupar_por')
            ->select('sys_campos.id', 'sys_campos.opciones')->get();

        foreach ($fields as $field) {
            $options = json_decode($field->opciones, true);
            if (!is_array($options)) {
                throw new \RuntimeException('Opciones inválidas en Agrupar por del resumen de ventas POS.');
            }
            if ($add) {
                $options['inv_bodega_id'] = 'Bodega';
            } else {
                unset($options['inv_bodega_id']);
            }
            DB::table('sys_campos')->where('id', $field->id)->update([
                'opciones' => json_encode($options, JSON_UNESCAPED_UNICODE)
            ]);
        }
    }
}
