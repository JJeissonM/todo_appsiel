<?php

use App\Core\Exceptions\TurnoIntegrityException;
use App\Core\Services\TurnoConfigurationService;
use App\Core\Services\TurnoFormService;
use App\Core\Services\TurnoManager;
use App\Core\Services\TurnoModeResolver;
use App\Core\TurnoConfiguracion;
use App\Core\TurnoEvento;
use App\Core\TurnoOperativo;
use App\Core\Empresa;
use App\Inventarios\Services\InventoryPhysicalPdvShiftService;
use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocumentoRelacionado;
use App\Tesoreria\TesoMovimiento;
use App\Tesoreria\TesoDocEncabezado;
use App\Tesoreria\TesoDocEncabezadoTraslado;
use App\Tesoreria\Services\CashCountTurnService;
use App\User;
use App\VentasPos\Pdv;
use App\VentasPos\Services\FacturaPosService;
use App\Sistema\Modelo;
use App\Sistema\Services\ModeloService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class TurnoAdminCrudTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp()
    {
        parent::setUp();
        $this->isolateTurnState();
    }

    protected function tearDown()
    {
        app(TurnoModeResolver::class)->clearCache();
        parent::tearDown();
    }

    protected function isolateTurnState()
    {
        if (Schema::hasTable('core_turno_configuraciones')) {
            DB::table('core_turno_configuraciones')->where('core_empresa_id', 1)->delete();
        }
        if (Schema::hasTable('core_turnos_operativos')) {
            DB::table('core_turnos_operativos')->where('core_empresa_id', 1)
                ->where('estado', TurnoOperativo::ESTADO_ABIERTO)
                ->update(array(
                    'estado' => TurnoOperativo::ESTADO_CERRADO,
                    'cerrado_en' => DB::raw('COALESCE(cerrado_en, NOW())'),
                    'clave_contexto_abierto' => null,
                ));
        }
        if (Schema::hasTable('vtas_pos_puntos_de_ventas')) {
            DB::table('vtas_pos_puntos_de_ventas')->where('core_empresa_id', 1)
                ->update(array('estado' => 'Cerrado'));
        }
        app(TurnoModeResolver::class)->clearCache();
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

        $this->assertSame(4, DB::table('permissions')->whereIn('name', array(
            'turnos.configuraciones.gestionar',
            'turnos.operativos.consultar',
            'turnos.eventos.consultar',
            'turnos.ajustes.registrar',
        ))->count());

        $configurationModelId = (int)DB::table('sys_modelos')
            ->where('name_space', 'App\\Core\\TurnoConfiguracion')->value('id');
        $turnModelId = (int)DB::table('sys_modelos')
            ->where('name_space', 'App\\Core\\TurnoOperativo')->value('id');
        $configurationContextField = $this->relatedField($configurationModelId, 'contexto_tipo');
        $turnContextField = $this->relatedField($turnModelId, 'contexto_tipo');
        $turnCompanyField = $this->relatedField($turnModelId, 'core_empresa_id');
        $inventoryModelId = (int)DB::table('sys_modelos')
            ->where('name_space', 'App\\Inventarios\\InvEntradaAlmacen')->value('id');
        $inventoryCompanyField = $this->relatedField($inventoryModelId, 'core_empresa_id');

        $this->assertSame('select', $configurationContextField->tipo);
        $this->assertSame('bsText', $turnContextField->tipo);
        $this->assertNotSame((int)$configurationContextField->id, (int)$turnContextField->id);
        $this->assertSame('bsLabel', $turnCompanyField->tipo);
        $this->assertSame('Empresa del turno', $turnCompanyField->descripcion);
        $this->assertNotSame((int)$inventoryCompanyField->id, (int)$turnCompanyField->id);

        foreach ((array)config('turnos.manual_assignment_models', array()) as $class => $module) {
            $instance = new $class();
            if (Schema::hasTable($instance->getTable())
                && Schema::hasColumn($instance->getTable(), 'turno_operativo_id')) {
                $this->assertContains(
                    'turno_operativo_id',
                    $instance->getFillable(),
                    $class . ' debe aceptar la FK explícita del turno desde el formulario.'
                );
            }
        }
    }

    public function test_transaccion_generica_carga_empresa_autenticada_en_el_selector()
    {
        $user = $this->authenticateCompanyUser();
        $options = Empresa::opciones_campo_select();

        $this->assertArrayHasKey((int)$user->empresa_id, $options);
        $this->assertNotEmpty($options[(int)$user->empresa_id]);

        $model = Modelo::find(248); // Entrada de almacén
        $this->assertNotNull($model);
        $fields = (new ModeloService())->get_campos_modelo($model, '', 'create');
        $companyField = $this->fieldNamed($fields, 'core_empresa_id');

        $this->assertNotNull($companyField);
        $this->assertSame((int)$user->empresa_id, (int)$companyField['value']);
        $this->assertArrayHasKey((int)$user->empresa_id, $companyField['opciones']);
        $this->assertNotEmpty($companyField['opciones'][(int)$user->empresa_id]);
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
        $this->assertSame('hidden', $contextField['tipo']);
        $this->assertSame('*', $contextField['value']);
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
            'modulo' => '*',
            'contexto_tipo' => '*',
            'contexto_id' => 0,
            'modo' => TurnoConfiguracion::MODO_TURNOS,
        ));

        $configuration = TurnoConfiguracion::where('core_empresa_id', 1)
            ->where('modulo', '*')->where('contexto_tipo', '*')->where('contexto_id', 0)->first();
        $listedConfiguration = null;
        foreach (TurnoConfiguracion::consultar_registros(100, '') as $listed) {
            if ((int)$listed->campo7 === (int)$configuration->id) {
                $listedConfiguration = $listed;
                break;
            }
        }
        $this->assertNotNull($listedConfiguration);
        $this->assertSame('Empresa / todos los contextos', $listedConfiguration->campo4);

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
            'modulo' => '*',
            'contexto_tipo' => '*',
            'contexto_id' => 0,
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
        app('request')->merge(array('pdv_id' => 1));
        $createFields = app(TurnoFormService::class)->decorate($model, null, 'create', array());
        $field = $this->turnField($createFields);
        $this->assertNotNull($field);
        $this->assertSame('input_lista_sugerencias', $field['tipo']);
        $this->assertSame(array('', ''), $field['value']);
        $this->assertSame(array(), $field['opciones']);
        $this->assertContains('/turnos/operativos/sugerencias?modulo=ventas_pos', $field['atributos']['data-url_busqueda']);
        $this->assertSame('pdv_id', $field['atributos']['data-ajax-fields']);
        $this->assertSame(1, $field['editable']);

        app('request')->replace(array());
        $adminFieldWithoutPdv = $this->turnField(
            app(TurnoFormService::class)->decorate($model, null, 'create', array())
        );
        $this->assertSame(array('', ''), $adminFieldWithoutPdv['value']);

        $cashierId = DB::table('users as user')
            ->join('user_has_roles as assigned_role', 'assigned_role.user_id', '=', 'user.id')
            ->join('roles as role', 'role.id', '=', 'assigned_role.role_id')
            ->where('user.empresa_id', 1)->where('role.name', 'Cajero PDV')
            ->value('user.id');
        $this->assertGreaterThan(0, (int)$cashierId);
        $this->be(User::find($cashierId));
        DB::table('core_turnos_operativos')->where('id', $turn->id)
            ->update(array('abierto_por' => (int)$cashierId));
        $turn = $turn->fresh();
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

    public function test_cajero_recibe_su_turno_abierto_en_transaccion_sin_pdv_y_el_valor_se_envia()
    {
        $this->authenticateCompanyUser();
        $cashierId = DB::table('users as user')
            ->join('user_has_roles as assigned_role', 'assigned_role.user_id', '=', 'user.id')
            ->join('roles as role', 'role.id', '=', 'assigned_role.role_id')
            ->where('user.empresa_id', 1)
            ->whereIn('role.name', (array)config('turnos.turn_selection_locked_roles', array()))
            ->value('user.id');

        $this->assertGreaterThan(0, (int)$cashierId);
        app(TurnoConfigurationService::class)->configure(array(
            'core_empresa_id' => 1,
            'modulo' => 'tesoreria',
            'contexto_tipo' => 'pdv',
            'contexto_id' => 1,
            'modo' => TurnoConfiguracion::MODO_TURNOS,
        ));
        $turn = app(TurnoManager::class)->openContext(
            1, 'tesoreria', 'pdv', 1, '2026-09-01', (int)$cashierId, 100, '2026-09-01 08:00:00'
        );

        $this->be(User::find($cashierId));
        app('request')->replace(array());
        $model = (object)array('name_space' => 'App\\Tesoreria\\TesoDocEncabezadoRecaudoCxc');
        $field = $this->turnField(app(TurnoFormService::class)->decorate($model, null, 'create', array()));

        $this->assertNotNull($field);
        $this->assertSame('select', $field['tipo']);
        $this->assertSame((int)$turn->id, (int)$field['value']);
        $this->assertSame(array($turn->id), array_keys($field['opciones']));
        $this->assertArrayHasKey('disabled', $field['atributos']);

        $html = View::make('components.form.select', array(
            'name' => $field['name'],
            'opciones' => $field['opciones'],
            'value' => $field['value'],
            'attributes' => $field['atributos'],
            'lbl' => $field['descripcion'],
        ))->render();
        $this->assertContains('type="hidden"', $html);
        $this->assertContains('name="turno_operativo_id"', $html);
        $this->assertContains('value="' . $turn->id . '"', $html);

        $this->call('POST', '/turnos/operativos/validar-seleccion', array(
            'modulo' => 'tesoreria',
            'turno_operativo_id' => '',
        ));
        $this->assertResponseOk();
        $this->assertContains('"turno_operativo_id":' . $turn->id, $this->response->getContent());

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

    public function test_edicion_de_inventario_fisico_construye_el_formulario_con_el_turno_persistido()
    {
        $controller = file_get_contents(app_path('Http/Controllers/Inventarios/InvFisicoController.php'));

        $recordLoad = '$registro = InvDocEncabezado::get_registro_impresion( $id );';
        $fieldBuild = "ModeloController::get_campos_modelo(\$modelo, \$registro, 'edit')";
        $fieldCustomization = "ModeloController::personalizar_campos(\$id_transaccion,\$tipo_transaccion,\$lista_campos,\$cantidad_campos,'edit' )";

        $this->assertContains($recordLoad, $controller);
        $this->assertContains($fieldBuild, $controller);
        $this->assertContains($fieldCustomization, $controller);
        $this->assertLessThan(strpos($controller, $fieldBuild), strpos($controller, $recordLoad));
    }

    public function test_ajuste_de_inventario_fisico_muestra_y_conserva_el_turno_del_documento_origen()
    {
        $this->authenticateCompanyUser();
        DB::table('vtas_pos_puntos_de_ventas')->where('id', 1)->update(array('estado' => 'Cerrado'));
        app(TurnoConfigurationService::class)->configure(array(
            'core_empresa_id' => 1, 'modulo' => 'inventarios', 'contexto_tipo' => 'pdv',
            'contexto_id' => 1, 'modo' => TurnoConfiguracion::MODO_TURNOS,
        ));
        $turn = app(TurnoManager::class)->openContext(
            1, 'inventarios', 'pdv', 1, '2026-09-01', 1, 0, '2026-09-01 06:00:00'
        );

        $origin = new InvDocEncabezado(array(
            'core_empresa_id' => 1,
            'turno_operativo_id' => $turn->id,
        ));
        $model = (object)array('name_space' => 'App\\Inventarios\\InvAjuste');
        $fields = array(
            array('name' => 'turno_operativo_id', 'tipo' => 'input_lista_sugerencias'),
            array('name' => 'turno_ajuste_motivo', 'tipo' => 'bsTextArea'),
        );

        $fields = app(TurnoFormService::class)->lockToOrigin($model, $origin, $fields);
        $turnField = $this->turnField($fields);

        $this->assertSame('select', $turnField['tipo']);
        $this->assertSame((int)$turn->id, (int)$turnField['value']);
        $this->assertArrayHasKey($turn->id, $turnField['opciones']);
        $this->assertArrayHasKey('disabled', $turnField['atributos']);
        $this->assertNotNull($this->fieldNamed($fields, 'turno_ajuste_motivo'));

        $controller = file_get_contents(app_path('Http/Controllers/Inventarios/InventarioController.php'));
        $this->assertContains("request->merge(array('turno_operativo_id' => \$inventarioFisico->turno_operativo_id))", $controller);
    }

    public function test_relacion_de_ajuste_ignora_origen_obsoleto_que_no_es_inventario_fisico()
    {
        $template = DB::table('inv_doc_encabezados')->where('core_empresa_id', 1)->first();
        $this->assertNotNull($template);

        $createDocument = function ($transactionId, $consecutive) use ($template) {
            $data = (array)$template;
            unset($data['id']);
            $data['core_tipo_transaccion_id'] = $transactionId;
            $data['consecutivo'] = $consecutive;
            $data['turno_operativo_id'] = null;
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            return DB::table('inv_doc_encabezados')->insertGetId($data);
        };

        $notPhysicalInventoryId = $createDocument(35, 979901);
        $physicalInventoryId = $createDocument(
            InvDocumentoRelacionado::TIPO_TRANSACCION_INVENTARIO_FISICO,
            979902
        );
        $adjustmentId = $createDocument(28, 979903);
        $now = date('Y-m-d H:i:s');

        DB::table('inv_documentos_relacionados')->insert(array(
            array(
                'inv_doc_encabezado_origen_id' => $notPhysicalInventoryId,
                'inv_doc_encabezado_relacionado_id' => $adjustmentId,
                'tipo_relacion' => InvDocumentoRelacionado::TIPO_IF_AJUSTE,
                'creado_por' => 'test@appsiel.com.co', 'modificado_por' => null,
                'created_at' => $now, 'updated_at' => $now,
            ),
            array(
                'inv_doc_encabezado_origen_id' => $physicalInventoryId,
                'inv_doc_encabezado_relacionado_id' => $adjustmentId,
                'tipo_relacion' => InvDocumentoRelacionado::TIPO_IF_AJUSTE,
                'creado_por' => 'test@appsiel.com.co', 'modificado_por' => null,
                'created_at' => $now, 'updated_at' => $now,
            ),
        ));

        $relation = InvDocumentoRelacionado::ajusteValidoParaDocumento($adjustmentId, 1);

        $this->assertNotNull($relation);
        $this->assertSame((int)$physicalInventoryId, (int)$relation->inv_doc_encabezado_origen_id);
        $this->assertSame(
            InvDocumentoRelacionado::TIPO_TRANSACCION_INVENTARIO_FISICO,
            (int)$relation->documento_origen->core_tipo_transaccion_id
        );
    }

    public function test_administrador_puede_registrar_ajuste_motivado_sobre_turno_cerrado()
    {
        (new TurnosAdminCrudSeeder())->run();
        $user = $this->authenticateCompanyUser();
        $this->assertTrue($user->can('turnos.ajustes.registrar'));
        DB::table('core_turnos_operativos')->where('core_empresa_id', 1)
            ->where('estado', TurnoOperativo::ESTADO_ABIERTO)
            ->update(array('estado' => TurnoOperativo::ESTADO_CERRADO, 'cerrado_en' => '2026-08-28 07:59:59'));
        DB::table('vtas_pos_puntos_de_ventas')->where('id', 1)->update(array('estado' => 'Cerrado'));
        app(TurnoConfigurationService::class)->configure(array(
            'core_empresa_id' => 1, 'modulo' => 'tesoreria', 'contexto_tipo' => 'pdv',
            'contexto_id' => 1, 'modo' => TurnoConfiguracion::MODO_TURNOS,
        ));
        $manager = app(TurnoManager::class);
        $turn = $manager->openContext(
            1, 'tesoreria', 'pdv', 1, '2026-08-28', $user->id, 0, '2026-08-28 08:00:00'
        );
        $manager->close($turn, $user->id, 0, 'Cierre de prueba', '2026-08-28 18:00:00');

        app('request')->merge(array('pdv_id' => 1, 'turno_ajuste_motivo' => ''));
        $model = (object)array('name_space' => 'App\\Tesoreria\\TesoMovimiento');
        $fields = app(TurnoFormService::class)->decorate($model, null, 'create', array());
        $turnField = $this->turnField($fields);
        $this->assertSame('input_lista_sugerencias', $turnField['tipo']);
        $this->assertSame(array(), $turnField['opciones']);
        $this->assertSame('1', $turnField['atributos']['data-turno-validation']);
        $this->assertContains('/turnos/operativos/validar-seleccion', $turnField['atributos']['data-turno-validation-url']);
        $this->assertNotNull($this->fieldNamed($fields, 'turno_ajuste_motivo'));

        $this->call('GET', '/turnos/operativos/sugerencias', array(
            'modulo' => 'tesoreria',
            'pdv_id' => 1,
            'texto_busqueda' => $turn->codigo,
        ));
        $this->assertResponseOk();
        $suggestions = $this->response->getContent();
        $this->assertContains('data-registro_id="' . $turn->id . '"', $suggestions);
        $this->assertContains($turn->codigo, $suggestions);
        $this->assertContains('CERRADO', $suggestions);
        $this->assertContains('data-turno-estado="CERRADO"', $suggestions);

        $this->call('POST', '/turnos/operativos/validar-seleccion', array(
            'modulo' => 'tesoreria',
            'turno_operativo_id' => $turn->id,
            'turno_ajuste_motivo' => '',
        ));
        $this->assertResponseStatus(422);
        $this->assertContains('motivo del ajuste', $this->response->getContent());

        $this->call('POST', '/turnos/operativos/validar-seleccion', array(
            'modulo' => 'tesoreria',
            'turno_operativo_id' => $turn->id,
            'turno_ajuste_motivo' => 'Corrección autorizada desde la transacción',
        ));
        $this->assertResponseOk();
        $this->assertContains('"ok":true', $this->response->getContent());

        $attributes = array(
            'fecha' => '2026-08-29', 'core_empresa_id' => 1, 'core_tercero_id' => 1,
            'core_tipo_transaccion_id' => 47, 'core_tipo_doc_app_id' => 45,
            'consecutivo' => 998878, 'turno_operativo_id' => $turn->id,
            'teso_medio_recaudo_id' => 1, 'teso_motivo_id' => 1,
            'teso_caja_id' => 1, 'pdv_id' => 1, 'valor_movimiento' => 10,
            'descripcion' => 'Corrección posterior', 'estado' => 'Activo',
        );
        app('request')->merge(array('pdv_id' => 1, 'turno_ajuste_motivo' => ''));
        try {
            TesoMovimiento::create($attributes);
            $this->fail('Debió exigir motivo para usar un turno cerrado.');
        } catch (TurnoIntegrityException $e) {
            $this->assertContains('motivo del ajuste', $e->getMessage());
        }

        app('request')->merge(array(
            'pdv_id' => 1,
            'turno_ajuste_motivo' => 'Corrección autorizada de pago omitido',
        ));
        $movement = TesoMovimiento::create($attributes);

        $this->assertSame((int)$turn->id, (int)$movement->turno_operativo_id);
        $this->seeInDatabase('core_turno_eventos', array(
            'turno_operativo_id' => $turn->id,
            'tipo' => 'AJUSTE_POSTERIOR',
            'entidad_tipo' => TesoMovimiento::class,
            'entidad_id' => $movement->id,
            'usuario_id' => $user->id,
            'motivo' => 'Corrección autorizada de pago omitido',
        ));

        $listedAdjustment = null;
        foreach (TurnoEvento::consultar_registros(100, $turn->codigo) as $event) {
            if ($event->campo3 === 'Ajuste posterior' && (string)$event->campo8 === 'Corrección autorizada de pago omitido') {
                $listedAdjustment = $event;
                break;
            }
        }
        $this->assertNotNull($listedAdjustment);
        $this->assertNotEmpty((string)$listedAdjustment->campo2);
        $this->assertNotSame('—', $listedAdjustment->campo6);

        $this->assertGreaterThan(0, TurnoEvento::consultar_registros(100, 'Ajuste posterior')->count());
    }

    public function test_busqueda_ajax_de_turnos_es_limitada_y_no_serializa_el_historico_en_el_formulario()
    {
        (new TurnosAdminCrudSeeder())->run();
        $user = $this->authenticateCompanyUser();
        app(TurnoConfigurationService::class)->configure(array(
            'core_empresa_id' => 1, 'modulo' => 'tesoreria', 'contexto_tipo' => 'pdv',
            'contexto_id' => 1, 'modo' => TurnoConfiguracion::MODO_TURNOS,
        ));

        for ($index = 1; $index <= 25; $index++) {
            TurnoOperativo::create(array(
                'core_empresa_id' => 1,
                'contexto_tipo' => 'pdv',
                'contexto_id' => 1,
                'pdv_id' => 1,
                'fecha_operativa' => '2026-08-01',
                'abierto_en' => '2026-08-01 08:' . str_pad($index, 2, '0', STR_PAD_LEFT) . ':00',
                'cerrado_en' => '2026-08-01 09:' . str_pad($index, 2, '0', STR_PAD_LEFT) . ':00',
                'abierto_por' => $user->id,
                'cerrado_por' => $user->id,
                'estado' => TurnoOperativo::ESTADO_CERRADO,
                'codigo' => 'AJAX-LIMIT-' . $index,
            ));
        }

        app('request')->merge(array('pdv_id' => 1));
        $model = (object)array('name_space' => 'App\\Tesoreria\\TesoMovimiento');
        $field = $this->turnField(app(TurnoFormService::class)->decorate($model, null, 'create', array()));
        $this->assertSame('input_lista_sugerencias', $field['tipo']);
        $this->assertSame(array(), $field['opciones']);

        $this->call('GET', '/turnos/operativos/sugerencias', array(
            'modulo' => 'tesoreria', 'pdv_id' => 1, 'texto_busqueda' => 'AJAX-LIMIT-',
        ));
        $this->assertResponseOk();
        $this->assertSame(20, substr_count($this->response->getContent(), 'data-registro_id='));
    }

    public function test_inventario_fisico_de_entrega_asigna_el_ultimo_turno_cerrado_del_cajero()
    {
        $this->authenticateCompanyUser();
        $cashierId = DB::table('users as user')
            ->join('user_has_roles as assigned_role', 'assigned_role.user_id', '=', 'user.id')
            ->join('roles as role', 'role.id', '=', 'assigned_role.role_id')
            ->where('user.empresa_id', 1)
            ->whereIn('role.name', (array)config('turnos.turn_selection_locked_roles', array()))
            ->value('user.id');
        $this->assertGreaterThan(0, (int)$cashierId);

        app(TurnoConfigurationService::class)->configure(array(
            'core_empresa_id' => 1,
            'modulo' => 'inventarios',
            'contexto_tipo' => 'pdv',
            'contexto_id' => 1,
            'modo' => TurnoConfiguracion::MODO_TURNOS,
        ));
        $manager = app(TurnoManager::class);
        $turn = $manager->openContext(
            1, 'inventarios', 'pdv', 1, '2026-09-02', (int)$cashierId, 0, '2026-09-02 06:00:00'
        );
        $manager->close($turn, (int)$cashierId, 0, 'Entrega de turno', '2026-09-02 14:00:00');

        $this->be(User::find($cashierId));
        app('request')->replace(array());
        $model = (object)array('name_space' => 'App\\Inventarios\\InvFisico');
        $field = $this->turnField(app(TurnoFormService::class)->decorate($model, null, 'create', array()));

        $this->assertNotNull($field);
        $this->assertSame('select', $field['tipo']);
        $this->assertSame((int)$turn->id, (int)$field['value']);
        $this->assertSame(array($turn->id), array_keys($field['opciones']));
        $this->assertArrayHasKey('disabled', $field['atributos']);
        $this->assertSame(
            InventoryPhysicalPdvShiftService::CLOSING_CONTROL_OPERATION,
            $field['atributos']['data-turno-operation']
        );

        $this->call('POST', '/turnos/operativos/validar-seleccion', array(
            'modulo' => 'inventarios',
            'turno_operativo_id' => $turn->id,
            'operacion_turno' => InventoryPhysicalPdvShiftService::CLOSING_CONTROL_OPERATION,
        ));
        $this->assertResponseOk();
        $this->assertContains('LAST_CLOSED_CASHIER', $this->response->getContent());

        // La misma FK cerrada continúa prohibida para cualquier otra operación.
        $this->call('POST', '/turnos/operativos/validar-seleccion', array(
            'modulo' => 'inventarios',
            'turno_operativo_id' => $turn->id,
        ));
        $this->assertResponseStatus(422);
    }

    public function test_traslado_de_efectivo_asigna_el_ultimo_turno_cerrado_del_cajero()
    {
        $this->authenticateCompanyUser();
        $cashierId = DB::table('users as user')
            ->join('user_has_roles as assigned_role', 'assigned_role.user_id', '=', 'user.id')
            ->join('roles as role', 'role.id', '=', 'assigned_role.role_id')
            ->where('user.empresa_id', 1)
            ->whereIn('role.name', (array)config('turnos.turn_selection_locked_roles', array()))
            ->value('user.id');
        $this->assertGreaterThan(0, (int)$cashierId);

        app(TurnoConfigurationService::class)->configure(array(
            'core_empresa_id' => 1,
            'modulo' => 'tesoreria',
            'contexto_tipo' => 'pdv',
            'contexto_id' => 1,
            'modo' => TurnoConfiguracion::MODO_TURNOS,
        ));
        $manager = app(TurnoManager::class);
        $closed = $manager->openContext(
            1, 'tesoreria', 'pdv', 1, '2099-09-02', (int)$cashierId, 0, '2099-09-02 06:00:00'
        );
        $manager->close($closed, (int)$cashierId, 0, 'Traslado de efectivo', '2099-09-02 14:00:00');

        // Aunque haya comenzado otro turno, el traslado pertenece al último
        // cerrado, no a la nueva operación de caja.
        $manager->openContext(
            1, 'tesoreria', 'pdv', 1, '2099-09-02', (int)$cashierId, 0, '2099-09-02 14:05:00'
        );

        $this->be(User::find($cashierId));
        app('request')->replace(array());
        $model = (object)array('name_space' => TesoDocEncabezadoTraslado::class);
        $field = $this->turnField(app(TurnoFormService::class)->decorate($model, null, 'create', array()));

        $this->assertNotNull($field);
        $this->assertSame('select', $field['tipo']);
        $this->assertSame((int)$closed->id, (int)$field['value']);
        $this->assertSame(array($closed->id), array_keys($field['opciones']));
        $this->assertArrayHasKey('disabled', $field['atributos']);
        $this->assertSame(
            TesoDocEncabezadoTraslado::POST_CLOSING_OPERATION,
            $field['atributos']['data-turno-operation']
        );

        $this->call('POST', '/turnos/operativos/validar-seleccion', array(
            'modulo' => 'tesoreria',
            'turno_operativo_id' => $closed->id,
            'operacion_turno' => TesoDocEncabezadoTraslado::POST_CLOSING_OPERATION,
        ));
        $this->assertResponseOk();
        $this->assertContains('LAST_CLOSED_CASHIER', $this->response->getContent());

        app('request')->replace(array('turno_operativo_id' => $closed->id));
        $document = new TesoDocEncabezadoTraslado(array(
            'core_empresa_id' => 1,
            'turno_operativo_id' => $closed->id,
        ));
        $this->assertTrue($document->allowsHistoricalTurnoAssignment());
    }

    public function test_arqueo_carga_el_ultimo_turno_cerrado_de_la_caja_y_pdv_aunque_sea_de_otra_fecha()
    {
        $user = $this->authenticateCompanyUser();
        $pdv = Pdv::where('core_empresa_id', 1)->whereNotNull('caja_default_id')->first();
        $this->assertNotNull($pdv);

        app(TurnoConfigurationService::class)->configure(array(
            'core_empresa_id' => 1,
            'modulo' => 'tesoreria',
            'contexto_tipo' => 'pdv',
            'contexto_id' => $pdv->id,
            'modo' => TurnoConfiguracion::MODO_TURNOS,
        ));
        $manager = app(TurnoManager::class);
        $turn = $manager->openContext(
            1,
            'tesoreria',
            'pdv',
            (int)$pdv->id,
            '2099-09-01',
            (int)$user->id,
            50000,
            '2099-09-01 22:00:00'
        );
        DB::table('core_turnos_operativos')->where('id', $turn->id)
            ->update(array('teso_caja_id' => (int)$pdv->caja_default_id));
        $manager->close($turn, (int)$user->id, 75000, 'Cierre para arqueo', '2099-09-02 06:00:00');

        $resolved = app(CashCountTurnService::class)->latestClosed(
            1,
            (int)$pdv->id,
            (int)$pdv->caja_default_id
        );
        $this->assertNotNull($resolved);
        $this->assertSame((int)$turn->id, (int)$resolved->id);

        $this->call('GET', '/tesoreria/get_turnos_pdv_fecha', array(
            'pdv_id' => $pdv->id,
            'teso_caja_id' => $pdv->caja_default_id,
            'fecha' => '2099-09-02',
            'preferir_ultimo_cerrado' => 1,
        ));
        $this->assertResponseOk();
        $payload = json_decode($this->response->getContent(), true);
        $this->assertSame('TURNOS', $payload['mode']);
        $this->assertSame((int)$turn->id, (int)$payload['range']['id']);
        $this->assertSame('2099-09-01', $payload['range']['operational_date']);
        $this->assertSame('2099-09-02 06:00:00', $payload['range']['closing_at']);
    }

    public function test_arqueo_de_cajero_bloquea_y_normaliza_al_ultimo_turno_que_cerro()
    {
        $this->authenticateCompanyUser();
        $cashierId = DB::table('users as user')
            ->join('user_has_roles as assigned_role', 'assigned_role.user_id', '=', 'user.id')
            ->join('roles as role', 'role.id', '=', 'assigned_role.role_id')
            ->where('user.empresa_id', 1)
            ->whereIn('role.name', (array)config('turnos.turn_selection_locked_roles', array()))
            ->value('user.id');
        $this->assertGreaterThan(0, (int)$cashierId);

        $pdv = Pdv::where('core_empresa_id', 1)->whereNotNull('caja_default_id')->first();
        $this->assertNotNull($pdv);
        app(TurnoConfigurationService::class)->configure(array(
            'core_empresa_id' => 1,
            'modulo' => 'tesoreria',
            'contexto_tipo' => 'pdv',
            'contexto_id' => $pdv->id,
            'modo' => TurnoConfiguracion::MODO_TURNOS,
        ));

        $manager = app(TurnoManager::class);
        $anterior = $manager->openContext(
            1, 'tesoreria', 'pdv', (int)$pdv->id, '2099-09-03', (int)$cashierId, 0, '2099-09-03 06:00:00'
        );
        DB::table('core_turnos_operativos')->where('id', $anterior->id)
            ->update(array('teso_caja_id' => (int)$pdv->caja_default_id));
        $manager->close($anterior, (int)$cashierId, 0, 'Primer cierre', '2099-09-03 14:00:00');

        $ultimo = $manager->openContext(
            1, 'tesoreria', 'pdv', (int)$pdv->id, '2099-09-04', (int)$cashierId, 0, '2099-09-04 06:00:00'
        );
        DB::table('core_turnos_operativos')->where('id', $ultimo->id)
            ->update(array('teso_caja_id' => (int)$pdv->caja_default_id));
        $manager->close($ultimo, (int)$cashierId, 0, 'Último cierre', '2099-09-04 14:00:00');

        $this->be(User::find($cashierId));
        $this->call('GET', '/tesoreria/get_turnos_pdv_fecha', array(
            'pdv_id' => $pdv->id,
            'teso_caja_id' => $pdv->caja_default_id,
            'fecha' => '2099-09-03',
        ));
        $this->assertResponseOk();
        $payload = json_decode($this->response->getContent(), true);
        $this->assertTrue($payload['selection_locked']);
        $this->assertCount(1, $payload['shifts']);
        $this->assertSame((int)$ultimo->id, (int)$payload['range']['id']);

        // Aunque el navegador envíe otro turno, el servidor conserva como
        // autoridad el último cierre del cajero para esta caja y PDV.
        $request = \Illuminate\Http\Request::create('/', 'POST', array(
            'pdv_id' => $pdv->id,
            'teso_caja_id' => $pdv->caja_default_id,
            'turno_operativo_id' => $anterior->id,
        ));
        (new \App\Tesoreria\ArqueoCaja())->validar_datos_creacion($request, new \stdClass());
        $this->assertSame((int)$ultimo->id, (int)$request->input('turno_operativo_id'));
        $this->assertSame('2099-09-04', $request->input('fecha'));
    }

    public function test_referencia_visual_muestra_turno_persistido_y_oculta_fk_nula()
    {
        $user = $this->authenticateCompanyUser();
        DB::table('core_turnos_operativos')->where('core_empresa_id', 1)
            ->where('estado', TurnoOperativo::ESTADO_ABIERTO)
            ->update(array('estado' => TurnoOperativo::ESTADO_CERRADO, 'cerrado_en' => '2026-08-28 07:59:59'));
        DB::table('vtas_pos_puntos_de_ventas')->where('id', 1)->update(array('estado' => 'Cerrado'));
        app(TurnoConfigurationService::class)->configure(array(
            'core_empresa_id' => 1, 'modulo' => 'tesoreria', 'contexto_tipo' => 'pdv',
            'contexto_id' => 1, 'modo' => TurnoConfiguracion::MODO_TURNOS,
        ));
        $turn = app(TurnoManager::class)->openContext(
            1, 'tesoreria', 'pdv', 1, '2026-08-29', $user->id, 0, '2026-08-29 08:00:00'
        );
        $document = TesoDocEncabezado::where('core_empresa_id', 1)->first();
        $this->assertNotNull($document);
        DB::table('teso_doc_encabezados')->where('id', $document->id)
            ->update(array('turno_operativo_id' => $turn->id));

        // Simula las proyecciones históricas de impresión que no seleccionaban la FK.
        $projected = TesoDocEncabezado::select('id', 'core_empresa_id')->find($document->id);
        $html = View::make('core.turnos.reference', array('documento' => $projected))->render();
        $this->assertContains('Turno operativo:', $html);
        $this->assertContains($turn->codigo, $html);
        $this->assertContains('Fecha operativa:', $html);
        $this->assertContains('PDV 1', $html);

        DB::table('teso_doc_encabezados')->where('id', $document->id)
            ->update(array('turno_operativo_id' => null));
        $withoutTurn = TesoDocEncabezado::select('id', 'core_empresa_id')->find($document->id);
        $this->assertSame('', trim(View::make('core.turnos.reference', array('documento' => $withoutTurn))->render()));
    }

    public function test_plantilla_de_creacion_pos_renderiza_sin_documento_persistido()
    {
        $empresa = Empresa::find(1);
        $pdv = Pdv::where('core_empresa_id', 1)->first();

        $this->assertNotNull($empresa);
        $this->assertNotNull($pdv);

        // La pantalla create todavía no tiene encabezado persistido. El turno se
        // selecciona en el formulario y sólo se imprime después de guardar.
        $pdv->plantilla_factura_pos_default = 'plantilla_factura_3';
        $html = (new FacturaPosService())->generar_plantilla_factura($pdv, $empresa);

        $this->assertContains('<html', $html);
        $this->assertNotContains('Turno operativo:', $html);
    }

    public function test_validacion_ajax_de_turnos_preserva_los_manejadores_y_lineas_del_formulario()
    {
        $script = file_get_contents(dirname(base_path()) . '/assets/js/core/turno_transacciones.js');
        $inventoryView = file_get_contents(resource_path('views/inventarios/inventario_fisico/create.blade.php'));

        $this->assertContains("button.id !== 'bs_boton_guardar'", $script);
        $this->assertContains("button.id !== 'btn_guardar'", $script);
        $this->assertContains("triggerHandler('click')", $script);
        $this->assertContains('cash_transfer_after_closing', $script);
        $this->assertContains("event.stopImmediatePropagation()", $script);
        $this->assertNotContains("addClass('disabled')", $script);
        $this->assertContains('data-turno-domain-valid', $inventoryView);
        $this->assertContains('data-turno-domain-message', $inventoryView);
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
            ->select('field.id', 'field.tipo', 'field.descripcion')
            ->first();
    }
}
