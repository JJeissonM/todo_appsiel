<?php

namespace App\Http\Controllers\Siesa;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DescuentosController extends Controller
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
     * Genera la tabla HTML de descuentos SIESA.
     *
     * @return \Illuminate\View\View
     */
    public function tabla_descuentos()
    {
        $miga_pan = [
            ['url' => 'dashboard', 'etiqueta' => 'Dashboard'],
            ['url' => 'siesa/tabla_descuentos', 'etiqueta' => 'Tabla descuentos SIESA'],
        ];

        $ldFiltro = trim((string)request()->get('ld', ''));
        $perPage = (int)request()->get('per_page', 200);
        if ($perPage <= 0 || $perPage > 2000) {
            $perPage = 200;
        }

        $rows = $this->getDescuentosQuery($ldFiltro)
            ->simplePaginate($perPage);

        return view('siesa.tabla_descuentos', compact('miga_pan', 'rows', 'perPage', 'ldFiltro'));
    }

    /**
     * Exporta la tabla completa a Excel (CSV).
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function tabla_descuentos_excel()
    {
        $ldFiltro = trim((string)request()->get('ld', ''));
        $filename = 'siesa_descuentos_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $query = $this->getDescuentosQuery($ldFiltro);

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'LD',
                'Codgio_dcto',
                'Linea',
                'Referencia_Item',
                'Extension 1',
                'Id_Cliente',
                'Sucursal_Cliente',
                'Lista_precio_cliente',
                'Fecha Inicial',
                'Cant Minima',
                'Porcentaje_dcto',
            ]);

            $linea = 1;
            foreach ($query->cursor() as $row) {
                fputcsv($handle, [
                    $row->ld,
                    $row->codigo_dcto,
                    $linea,
                    $row->referencia_item,
                    $row->extension_1,
                    $row->id_cliente,
                    $row->sucursal_cliente,
                    $row->lista_precio_cliente,
                    $row->fecha_inicial,
                    $row->cant_minima,
                    $row->porcentaje_dcto,
                ]);
                $linea++;
            }

            fclose($handle);
        }, 200, $headers);
    }

    private function getDescuentosQuery($ldFiltro = '')
    {
        $baseSelect = "c.ld as ld,
            IFNULL(e.id_entreprise, c.ld) as codigo_dcto,
            d.referencia_item as referencia_item,
            'FN' as extension_1,
            c.id_cliente as id_cliente,
            CASE
                WHEN COALESCE(lc.cnt_ld, 0) <= 1 THEN ''
                WHEN c.sucursal_cliente IS NULL OR c.sucursal_cliente = '' OR c.sucursal_cliente = '0' OR c.sucursal_cliente = '00'
                    THEN '' ELSE c.sucursal_cliente END as sucursal_cliente,
            '' as lista_precio_cliente,
            '20260201' as fecha_inicial,
            1 as cant_minima,
            %s as porcentaje_dcto,
            %d as orden_desc";

        $q1 = DB::table('siesa_clientes as c')
            ->join('siesa_listas_descuentos as d', 'c.ld', '=', 'd.ld')
            ->leftJoin('siesa_encabezados_listas_descuentos as e', 'e.lp', '=', 'c.ld')
            ->leftJoin(DB::raw('(select id_cliente, sucursal_cliente, count(distinct ld) as cnt_ld from siesa_clientes group by id_cliente, sucursal_cliente) as lc'), function ($join) {
                $join->on('lc.id_cliente', '=', 'c.id_cliente')
                    ->on('lc.sucursal_cliente', '=', 'c.sucursal_cliente');
            })
            ->selectRaw(sprintf($baseSelect, 'd.descuento1', 1));

        $q2 = DB::table('siesa_clientes as c')
            ->join('siesa_listas_descuentos as d', 'c.ld', '=', 'd.ld')
            ->leftJoin('siesa_encabezados_listas_descuentos as e', 'e.lp', '=', 'c.ld')
            ->leftJoin(DB::raw('(select id_cliente, sucursal_cliente, count(distinct ld) as cnt_ld from siesa_clientes group by id_cliente, sucursal_cliente) as lc'), function ($join) {
                $join->on('lc.id_cliente', '=', 'c.id_cliente')
                    ->on('lc.sucursal_cliente', '=', 'c.sucursal_cliente');
            })
            ->whereNotNull('d.descuento2')
            ->where('d.descuento2', '!=', 0)
            ->selectRaw(sprintf($baseSelect, 'd.descuento2', 2));

        if ($ldFiltro !== '') {
            $q1->where('c.ld', $ldFiltro);
            $q2->where('c.ld', $ldFiltro);
        }

        $union = $q1->unionAll($q2);

        return DB::table(DB::raw('(' . $union->toSql() . ') as t'))
            ->mergeBindings($union)
            ->distinct()
            ->orderBy('id_cliente')
            ->orderBy('referencia_item')
            ->orderBy('orden_desc');
    }
}
