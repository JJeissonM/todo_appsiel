<?php

use App\CxP\CxpAbono;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

class CxpAbonoCruceStateTest extends PHPUnit_Framework_TestCase
{
    protected $db;

    protected function setUp()
    {
        parent::setUp();

        $this->db = new Capsule();
        $this->db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $this->db->setAsGlobal();
        $this->db->bootEloquent();

        $this->db->schema()->create('cxp_abonos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_empresa_id');
            $table->integer('core_tercero_id');
            $table->integer('core_tipo_transaccion_id');
            $table->integer('core_tipo_doc_app_id');
            $table->integer('consecutivo');
            $table->integer('doc_cruce_transacc_id');
            $table->integer('doc_cruce_tipo_doc_id');
            $table->integer('doc_cruce_consecutivo');
        });

        $this->db->schema()->create('cxp_doc_encabezados', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_empresa_id');
            $table->integer('core_tipo_transaccion_id');
            $table->integer('core_tipo_doc_app_id');
            $table->integer('consecutivo');
            $table->string('estado');
        });
    }

    public function test_un_cruce_activo_sigue_bloqueando_la_anulacion_del_pago()
    {
        $documento = $this->crearEscenario('Activo');

        $this->assertTrue(CxpAbono::estaEnCruceActivo($documento));
        $this->assertSame(1, CxpAbono::count());
    }

    public function test_un_cruce_anulado_depura_la_referencia_y_desbloquea_el_pago()
    {
        $documento = $this->crearEscenario('Anulado');

        $this->assertFalse(CxpAbono::estaEnCruceActivo($documento));
        $this->assertSame(0, CxpAbono::count());
    }

    protected function crearEscenario($estadoCruce)
    {
        $documento = (object)[
            'core_empresa_id' => 7,
            'core_tercero_id' => 11,
            'core_tipo_transaccion_id' => 33,
            'core_tipo_doc_app_id' => 240,
            'consecutivo' => 15,
        ];

        $this->db->table('cxp_doc_encabezados')->insert([
            'core_empresa_id' => 7,
            'core_tipo_transaccion_id' => 39,
            'core_tipo_doc_app_id' => 170,
            'consecutivo' => 8,
            'estado' => $estadoCruce,
        ]);

        $this->db->table('cxp_abonos')->insert([
            'core_empresa_id' => 7,
            'core_tercero_id' => 19,
            'core_tipo_transaccion_id' => 33,
            'core_tipo_doc_app_id' => 240,
            'consecutivo' => 15,
            'doc_cruce_transacc_id' => 39,
            'doc_cruce_tipo_doc_id' => 170,
            'doc_cruce_consecutivo' => 8,
        ]);

        return $documento;
    }
}
