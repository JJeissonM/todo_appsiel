<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $seeders = [
            [
                'class' => ProveedorCuentasBancariasSeeder::class,
                'tables' => [
                    'compras_proveedores_cuentas_bancarias',
                    'compras_proveedores',
                    'core_terceros',
                    'teso_entidades_financieras',
                    'siesa_proveedores_enterprise',
                ],
            ],
            [
                'class' => TesoreriaChequerasPermissionSeeder::class,
                'tables' => ['permissions', 'roles', 'role_has_permissions'],
            ],
            [
                'class' => CorePaisesGentilicioSeeder::class,
                'tables' => ['core_paises'],
            ],
            [
                'class' => ComprasRetencionesLineaSeeder::class,
                'any_tables' => ['compras_doc_registros', 'contab_registros_retenciones'],
            ],
            [
                'class' => ComprasRetencionFuente2026Seeder::class,
                'tables' => ['compras_retencion_fuente_conceptos_anuales', 'contab_retenciones'],
            ],
            [
                'class' => ApmPrintStatusesSeeder::class,
                'tables' => ['apm_print_statuses'],
            ],
            [
                'class' => NominaParametrosLegalesSeeder::class,
                'tables' => ['nom_parametros_legales'],
            ],
            [
                'class' => RestauranteCocinasSeeder::class,
                'any_tables' => ['vtas_restaurante_cocinas', 'sys_modelos', 'sys_campos', 'permissions'],
            ],
            [
                'class' => HotelApplicationSeeder::class,
                'any_tables' => ['sys_aplicaciones', 'sys_modelos', 'permissions', 'sys_reportes'],
                'enabled_by_env' => 'HOTEL_MODULE_ENABLED',
            ],
            [
                'class' => InventoryDefaultWarehouseSeeder::class,
                'tables' => ['inv_bodegas'],
            ],
            [
                'class' => HotelRoomsTableSeeder::class,
                'tables' => ['hotel_rooms', 'inv_productos'],
                'enabled_by_env' => 'HOTEL_MODULE_ENABLED',
            ],
            [
                'class' => PdvCrudFieldsSeeder::class,
                'tables' => ['vtas_pos_puntos_de_ventas', 'sys_modelos', 'sys_campos', 'sys_modelo_tiene_campos'],
            ],
            [
                'class' => AcademicInclusionFieldsSeeder::class,
                'tables' => ['sys_modelos', 'sys_campos', 'sys_modelo_tiene_campos'],
            ],
            [
                'class' => NominaActualizacionSueldosSeeder::class,
                'tables' => ['permissions', 'roles', 'role_has_permissions'],
            ],
            [
                'class' => NominaCotizante51Seeder::class,
                'tables' => ['nom_contratos'],
            ],
            [
                'class' => CumplimientoGuiasReporteSeeder::class,
                'tables' => ['sys_reportes', 'sys_campos', 'sys_reporte_tiene_campos'],
            ],
            [
                'class' => IcfesQuestionBankSeeder::class,
                'tables' => ['sga_cuestionarios', 'sga_preguntas', 'sga_cuestionario_tiene_preguntas'],
            ],
            [
                'class' => NominaSeeder::class,
                'tables' => ['core_terceros', 'nom_entidades'],
                'skip_when_table_has_data' => 'nom_entidades',
            ],
        ];

        foreach ($seeders as $seeder) {
            $this->callIfSafe($seeder);
        }
    }

    protected function callIfSafe(array $seeder)
    {
        $class = $seeder['class'];

        if (isset($seeder['enabled_by_env']) && !$this->envFlagEnabled($seeder['enabled_by_env'])) {
            $this->warnSeeder($class, 'bandera ' . $seeder['enabled_by_env'] . ' inactiva');
            return;
        }

        $this->loadSeederClassIfNeeded($class);

        if (!class_exists($class)) {
            $this->warnSeeder($class, 'no existe la clase');
            return;
        }

        if (isset($seeder['tables']) && !$this->hasTables($seeder['tables'])) {
            $this->warnSeeder($class, 'faltan tablas requeridas');
            return;
        }

        if (isset($seeder['any_tables']) && !$this->hasAnyTable($seeder['any_tables'])) {
            $this->warnSeeder($class, 'no existe ninguna tabla aplicable');
            return;
        }

        if (isset($seeder['skip_when_table_has_data']) && $this->tableHasData($seeder['skip_when_table_has_data'])) {
            $this->warnSeeder($class, 'ya existen registros base');
            return;
        }

        try {
            $this->call($class);
        } catch (Exception $exception) {
            $this->warnSeeder($class, $exception->getMessage());
        }
    }

    protected function loadSeederClassIfNeeded($class)
    {
        if (class_exists($class)) {
            return;
        }

        $file = __DIR__ . '/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }

    protected function hasTables(array $tables)
    {
        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }

    protected function hasAnyTable(array $tables)
    {
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                return true;
            }
        }

        return false;
    }

    protected function tableHasData($table)
    {
        return Schema::hasTable($table) && DB::table($table)->limit(1)->exists();
    }

    protected function envFlagEnabled($key)
    {
        return filter_var(env($key, env('HOTEL_MODULE_SEEDERS_ENABLED', false)), FILTER_VALIDATE_BOOLEAN);
    }

    protected function warnSeeder($class, $reason)
    {
        if (isset($this->command) && method_exists($this->command, 'warn')) {
            $this->command->warn('Seeder omitido: ' . $class . ' (' . $reason . ')');
        }
    }
}
