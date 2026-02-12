<?php

namespace App\Http\Controllers\Siesa;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProveedoresEnterpriseController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Genera la tabla HTML de proveedores SIESA Enterprise.
     *
     * @return \Illuminate\View\View
     */
    public function tabla()
    {
        $miga_pan = [
            ['url' => 'dashboard', 'etiqueta' => 'Dashboard'],
            ['url' => 'siesa/tabla_proveedores_enterprise', 'etiqueta' => 'Tabla proveedores SIESA Enterprise'],
        ];

        $semaforo = trim((string)request()->get('semaforo', 'codigo_sucursal'));
        $perPage = (int)request()->get('per_page', 200);
        if ($perPage <= 0 || $perPage > 2000) {
            $perPage = 200;
        }

        $rows = $this->buildQuery($semaforo)
            ->simplePaginate($perPage);

        $rows->getCollection()->transform(function ($row) {
            $row->fecha_de_ingreso = $this->formatFechaYmd($row->fecha_de_ingreso);
            return $row;
        });

        return view('siesa.tabla_proveedores_enterprise', compact('miga_pan', 'rows', 'perPage', 'semaforo'));
    }

    /**
     * Exporta la tabla completa a Excel (CSV).
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function tabla_excel()
    {
        $semaforo = trim((string)request()->get('semaforo', 'codigo_sucursal'));
        $filename = 'siesa_proveedores_enterprise_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $query = $this->buildQuery($semaforo);

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'CodProveedor',
                'SucurProveedor',
                'DescripSucursal',
                'ClaseProveedor',
                'CondPago',
                'TipoProveedor',
                'FormaPagoProveedores',
                'Observaciones',
                'Contacto',
                'Direccion1',
                'Direccion2',
                'Direccion3',
                'Pais',
                'Departamento',
                'Ciudad',
                'Barrio',
                'Telefono',
                'CorreoElectronico',
                'FechaDeIngreso',
                'MontoAnualDeCompra',
                'IndDelMontoAnual',
                'IndCotizDeCompra',
                'IndOrdenDeCompraEDI',
                'GrupoCentroOperacion',
                'TelefonoCelular',
                'IndSucPagosElectronicos',
            ]);

            foreach ($query->cursor() as $row) {
                fputcsv($handle, [
                    $this->asText($row->cod_proveedor),
                    $this->asText($row->sucur_proveedor),
                    $this->asText($row->descrip_sucursal),
                    $this->asText($row->clase_proveedor),
                    $this->asText($row->cond_pago),
                    $this->asText($row->tipo_proveedor),
                    $this->asText($row->forma_pago_proveedores),
                    $this->asText($row->observaciones),
                    $this->asText($row->contacto),
                    $this->asText($row->direccion1),
                    $this->asText($row->direccion2),
                    $this->asText($row->direccion3),
                    $this->asText($row->pais),
                    $this->asText($row->departamento),
                    $this->asText($row->ciudad),
                    $this->asText($row->barrio),
                    $this->asText($row->telefono),
                    $this->asText($row->correo_electronico),
                    $this->asText($this->formatFechaYmd($row->fecha_de_ingreso)),
                    $this->asText($row->monto_anual_de_compra),
                    $this->asText($row->ind_del_monto_anual),
                    $this->asText($row->ind_cotiz_de_compra),
                    $this->asText($row->ind_orden_de_compra_edi),
                    $this->asText($row->grupo_centro_operacion),
                    $this->asText($row->telefono_celular),
                    $this->asText($row->ind_suc_pagos_electronicos),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    private function asText($value)
    {
        $value = is_null($value) ? '' : (string)$value;
        $value = str_replace('"', '""', $value);

        return '="' . $value . '"';
    }

    private function buildQuery($semaforo)
    {
        $groupBy = $semaforo === 'codigo' ? 'codigo' : 'codigo, sucursal';
        $subquery = DB::table('siesa_datos_completos_proveedores')
            ->selectRaw(
                "codigo,
                " . ($semaforo === 'codigo' ? "'' as sucursal," : "sucursal,") . "
                MIN(razon_social_sucursal) as razon_social_sucursal,
                MIN(clase_de_proveedor) as clase_de_proveedor,
                MIN(condicion_de_pago) as condicion_de_pago,
                MIN(tipo_proveedor) as tipo_proveedor,
                MIN(forma_de_pago) as forma_de_pago,
                MIN(notas) as notas,
                MIN(contacto) as contacto,
                MIN(direccion_1) as direccion_1,
                MIN(direccion_2) as direccion_2,
                MIN(direccion_3) as direccion_3,
                MIN(cod_depto) as cod_depto,
                MIN(cod_ciudad) as cod_ciudad,
                MIN(barrio) as barrio,
                MIN(telefono) as telefono,
                MIN(email) as email,
                MIN(fecha_ingreso) as fecha_ingreso,
                MIN(monto_anual_compras) as monto_anual_compras,
                MIN(exige_cotizacion_en_oc_y_entrada) as exige_cotizacion_en_oc_y_entrada,
                MIN(exige_oc_en_entrada_de_almacen) as exige_oc_en_entrada_de_almacen,
                MIN(grupo_co) as grupo_co,
                MIN(celular) as celular,
                MIN(suc_defecto_pe) as suc_defecto_pe"
            )
            ->groupBy(DB::raw($groupBy));

        $query = DB::table('siesa_proveedores_enterprise as p')
            ->leftJoin(DB::raw('(' . $subquery->toSql() . ') as d'), function ($join) use ($semaforo) {
                $join->on('d.codigo', '=', 'p.codigo');
                if ($semaforo !== 'codigo') {
                    $join->on('d.sucursal', '=', 'p.sucursal');
                }
            })
            ->mergeBindings($subquery)
            ->select([
                'p.codigo as cod_proveedor',
                'p.sucursal as sucur_proveedor',
                DB::raw("COALESCE(p.razon_social_sucursal, d.razon_social_sucursal) as descrip_sucursal"),
                DB::raw("COALESCE(p.clase_de_proveedor, d.clase_de_proveedor) as clase_proveedor"),
                DB::raw("COALESCE(p.condicion_de_pago, d.condicion_de_pago) as cond_pago"),
                DB::raw("COALESCE(p.tipo_proveedor, d.tipo_proveedor) as tipo_proveedor"),
                DB::raw("CASE
                    WHEN d.forma_de_pago IS NULL OR d.forma_de_pago = '' THEN ''
                    WHEN LOWER(d.forma_de_pago) LIKE '%cheque%' THEN 0
                    WHEN LOWER(d.forma_de_pago) LIKE '%electron%' OR LOWER(d.forma_de_pago) LIKE '%consign%' OR LOWER(d.forma_de_pago) LIKE '%transfer%' THEN 1
                    WHEN LOWER(d.forma_de_pago) LIKE '%efectivo%' THEN 2
                    ELSE '' END as forma_pago_proveedores"),
                DB::raw("COALESCE(p.nota, d.notas) as observaciones"),
                DB::raw("COALESCE(d.contacto, '') as contacto"),
                DB::raw("COALESCE(d.direccion_1, '') as direccion1"),
                DB::raw("COALESCE(d.direccion_2, '') as direccion2"),
                DB::raw("COALESCE(d.direccion_3, '') as direccion3"),
                DB::raw("169 as pais"),
                DB::raw("COALESCE(d.cod_depto, '') as departamento"),
                DB::raw("COALESCE(d.cod_ciudad, '') as ciudad"),
                DB::raw("COALESCE(d.barrio, '') as barrio"),
                DB::raw("COALESCE(d.telefono, '') as telefono"),
                DB::raw("COALESCE(d.email, '') as correo_electronico"),
                DB::raw("COALESCE(p.fecha_ingreso, d.fecha_ingreso) as fecha_de_ingreso"),
                DB::raw("COALESCE(d.monto_anual_compras, '') as monto_anual_de_compra"),
                DB::raw("0 as ind_del_monto_anual"),
                DB::raw("0 as ind_cotiz_de_compra"),
                DB::raw("CASE WHEN d.exige_oc_en_entrada_de_almacen = 'Si' THEN 1 WHEN d.exige_oc_en_entrada_de_almacen = 'No' THEN 0 ELSE '' END as ind_orden_de_compra_edi"),
                DB::raw("COALESCE(d.grupo_co, '') as grupo_centro_operacion"),
                DB::raw("COALESCE(d.celular, '') as telefono_celular"),
                DB::raw("CASE WHEN d.suc_defecto_pe = 'Si' THEN 1 WHEN d.suc_defecto_pe = 'No' THEN 0 ELSE '' END as ind_suc_pagos_electronicos"),
            ])
            ->orderBy('p.codigo');

        return $query;
    }

    private function formatFechaYmd($value)
    {
        if ($value === null) {
            return '';
        }

        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }

        if (is_numeric($value)) {
            $days = (int)floor((float)$value);
            $date = Carbon::create(1899, 12, 30)->addDays($days);
            return $date->format('Ymd');
        }

        $formats = ['d/m/Y', 'd-m-Y', 'm/d/Y', 'm-d-Y', 'Y-m-d', 'Y/m/d'];
        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date) {
                    return $date->format('Ymd');
                }
            } catch (\Exception $e) {
                // Try next format
            }
        }

        return $value;
    }
}

