<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Contabilidad\CategoriaRetencion;
use App\Contabilidad\Retencion;

class ComprasReteicaValledupar2026Seeder extends Seeder
{
    public function run()
    {
        // Acuerdo 027 de 2024, art. 12, pp. 10-12 (tarifas expresadas por mil).
        // https://concejodevalledupar.gov.co/wp-content/uploads/2024/03/Acuerdo-027-de-2024.pdf
        // Catálogo de tarifas; la selección según actividad corresponde al responsable contable.
        DB::transaction(function () {
            $categoria = CategoriaRetencion::where('nombre_corto', 'ReteICA')->first();
            if (!$categoria) {
                $categoria = CategoriaRetencion::create(['nombre_corto' => 'ReteICA',
                    'descripcion' => 'Retención de industria y comercio', 'estado' => 'Activo']);
            }
            foreach ([3, 5, 6, 7, 8, 10, 10.62, 11, 11.04, 14] as $porMil) {
                $tasa = $porMil / 10;
                if (Retencion::where('categoria_retenciones_id', $categoria->id)->where('tasa_retencion', $tasa)->exists()) {
                    continue;
                }
                Retencion::create([
                    'categoria_retenciones_id' => $categoria->id,
                    'descripcion' => 'ReteICA Valledupar 2026 - ' . $porMil . ' por mil',
                    'nombre_corto' => 'ICA ' . $porMil,
                    'tasa_retencion' => $tasa,
                    'cta_ventas_id' => 0, 'cta_ventas_devol_id' => 0,
                    'cta_compras_id' => 0, 'cta_compras_devol_id' => 0,
                    'estado' => 'Activo',
                ]);
            }
        });
    }
}
