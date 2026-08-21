<?php

use App\Nomina\NovedadTnl;
use App\Http\Controllers\Nomina\NovedadesTnlController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NovedadTnlHorasTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        config(['database.connections.novedad_tnl_horas_testing' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => ''
        ]]);
        DB::setDefaultConnection('novedad_tnl_horas_testing');

        Schema::create('nom_parametros_legales', function (Blueprint $table) {
            $table->increments('id');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->decimal('horas_laborales', 8, 3);
            $table->string('estado');
        });
        Schema::create('nom_novedades_tnl', function (Blueprint $table) {
            $table->increments('id');
            $table->date('fecha_inicial_tnl');
            $table->date('fecha_final_tnl');
            $table->decimal('cantidad_dias_tnl', 10, 6);
            $table->decimal('cantidad_horas_tnl', 10, 6);
            $table->timestamps();
        });

        DB::table('nom_parametros_legales')->insert([
            [
                'fecha_inicio' => '2026-01-01',
                'fecha_fin' => '2026-07-14',
                'horas_laborales' => 220,
                'estado' => 'Activo'
            ],
            [
                'fecha_inicio' => '2026-07-15',
                'fecha_fin' => '2026-12-31',
                'horas_laborales' => 210,
                'estado' => 'Activo'
            ]
        ]);
    }

    /** @test */
    public function calcula_las_horas_proporcionalmente_sobre_treinta_dias()
    {
        $this->assertSame(210.0, NovedadTnl::calcular_horas_tnl(30, '2026-07-30'));
        $this->assertSame(105.0, NovedadTnl::calcular_horas_tnl(15, '2026-07-30'));
        $this->assertSame(7.0, NovedadTnl::calcular_horas_tnl(1, '2026-07-30'));
        $this->assertSame(110.0, NovedadTnl::calcular_horas_tnl(15, '2026-07-10'));
    }

    /** @test */
    public function el_endpoint_devuelve_el_mismo_calculo_para_la_interfaz()
    {
        $respuesta = (new NovedadesTnlController)->calcular_horas('2026-07-30', 30);
        $datos = json_decode($respuesta->getContent(), true);

        $this->assertSame(210.0, (float) $datos['horas_laborales_mes']);
        $this->assertSame(210.0, (float) $datos['cantidad_horas_tnl']);
    }

    /** @test */
    public function reemplaza_el_valor_enviado_al_crear_por_el_calculo_legal()
    {
        $novedad = NovedadTnl::create([
            'fecha_inicial_tnl' => '2026-07-01',
            'fecha_final_tnl' => '2026-07-30',
            'cantidad_dias_tnl' => 30,
            'cantidad_horas_tnl' => 240
        ]);

        $this->assertSame(210.0, (float) $novedad->cantidad_horas_tnl);
        $this->assertSame(210.0, (float) DB::table('nom_novedades_tnl')->where('id', $novedad->id)->value('cantidad_horas_tnl'));
    }

    /** @test */
    public function recalcula_las_horas_cuando_se_actualiza_la_novedad()
    {
        $novedad = NovedadTnl::create([
            'fecha_inicial_tnl' => '2026-07-01',
            'fecha_final_tnl' => '2026-07-10',
            'cantidad_dias_tnl' => 10,
            'cantidad_horas_tnl' => 80
        ]);

        $this->assertEquals(73.333333, (float) $novedad->cantidad_horas_tnl, '', 0.000001);

        $novedad->fecha_final_tnl = '2026-07-30';
        $novedad->cantidad_dias_tnl = 10;
        $novedad->cantidad_horas_tnl = 999;
        $novedad->save();

        $this->assertSame(70.0, (float) $novedad->cantidad_horas_tnl);
    }
}
