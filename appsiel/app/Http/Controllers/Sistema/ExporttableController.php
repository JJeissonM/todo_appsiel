<?php

namespace App\Http\Controllers\Sistema;

use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use View;
use Maatwebsite\Excel\Facades\Excel as Excel;

class ExporttableController extends Controller
{
    //method export
    public function export(Request $request)
    {
        if ($request->tipo == 'PDF') {
            //pdf export
            return $this->pdfExport($request->sqlString, $request->tituloExport, $request->search);
        } else {
            //excel export
            return $this->excelExport($request->sqlString, $request->tituloExport, $request->search);
        }
    }

    //PDF format export
    public function pdfExport($query, $tituloExport, $search)
    {
        $registros = DB::select($query);
        if (count($registros) > 0) {
            //cabeceras
            $cabeceras = $this->cabeceras($registros[0]);
            $hoy = getdate();
            $fecha = $hoy['mday'] . " DE " . $this->month($hoy['mon']) . " DE " . $hoy['year'];
            $nivel = 1;
            $filtros = ['FILTRO DE DATOS' => $search == '' ? 'SIN FILTRO' : $search];
            $documento_vista =  View::make('layouts.print', compact('registros', 'filtros', 'nivel', 'cabeceras', 'fecha', 'tituloExport'))->render();
            // Se prepara el PDF
            $pdf = App::make('dompdf.wrapper');
            $pdf->loadHTML($documento_vista); //->setPaper('Letter', 'Portrait');
            return $pdf->stream('listado.pdf');
        } else {
            //no hay registros para exportar
            return "<p style='position: absolute; top:50%; left:50%; width:400px; margin-left:-200px; height:150px; margin-top:-150px; border:3px solid #2c3e50; background-color:#f0f3f4; padding:40px; font-size:30px; color:red;'>Su consulta no produjo resultados.<br/><br/></p>";
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
    public function excelExport($query, $tituloExport, $search)
    {
        $data = DB::select($query);
        if (count($data) > 0) {
            $registros = null;
            //cabeceras
            $cabeceras = $this->cabeceras($data[0]);
            foreach ($data as $d) {
                $reg = null;
                foreach ($d as $key => $value) {
                    $reg[] = strtoupper($value);
                }
                $registros[] = $reg;
            }
            Excel::create($tituloExport, function ($excel) use ($cabeceras, $registros, $tituloExport, $search) {
                // Set the title
                $excel->setTitle($tituloExport);
                // Chain the setters
                $excel->setCreator('Ing. Jeisson Mandon')
                    ->setCompany('Appsiel SAS')
                    ->setDescription('Listado de registros del modelo indicado');
                $excel->sheet('Listado', function ($sheet) use ($cabeceras, $registros, $search, $tituloExport) {
                    $total = count($cabeceras);
                    // Sheet manipulation
                    $sheet->setAutoSize(true);
                    $sheet->row(1, [$tituloExport]);
                    $sheet->mergeCells($this->mergeCell(1, $total, 1));
                    $sheet->row(1, function ($row) {
                        // call cell manipulation methods
                        $row->setBackground('#50B794');
                        $row->setFontColor('#FFFFFF');
                        $row->setFontSize(14);
                        $row->setFontWeight('bold');
                        $row->setAlignment('center');
                    });
                    $sheet->row(2, ['']);
                    $sheet->row(3, ['FILTRO DE DATOS', $search == '' ? 'SIN FILTRO' : $search]);
                    $sheet->row(4, ['']);
                    $sheet->row(5, $cabeceras);
                    $sheet->row(5, function ($row) {
                        // call cell manipulation methods
                        $row->setBackground('#42A3DC');
                        $row->setFontColor('#FFFFFF');
                        $row->setFontSize(12);
                        $row->setFontWeight('bold');
                    });
                    $i = 6;
                    foreach ($registros as $r) {
                        $i = $i + 1;
                        $sheet->row($i, $r);
                    }
                });
            })->download('xlsx');
        } else {
            //no hay registros para exportar
            return "<p style='position: absolute; top:50%; left:50%; width:400px; margin-left:-200px; height:150px; margin-top:-150px; border:3px solid #2c3e50; background-color:#f0f3f4; padding:40px; font-size:30px; color:red;'>Su consulta no produjo resultados.<br/><br/></p>";
        }
    }

    public function mergeCell($inicio, $fin, $row)
    {
        $letras = [
            '1' => 'A',
            '2' => 'B',
            '3' => 'C',
            '4' => 'D',
            '5' => 'E',
            '6' => 'F',
            '7' => 'G',
            '8' => 'H',
            '9' => 'I',
            '10' => 'J',
            '11' => 'K',
            '12' => 'L',
            '13' => 'M',
            '14' => 'N',
            '15' => 'O',
            '16' => 'P',
            '17' => 'Q',
            '18' => 'R',
            '19' => 'S',
            '20' => 'T',
            '21' => 'U',
            '22' => 'V',
            '23' => 'W',
            '24' => 'X',
            '25' => 'Y',
            '26' => 'Z'
        ];
        return $letras[$inicio] . $row . ":" . $letras[$fin] . $row;
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
