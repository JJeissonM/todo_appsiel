<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ComprasRetencionFuente2026Seeder extends Seeder
{
    protected $uvt = 52374;

    public function run()
    {
        if (!Schema::hasTable('compras_retencion_fuente_conceptos_anuales') || !Schema::hasTable('contab_retenciones')) {
            return;
        }

        $categoriaId = $this->getCategoriaRetefuenteId();
        $cuentas = $this->getCuentasRetencion();

        $conceptos = [
            ['compras_general_declarante', 'Compras generales', 'compras', 'producto', 'declarante', 2.5, 10],
            ['compras_general_no_declarante', 'Compras generales', 'compras', 'producto', 'no_declarante', 3.5, 10],
            ['compras_tarjeta', 'Compras con tarjeta débito o crédito', 'compras', 'producto', 'cualquiera', 1.5, 0],
            ['bienes_agricolas_sin_proceso', 'Bienes agrícolas o pecuarios sin procesamiento industrial', 'compras', 'producto', 'cualquiera', 1.5, 70],
            ['bienes_agricolas_con_proceso_declarante', 'Bienes agrícolas o pecuarios con procesamiento industrial', 'compras', 'producto', 'declarante', 2.5, 10],
            ['bienes_agricolas_con_proceso_no_declarante', 'Bienes agrícolas o pecuarios con procesamiento industrial', 'compras', 'producto', 'no_declarante', 3.5, 10],
            ['cafe_pergamino_cereza', 'Compras de café pergamino o cereza', 'compras', 'producto', 'cualquiera', 0.5, 70],
            ['combustibles', 'Combustibles derivados del petróleo', 'compras', 'producto', 'cualquiera', 0.1, 0],
            ['activos_fijos_persona_natural', 'Enajenación de activos fijos de personas naturales', 'compras', 'producto', 'cualquiera', 1, 0],
            ['vehiculos', 'Compras de vehículos', 'compras', 'producto', 'cualquiera', 1, 0],
            ['oro_comercializadoras_internacionales', 'Compra de oro por sociedades de comercialización internacional', 'compras', 'producto', 'cualquiera', 2.5, 0],
            ['bienes_raices_vivienda_primeras_10000_uvt', 'Bienes raíces vivienda de habitación primeras 10.000 UVT', 'compras', 'producto', 'cualquiera', 1, 0],
            ['bienes_raices_vivienda_exceso_10000_uvt', 'Bienes raíces vivienda de habitación exceso 10.000 UVT', 'compras', 'producto', 'cualquiera', 2.5, 10000],
            ['bienes_raices_no_vivienda', 'Bienes raíces uso distinto a vivienda de habitación', 'compras', 'producto', 'cualquiera', 2.5, 0],
            ['servicios_general_declarante', 'Servicios generales', 'servicios', 'servicio', 'declarante', 4, 2],
            ['servicios_general_no_declarante', 'Servicios generales', 'servicios', 'servicio', 'no_declarante', 6, 2],
            ['emolumentos_eclesiasticos_declarante', 'Emolumentos eclesiásticos', 'servicios', 'servicio', 'declarante', 4, 10],
            ['emolumentos_eclesiasticos_no_declarante', 'Emolumentos eclesiásticos', 'servicios', 'servicio', 'no_declarante', 3.5, 10],
            ['transporte_carga', 'Servicios de transporte de carga', 'servicios', 'servicio', 'cualquiera', 1, 2],
            ['transporte_pasajeros_terrestre_declarante', 'Transporte nacional de pasajeros terrestre', 'servicios', 'servicio', 'declarante', 3.5, 10],
            ['transporte_pasajeros_terrestre_no_declarante', 'Transporte nacional de pasajeros terrestre', 'servicios', 'servicio', 'no_declarante', 3.5, 10],
            ['transporte_pasajeros_aereo_maritimo', 'Transporte nacional de pasajeros aéreo o marítimo', 'servicios', 'servicio', 'cualquiera', 1, 2],
            ['servicios_temporales_aiu', 'Servicios temporales de empleo sobre AIU', 'servicios', 'servicio', 'cualquiera', 1, 2],
            ['servicios_vigilancia_aseo_aiu', 'Servicios de vigilancia y aseo sobre AIU', 'servicios', 'servicio', 'cualquiera', 2, 2],
            ['servicios_integrales_salud_ips', 'Servicios integrales de salud prestados por IPS', 'servicios', 'servicio', 'cualquiera', 2, 2],
            ['servicios_hoteles_restaurantes_declarante', 'Servicios de hoteles y restaurantes', 'servicios', 'servicio', 'declarante', 3.5, 2],
            ['servicios_hoteles_restaurantes_no_declarante', 'Servicios de hoteles y restaurantes', 'servicios', 'servicio', 'no_declarante', 3.5, 2],
            ['arrendamiento_bienes_muebles', 'Arrendamiento de bienes muebles', 'arrendamientos', 'servicio', 'cualquiera', 4, 0],
            ['arrendamiento_inmuebles_declarante', 'Arrendamiento de bienes inmuebles', 'arrendamientos', 'servicio', 'declarante', 3.5, 10],
            ['arrendamiento_inmuebles_no_declarante', 'Arrendamiento de bienes inmuebles', 'arrendamientos', 'servicio', 'no_declarante', 3.5, 10],
            ['otros_ingresos_declarante', 'Otros ingresos tributarios', 'compras', 'producto', 'declarante', 2.5, 10],
            ['otros_ingresos_no_declarante', 'Otros ingresos tributarios', 'compras', 'producto', 'no_declarante', 3.5, 10],
            ['honorarios_comisiones_personas_juridicas', 'Honorarios y comisiones personas jurídicas', 'servicios', 'servicio', 'declarante', 11, 0],
            ['honorarios_comisiones_pn_mayor_3300_uvt', 'Honorarios y comisiones PN contratos/pagos mayores a 3.300 UVT', 'servicios', 'servicio', 'declarante', 11, 0],
            ['honorarios_comisiones_no_declarante', 'Honorarios y comisiones', 'servicios', 'servicio', 'no_declarante', 10, 0],
            ['licenciamiento_software', 'Licenciamiento o derecho de uso de software', 'servicios', 'servicio', 'cualquiera', 3.5, 0],
            ['intereses_rendimientos_financieros', 'Intereses o rendimientos financieros', 'financieros', 'servicio', 'cualquiera', 7, 0],
            ['rendimientos_titulos_renta_fija', 'Rendimientos financieros de títulos de renta fija', 'financieros', 'servicio', 'cualquiera', 4, 0],
            ['loterias_rifas_apuestas', 'Loterías, rifas, apuestas y similares', 'otros', 'servicio', 'cualquiera', 20, 48],
            ['juegos_suerte_azar_independiente', 'Colocación independiente de juegos de suerte y azar', 'otros', 'servicio', 'cualquiera', 3, 5],
            ['construccion_urbanizacion', 'Contratos de construcción y urbanización', 'servicios', 'servicio', 'cualquiera', 2, 10],
            ['reteiva_servicios', 'Retención en la fuente por IVA en servicios', 'reteiva', 'servicio', 'cualquiera', 15, 2],
            ['reteiva_compras', 'Retención en la fuente por IVA en compras', 'reteiva', 'producto', 'cualquiera', 15, 10],
        ];

        foreach ($conceptos as $concepto) {
            $retencionId = $this->upsertRetencion($categoriaId, $cuentas, $concepto);

            $keys = ['anio' => 2026, 'codigo' => $concepto[0]];
            $values = [
                'uvt' => $this->uvt,
                'concepto' => $concepto[1],
                'tipo_operacion' => $concepto[2],
                'tipo_item' => $concepto[3],
                'tasa_retencion' => $concepto[5],
                'cuantia_minima_uvt' => $concepto[6],
                'cuantia_minima_pesos' => $concepto[6] * $this->uvt,
                'base_calculo' => 'sin_iva',
                'contab_retencion_id' => $retencionId,
                'estado' => 'Activo',
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if (Schema::hasColumn('compras_retencion_fuente_conceptos_anuales', 'tipo_declarante')) {
                $values['tipo_declarante'] = $concepto[4];
            }

            $exists = DB::table('compras_retencion_fuente_conceptos_anuales')->where($keys)->first();
            if ($exists) {
                DB::table('compras_retencion_fuente_conceptos_anuales')->where('id', $exists->id)->update($values);
            } else {
                DB::table('compras_retencion_fuente_conceptos_anuales')->insert($keys + $values + [
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        DB::table('compras_retencion_fuente_conceptos_anuales')
            ->where('anio', 2026)
            ->whereNotIn('codigo', array_map(function ($concepto) {
                return $concepto[0];
            }, $conceptos))
            ->update([
                'estado' => 'Inactivo',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    protected function getCategoriaRetefuenteId()
    {
        if (!Schema::hasTable('contab_categorias_retenciones')) {
            return 1;
        }

        $categoria = DB::table('contab_categorias_retenciones')
            ->where('nombre_corto', 'RTEFTE')
            ->orWhere('descripcion', 'LIKE', '%Renta%')
            ->first();

        if ($categoria) {
            return $categoria->id;
        }

        return DB::table('contab_categorias_retenciones')->insertGetId([
            'descripcion' => 'Retención en la fuente renta',
            'nombre_corto' => 'RTEFTE',
            'estado' => 'Activo',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    protected function getCuentasRetencion()
    {
        $base = DB::table('contab_retenciones')->where('estado', 'Activo')->first();

        return [
            'cta_ventas_id' => $base ? (int) $base->cta_ventas_id : 0,
            'cta_ventas_devol_id' => $base ? (int) $base->cta_ventas_devol_id : 0,
            'cta_compras_id' => $base ? (int) $base->cta_compras_id : 0,
            'cta_compras_devol_id' => $base ? (int) $base->cta_compras_devol_id : 0,
        ];
    }

    protected function upsertRetencion($categoriaId, array $cuentas, array $concepto)
    {
        $nombreCorto = strtoupper(substr($concepto[0], 0, 20));
        $data = [
            'categoria_retenciones_id' => $categoriaId,
            'descripcion' => $concepto[1] . ' 2026',
            'nombre_corto' => $nombreCorto,
            'tasa_retencion' => $concepto[5],
            'cta_ventas_id' => $cuentas['cta_ventas_id'],
            'cta_ventas_devol_id' => $cuentas['cta_ventas_devol_id'],
            'cta_compras_id' => $cuentas['cta_compras_id'],
            'cta_compras_devol_id' => $cuentas['cta_compras_devol_id'],
            'estado' => 'Activo',
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $retencion = DB::table('contab_retenciones')->where('nombre_corto', $nombreCorto)->first();
        if ($retencion) {
            DB::table('contab_retenciones')->where('id', $retencion->id)->update($data);
            return $retencion->id;
        }

        return DB::table('contab_retenciones')->insertGetId($data + [
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
