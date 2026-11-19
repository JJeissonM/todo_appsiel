<?php
namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Contabilidad\Services\Retenciones\FiltrosCertificados;
use App\Contabilidad\Services\Retenciones\CertificadosRetencionesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CertificadosRetencionesController extends Controller
{
    public function __construct() { $this->middleware('auth'); }

    private function autorizar($permiso = 'contab_certificados_retenciones')
    {
        RetencionesReportController::autorizar($permiso);
    }

    public function index()
    {
        $this->autorizar();
        $empresaId=Auth::user()->empresa_id;
        $terceros=DB::table('core_terceros')->where('core_empresa_id',$empresaId)->orderBy('descripcion')->get();
        $grupos=DB::table('compras_clases_proveedores AS g')
            ->whereRaw('EXISTS (SELECT 1 FROM compras_proveedores p INNER JOIN core_terceros t ON t.id=p.core_tercero_id WHERE p.clase_proveedor_id=g.id AND t.core_empresa_id=?)', [$empresaId])
            ->orderBy('g.descripcion')->select('g.id','g.descripcion')->get();
        $historial=DB::table('contab_certificados_retenciones AS c')->join('core_terceros AS t','t.id','=','c.core_tercero_id')
            ->where('c.core_empresa_id',$empresaId)->where('c.user_id',Auth::id())->where('c.estado','Emitido')
            ->orderBy('c.id','desc')->take(50)->select('c.*','t.descripcion AS proveedor')->get();
        return view('contabilidad.certificados.index',compact('terceros','grupos','historial'));
    }

    public function previsualizar(Request $request)
    {
        $this->autorizar();
        try {
            $campos=['periodo','anio','bimestre','fecha_desde','fecha_hasta','tipo_impuesto','compras','tercero_id','grupo_id',
                'codigo_municipio','municipio','confirmar_municipio','ciudad_consignacion','responsable',
                'identificacion_responsable','norma_ica','firma_fuente'];
            $f=FiltrosCertificados::normalizar($request->only($campos));
            if ($request->hasFile('firma')) {
                $archivo=$request->file('firma');
                $info=$archivo->isValid() ? @getimagesize($archivo->getRealPath()) : false;
                if (!$info || !in_array($info['mime'],['image/png','image/jpeg']) || $archivo->getSize()>2097152 || $info[0]>3000 || $info[1]>3000) {
                    throw new \InvalidArgumentException('La firma debe ser PNG o JPG, hasta 2 MB y 3000 × 3000 píxeles.');
                }
                $f['firma_imagen']='data:'.$info['mime'].';base64,'.base64_encode(file_get_contents($archivo->getRealPath()));
            }
            if ($f['tercero_id'] && !DB::table('core_terceros')->where('core_empresa_id',Auth::user()->empresa_id)->where('id',$f['tercero_id'])->exists()) { abort(403); }
            if ($f['grupo_id'] && !DB::table('compras_proveedores AS p')->join('core_terceros AS t','t.id','=','p.core_tercero_id')
                ->where('t.core_empresa_id',Auth::user()->empresa_id)->where('p.clase_proveedor_id',$f['grupo_id'])->exists()) { abort(403); }
            $certificados=(new CertificadosRetencionesService)->previsualizar(Auth::user()->empresa_id,Auth::id(),$f);
            return view('contabilidad.certificados.resultados',compact('certificados'));
        } catch (\InvalidArgumentException $e) { return response()->json(['error'=>$e->getMessage()],422); }
    }

    public function ver($token)
    {
        $this->autorizar();
        $servicio=new CertificadosRetencionesService;
        return response($servicio->html($servicio->obtener($token,Auth::user()->empresa_id,Auth::id())),200,['Cache-Control'=>'private, no-store']);
    }

    public function pdf($token)
    {
        $this->autorizar();
        try {
            $pdf=(new CertificadosRetencionesService)->emitir($token,Auth::user()->empresa_id,Auth::id());
            return response($pdf['bytes'],200,['Content-Type'=>'application/pdf','Content-Disposition'=>'attachment; filename="'.$pdf['nombre'].'"','Cache-Control'=>'private, no-store']);
        } catch (\InvalidArgumentException $e) { return response(e($e->getMessage()),422); }
    }

    public function enviar($token)
    {
        $this->autorizar('contab_enviar_certificados_retenciones');
        try {
            return response()->json((new CertificadosRetencionesService)->enviar($token,Auth::user()->empresa_id,Auth::id()));
        } catch (\InvalidArgumentException $e) { return response()->json(['error'=>$e->getMessage()],422); }
        catch (\RuntimeException $e) { return response()->json(['error'=>$e->getMessage()],422); }
    }

    public function descargarLote(Request $request)
    {
        $this->autorizar();
        try {
            $tokens=$request->input('tokens',[]);
            if (!is_array($tokens)) { throw new \InvalidArgumentException('Seleccione los certificados a descargar.'); }
            $path=(new CertificadosRetencionesService)->descargarLote($tokens,Auth::user()->empresa_id,Auth::id());
            return response()->download($path,'certificados_retenciones.zip',['Cache-Control'=>'private, no-store'])->deleteFileAfterSend(true);
        } catch (\InvalidArgumentException $e) { return response(e($e->getMessage()),422); }
    }
}
