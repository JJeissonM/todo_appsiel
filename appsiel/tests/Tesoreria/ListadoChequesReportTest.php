<?php

use App\Http\Controllers\Tesoreria\ReporteController;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ListadoChequesReportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_filtra_por_fecha_tercero_y_empresa()
    {
        $user = User::findOrFail(1);
        Auth::login($user);

        $terceroEmpresa = DB::table('core_terceros')
            ->where('core_empresa_id', $user->empresa_id)
            ->first();
        $otraEmpresaId = $this->duplicarRegistro('core_empresas', 'Empresa aislada para reporte de cheques');
        $terceroOtraEmpresaId = $this->duplicarRegistro(
            'core_terceros',
            'Tercero de otra empresa',
            array('core_empresa_id' => $otraEmpresaId)
        );

        $this->assertNotNull($terceroEmpresa);

        $this->crearCheque($terceroEmpresa->id, '2099-11-05', 'CHEQUE-INCLUIDO', 125000);
        $this->crearCheque($terceroEmpresa->id, '2099-10-31', 'CHEQUE-FUERA-FECHA', 50000);
        $this->crearCheque($terceroOtraEmpresaId, '2099-11-05', 'CHEQUE-OTRA-EMPRESA', 75000);

        $request = new Request(array(
            'teso_cheques_fecha_desde' => '2099-11-01',
            'teso_cheques_fecha_hasta' => '2099-11-30',
            'teso_cheques_tercero_id' => $terceroEmpresa->id,
            'reporte_instancia' => json_encode(array('id' => 99999)),
        ));

        $html = (new ReporteController())->teso_listado_cheques($request);

        $this->assertContains('CHEQUE-INCLUIDO', $html);
        $this->assertNotContains('CHEQUE-FUERA-FECHA', $html);
        $this->assertNotContains('CHEQUE-OTRA-EMPRESA', $html);
        $this->assertContains('125.000,00', $html);
        $this->assertContains('>Identificación</th>', $html);
        $this->assertNotContains('>Cobro</th>', $html);
        $this->assertNotContains('>Fuente</th>', $html);
        $this->assertNotContains('>Referencia</th>', $html);
        $this->assertNotContains('>Banco</th>', $html);
        $this->assertNotContains('>Doc. consumo</th>', $html);
        $this->assertContains('enlace_show_documento/', $html);

        $requestSinTercero = new Request(array(
            'teso_cheques_fecha_desde' => '2099-11-01',
            'teso_cheques_fecha_hasta' => '2099-11-30',
            'teso_cheques_tercero_id' => '',
            'reporte_instancia' => json_encode(array('id' => 99998)),
        ));
        $htmlSinTercero = (new ReporteController())->teso_listado_cheques($requestSinTercero);

        $this->assertContains('CHEQUE-INCLUIDO', $htmlSinTercero);
        $this->assertNotContains('CHEQUE-OTRA-EMPRESA', $htmlSinTercero);
    }

    public function test_rechaza_un_rango_invertido()
    {
        Auth::login(User::findOrFail(1));

        $request = new Request(array(
            'teso_cheques_fecha_desde' => '2099-11-30',
            'teso_cheques_fecha_hasta' => '2099-11-01',
        ));

        $html = (new ReporteController())->teso_listado_cheques($request);

        $this->assertContains('Fecha desde no puede ser posterior', $html);
    }

    protected function crearCheque($terceroId, $fecha, $numero, $valor)
    {
        $entidadId = (int)DB::table('teso_entidades_financieras')->value('id');
        $tipoDocumentoId = (int)DB::table('core_tipos_docs_apps')->value('id');
        $tipoTransaccionId = (int)DB::table('sys_tipos_transacciones')->value('id');

        return DB::table('teso_control_cheques')->insertGetId(array(
            'fuente' => 'propio',
            'tercero_id' => $terceroId,
            'fecha_emision' => $fecha,
            'fecha_cobro' => $fecha,
            'numero_cheque' => $numero,
            'referencia_cheque' => '',
            'entidad_financiera_id' => $entidadId,
            'valor' => $valor,
            'detalle' => 'Cheque de prueba',
            'creado_por' => 'tests@appsiel.test',
            'modificado_por' => '',
            'core_tipo_transaccion_id_origen' => $tipoTransaccionId,
            'core_tipo_doc_app_id_origen' => $tipoDocumentoId,
            'consecutivo' => 1,
            'core_tipo_transaccion_id_consumo' => 0,
            'core_tipo_doc_app_id_consumo' => 0,
            'consecutivo_doc_consumo' => 0,
            'teso_caja_id' => 0,
            'tipo' => 'cheque_propio',
            'estado' => 'Emitido',
            'created_at' => $fecha . ' 08:00:00',
            'updated_at' => $fecha . ' 08:00:00',
        ));
    }

    protected function duplicarRegistro($tabla, $descripcion, array $reemplazos = array())
    {
        $datos = (array)DB::table($tabla)->first();
        $this->assertNotEmpty($datos);
        unset($datos['id']);

        if (array_key_exists('descripcion', $datos)) {
            $datos['descripcion'] = $descripcion;
        }
        if (array_key_exists('numero_identificacion', $datos)) {
            $datos['numero_identificacion'] = uniqid('test-cheques-');
        }
        if (array_key_exists('email', $datos)) {
            $datos['email'] = uniqid('test-cheques-') . '@appsiel.test';
        }

        return (int)DB::table($tabla)->insertGetId(array_merge($datos, $reemplazos));
    }
}
