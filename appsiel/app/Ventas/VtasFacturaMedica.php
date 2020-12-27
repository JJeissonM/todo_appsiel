<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use App\Ventas\DocEncabezadoTieneFormulaMedica;

class VtasFacturaMedica extends VtasDocEncabezado
{
    protected $table = 'vtas_doc_encabezados';

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Paciente', 'Detalle', 'Valor total', 'Estado'];

    public $vistas = '{}';

    public $urls_acciones = '{"create":"factura_medica/create","show":"factura_medica/id_fila","store":"ventas"}';

    public static function consultar_registros($nro_registros)
    {
        $core_tipo_transaccion_id = 44; // Facturas Médicas
        return VtasFacturaMedica::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
            ->where('vtas_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('vtas_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'vtas_doc_encabezados.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS campo2'),
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                'vtas_doc_encabezados.descripcion AS campo4',
                'vtas_doc_encabezados.valor_total AS campo5',
                'vtas_doc_encabezados.estado AS campo6',
                'vtas_doc_encabezados.id AS campo7'
            )
            ->orderBy('vtas_doc_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
        /*
                    ->leftJoin('vtas_doc_registros', 'vtas_doc_registros.vtas_doc_encabezado_id', '=', 'vtas_doc_encabezados.id')
                                DB::raw( 'SUM(vtas_doc_registros.precio_total) AS campo5' ),
                    */
    }

    // Solo se creó un registro vacío en la tabla clientes
    public function store_adicional($datos, $doc_encabezado)
    {

        // Se asocia la formula seleccionada a la factura de ventas
        if (isset($datos['formula_id'])) {
            DocEncabezadoTieneFormulaMedica::create(
                [
                    'vtas_doc_encabezado_id' => $doc_encabezado->id,
                    'formula_medica_id' => $datos['formula_id']
                ]
            );
        }

        // Cuando el cliente no es un paciente, se almacenan sus datos de formula médica en un campo
        if ($datos['no_es_paciente']) {
            $cadena = '{"esfera_ojo_derecho":"' . $datos['esfera_ojo_derecho'] . '", "cilindro_ojo_derecho":"' . $datos['cilindro_ojo_derecho'] . '", "eje_ojo_derecho":"' . $datos['eje_ojo_derecho'] . '", "adicion_ojo_derecho":"' . $datos['adicion_ojo_derecho'] . '", "agudeza_visual_ojo_derecho":"' . $datos['agudeza_visual_ojo_derecho'] . '", "distancia_pupilar_ojo_derecho":"' . $datos['distancia_pupilar_ojo_derecho'] . '", "esfera_ojo_izquierdo":"' . $datos['esfera_ojo_izquierdo'] . '", "cilindro_ojo_izquierdo":"' . $datos['cilindro_ojo_izquierdo'] . '", "eje_ojo_izquierdo":"' . $datos['eje_ojo_izquierdo'] . '", "adicion_ojo_izquierdo":"' . $datos['adicion_ojo_izquierdo'] . '", "agudeza_visual_ojo_izquierdo":"' . $datos['agudeza_visual_ojo_izquierdo'] . '", "distancia_pupilar_ojo_izquierdo":"' . $datos['distancia_pupilar_ojo_izquierdo'] . '"}';

            DocEncabezadoTieneFormulaMedica::create(
                [
                    'vtas_doc_encabezado_id' => $doc_encabezado->id,
                    'formula_medica_id' => 0,
                    'contenido_formula' => $cadena
                ]
            );
        }
    }
}
