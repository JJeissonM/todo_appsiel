<?php

use App\CxC\CxcAbono;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

class CxcAbonoCruceStateTest extends PHPUnit_Framework_TestCase
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

        $this->db->schema()->create('cxc_abonos', function (Blueprint $table) {
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

        $this->db->schema()->create('cxc_doc_encabezados', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_empresa_id');
            $table->integer('core_tercero_id');
            $table->integer('core_tipo_transaccion_id');
            $table->integer('core_tipo_doc_app_id');
            $table->integer('consecutivo');
            $table->string('estado');
        });
    }

    public function test_un_cruce_activo_sigue_bloqueando_la_anulacion_del_recaudo()
    {
        $documento = $this->crearEscenario('Activo');

        $this->assertTrue(CxcAbono::estaEnCruceActivo($documento));
        $this->assertSame(1, CxcAbono::count());
    }

    public function test_un_cruce_anulado_depura_la_referencia_y_desbloquea_el_recaudo()
    {
        $documento = $this->crearEscenario('Anulado');

        $this->assertFalse(CxcAbono::estaEnCruceActivo($documento));
        $this->assertSame(0, CxcAbono::count());
    }

    protected function crearEscenario($estadoCruce)
    {
        $documento = (object)[
            'core_empresa_id' => 7,
            'core_tercero_id' => 11,
            'core_tipo_transaccion_id' => 20,
            'core_tipo_doc_app_id' => 230,
            'consecutivo' => 15,
        ];

        $this->db->table('cxc_doc_encabezados')->insert([
            'core_empresa_id' => 7,
            'core_tercero_id' => 11,
            'core_tipo_transaccion_id' => 39,
            'core_tipo_doc_app_id' => 170,
            'consecutivo' => 8,
            'estado' => $estadoCruce,
        ]);

        $this->db->table('cxc_abonos')->insert([
            'core_empresa_id' => 7,
            'core_tercero_id' => 11,
            'core_tipo_transaccion_id' => 20,
            'core_tipo_doc_app_id' => 230,
            'consecutivo' => 15,
            'doc_cruce_transacc_id' => 39,
            'doc_cruce_tipo_doc_id' => 170,
            'doc_cruce_consecutivo' => 8,
        ]);

        return $documento;
    }
}
