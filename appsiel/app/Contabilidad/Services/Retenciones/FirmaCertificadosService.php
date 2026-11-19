<?php
namespace App\Contabilidad\Services\Retenciones;

use App\Core\FirmaAutorizada;

class FirmaCertificadosService
{
    private function consulta($empresaId)
    {
        return FirmaAutorizada::join('core_terceros AS t', 't.id', '=', 'core_firmas_autorizadas.core_tercero_id')
            ->where('core_firmas_autorizadas.core_empresa_id', $empresaId)
            ->where('t.core_empresa_id', $empresaId)->where('core_firmas_autorizadas.estado', 'Activo');
    }

    public function opciones($empresaId)
    {
        return $this->consulta($empresaId)->orderBy('t.descripcion')->select('core_firmas_autorizadas.id',
            'core_firmas_autorizadas.titulo_tercero', 't.descripcion', 't.numero_identificacion')->get();
    }

    public function resolver($empresaId, array $filtros)
    {
        $firma=$this->consulta($empresaId)->where('core_firmas_autorizadas.id', isset($filtros['firma_autorizada_id']) ? $filtros['firma_autorizada_id'] : 0)
            ->select('core_firmas_autorizadas.*', 't.descripcion AS responsable', 't.numero_identificacion AS identificacion_responsable')->first();
        if (!$firma) { throw new \InvalidArgumentException('Seleccione una firma autorizada activa de la empresa actual.'); }
        if (!trim($firma->responsable) || !$firma->identificacion_responsable) { throw new \InvalidArgumentException('Complete el nombre y la identificación del tercero de la firma autorizada.'); }
        $manual=$filtros['tipo_impuesto']==='ICA' || $filtros['firma_fuente']==='manual';
        $imagen='';
        $directorio=realpath(storage_path('app/firmas_autorizadas'));
        $ruta=$directorio && !empty($firma->imagen) ? realpath($directorio.'/'.$firma->imagen) : false;
        if ($ruta && strpos($ruta, $directorio.DIRECTORY_SEPARATOR)===0 && is_file($ruta) && is_readable($ruta) && filesize($ruta)<=2097152) {
            $info=@getimagesize($ruta);
            if ($info && in_array($info['mime'], ['image/png','image/jpeg','image/gif']) && $info[0]<=3000 && $info[1]<=3000) {
                $imagen='data:'.$info['mime'].';base64,'.base64_encode(file_get_contents($ruta));
            }
        }
        if ($manual && !$imagen) { throw new \InvalidArgumentException('La firma autorizada debe tener una imagen de firma adjunta válida en el catálogo para firma manual o ICA.'); }
        return ['firma_autorizada_id'=>$firma->id, 'responsable'=>$firma->responsable,
            'identificacion_responsable'=>$firma->identificacion_responsable,
            'titulo_responsable'=>$firma->titulo_tercero, 'firma_imagen'=>$imagen];
    }
}
