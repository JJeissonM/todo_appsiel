<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProveedorCuentasBancariasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (
            !Schema::hasTable('compras_proveedores_cuentas_bancarias') ||
            !Schema::hasTable('compras_proveedores') ||
            !Schema::hasTable('core_terceros') ||
            !Schema::hasTable('teso_entidades_financieras') ||
            !Schema::hasTable('siesa_proveedores_enterprise')
        ) {
            return;
        }

        $entidad_financiera_id = DB::table('teso_entidades_financieras')
            ->where('estado', 'Activo')
            ->orderBy('id')
            ->value('id');

        if (is_null($entidad_financiera_id)) {
            return;
        }

        $registros = DB::table('compras_proveedores as p')
            ->join('core_terceros as t', 't.id', '=', 'p.core_tercero_id')
            ->join('siesa_proveedores_enterprise as s', 's.codigo', '=', 'p.codigo')
            ->whereNotNull('s.numero_cuenta')
            ->where('s.numero_cuenta', '<>', '')
            ->select(
                'p.core_tercero_id as tercero_id',
                't.codigo_ciudad',
                's.numero_cuenta',
                's.tipo_cuenta'
            )
            ->distinct()
            ->get();

        if (empty($registros)) {
            return;
        }

        $ahora = Carbon::now();
        foreach ($registros as $registro) {
            $tipo_cuenta = $this->normalizar_tipo_cuenta($registro->tipo_cuenta);

            DB::table('compras_proveedores_cuentas_bancarias')->updateOrInsert(
                [
                    'tercero_id' => $registro->tercero_id,
                    'entidad_financiera_id' => $entidad_financiera_id,
                    'numero_cuenta' => $registro->numero_cuenta
                ],
                [
                    'tipo_cuenta' => $tipo_cuenta,
                    'codigo_ciudad' => $registro->codigo_ciudad,
                    'estado' => 'Activo',
                    'updated_at' => $ahora,
                    'created_at' => $ahora
                ]
            );
        }
    }

    protected function normalizar_tipo_cuenta($tipo_cuenta)
    {
        $tipo = strtolower(trim((string)$tipo_cuenta));

        if (strpos($tipo, 'corr') !== false) {
            return 'Corriente';
        }

        return 'Ahorros';
    }
}
