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

        $configurationModelId = (int)DB::table('sys_modelos')
            ->where('name_space', 'App\\Core\\TurnoConfiguracion')->value('id');
        $turnModelId = (int)DB::table('sys_modelos')
            ->where('name_space', 'App\\Core\\TurnoOperativo')->value('id');
        $configurationContextField = $this->relatedField($configurationModelId, 'contexto_tipo');
        $turnContextField = $this->relatedField($turnModelId, 'contexto_tipo');

        $this->assertSame('select', $configurationContextField->tipo);
        $this->assertSame('bsText', $turnContextField->tipo);
        $this->assertNotSame((int)$configurationContextField->id, (int)$turnContextField->id);
    }

    public function test_formulario_configuracion_muestra_label_de_empresa_y_tipo_de_contexto_select()
    {
        $this->authenticateCompanyUser();
        $fields = TurnoConfiguracion::get_campos_adicionales_create(array(
            array(
                'name' => 'core_empresa_id', 'tipo' => 'hidden', 'descripcion' => 'Empresa',
                'opciones' => array(), 'value' => null, 'editable' => 1, 'atributos' => array(),
            ),
            array(
                'name' => 'contexto_tipo', 'tipo' => 'bsText', 'descripcion' => 'Tipo de contexto',
                'opciones' => array(), 'value' => 'pdv', 'editable' => 1, 'atributos' => array(),
            ),
        ));

        $empresaField = $this->fieldNamed($fields, 'core_empresa_id');
        $contextField = $this->fieldNamed($fields, 'contexto_tipo');

        $this->assertSame('bsLabel', $empresaField['tipo']);
        $this->assertSame(1, (int)$empresaField['value']);
        $this->assertSame(array(), $empresaField['atributos']);
        $this->assertSame('select', $contextField['tipo']);
        $this->assertSame(array('pdv' => 'PDV', '*' => 'Empresa / todos'), $contextField['opciones']);
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
        $pdvDescription = DB::table('vtas_pos_puntos_de_ventas')->where('id', 1)->value('descripcion');
        $listedConfiguration = null;
        foreach (TurnoConfiguracion::consultar_registros(100, '') as $listed) {
            if ((int)$listed->campo7 === (int)$configuration->id) {
                $listedConfiguration = $listed;
                break;
            }
        }
        $this->assertNotNull($listedConfiguration);
        $this->assertSame('PDV 1 - ' . $pdvDescription, $listedConfiguration->campo4);

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
        DB::table('core_turnos_operativos')->where('core_empresa_id', 1)
            ->where('estado', TurnoOperativo::ESTADO_ABIERTO)
            ->update(array('estado' => TurnoOperativo::ESTADO_CERRADO, 'cerrado_en' => '2026-08-28 07:59:59'));
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
        $pdvName = DB::table('vtas_pos_puntos_de_ventas')->where('id', 1)->value('descripcion');
        $this->assertContains('TUR-' . strtoupper(str_slug($pdvName, '-')) . '-1-1-20260828080000-', $turn->codigo);

        $model = (object)array('name_space' => 'App\\VentasPos\\FacturaPos');
        $createFields = app(TurnoFormService::class)->decorate($model, null, 'create', array());
        $field = $this->turnField($createFields);
        $this->assertNotNull($field);
        $this->assertSame((int)$turn->id, (int)$field['value']);
        $this->assertArrayHasKey($turn->id, $field['opciones']);
        $this->assertSame(1, $field['editable']);

        $cashierId = DB::table('users as user')
            ->join('user_has_roles as assigned_role', 'assigned_role.user_id', '=', 'user.id')
            ->join('roles as role', 'role.id', '=', 'assigned_role.role_id')
            ->where('user.empresa_id', 1)->where('role.name', 'Cajero PDV')
            ->value('user.id');
        $this->assertGreaterThan(0, (int)$cashierId);
        $this->be(User::find($cashierId));
        app('request')->merge(array('pdv_id' => 1));

        $cashierField = $this->turnField(app(TurnoFormService::class)->decorate($model, null, 'create', array()));
        $this->assertSame(0, $cashierField['editable']);
        $this->assertArrayHasKey('disabled', $cashierField['atributos']);
        $this->assertSame('1', $cashierField['atributos']['data-turno-locked']);
        $this->assertSame(array($turn->id), array_keys($cashierField['opciones']));
        $this->assertSame((int)$turn->id, (int)$cashierField['value']);

        $invoiceWithoutSubmittedTurn = new \App\VentasPos\FacturaPos(array(
            'core_empresa_id' => 1, 'pdv_id' => 1, 'turno_operativo_id' => null,
        ));
        app(\App\Core\Services\TurnoAssignmentResolver::class)
            ->assign($invoiceWithoutSubmittedTurn, 'ventas_pos', 1);
        $this->assertSame((int)$turn->id, (int)$invoiceWithoutSubmittedTurn->turno_operativo_id);

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
        return $this->fieldNamed($fields, 'turno_operativo_id');
    }

    protected function fieldNamed(array $fields, $name)
    {
        foreach ($fields as $field) {
            if (isset($field['name']) && $field['name'] === $name) {
                return $field;
            }
        }
        return null;
    }

    protected function relatedField($modelId, $name)
    {
        return DB::table('sys_modelo_tiene_campos as relation')
            ->join('sys_campos as field', 'field.id', '=', 'relation.core_campo_id')
            ->where('relation.core_modelo_id', $modelId)
            ->where('field.name', $name)
            ->select('field.id', 'field.tipo')
            ->first();
    }
}
