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

        $columna = trim((string)request()->get('columna', ''));
        $perPage = (int)request()->get('per_page', 200);
        if ($perPage <= 0 || $perPage > 2000) {
            $perPage = 200;
        }

        $rows = $this->getQuery($columna)->simplePaginate($perPage);

        $columnas = [
            '' => 'Todas',
            'IVA_INTERP' => 'IVA INTERP',
            'RTSERVIC' => 'RTSERVIC',
            'RTSALARI' => 'RTSALARI',
            'RTIVA1' => 'RTIVA1',
            'RTHONORA' => 'RTHONORA',
            'RTCOMISI' => 'RTCOMISI',
            'RTBIENES' => 'RTBIENES',
            'RTARREND' => 'RTARREND',
            'RIVAGRAN' => 'RIVAGRAN',
            'ICASER' => 'ICASER',
            'ICACOMER' => 'ICACOMER',
            'INCBolsa' => 'INCBolsa',
            'ICUI' => 'ICUI',
            'ICINDUST' => 'ICINDUST',
            'ICD' => 'ICD',
            'FEDEGAN' => 'FEDEGAN',
            'IBUA' => 'IBUA',
        ];

        return view('siesa.tabla_proveedores_impuestos_retenciones', compact('miga_pan', 'rows', 'perPage', 'columnas', 'columna'));
    }

    /**
     * Exporta la tabla completa a Excel (CSV).
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function tabla_excel()
    {
        $columna = trim((string)request()->get('columna', ''));
        $filename = 'siesa_proveedores_impuestos_retenciones_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $query = $this->getQuery($columna);

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
                    $this->asText($row->tipo_registro),
                    $this->asText($row->cod_cliente_proveedor),
                    $this->asText($row->sucur_cliente_proveedor),
                    $this->asText($row->razon_social),
                    $this->asText($row->cod_clase_imp_retencion),
                    $this->asText($row->conf_tercero),
                    $this->asText($row->llave),
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

    private function getQuery($columna = '')
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
            if ($columna !== '' && $columna !== $def['sigla']) {
                continue;
            }
            $col = $def['col'];
            $llaveSelect = isset($def['llave_col']) ? "p.{$def['llave_col']} as llave" : "'' as llave";
            $confExpr = "CASE WHEN p.{$col} LIKE '%Autoretenedor%' THEN 2 WHEN p.{$col} LIKE '%Sujeto a retenci%' THEN 1 ELSE 0 END";

            $q = DB::table('siesa_proveedores_enterprise as p')
                ->join('siesa_retenciones as r', function ($join) use ($def) {
                    $join->where('r.sigla', '=', $def['sigla']);
                })
                ->whereNotNull($col)
                ->where($col, '!=', '')
                ->where($col, '!=', '<No definido>')
                ->selectRaw(
                    "50 as tipo_registro, p.codigo as cod_cliente_proveedor, p.sucursal as sucur_cliente_proveedor, p.razon_social as razon_social, r.clase as cod_clase_imp_retencion, {$confExpr} as conf_tercero, {$llaveSelect}"
                );

            $queries[] = $q;
        }

        foreach ($impuestos as $def) {
            if ($columna !== '' && $columna !== $def['sigla'] && !($def['is_iva'] ?? false && $columna === 'IVA_INTERP')) {
                continue;
            }
            $col = $def['col'];
            $llaveSelect = "'' as llave";

            if (!empty($def['is_iva'])) {
                $confExpr = "CASE WHEN p.{$col} = 'Responsable de IVA' THEN 1 WHEN p.{$col} = 'No Responsable de IVA' THEN 0 ELSE 0 END";
                $q = DB::table('siesa_proveedores_enterprise as p')
                    ->join('siesa_impuestos as i', function ($join) use ($def) {
                        $join->where('i.sigla', '=', $def['sigla']);
                    })
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
