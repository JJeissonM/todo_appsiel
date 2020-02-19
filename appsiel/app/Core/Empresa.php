<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

use DB;

class Empresa extends Model
{
    protected $table = 'core_empresas'; 

    protected $fillable = ['tipo','razon_social','nombre1','otros_nombres','apellido1','apellido2',
    						'descripcion','id_tipo_documento_id','numero_identificacion',
    						'digito_verificacion','ciudad_expedicion','direccion1','direccion2','barrio','codigo_ciudad','codigo_postal','telefono1','telefono2','email','pagina_web','estado'];

    public $encabezado_tabla = ['Nombre/Razón Social','Establecimiento','Identificación','Dirección','Teléfono','Estado','Acción'];

    public static function consultar_registros()
    {
    	
    	$select_raw = 'CONCAT(core_empresas.nombre1," ",core_empresas.otros_nombres," ",core_empresas.apellido1," ",core_empresas.apellido2," ",core_empresas.razon_social) AS campo1';

        $registros = Empresa::select(DB::raw($select_raw),'core_empresas.descripcion AS campo2','core_empresas.numero_identificacion AS campo3','core_empresas.direccion1 AS campo4','core_empresas.telefono1 AS campo5','core_empresas.estado AS campo6','core_empresas.id AS campo7')
                    ->get()
                    ->toArray();

        return $registros;
    }
}
