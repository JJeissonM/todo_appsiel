<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Contabilidad\Services\AuxiliarTercerosService;
use App\Core\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuxiliarTercerosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $empresa = Empresa::findOrFail(Auth::user()->empresa_id);
        $terceros = [''=>'Todos los terceros'];
        foreach (DB::table('core_terceros')->where('core_empresa_id', $empresa->id)->orderBy('descripcion')->get(['id', 'numero_identificacion', 'descripcion']) as $tercero) {
            $terceros[$tercero->id] = $tercero->numero_identificacion.' - '.$tercero->descripcion;
        }
        $cuentas = [''=>'Todas las cuentas'];
        foreach (DB::table('contab_cuentas')->where('core_empresa_id', $empresa->id)->orderBy('codigo')->get(['id', 'codigo', 'descripcion']) as $cuenta) {
            $cuentas[$cuenta->id] = $cuenta->codigo.' - '.$cuenta->descripcion;
        }
        return view('contabilidad.auxiliar_terceros', compact('empresa', 'terceros', 'cuentas'));
    }

    public function export(Request $request)
    {
        // La empresa autorizada es la activa, igual que en los otros libros contables.
        if ((string)$request->input('empresa') !== (string)Auth::user()->empresa_id) { abort(403); }
        $filters = array_filter($request->only('empresa', 'ano', 'tercero', 'cuenta', 'cuenta_desde', 'cuenta_hasta'), function ($value) { return $value !== null && $value !== ''; });
        try { $service = new AuxiliarTercerosService($filters); }
        catch (\InvalidArgumentException $e) { return redirect()->back()->withInput()->with('mensaje_error', $e->getMessage()); }
        $directory = storage_path('app/auxiliar_terceros_'.bin2hex(random_bytes(16)));
        set_time_limit(0);
        try { $result = $service->export($directory); }
        catch (\Exception $e) {
            AuxiliarTercerosService::removeDirectory($directory);
            // No publicar excepciones SQL: pueden contener datos de conexión.
            return redirect()->back()->withInput()->with('mensaje_error', 'No se pudo generar el auxiliar completo. Revise el espacio disponible y ejecute el comando contabilidad:auxiliar-terceros para verificar la generación.');
        }
        return new StreamedResponse(function () use ($result, $directory) {
            try { readfile($result['path']); }
            finally { AuxiliarTercerosService::removeDirectory($directory); }
        }, 200, ['Content-Type'=>'application/zip', 'Content-Disposition'=>'attachment; filename="'.basename($result['path']).'"', 'Content-Length'=>filesize($result['path']), 'Cache-Control'=>'private, no-store']);
    }
}
