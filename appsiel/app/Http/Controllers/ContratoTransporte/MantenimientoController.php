<?php

namespace App\Http\Controllers\ContratoTransporte;

use App\Contratotransporte\Anioperiodo;
use App\Contratotransporte\Mantenimiento;
use App\Contratotransporte\Mantobs;
use App\Contratotransporte\Mantreportes;
use App\Contratotransporte\Vehiculo;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

class MantenimientoController extends Controller
{
    //index
    public function index()
    {
        $idapp = Input::get('id');
        $modelo = Input::get('id_modelo');
        $transaccion = Input::get('id_transaccion');
        $miga_pan = [
            [
                'url' => 'contratos_transporte' . '?id=' . $idapp,
                'etiqueta' => 'Contratos transporte'
            ],
            [
                'url' => 'cte_mantenimientos?id=' . $idapp . "&id_modelo=" . $modelo,
                'etiqueta' => 'Mantenimientos'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Listado de Vehículos'
            ]
        ];
        $vehiculos = Vehiculo::all();
        $variables_url = "?id=" . $idapp . "&id_modelo=" . $modelo . "&id_transaccion=" . $transaccion;
        return view('contratos_transporte.mantenimientos.index')
            ->with('variables_url', $variables_url)
            ->with('miga_pan', $miga_pan)
            ->with('vehiculos', $vehiculos);
    }

    //continuar
    public function continuar($id)
    {
        $v = Vehiculo::find($id);
        $idapp = Input::get('id');
        $modelo = Input::get('id_modelo');
        $transaccion = Input::get('id_transaccion');
        $miga_pan = [
            [
                'url' => 'contratos_transporte' . '?id=' . $idapp,
                'etiqueta' => 'Contratos transporte'
            ],
            [
                'url' => 'cte_mantenimientos?id=' . $idapp . "&id_modelo=" . $modelo,
                'etiqueta' => 'Mantenimientos'
            ],
            [
                'url' => 'cte_mantenimientos?id=' . $idapp . "&id_modelo=" . $modelo,
                'etiqueta' => 'Listado de Vehículos'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Gestión de Mantenimientos'
            ]
        ];
        $periodos = Anioperiodo::all();
        $mantenimientos = null;
        if (count($periodos) > 0) {
            foreach ($periodos as $p) {
                $mantenimientos[$p->inicio . " - " . $p->fin . " (" . $p->anio->anio . ")"] = [
                    'mantenimientos' => Mantenimiento::where([['anioperiodo_id', $p->id], ['vehiculo_id', $v->id]])->get(),
                    'anioperiodo_id' => $p->id
                ];
            }
        }
        $variables_url = "?id=" . $idapp . "&id_modelo=" . $modelo . "&id_transaccion=" . $transaccion;
        return view('contratos_transporte.mantenimientos.continuar')
            ->with('variables_url', $variables_url)
            ->with('miga_pan', $miga_pan)
            ->with('v', $v)
            ->with('periodos', $mantenimientos);
    }

    //crear mantenimiento
    public function create($id, $ap)
    {
        $v = Vehiculo::find($id);
        $aniop = Anioperiodo::find($ap);
        $idapp = Input::get('id');
        $modelo = Input::get('id_modelo');
        $transaccion = Input::get('id_transaccion');
        $miga_pan = [
            [
                'url' => 'contratos_transporte' . '?id=' . $idapp,
                'etiqueta' => 'Contratos transporte'
            ],
            [
                'url' => 'cte_mantenimientos?id=' . $idapp . "&id_modelo=" . $modelo,
                'etiqueta' => 'Mantenimientos'
            ],
            [
                'url' => 'cte_mantenimientos?id=' . $idapp . "&id_modelo=" . $modelo,
                'etiqueta' => 'Listado de Vehículos'
            ],
            [
                'url' => 'cte_mantenimientos/' . $v->id . '/continuar?id=' . $idapp . "&id_modelo=" . $modelo,
                'etiqueta' => 'Gestión de Mantenimientos'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Crear Mantenimiento'
            ]
        ];
        $variables_url = "?id=" . $idapp . "&id_modelo=" . $modelo . "&id_transaccion=" . $transaccion;
        return view('contratos_transporte.mantenimientos.create')
            ->with('variables_url', $variables_url)
            ->with('miga_pan', $miga_pan)
            ->with('v', $v)
            ->with('ap', $aniop);
    }

    //guarda un mantenimiento
    public function store(Request $request)
    {
        $m = new Mantenimiento($request->all());
        $m->documento = null;
        if (isset($request->documento)) {
            $file = $request->file("documento");
            $name = time() . $file->getClientOriginalName();
            $filename = "img/documentos/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
            if ($flag !== false) {
                $m->documento = $filename;
            }
        }
        if ($m->save()) {
            $totalo = 0;
            $totalr = 0;
            if (isset($request->reporte_reporte)) {
                foreach ($request->reporte_reporte as $key => $re) {
                    $r = new Mantreportes();
                    $r->fecha_suceso = $this->fecha_null($request->reporte_fecha_suceso[$key]);
                    $r->reporte = $re;
                    $r->mantenimiento_id = $m->id;
                    if ($r->save()) {
                        $totalr = $totalr + 1;
                    }
                }
            }
            if (isset($request->obs_observacion)) {
                foreach ($request->obs_observacion as $key => $ob) {
                    $o = new Mantobs();
                    $o->fecha_suceso = $this->fecha_null($request->obs_fecha_suceso[$key]);
                    $o->observacion = $ob;
                    $o->mantenimiento_id = $m->id;
                    if ($o->save()) {
                        $totalo = $totalo + 1;
                    }
                }
            }
            return redirect("cte_mantenimientos/" . $request->vehiculo_id . "/" . $request->anioperiodo_id . "/create" . $request->variables_url)->with('flash_message', 'Mantenimiento creado con exito, con él han sido almacenados ' . $totalr . " reportes y " . $totalo . " observaciones");
        } else {
            return redirect("cte_mantenimientos/" . $request->vehiculo_id . "/" . $request->anioperiodo_id . "/create" . $request->variables_url)->with('mensaje_error', 'El mantenimiento no pudo ser almacenado');
        }
    }

    public function fecha_null($fecha)
    {
        if ($fecha == '') {
            return null;
        } else {
            return $fecha;
        }
    }

    //delete reporte
    public function deletereporte($id)
    {
        $reporte = Mantreportes::find($id);
        $variables_url = "?id=" . Input::get('id') . "&id_modelo=" . Input::get('id_modelo') . "&id_transaccion=" . Input::get('id_transaccion');
        if ($reporte->delete()) {
            return redirect("cte_mantenimientos/" . $reporte->mantenimiento->vehiculo_id . "/continuar" . $variables_url)->with('flash_message', 'Eliminado con exito');
        } else {
            return redirect("cte_mantenimientos/" . $reporte->mantenimiento->vehiculo_id . "/continuar" . $variables_url)->with('mensaje_error', 'No pudo ser borrado');
        }
    }

    //delete observacion
    public function deleteobs($id)
    {
        $obs = Mantobs::find($id);
        $variables_url = "?id=" . Input::get('id') . "&id_modelo=" . Input::get('id_modelo') . "&id_transaccion=" . Input::get('id_transaccion');
        if ($obs->delete()) {
            return redirect("cte_mantenimientos/" . $obs->mantenimiento->vehiculo_id . "/continuar" . $variables_url)->with('flash_message', 'Eliminado con exito');
        } else {
            return redirect("cte_mantenimientos/" . $obs->mantenimiento->vehiculo_id . "/continuar" . $variables_url)->with('mensaje_error', 'No pudo ser borrado');
        }
    }

    //store reporte
    public function storemant(Request $request)
    {
        if ($request->reporte == '') {
            return redirect("cte_mantenimientos/" . $request->vehiculo_id . "/continuar" . $request->variables_url)->with('mensaje_error', 'Debe indicar el reporte para proceder');
        }
        $r = new Mantreportes($request->all());
        if ($r->save()) {
            return redirect("cte_mantenimientos/" . $request->vehiculo_id . "/continuar" . $request->variables_url)->with('flash_message', 'Almacenado con exito');
        } else {
            return redirect("cte_mantenimientos/" . $request->vehiculo_id . "/continuar" . $request->variables_url)->with('mensaje_error', 'No pudo ser almacenado');
        }
    }

    //store observacion
    public function storeobs(Request $request)
    {
        if ($request->observacion == '') {
            return redirect("cte_mantenimientos/" . $request->vehiculo_id . "/continuar" . $request->variables_url)->with('mensaje_error', 'Debe indicar la observación para proceder');
        }
        $r = new Mantobs($request->all());
        if ($r->save()) {
            return redirect("cte_mantenimientos/" . $request->vehiculo_id . "/continuar" . $request->variables_url)->with('flash_message', 'Almacenado con exito');
        } else {
            return redirect("cte_mantenimientos/" . $request->vehiculo_id . "/continuar" . $request->variables_url)->with('mensaje_error', 'No pudo ser almacenado');
        }
    }
}
