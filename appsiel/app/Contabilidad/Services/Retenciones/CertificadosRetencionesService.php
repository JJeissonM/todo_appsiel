<?php
namespace App\Contabilidad\Services\Retenciones;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class CertificadosRetencionesService
{
    public function previsualizar($empresaId, $usuarioId, array $filtros)
    {
        $filtros=array_merge($filtros,(new FirmaCertificadosService)->resolver($empresaId,$filtros));
        $datos = (new DatosCertificadosService)->generar($empresaId, $filtros);
        return DB::transaction(function () use ($empresaId, $usuarioId, $datos, $filtros) {
            $salida = [];
            foreach ($datos as $dato) {
                $token = bin2hex(random_bytes(24));
                $id = DB::table('contab_certificados_retenciones')->insertGetId([
                    'token'=>$token, 'core_empresa_id'=>$empresaId, 'user_id'=>$usuarioId,
                    'core_tercero_id'=>$dato['tercero']['id'], 'impuesto'=>$filtros['tipo_impuesto'],
                    'fecha_desde'=>$dato['desde'], 'fecha_hasta'=>$dato['hasta'],
                    'datos'=>json_encode($dato, JSON_UNESCAPED_UNICODE), 'estado'=>'Previsualizado',
                    'created_at'=>date('Y-m-d H:i:s'), 'updated_at'=>date('Y-m-d H:i:s')
                ]);
                $salida[] = ['id'=>$id, 'token'=>$token, 'datos'=>$dato];
            }
            return $salida;
        });
    }

    public function obtener($token, $empresaId, $usuarioId)
    {
        $r = DB::table('contab_certificados_retenciones')->where('token', $token)
            ->where('core_empresa_id', $empresaId)->where('user_id', $usuarioId)->first();
        abort_unless($r, 404);
        if ($r->estado === 'Previsualizado' && strtotime($r->created_at) < time() - 86400) {
            abort(410, 'La previsualización venció. Genere una nueva consulta.');
        }
        return $r;
    }

    public function html($registro, $emitido = false)
    {
        if ($registro->html) { return $registro->html; }
        return view('contabilidad.certificados.documento', ['c'=>json_decode($registro->datos, true),
            'numero'=>$registro->id, 'emitido'=>$emitido, 'fecha_expedicion'=>date('Y-m-d')])->render();
    }

    /** El PDF emitido se conserva: una descarga o envío posterior utiliza el mismo archivo. */
    public function emitir($token, $empresaId, $usuarioId)
    {
        $this->obtener($token, $empresaId, $usuarioId);
        return DB::transaction(function () use ($token, $empresaId, $usuarioId) {
            $r = DB::table('contab_certificados_retenciones')->where('token', $token)
                ->where('core_empresa_id', $empresaId)->where('user_id', $usuarioId)->lockForUpdate()->first();
            $c = json_decode($r->datos, true);
            if ($c['bloqueos']) { throw new \InvalidArgumentException(implode(' ', $c['bloqueos'])); }
            if ($r->pdf_path) {
                $bytes = Storage::disk('local')->get($r->pdf_path);
                if (hash('sha256', $bytes) !== $r->pdf_sha256) { throw new \RuntimeException('El PDF archivado no pasó la verificación de integridad.'); }
                return ['registro'=>$r, 'bytes'=>$bytes, 'nombre'=>$this->nombre($r)];
            }
            $firma=(new FirmaCertificadosService)->resolver($empresaId,$c['filtros']);
            foreach ($firma as $campo=>$valor) {
                if (!array_key_exists($campo,$c['filtros']) || $c['filtros'][$campo]!=$valor) {
                    throw new \InvalidArgumentException('La firma autorizada cambió. Genere una nueva previsualización.');
                }
            }
            // Una anulación o corrección posterior a la consulta exige una nueva previsualización.
            $f=$c['filtros']; $f['tercero_id']=$r->core_tercero_id; $f['grupo_id']='';
            $vigente=null;
            foreach ((new DatosCertificadosService)->generar($empresaId,$f) as $actual) {
                if ($actual['anio']===$c['anio']) { $vigente=$actual; break; }
            }
            $comparar=$c; unset($comparar['filtros']);
            if ($vigente) { unset($vigente['filtros']); }
            if ($vigente != $comparar) { throw new \InvalidArgumentException('Los datos cambiaron después de la consulta. Genere una nueva previsualización.'); }
            $html = $this->html($r, true);
            $bytes = app('dompdf.wrapper')->loadHTML($html)->setPaper('letter', 'portrait')->output();
            $path = 'certificados_retenciones/'.$empresaId.'/'.$r->token.'.pdf';
            if (!Storage::disk('local')->put($path, $bytes)) { throw new \RuntimeException('No se pudo archivar el PDF.'); }
            DB::table('contab_certificados_retenciones')->where('id', $r->id)->update([
                'html'=>$html, 'pdf_path'=>$path, 'pdf_sha256'=>hash('sha256', $bytes),
                'estado'=>'Emitido', 'emitido_at'=>date('Y-m-d H:i:s'), 'updated_at'=>date('Y-m-d H:i:s')
            ]);
            $r->html=$html; $r->pdf_path=$path; $r->pdf_sha256=hash('sha256',$bytes); $r->estado='Emitido';
            return ['registro'=>$r, 'bytes'=>$bytes, 'nombre'=>$this->nombre($r)];
        });
    }

    private function nombre($r)
    {
        return 'certificado_'.$r->impuesto.'_'.$r->core_tercero_id.'_'.$r->fecha_desde.'_'.$r->fecha_hasta.'_'.$r->id.'.pdf';
    }

    public function descargarLote(array $tokens, $empresaId, $usuarioId)
    {
        if (!$tokens || count($tokens)>200) { throw new \InvalidArgumentException('Seleccione entre 1 y 200 certificados.'); }
        foreach ($tokens as $token) {
            if (!is_string($token)) { throw new \InvalidArgumentException('Identificador de certificado inválido.'); }
            $r=$this->obtener($token,$empresaId,$usuarioId);
            $c=json_decode($r->datos,true);
            if ($c['bloqueos']) { throw new \InvalidArgumentException('Hay certificados pendientes de revisión.'); }
        }
        $tokens=array_values(array_unique($tokens));
        $path=tempnam(storage_path('app'),'certificados_');
        if (!$path) { throw new \RuntimeException('No se pudo crear el archivo de descarga.'); }
        $zip=new \ZipArchive;
        if ($zip->open($path,\ZipArchive::OVERWRITE)!==true) { unlink($path); throw new \RuntimeException('No se pudo crear el ZIP.'); }
        try {
            foreach ($tokens as $token) {
                $pdf=$this->emitir($token,$empresaId,$usuarioId);
                if (!$zip->addFromString($pdf['nombre'],$pdf['bytes'])) { throw new \RuntimeException('No se pudo agregar un certificado al ZIP.'); }
            }
            if (!$zip->close()) { throw new \RuntimeException('No se pudo completar el ZIP.'); }
        } catch (\Exception $e) { @$zip->close(); @unlink($path); throw $e; }
        return $path;
    }

    public function enviar($token, $empresaId, $usuarioId)
    {
        $r = $this->obtener($token, $empresaId, $usuarioId);
        $c = json_decode($r->datos, true);
        $email = trim(isset($c['tercero']['email']) ? $c['tercero']['email'] : '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { throw new \InvalidArgumentException('El proveedor no tiene un correo válido. Corríjalo y genere una nueva previsualización.'); }
        if (($c['filtros']['tipo_impuesto']==='ICA' || $c['filtros']['firma_fuente']==='manual') && empty($c['filtros']['firma_imagen'])) {
            throw new \InvalidArgumentException('Actualice la imagen de la firma autorizada en el catálogo y genere una nueva previsualización antes de enviar.');
        }
        $pdf = $this->emitir($token, $empresaId, $usuarioId);
        $envioId = DB::transaction(function () use ($r, $usuarioId, $email) {
            DB::table('contab_certificados_retenciones')->where('id', $r->id)->lockForUpdate()->first();
            if (DB::table('contab_certificados_envios')->where('certificado_id', $r->id)->whereIn('estado', ['Enviando', 'Enviado'])->exists()) {
                throw new \InvalidArgumentException('El certificado ya fue enviado o tiene un envío en curso.');
            }
            return DB::table('contab_certificados_envios')->insertGetId(['certificado_id'=>$r->id,
                'destinatario'=>$email, 'estado'=>'Enviando', 'user_id'=>$usuarioId,
                'created_at'=>date('Y-m-d H:i:s'), 'updated_at'=>date('Y-m-d H:i:s')]);
        });
        try {
            $this->entregar($email, $c, $pdf);
            DB::table('contab_certificados_envios')->where('id', $envioId)->update(['estado'=>'Enviado', 'updated_at'=>date('Y-m-d H:i:s')]);
        } catch (\Exception $e) {
            DB::table('contab_certificados_envios')->where('id', $envioId)->update([
                'estado'=>'Fallido', 'error'=>'No se pudo entregar al servicio de correo.', 'updated_at'=>date('Y-m-d H:i:s')]);
            \Log::error('Fallo de correo certificado '.$r->id, ['exception'=>$e]);
            throw new \RuntimeException('No se pudo enviar. Revise la configuración de correo y vuelva a intentar.');
        }
        return ['mensaje'=>'Enviado al servicio de correo: '.$email, 'destinatario'=>$email];
    }

    protected function entregar($email, array $c, array $pdf)
    {
        // Mismo transporte y plantilla de correo que los desprendibles; adjunto archivado e individual.
        $cuerpo = 'Se adjunta su certificado de '.($c['filtros']['tipo_impuesto']==='ICA' ? 'retención de ICA' : 'retención en la fuente')
            .' del período '.e($c['desde']).' a '.e($c['hasta']).'.<br>'.e($c['empresa']['descripcion']);
        Mail::send('layouts.cuerpo_email', ['cuerpo_mensaje'=>$cuerpo], function ($m) use ($email, $c, $pdf) {
            $from = config('mail.username') ?: config('mail.from.address');
            if (!$from) { throw new \RuntimeException('Falta el remitente de correo.'); }
            $m->from($from, $c['empresa']['descripcion'])->to($email)
                ->subject('Certificado de retenciones '.$c['filtros']['tipo_impuesto'].' '.$c['anio'])
                ->attachData($pdf['bytes'], $pdf['nombre'], ['mime'=>'application/pdf']);
        });
        if (Mail::failures()) { throw new \RuntimeException('El transporte rechazó destinatarios.'); }
    }
}
