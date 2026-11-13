<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddSalesReportFilters extends Migration
{
    private $marker = 'vtas_ventas_filtros_20261113';

    public function up()
    {
        foreach (DB::table('sys_reportes')->where('url_form_action', 'vtas_reporte_ventas')->get() as $report) {
            $fields = [
                [
                    'name' => 'ordenar_por', 'descripcion' => 'Ordenar por',
                    'opciones' => json_encode([
                        'mayor_vendido' => 'Mayor vendido', 'id' => 'ID', 'alfabetico' => 'Alfabético'
                    ], JSON_UNESCAPED_UNICODE),
                    'value' => 'mayor_vendido'
                ],
                [
                    'name' => 'pdv_id', 'descripcion' => 'Punto de Ventas',
                    'opciones' => 'model_App\Ventas\Services\ProfitabilityReportService',
                    'value' => ''
                ]
            ];
            $order = (int)DB::table('sys_reporte_tiene_campos')
                ->where('core_reporte_id', $report->id)->max('orden');
            foreach ($fields as $field) {
                $existing = DB::table('sys_reporte_tiene_campos')
                    ->join('sys_campos', 'sys_campos.id', '=', 'sys_reporte_tiene_campos.core_campo_id')
                    ->where('core_reporte_id', $report->id)->where('sys_campos.name', $field['name'])->exists();
                if ($existing) {
                    continue;
                }
                $fieldId = DB::table('sys_campos')->insertGetId(array_merge($field, [
                    'tipo' => 'select', 'atributos' => '{"class":"form-control"}',
                    'definicion' => $this->marker, 'requerido' => 0, 'editable' => 1, 'unico' => 0,
                    'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
                ]));
                DB::table('sys_reporte_tiene_campos')->insert([
                    'core_reporte_id' => $report->id, 'core_campo_id' => $fieldId, 'orden' => ++$order
                ]);
            }
        }
    }

    public function down()
    {
        foreach (DB::table('sys_campos')->where('definicion', $this->marker)->get() as $field) {
            $reportIds = DB::table('sys_reportes')->where('url_form_action', 'vtas_reporte_ventas')->lists('id');
            DB::table('sys_reporte_tiene_campos')->whereIn('core_reporte_id', $reportIds)
                ->where('core_campo_id', $field->id)->delete();
            if (!DB::table('sys_reporte_tiene_campos')->where('core_campo_id', $field->id)->exists()
                && !DB::table('sys_modelo_tiene_campos')->where('core_campo_id', $field->id)->exists()) {
                DB::table('sys_campos')->where('id', $field->id)->delete();
            }
        }
    }
}
