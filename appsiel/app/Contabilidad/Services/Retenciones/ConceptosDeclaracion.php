<?php

namespace App\Contabilidad\Services\Retenciones;

/** Estructura oficial del formulario 350 de 2026. No se infieren conceptos por tarifa. */
class ConceptosDeclaracion
{
    const FUENTE = 'https://www.dian.gov.co/atencionciudadano/formulariosinstructivos/Formularios/2026/Formulario_350_2026.pdf';

    public static function catalogo()
    {
        // Código => etiqueta, base PJ, retención PJ, base PN, retención PN.
        return [
            'trabajo' => ['Rentas de trabajo', null, null, 77, 93],
            'pensiones' => ['Rentas de pensiones', null, null, 78, 94],
            'honorarios' => ['Honorarios', 29, 42, 79, 95],
            'comisiones' => ['Comisiones', 30, 43, 80, 96],
            'servicios' => ['Servicios', 31, 44, 81, 97],
            'rendimientos' => ['Rendimientos financieros e intereses', 32, 45, 82, 98],
            'arrendamientos' => ['Arrendamientos', 33, 46, 83, 99],
            'regalias' => ['Regalías y propiedad intelectual', 34, 47, 84, 100],
            'dividendos' => ['Dividendos y participaciones', 35, 48, 85, 101],
            'compras' => ['Compras', 36, 49, 86, 102],
            'tarjetas' => ['Transacciones con tarjetas', 37, 50, 87, 103],
            'construccion' => ['Contratos de construcción', 38, 51, 88, 104],
            'activos_fijos' => ['Enajenación de activos fijos de personas naturales', null, null, 89, 105],
            'loterias' => ['Loterías, rifas, apuestas y similares', 39, 52, 90, 106],
            'mineria' => ['Hidrocarburos, carbón y productos mineros', 40, 53, 91, 107],
            'otros' => ['Otros pagos sujetos a retención', 41, 54, 92, 108],
            'exterior_sin_convenio' => ['Pagos al exterior sin convenio', 55, 57, 109, 111],
            'exterior_con_convenio' => ['Pagos al exterior con convenio', 56, 58, 110, 112],
            'iva_responsables' => ['IVA a responsables del impuesto', null, 131, null, 131],
            'iva_no_residentes' => ['IVA por servicios de no residentes / no domiciliados', null, 132, null, 132],
            'devolucion_fuente' => ['Retenciones de renta en exceso, indebidas u operaciones anuladas', null, 129, null, 129],
            'devolucion_iva' => ['Retenciones de IVA en exceso, indebidas u operaciones anuladas', null, 133, null, 133],
            'ica' => ['Retención de ICA (declaración municipal)', null, null, null, null]
        ];
    }

    public static function opciones()
    {
        $opciones = ['' => 'Sin clasificar'];
        foreach (static::catalogo() as $codigo => $concepto) {
            $opciones[$codigo] = $concepto[0];
        }
        return $opciones;
    }

    public static function casillas($codigo, $persona, $anio)
    {
        if ((int)$anio !== 2026 || !isset(static::catalogo()[$codigo])) {
            return [null, null];
        }
        $dato = static::catalogo()[$codigo];
        if (strpos($codigo, 'iva_') === 0 || strpos($codigo, 'devolucion_') === 0) {
            return [$dato[1], $dato[2]];
        }
        if (!in_array($persona, ['natural', 'juridica'])) {
            return [null, null];
        }
        return $persona === 'natural' ? [$dato[3], $dato[4]] : [$dato[1], $dato[2]];
    }

    public static function persona($tipo)
    {
        $tipo = str_slug(trim((string)$tipo));
        if (in_array($tipo, ['persona-natural', 'natural'])) { return 'natural'; }
        if (in_array($tipo, ['persona-juridica', 'juridica'])) { return 'juridica'; }
        return 'sin_clasificar';
    }
}
