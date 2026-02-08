<?php

namespace App\Http\Controllers\Siesa;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ProveedoresImpuestosRetencionesController extends Controller
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
     * Genera la tabla HTML de impuestos y retenciones por proveedor.
     *
     * @return \Illuminate\View\View
     */
    public function tabla()
    {
        $miga_pan = [
            ['url' => 'dashboard', 'etiqueta' => 'Dashboard'],
            ['url' => 'siesa/tabla_proveedores_impuestos_retenciones', 'etiqueta' => 'Impuestos y retenciones proveedores'],
        ];

        $perPage = (int)request()->get('per_page', 200);
        if ($perPage <= 0 || $perPage > 2000) {
            $perPage = 200;
        }

        $rows = $this->getQuery()->simplePaginate($perPage);

        return view('siesa.tabla_proveedores_impuestos_retenciones', compact('miga_pan', 'rows', 'perPage'));
    }

    /**
     * Exporta la tabla completa a Excel (CSV).
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function tabla_excel()
    {
        $filename = 'siesa_proveedores_impuestos_retenciones_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $query = $this->getQuery();

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'TipoRegistro',
                'CodClienteProveedor',
                'SucurClienteProveedor',
                'RazonSocial',
                'CodClaseImpRetencion',
                'ConfTercero',
                'Llave',
            ]);

            foreach ($query->cursor() as $row) {
                fputcsv($handle, [
                    $row->tipo_registro,
                    $row->cod_cliente_proveedor,
                    $row->sucur_cliente_proveedor,
                    $row->razon_social,
                    $row->cod_clase_imp_retencion,
                    $row->conf_tercero,
                    $row->llave,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    private function getQuery()
    {
        $retenciones = [
            ['sigla' => 'RTSERVIC', 'col' => 'rtservic', 'llave_col' => 'llave_rtservic'],
            ['sigla' => 'RTSALARI', 'col' => 'rtsalari', 'llave_col' => 'llave_rtsalari'],
            ['sigla' => 'RTIVA1', 'col' => 'rtiva1', 'llave_col' => 'llave_rtiva1'],
            ['sigla' => 'RTHONORA', 'col' => 'rthonora', 'llave_col' => 'llave_rthonora'],
            ['sigla' => 'RTCOMISI', 'col' => 'rtcomisi', 'llave_col' => 'llave_rtcomisi'],
            ['sigla' => 'RTBIENES', 'col' => 'rtbienes', 'llave_col' => 'llave_rtbienes'],
            ['sigla' => 'RTARREND', 'col' => 'rtarrend', 'llave_col' => 'llave_rtarrend'],
            ['sigla' => 'RIVAGRAN', 'col' => 'rivagran'],
            ['sigla' => 'ICASER', 'col' => 'icaser', 'llave_col' => 'llave_icaser'],
            ['sigla' => 'ICACOMER', 'col' => 'icacomer', 'llave_col' => 'llave_icacomer'],
        ];

        $impuestos = [
            ['sigla' => 'INCBolsa', 'col' => 'incbolsa'],
            ['sigla' => 'ICUI', 'col' => 'icui'],
            ['sigla' => 'ICINDUST', 'col' => 'icindust'],
            ['sigla' => 'ICD', 'col' => 'icd'],
            ['sigla' => 'FEDEGAN', 'col' => 'fedegan'],
            ['sigla' => 'IBUA', 'col' => 'ibua'],
            ['sigla' => 'IVA', 'col' => 'iva_interp', 'is_iva' => true],
        ];

        $queries = [];

        foreach ($retenciones as $def) {
            $col = $def['col'];
            $llaveSelect = isset($def['llave_col']) ? "p.{$def['llave_col']} as llave" : "'' as llave";
            $confExpr = "CASE WHEN p.{$col} LIKE '%Autoretenedor%' THEN 2 WHEN p.{$col} LIKE '%Sujeto a retenci%' THEN 1 ELSE 0 END";

            $q = DB::table('siesa_proveedores_enterprise as p')
                ->join('siesa_retenciones as r', function ($join) use ($def) {
                    $join->where('r.sigla', '=', $def['sigla']);
                })
                ->where(function ($query) use ($col) {
                    $query->where($col, 'like', '%Sujeto a retenci%')
                        ->orWhere($col, 'like', '%Autoretenedor%');
                })
                ->selectRaw(
                    "50 as tipo_registro, p.codigo as cod_cliente_proveedor, p.sucursal as sucur_cliente_proveedor, p.razon_social as razon_social, r.clase as cod_clase_imp_retencion, {$confExpr} as conf_tercero, {$llaveSelect}"
                );

            $queries[] = $q;
        }

        foreach ($impuestos as $def) {
            $col = $def['col'];
            $llaveSelect = "'' as llave";

            if (!empty($def['is_iva'])) {
                $confExpr = "CASE WHEN p.{$col} = 'Responsable de IVA' THEN 1 ELSE 0 END";
                $q = DB::table('siesa_proveedores_enterprise as p')
                    ->join('siesa_impuestos as i', function ($join) use ($def) {
                        $join->where('i.sigla', '=', $def['sigla']);
                    })
                    ->where($col, '=', 'Responsable de IVA')
                    ->selectRaw(
                        "49 as tipo_registro, p.codigo as cod_cliente_proveedor, p.sucursal as sucur_cliente_proveedor, p.razon_social as razon_social, i.clase as cod_clase_imp_retencion, {$confExpr} as conf_tercero, {$llaveSelect}"
                    );
            } else {
                $q = DB::table('siesa_proveedores_enterprise as p')
                    ->join('siesa_impuestos as i', function ($join) use ($def) {
                        $join->where('i.sigla', '=', $def['sigla']);
                    })
                    ->whereNotNull($col)
                    ->where($col, '!=', '')
                    ->where($col, '!=', '<No definido>')
                    ->selectRaw(
                        "49 as tipo_registro, p.codigo as cod_cliente_proveedor, p.sucursal as sucur_cliente_proveedor, p.razon_social as razon_social, i.clase as cod_clase_imp_retencion, 1 as conf_tercero, {$llaveSelect}"
                    );
            }

            $queries[] = $q;
        }

        $union = array_shift($queries);
        foreach ($queries as $q) {
            $union->unionAll($q);
        }

        return DB::table(DB::raw('(' . $union->toSql() . ') as t'))
            ->mergeBindings($union)
            ->orderBy('cod_cliente_proveedor')
            ->orderBy('sucur_cliente_proveedor')
            ->orderBy('tipo_registro');
    }
}
