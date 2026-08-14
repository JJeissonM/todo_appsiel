<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPayrollBenefitsFilterToAccountsPayableReport extends Migration
{
    protected $fieldName = 'incluir_prestaciones_nomina';

    public function up()
    {
        if (!Schema::hasTable('sys_reportes') ||
            !Schema::hasTable('sys_campos') ||
            !Schema::hasTable('sys_reporte_tiene_campos')) {
            return;
        }

        $reportIds = DB::table('sys_reportes')
            ->where('url_form_action', 'compras_ctas_por_pagar')
            ->pluck('id');

        if (count($reportIds) === 0) {
            return;
        }

        $fieldId = (int)DB::table('sys_campos')
            ->where('name', $this->fieldName)
            ->value('id');

        if ($fieldId === 0) {
            $now = date('Y-m-d H:i:s');
            $fieldId = (int)DB::table('sys_campos')->insertGetId(array(
                'descripcion' => 'Incluir prestaciones de nómina',
                'tipo' => 'select',
                'name' => $this->fieldName,
                'opciones' => '{"1":"Sí","0":"No"}',
                'value' => '1',
                'atributos' => '',
                'definicion' => '',
                'requerido' => 0,
                'editable' => 1,
                'unico' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ));
        }

        foreach ($reportIds as $reportId) {
            $exists = DB::table('sys_reporte_tiene_campos')
                ->where('core_reporte_id', $reportId)
                ->where('core_campo_id', $fieldId)
                ->exists();

            if (!$exists) {
                DB::table('sys_reporte_tiene_campos')->insert(array(
                    'orden' => 8,
                    'core_reporte_id' => $reportId,
                    'core_campo_id' => $fieldId,
                ));
            }
        }
    }

    public function down()
    {
        if (!Schema::hasTable('sys_campos')) {
            return;
        }

        $fieldId = (int)DB::table('sys_campos')
            ->where('name', $this->fieldName)
            ->value('id');

        if ($fieldId === 0) {
            return;
        }

        if (Schema::hasTable('sys_reporte_tiene_campos')) {
            DB::table('sys_reporte_tiene_campos')
                ->where('core_campo_id', $fieldId)
                ->delete();
        }

        DB::table('sys_campos')->where('id', $fieldId)->delete();
    }
}
