<?php

namespace App\Http\Controllers\Sistema;

use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use View;

class ExporttableController extends Controller
{
    //method export
    public function export(Request $request)
    {
        if ($request->tipo == 'PDF') {
            //pdf export
            return $this->pdfExport($request->sqlString, $request->tituloExport);
        } else {
            //excel export
            $this->excelExport($request->sqlString, $request->tituloExport);
        }
    }

    //PDF format export
    public function pdfExport($query, $tituloExport)
    {
        $registros = DB::select($query);
        if (count($registros) > 0) {
            //cabeceras
            $cabeceras = $this->cabeceras($registros[0]);
            $hoy = getdate();
            $fecha = $hoy['mday'] . " DE " . $this->month($hoy['mon']) . " DE " . $hoy['year'];
            $nivel = 1;
            $documento_vista =  View::make('layouts.print', compact('registros', 'nivel', 'cabeceras', 'fecha', 'tituloExport'))->render();
            // Se prepara el PDF
            $pdf = App::make('dompdf.wrapper');
            $pdf->loadHTML($documento_vista); //->setPaper( $tam_hoja, $orientacion );
            return $pdf->stream('listado.pdf');
        } else {
            //no hay registros para exportar
        }
    }

    //mes
    public function month($m)
    {
        return [
            '1' => 'ENERO',
            '2' => 'FEBRERO',
            '3' => 'MARZO',
            '4' => 'ABRIL',
            '5' => 'MAYO',
            '6' => 'JUNIO',
            '7' => 'JULIO',
            '8' => 'AGOSTO',
            '9' => 'SEPTIEMBRE',
            '10' => 'OCTUBRE',
            '11' => 'NOVIEMBRE',
            '12' => 'DICIEMBRE'
        ][$m];
    }

    //EXCEL format export
    public function excelExport($query, $tituloExport)
    {
        $registros = DB::select($query);
        if (count($registros) > 0) {
            //cabeceras
            $cabeceras = $this->cabeceras($registros[0]);
            dd($registros);
        } else {
            //no hay registros para exportar
        }
    }

    //llenado de cabeceras
    public function cabeceras($row)
    {
        $cabeceras = null;
        //llenamos las cabeceras
        foreach ($row as $key => $value) {
            $cabeceras[] = $key;
        }
        return $cabeceras;
    }
}
