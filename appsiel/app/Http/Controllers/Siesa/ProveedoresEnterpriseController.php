<?php

namespace App\Http\Controllers\Siesa;

use App\Http\Controllers\Controller;
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

        $perPage = (int)request()->get('per_page', 200);
        if ($perPage <= 0 || $perPage > 2000) {
            $perPage = 200;
        }

        $rows = DB::table('siesa_proveedores_enterprise')
            ->select([
                'codigo as cod_proveedor',
                'sucursal as sucur_proveedor',
                'razon_social_sucursal as descrip_sucursal',
                'clase_de_proveedor as clase_proveedor',
                'condicion_de_pago as cond_pago',
                'tipo_proveedor as tipo_proveedor',
                DB::raw("'' as forma_pago_proveedores"),
                'nota as observaciones',
                DB::raw("'' as contacto"),
                DB::raw("'' as direccion1"),
                DB::raw("'' as direccion2"),
                DB::raw("'' as direccion3"),
                DB::raw("'' as pais"),
                DB::raw("'' as departamento"),
                DB::raw("'' as ciudad"),
                DB::raw("'' as barrio"),
                DB::raw("'' as telefono"),
                DB::raw("'' as correo_electronico"),
                'fecha_ingreso as fecha_de_ingreso',
                DB::raw("'' as monto_anual_de_compra"),
                DB::raw("'' as ind_del_monto_anual"),
                DB::raw("'' as ind_cotiz_de_compra"),
                DB::raw("'' as ind_orden_de_compra_edi"),
                DB::raw("'' as grupo_centro_operacion"),
                DB::raw("'' as telefono_celular"),
                DB::raw("'' as ind_suc_pagos_electronicos"),
            ])
            ->orderBy('codigo')
            ->simplePaginate($perPage);

        return view('siesa.tabla_proveedores_enterprise', compact('miga_pan', 'rows', 'perPage'));
    }

    /**
     * Exporta la tabla completa a Excel (CSV).
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function tabla_excel()
    {
        $filename = 'siesa_proveedores_enterprise_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $query = DB::table('siesa_proveedores_enterprise')
            ->select([
                'codigo as cod_proveedor',
                'sucursal as sucur_proveedor',
                'razon_social_sucursal as descrip_sucursal',
                'clase_de_proveedor as clase_proveedor',
                'condicion_de_pago as cond_pago',
                'tipo_proveedor as tipo_proveedor',
                DB::raw("'' as forma_pago_proveedores"),
                'nota as observaciones',
                DB::raw("'' as contacto"),
                DB::raw("'' as direccion1"),
                DB::raw("'' as direccion2"),
                DB::raw("'' as direccion3"),
                DB::raw("'' as pais"),
                DB::raw("'' as departamento"),
                DB::raw("'' as ciudad"),
                DB::raw("'' as barrio"),
                DB::raw("'' as telefono"),
                DB::raw("'' as correo_electronico"),
                'fecha_ingreso as fecha_de_ingreso',
                DB::raw("'' as monto_anual_de_compra"),
                DB::raw("'' as ind_del_monto_anual"),
                DB::raw("'' as ind_cotiz_de_compra"),
                DB::raw("'' as ind_orden_de_compra_edi"),
                DB::raw("'' as grupo_centro_operacion"),
                DB::raw("'' as telefono_celular"),
                DB::raw("'' as ind_suc_pagos_electronicos"),
            ])
            ->orderBy('codigo');

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
                    $row->cod_proveedor,
                    $row->sucur_proveedor,
                    $row->descrip_sucursal,
                    $row->clase_proveedor,
                    $row->cond_pago,
                    $row->tipo_proveedor,
                    $row->forma_pago_proveedores,
                    $row->observaciones,
                    $row->contacto,
                    $row->direccion1,
                    $row->direccion2,
                    $row->direccion3,
                    $row->pais,
                    $row->departamento,
                    $row->ciudad,
                    $row->barrio,
                    $row->telefono,
                    $row->correo_electronico,
                    $row->fecha_de_ingreso,
                    $row->monto_anual_de_compra,
                    $row->ind_del_monto_anual,
                    $row->ind_cotiz_de_compra,
                    $row->ind_orden_de_compra_edi,
                    $row->grupo_centro_operacion,
                    $row->telefono_celular,
                    $row->ind_suc_pagos_electronicos,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
