<?php

use App\Core\Exceptions\TurnoIntegrityException;
use App\Core\Services\TurnoConfigurationService;
use App\Core\Services\TurnoFormService;
use App\Core\Services\TurnoManager;
use App\Core\Services\TurnoModeResolver;
use App\Core\TurnoConfiguracion;
use App\Core\TurnoOperativo;
use App\Tesoreria\TesoMovimiento;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class TurnoAdminCrudTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown()
    {
        app(TurnoModeResolver::class)->clearCache();
        parent::tearDown();
    }

    public function test_seeder_registra_catalogos_campos_y_permisos_de_forma_idempotente()
    {
        $seeder = new TurnosAdminCrudSeeder();
        $seeder->run();
        $seeder->run();

        foreach (array(
            'App\\Core\\TurnoConfiguracion' => 7,
            'App\\Core\\TurnoOperativo' => 15,
            'App\\Core\\TurnoEvento' => 9,
        ) as $namespace => $fieldCount) {
            $models = DB::table('sys_modelos')->where('name_space', $namespace)->get();
            $this->assertCount(1, $models);
            $this->assertSame($fieldCount, DB::table('sys_modelo_tiene_campos')
                ->where('core_modelo_id', $models[0]->id)->count());
        }

        $this->assertSame(3, DB::table('permissions')->whereIn('name', array(
            'turnos.configuraciones.gestionar',
            'turnos.operativos.consultar',
            'turnos.eventos.consultar',
        ))->count());
    }

    public function test_crud_generico_renderiza_catalogo_y_controlador_guarda_por_servicio()
    {
        $seeder = new TurnosAdminCrudSeeder();
        $seeder->run();
        $user = $this->authenticateCompanyUser();
        if (!$user->hasPermissionTo('turnos.configuraciones.gestionar')) {
            $user->givePermissionTo('turnos.configuraciones.gestionar');
        }
        DB::table('vtas_pos_puntos_de_ventas')->where('id', 1)->update(array('estado' => 'Cerrado'));

        $modelId = (int)DB::table('sys_modelos')
            ->where('name_space', 'App\\Core\\TurnoConfiguracion')->value('id');
        $this->call('GET', '/web', array('id' => 7, 'id_modelo' => $modelId));
        $this->assertResponseOk();
        $this->call(
            'GET', '/web/create', array('id' => 7, 'id_modelo' => $modelId),
            array(), array(), array('HTTP_REFERER' => 'http://localhost/web?id=7&id_modelo=' . $modelId)
        );
        $this->assertResponseOk();

        $this->call('POST', '/turnos/configuraciones', array(
            'url_id' => 7,
            'url_id_modelo' => $modelId,
            'modulo' => 'ventas_pos',
            'contexto_tipo' => 'pdv',
            'contexto_id' => 1,
            'modo' => TurnoConfiguracion::MODO_TURNOS,
        ));
        $this->assertResponseStatus(302);
        $this->seeInDatabase('core_turno_configuraciones', array(
            'core_empresa_id' => 1,
            'modulo' => 'ventas_pos',
            'contexto_tipo' => 'pdv',
            'contexto_id' => 1,
            'modo' => TurnoConfiguracion::MODO_TURNOS,
        ));

        $configuration = TurnoConfiguracion::where('core_empresa_id', 1)
            ->where('modulo', 'ventas_pos')->where('contexto_tipo', 'pdv')->where('contexto_id', 1)->first();
        $this->call('GET', '/web/' . $configuration->id . '/edit', array('id' => 7, 'id_modelo' => $modelId));
        $this->assertResponseOk();
        $this->call('PUT', '/turnos/configuraciones/' . $configuration->id . '?id=7&id_modelo=' . $modelId, array(
            'url_id' => 7,
            'url_id_modelo' => $modelId,
            'modo' => TurnoConfiguracion::MODO_TRADICIONAL,
        ));
        $this->assertResponseStatus(302);
        $this->seeInDatabase('core_turno_configuraciones', array(
            'id' => $configuration->id,
            'core_empresa_id' => 1,
            'modulo' => 'ventas_pos',
            'contexto_tipo' => 'pdv',
            'contexto_id' => 1,
            'modo' => TurnoConfiguracion::MODO_TRADICIONAL,
        ));
        $this->call(
            'GET', '/turnos/configuraciones/' . $configuration->id . '/eliminar',
            array('id' => 7, 'id_modelo' => $modelId)
        );
        $this->assertResponseStatus(302);
        $this->notSeeInDatabase('core_turno_configuraciones', array('id' => $configuration->id));
    }

    public function test_empresa_tradicional_no_recibe_selector_de_turno()
    {
        $this->authenticateCompanyUser();
        DB::table('core_turno_configuraciones')->where('core_empresa_id', 1)->delete();
        app(TurnoModeResolver::class)->clearCache();

        $fields = app(TurnoFormService::class)->decorate(
            (object)array('name_space' => 'App\\VentasPos\\FacturaPos'), null, 'create', array()
        );

        $this->assertFalse($this->hasTurnField($fields));
    }

    public function test_formulario_turnos_muestra_solo_turno_abierto_valido_y_lo_deja_inmutable_en_edit()
    {
        $this->authenticateCompanyUser();
        DB::table('vtas_pos_puntos_de_ventas')->where('id', 1)->update(array('estado' => 'Cerrado'));
        app(TurnoConfigurationService::class)->configure(array(
            'core_empresa_id' => 1,
            'modulo' => 'ventas_pos',
            'contexto_tipo' => 'pdv',
            'contexto_id' => 1,
            'modo' => TurnoConfiguracion::MODO_TURNOS,
        ));
        $turn = app(TurnoManager::class)->openContext(
            1, 'ventas_pos', 'pdv', 1, '2026-08-28', 1, 100, '2026-08-28 08:00:00'
        );

        $model = (object)array('name_space' => 'App\\VentasPos\\FacturaPos');
        $createFields = app(TurnoFormService::class)->decorate($model, null, 'create', array());
        $field = $this->turnField($createFields);
        $this->assertNotNull($field);
        $this->assertSame((int)$turn->id, (int)$field['value']);
        $this->assertArrayHasKey($turn->id, $field['opciones']);
        $this->assertSame(1, $field['editable']);

        $record = new \App\VentasPos\FacturaPos(array(
            'core_empresa_id' => 1, 'pdv_id' => 1, 'turno_operativo_id' => $turn->id,
        ));
        $editField = $this->turnField(app(TurnoFormService::class)->decorate($model, $record, 'edit', array()));
        $this->assertSame(0, $editField['editable']);
        $this->assertArrayHasKey('disabled', $editField['atributos']);
        $this->assertSame((int)$turn->id, (int)$editField['value']);
    }

    public function test_turno_persistido_no_se_puede_reasignar_desde_una_edicion_ordinaria()
    {
        $this->authenticateCompanyUser();
        DB::table('vtas_pos_puntos_de_ventas')->where('id', 1)->update(array('estado' => 'Cerrado'));
        app(TurnoConfigurationService::class)->configure(array(
            'core_empresa_id' => 1, 'modulo' => '*', 'contexto_tipo' => 'pdv',
            'contexto_id' => 1, 'modo' => TurnoConfiguracion::MODO_TURNOS,
        ));
        $turn = app(TurnoManager::class)->openContext(
            1, 'ventas_pos', 'pdv', 1, '2026-08-28', 1, 100, '2026-08-28 08:00:00'
        );

        $movement = TesoMovimiento::create(array(
            'fecha' => '2026-08-28', 'core_empresa_id' => 1, 'core_tercero_id' => 1,
            'core_tipo_transaccion_id' => 47, 'core_tipo_doc_app_id' => 45,
            'consecutivo' => 998877, 'turno_operativo_id' => $turn->id,
            'teso_medio_recaudo_id' => 1, 'teso_motivo_id' => 1,
            'teso_caja_id' => 1, 'pdv_id' => 1, 'valor_movimiento' => 10,
            'descripcion' => 'Prueba de inmutabilidad', 'estado' => 'Activo',
        ));
        $movement->turno_operativo_id = (string)$turn->id;
        $movement->descripcion = 'Edición ordinaria conservando el turno';
        $movement->save();
        $this->assertSame((int)$turn->id, (int)$movement->fresh()->turno_operativo_id);

        $movement->turno_operativo_id = null;

        $this->setExpectedException(TurnoIntegrityException::class, 'inmutable');
        $movement->save();
    }

    protected function authenticateCompanyUser()
    {
        $user = User::where('empresa_id', 1)->first();
        $this->assertNotNull($user);
        $this->be($user);
        return $user;
    }

    protected function hasTurnField(array $fields)
    {
        return !is_null($this->turnField($fields));
    }

    protected function turnField(array $fields)
    {
        foreach ($fields as $field) {
            if (isset($field['name']) && $field['name'] === 'turno_operativo_id') {
                return $field;
            }
        }
        return null;
    }
}
