<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddRetentionReportThirdPartyDetailFilter extends Migration
{
    private $marker = 'contab_retenciones_detalla_tercero_20261113';

    public function up()
    {
        foreach (DB::table('sys_reportes')->where('url_form_action', 'contab_reporte_retenciones')->get() as $reporte) {
            $existe = DB::table('sys_reporte_tiene_campos')
                ->join('sys_campos', 'sys_campos.id', '=', 'sys_reporte_tiene_campos.core_campo_id')
                ->where('core_reporte_id', $reporte->id)->where('sys_campos.name', 'detalla_terceros')->exists();
            if ($existe) { continue; }
            $campoId = DB::table('sys_campos')->where('definicion', $this->marker)->value('id');
            if (!$campoId) {
                $campoId = DB::table('sys_campos')->insertGetId([
                    'descripcion' => 'Detalla tercero', 'tipo' => 'select', 'name' => 'detalla_terceros',
                    'opciones' => json_encode(['Si' => 'Sí', 'No' => 'No'], JSON_UNESCAPED_UNICODE),
                    'value' => 'Si', 'atributos' => '{"class":"form-control","id":"detalla_terceros"}',
                    'definicion' => $this->marker, 'requerido' => 1, 'editable' => 1, 'unico' => 0,
                    'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            $orden = (int)DB::table('sys_reporte_tiene_campos')->where('core_reporte_id', $reporte->id)->max('orden');
            DB::table('sys_reporte_tiene_campos')->insert([
                'core_reporte_id' => $reporte->id, 'core_campo_id' => $campoId, 'orden' => $orden + 1
            ]);
        }
    }

    public function down()
    {
        foreach (DB::table('sys_campos')->where('definicion', $this->marker)->get() as $campo) {
            DB::table('sys_reporte_tiene_campos')->where('core_campo_id', $campo->id)->delete();
            DB::table('sys_campos')->where('id', $campo->id)->delete();
        }
    }
}
