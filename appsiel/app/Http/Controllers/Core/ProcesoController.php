<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Sistema\ModeloController;

use App\Sistema\Permiso;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class ProcesoController extends ModeloController
{

    public function principal( $vista_proceso )
    {
        if( !view()->exists($vista_proceso) )
        {
            return redirect( url()->previous() )->with('mensaje_error','Vista NO existe: ' . $vista_proceso );
        }

        $permiso = Permiso::where( 'url', 'index_procesos/'.$vista_proceso )->get()->first();

        if ( is_null( $permiso ) )
        {
            return redirect( url()->previous() )->with('flash_message','Proceso no existe');
        }

        $miga_pan = [
                        ['url' => $this->aplicacion->app.'?id='.Input::get('id'), 'etiqueta'=> $this->aplicacion->descripcion ],
                        ['url' => 'NO','etiqueta'=> 'Proceso: ' . $permiso->descripcion ]
                    ];

        return view( $vista_proceso, compact( 'miga_pan' ) );
    }

    public function set_impuesto_id()
    {
        $sql_impuesto = '(SELECT tasa_impuesto, MIN(id) AS impuesto_id FROM contab_impuestos GROUP BY tasa_impuesto) AS imp';

        $tablas = [
            'vtas_doc_registros',
            'vtas_movimientos',
            'contab_movimientos',
            'vtas_pos_doc_registros',
            'vtas_pos_movimientos'
        ];

        $resultados = [];

        foreach ($tablas as $tabla) {
            $sql = "UPDATE {$tabla} t JOIN {$sql_impuesto} ON imp.tasa_impuesto = t.tasa_impuesto " .
                   "SET t.impuesto_id = imp.impuesto_id " .
                   "WHERE (t.impuesto_id IS NULL OR t.impuesto_id = 0)";

            $resultados[$tabla] = DB::update($sql);
        }

        $resumen = [];
        foreach ($resultados as $tabla => $cantidad) {
            $resumen[] = $tabla . ': ' . $cantidad;
        }

        $mensaje = 'Impuesto actualizado. ' . implode(', ', $resumen);

        if (request()->wantsJson()) {
            return response()->json($resultados);
        }

        return redirect()->back()->with('flash_message', $mensaje);
    }
}


