<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use App\Nomina\NomContrato;
use App\Nomina\RegistroTurno;
use App\Nomina\TipoTurno;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use PHPExcel_IOFactory;

class RegistroTurnoImportController extends Controller
{
    public function create()
    {
        $miga_pan = [
                        ['url'=> 'nomina?id=' . Input::get('id') ,'etiqueta'=>'Nómina'],
                        ['url'=> 'web?id=' . Input::get('id') . '&id_modelo=337', 'etiqueta'=>'Registros de Turnos'],
                        ['url'=> 'NO','etiqueta'=>'Importar Registros de Turnos']
                    ];

        return view( 'nomina.turnos.import', compact('miga_pan') );
    }

    public function store(Request $request)
    {
        // validar archivo
        $this->validate($request, [
            'archivo' => 'required|file|mimes:xlsx,xls|max:5120',
            'fecha_primer_dia' => 'required|date',
        ]);

        $path = $request->file('archivo')->getRealPath();
        $sheet = PHPExcel_IOFactory::load($path)->getActiveSheet();

        $tiposTurnoActivos = TipoTurno::where('estado', 'Activo')
            ->with('cargos')
            ->get(['id', 'valor', 'checkin_time_1', 'checkout_time_1', 'checkin_time_2', 'checkout_time_2']);

        $turnosPorCargo = [];
        foreach ($tiposTurnoActivos as $turno) {
            if ($turno->cargos->isEmpty()) {
                $turnosPorCargo[0][] = $turno; // turnos globales (sin cargo)
            }

            foreach ($turno->cargos as $cargo) {
                $turnosPorCargo[$cargo->id][] = $turno;
            }
        }

        $cacheContratos = []; // fingerprint_reader_id => modelo contrato
        $dedup = [];          // contrato|Y-m-d H:i:s
        $marcas = [];         // contrato|fecha => [horas]
        $fechaPrimerDia = null;

        $fechasArchivo = [];      // set de fechas ajustadas (Y-m-d)
        $contratosArchivo = [];   // set de contratos presentes en el archivo

        $tolerancia = (int)config('nomina.tolerancia_minutos_turnos_importacion');

        foreach ($sheet->getRowIterator(2) as $row) {
            $r = $row->getRowIndex();
            $cells = array_first($sheet->rangeToArray("A{$r}:L{$r}", null, true, true, true));

            $fingerId = trim($cells['D'] ?? '');
            $horaRaw  = trim($cells['J'] ?? '');

            if ($fingerId === '' || $horaRaw === '') {
                continue;
            }

            try {
                $dt = Carbon::parse(str_replace('/', '-', $horaRaw));
            } catch (\Throwable $e) {
                continue;
            }

            if (!isset($cacheContratos[$fingerId])) {
                    $cacheContratos[$fingerId] = NomContrato::where('fingerprint_reader_id', 'like', '%'.$fingerId.'%')
                        ->select('id', 'cargo_id', 'turno_default_id')
                        ->with('turno_default')
                        ->first();
            }
            
            $contrato = $cacheContratos[$fingerId];
            
            if (!$contrato) {
                continue; // sin contrato asociado
            }

            $dedupKey = $contrato->id . '|' . $dt->toDateTimeString();
            if (isset($dedup[$dedupKey])) {
                continue;
            }
            $dedup[$dedupKey] = true;

            // Si se pasa al dia siguiente (después de la medianoche), se asigna a la fecha anterior y la hora del final de la jornada
            $fechaBase = $dt->toDateString();
            $horaBase = $dt->format('H:i:s');
            if ($horaBase < '02:00:00') {
                $fechaBase = $dt->copy()->subDay()->toDateString();
                $horaBase = '23:30:00';
            }

            // guardar mínimo
            if (is_null($fechaPrimerDia) || $fechaBase < $fechaPrimerDia) {
                $fechaPrimerDia = $fechaBase;
            }

            if ( $fechaPrimerDia < $request->fecha_primer_dia ) {
                $fechaPrimerDia = $request->fecha_primer_dia;
                continue; // omitir marcas antes del primer día
            }

            if ( $fechaBase > $request->fecha_corte_final ) {
                continue; // omitir marcas después del corte final
            }            

            $grupoKey = $contrato->id . '|' . $fechaBase;
            $marcas[$grupoKey][] = $horaBase;

            $fechasArchivo[$fechaBase] = true;
            $contratosArchivo[$contrato->id] = true;

        }

        //  Validar contra BD si ya existen registros en las fechas del archivo
        $fechasImport = array_keys($fechasArchivo);
        $contratosImport = array_keys($contratosArchivo);

        if (!empty($fechasImport)) {
            $existe = RegistroTurno::whereIn('fecha', $fechasImport)
                ->whereIn('contrato_id', $contratosImport)
                ->exists();

            if ($existe) {
                return redirect( url( '/nomina/turnos/importar' ) . '?id=' . $request->app_id . '&id_modelo=' . $request->modelo_id )
                            ->withErrors(['archivo' => 'El archivo No pudo ser importado: contiene fechas que ya tienen registros de turnos.']);
            }
        }

        foreach ($marcas as $grupoKey => $horas) {
            [$contratoId, $fecha] = explode('|', $grupoKey);

            sort($horas, SORT_STRING);
            $horas = array_values(array_unique($horas)); // dedup en el día

            // descartar marcas muy cercanas (menos de 30 minutos) para evitar duplicados accidentales
            $horasFiltradas = [];
            foreach ($horas as $hora) {
                if (empty($horasFiltradas)) {
                    $horasFiltradas[] = $hora;
                    continue;
                }

                $prev = Carbon::createFromFormat('H:i:s', end($horasFiltradas));
                $curr = Carbon::createFromFormat('H:i:s', $hora);

                if ($prev->diffInMinutes($curr) >= 30) {
                    $horasFiltradas[] = $hora;
                }
            }

            $horas = $horasFiltradas;

            if (count($horas) > 4) {
                $horas = array_slice($horas, 0, 4); // descarta extras
            }

            // Match contra TipoTurno por cercanía de horarios (según tolerancia)
            $mejorTurno = null;
            $mejorDiff = null;
            $contratoActual = $cacheContratosPorId[$contratoId]
                ?? NomContrato::select('id','cargo_id','turno_default_id')->with('turno_default')->find($contratoId);
            $cargoId = $contratoActual->cargo_id ?? 0;
            $turnosDisponibles = $turnosPorCargo[$cargoId] ?? ($turnosPorCargo[0] ?? []);

            foreach ($turnosDisponibles as $turno) {
                $esperados = array_values(array_filter([
                    $turno->checkin_time_1,
                    $turno->checkout_time_1,
                    $turno->checkin_time_2,
                    $turno->checkout_time_2,
                ], function ($t) {
                    return !is_null($t) && $t !== '';
                }));

                if (count($esperados) === 0) {
                    continue;
                }

                // si faltan marcas para comparar, no aplica
                if (count($horas) < count($esperados)) {
                    continue;
                }

                $totalDiff = 0;
                for ($i = 0; $i < count($esperados); $i++) {
                    $exp = Carbon::createFromFormat('H:i:s', $esperados[$i]);
                    $obs = Carbon::createFromFormat('H:i:s', $horas[$i]);
                    $diff = $exp->diffInMinutes($obs);
                    if ($diff > $tolerancia) {
                        $totalDiff = null;
                        break;
                    }
                    $totalDiff += $diff;
                }

                if (is_null($totalDiff)) {
                    continue;
                }

                if (is_null($mejorDiff) || $totalDiff < $mejorDiff) {
                    $mejorDiff = $totalDiff;
                    $mejorTurno = $turno;
                }
            }

            $turnoDefault = null;
            foreach ($cacheContratos as $c) {
                if ($c && $c->id == $contratoId) {
                    $turnoDefault = $c->turno_default;
                    break;
                }
            }

            if ($mejorTurno == null) {

                $mejorTurno = $turnoDefault;
            }

            if ($mejorTurno == null) {

                $mejorTurno = TipoTurno::find( (int)config('nomina.turno_default_id') );
            }

            if ( $mejorTurno->valor != 0) {
                RegistroTurno::create([
                    'contrato_id'     => $contratoId,
                    'tipo_turno_id'   => $mejorTurno->id,
                    'fecha'           => $fecha,
                    'checkin_time_1'  => $horas[0] ?? null,
                    'checkout_time_1' => $horas[1] ?? null,
                    'checkin_time_2'  => $horas[2] ?? null,
                    'checkout_time_2' => $horas[3] ?? null,
                    'valor'           => $mejorTurno->valor,
                    'anotacion'       => '',
                    'estado'          => 'Pendiente',
                ]);
            }
            
        }
        
        return redirect( 'nom_turnos_registros/create?id=' . $request->app_id . '&id_modelo=' . $request->modelo_id . '&fecha=' . $fechaPrimerDia )->with( 'flash_message','Turnos importados correctamente. Fecha primer día: ' . $fechaPrimerDia );
    }

    public function borrar_registros($fecha_primer_dia, $fecha_corte_final)
    {
        RegistroTurno::where('fecha', '>=', $fecha_primer_dia)
            ->where('fecha', '<=', $fecha_corte_final)
            ->where('estado', '=', 'Pendiente')
            ->delete();

        return redirect()->back()->with('flash_message', 'Registros de turnos borrados correctamente.');
    }

}
