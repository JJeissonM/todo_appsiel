<?php
// Prueba aislada: php tests/integration/pos_order_document_type.php
// No inicia Laravel ni conecta a bases de datos o servicios externos.
namespace Illuminate\Http {
    class Request {
        public $core_tipo_transaccion_id;
        public $core_tipo_doc_app_id;
        public $lineas_registros = '[]';
        public $url_id_modelo = 175;
    }
}
namespace App\Http\Controllers\Core {
    class TransaccionController {
        public function crear_encabezado_documento($request, $modelo) {
            throw new \RuntimeException('VALIDACION_SUPERADA');
        }
    }
}
namespace App\Sistema {
    class TipoTransaccion {
        public $id;
        public static function find($id) {
            if (!in_array($id, [42, 60, 47])) return null;
            $model = new self;
            $model->id = $id;
            return $model;
        }
        public function tipos_documentos() { return new Documentos; }
    }
    class Documentos {
        private $id;
        public function where($column, $id) {
            if ($column !== 'core_tipos_docs_apps.id') throw new \RuntimeException('Columna incorrecta');
            $this->id = $id;
            return $this;
        }
        public function exists() { return $this->id === 41; }
    }
}
namespace {
    function response() {
        return new class {
            public function json($data, $status) { return ['data' => $data, 'status' => $status]; }
        };
    }
    require __DIR__ . '/../../app/Http/Controllers/VentasPos/PedidosPosController.php';
    $controller = new \App\Http\Controllers\VentasPos\PedidosPosController;
    $cases = [[42, 41, true], [60, 41, true], [42, 52, false], [60, 52, false], [47, 41, false], [999, 41, false], [42, 0, false]];
    foreach ($cases as $case) {
        $request = new \Illuminate\Http\Request;
        $request->core_tipo_transaccion_id = $case[0];
        $request->core_tipo_doc_app_id = $case[1];
        $allowed = false;
        try {
            $result = $controller->store($request);
            if ($result['status'] !== 422) throw new \RuntimeException('Se esperaba 422');
        } catch (\RuntimeException $e) {
            if ($e->getMessage() !== 'VALIDACION_SUPERADA') throw $e;
            $allowed = true;
        }
        if ($allowed !== $case[2]) throw new \RuntimeException('Resultado incorrecto: ' . json_encode($case));
    }
    echo "OK: 7 casos; pedidos válidos llegan a creación; documentos ajenos se rechazan antes de escribir.\n";
}
