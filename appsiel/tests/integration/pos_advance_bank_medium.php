<?php
// Ejecutar directamente con PHP. Dobles en memoria; no carga .env ni conecta a BD.
namespace Illuminate\Support\Facades { class Auth { public static function check() { return false; } } }
namespace App\Tesoreria {
    class TesoDocEncabezado { public function transacciones_adicionales($a,$b,$c) {} }
    class FixtureQuery {
        private $class; private $id;
        public function __construct($class,$id) { $this->class=$class; $this->id=$id; }
        public function where($key,$value) { return $this; }
        public function first() { $class=$this->class; return $class::find($this->id); }
    }
    class TesoMotivo {
        public static function find($id) { return (object)['id'=>$id,'movimiento'=>'entrada','teso_tipo_motivo'=>'recaudo-cartera']; }
        public static function where($key,$id) { return new FixtureQuery(__CLASS__,$id); }
    }
    class TesoCuentaBancaria {
        public static function find($id) { return (object)['id'=>$id,'contab_cuenta_id'=>123]; }
        public static function where($key,$id) { return new FixtureQuery(__CLASS__,$id); }
    }
    class TesoMedioRecaudo {
        public $id; public $estado='Activo'; public $comportamiento='Tarjeta bancaria'; public $descripcion;
        public static function get_id_por_tipo_registro($type) { return 4; }
        public static function find($id) {
            if (!in_array($id,[1,2,4])) return null;
            $m=new self; $m->id=$id; $m->descripcion=$id==2?'Tar. DB/CR':'QR/Transf';
            if ($id==1) $m->comportamiento='Efectivo';
            return $m;
        }
        public function tiene_cuenta_bancaria_destino($id) { return $this->id==2 ? $id==3 : in_array($id,[1,5]); }
    }
    class TesoDocRegistro { public static function create($data) {} }
    class TesoMovimiento {
        public static $rows=[];
        public static function create($data) { self::$rows[]=$data; }
    }
}
namespace App\Contabilidad { class ContabMovimiento { public function contabilizar_linea_registro($a,$b,$c,$d,$e) {} } }
namespace {
    function config($key) {
        if ($key==='tesoreria.tipo_operacion_recaudos_cxc') return 'recaudo-cartera';
        if ($key==='tesoreria.recaudos_cxc_motivo_id') return 9;
        return 0;
    }
    require __DIR__.'/../../app/VentasPos/Services/TreasuryService.php';
    require __DIR__.'/../../app/Tesoreria/RegistroDeTransferenciaConsignacion.php';
    function check($ok,$message) { if (!$ok) throw new \RuntimeException($message); }
    $service=new \App\VentasPos\Services\TreasuryService;
    $input='[{"teso_medio_recaudo_id":"0-Anticipo","valor":"$100000"},'
        .'{"teso_medio_recaudo_id":"2-Tarjeta bancaria","teso_motivo_id":"70-Venta","teso_caja_id":"0-","teso_cuenta_bancaria_id":"3-Banco","valor":"$584800"}]';
    $result=$service->get_lineas_registros_medios_recaudos($input);
    check($result->valor_abono==584800,'No debe cobrar nuevamente el anticipo');
    $json='['.$result->lineas_registros_transferencia_consignacion.']';
    $rows=json_decode($json);
    check($rows[0]->teso_medio_recaudo_id==2,'Conservar tarjeta');
    $doc=new class { public $id=10; public $core_empresa_id=1; public function toArray() { return ['core_empresa_id'=>1]; } };
    $writer=new \App\Tesoreria\RegistroDeTransferenciaConsignacion;
    $writer->almacenar_registros($json,$doc,1);
    $stored=\App\Tesoreria\TesoMovimiento::$rows[0];
    check($stored['teso_medio_recaudo_id']==2 && $stored['teso_cuenta_bancaria_id']==3 && $stored['valor_movimiento']==584800 && $stored['pdv_id']==1,'Movimiento tarjeta');
    $rows[0]->teso_medio_recaudo_id=4;
    try { $writer->almacenar_registros(json_encode($rows),$doc,1); throw new \RuntimeException('Aceptó cuenta no asociada'); }
    catch (\InvalidArgumentException $e) { check(count(\App\Tesoreria\TesoMovimiento::$rows)==1,'No escribir movimiento inválido'); }
    unset($rows[0]->teso_medio_recaudo_id);
    $rows[0]->banco_id_transferencia_consignacion=1;
    $writer->almacenar_registros(json_encode($rows),$doc,1);
    check(\App\Tesoreria\TesoMovimiento::$rows[1]['teso_medio_recaudo_id']==4,'Compatibilidad transferencia tradicional');
    foreach ([0,1,999] as $id) {
        try { $writer::resolverMedioBancario((object)['teso_medio_recaudo_id'=>$id]); throw new \RuntimeException('Aceptó medio inválido'); }
        catch (\InvalidArgumentException $e) {}
    }
    $cash=$service->get_lineas_registros_medios_recaudos(str_replace(['2-Tarjeta bancaria','"teso_caja_id":"0-"'],['1-Efectivo','"teso_caja_id":"1-Caja"'],$input));
    check(json_decode('['.$cash->lineas_registros_efectivo.']')[0]->valor_efectivo==584800,'Efectivo con anticipo');
    check($service->crear_abonos_documento($doc,'[{"teso_medio_recaudo_id":"0-Anticipo","valor":"$100000"}]')===false,'Solo anticipo');
    echo "OK: anticipo + tarjeta, destino inválido, transferencia tradicional, medios inválidos, efectivo y solo anticipo.\n";
}
