<?php

namespace App\Http\Controllers\web;

use App\Core\Configuracion;
use App\web\Configuraciones;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\web\Configuracionfuente;

class ConfiguracionesController extends Controller
{

    public function index()
    {
    }

    public function store(Request $request)
    {

        $fuentes = null;
        foreach ($request->fuentes as $f) {
            if ($f != '') {
                $fuentes[] = $f;
            }
        }

        $conf = new Configuraciones($request->all());
        $flag = $conf->save();



        if ($flag) {
            //guardamos las fuentes
            $total = count($fuentes);
            $guardadas = 0;
            foreach ($fuentes as $f) {
                $cf = new Configuracionfuente();
                $cf->fuente_id = $f;
                $cf->configuracion_id = $conf->id;
                if ($cf->save()) {
                    $guardadas = $guardadas + 1;
                }
            }
            $message = 'Configuración almacenada correctamente, además se guardaron ' . $guardadas . '/' . $total . ' fuentes';
            return redirect()->back()
                ->with('flash_message', $message);
        } else {
            $message = 'Error inesperado, por favor intente nuevamente mas tarde';
            return redirect()->back()
                ->with('mensaje_error', $message);
        }
    }

    public function update(Request $request, $id)
    {

        $fuentes = null;
        foreach ($request->fuentes as $f) {
            if ($f != '') {
                $fuentes[] = $f;
            }
        }

        $conf = Configuraciones::find($id);
        $conf->fill($request->all());
        $flag = $conf->save();



        if ($flag) {
            //eliminamos las fuentes que había
            $fuentesya = Configuracionfuente::where('configuracion_id', $id)->get();
            if (count($fuentesya) > 0) {
                foreach ($fuentesya as $fy) {
                    $fy->delete();
                }
            }
            //guardamos las nuevas fuentes
            $total = count($fuentes);
            $guardadas = 0;
            foreach ($fuentes as $f) {
                $cf = new Configuracionfuente();
                $cf->fuente_id = $f;
                $cf->configuracion_id = $conf->id;
                if ($cf->save()) {
                    $guardadas = $guardadas + 1;
                }
            }
            $message = 'Configuración modificada
             correctamente, además se guardaron ' . $guardadas . '/' . $total . ' fuentes';
            return redirect()->back()
                ->with('flash_message', $message);
        } else {
            $message = 'Error inesperado, por favor intente nuevamente mas tarde';
            return redirect()->back()
                ->with('mensaje_error', $message);
        }
    }
}
