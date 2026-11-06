<?php
namespace App\Http\Controllers\Calificaciones;

use App\Http\Controllers\Controller;
use App\Calificaciones\AgrupacionCalificacion;
use App\Calificaciones\Services\AgrupacionesColumnasService;
use Illuminate\Http\Request;

class AgrupacionesColumnasController extends Controller
{
    public function __construct() { $this->middleware('auth'); }
    private function grupo($id)
    {
        $grupo = AgrupacionCalificacion::findOrFail($id);
        if (!$grupo->authorizeCrudAction('edit', auth()->user())) abort(403);
        return $grupo;
    }
    public function guardar(Request $request, $id)
    {
        $grupo = $this->grupo($id);
        $this->validate($request, ['columna_calificacion' => ['required', 'regex:/^C([1-9]|1[0-5])$/'],
            'label' => 'required|max:60', 'porcentaje' => 'required|numeric|min:0.0001|max:100']);
        try {
            app(AgrupacionesColumnasService::class)->guardarColumna($grupo, $request->only('columna_calificacion', 'label', 'porcentaje'));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() !== 422) throw $e;
            return redirect()->back()->withInput()->with('mensaje_error', $e->getMessage());
        }
        return redirect()->back()->with('flash_message', 'Columna asociada correctamente.');
    }
    public function eliminar(Request $request, $id, $columna)
    {
        $grupo = $this->grupo($id);
        try {
            app(AgrupacionesColumnasService::class)->eliminarColumna($grupo, $columna);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() !== 422) throw $e;
            return redirect()->back()->with('mensaje_error', $e->getMessage());
        }
        return redirect()->back()->with('flash_message', 'Columna desasociada correctamente.');
    }
}
