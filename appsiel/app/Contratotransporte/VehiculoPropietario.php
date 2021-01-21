<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use Auth;

use App\Contratotransporte\Propietario;

class VehiculoPropietario extends Vehiculo
{

    protected $fillable = ['id', 'int', 'bloqueado_cuatro_contratos', 'placa', 'numero_vin', 'numero_motor', 'modelo', 'marca', 'clase', 'color', 'cilindraje', 'capacidad', 'fecha_control_kilometraje', 'propietario_id', 'created_at', 'updated_at'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Interno', 'Vinculación', 'Placa', 'Marca', 'Clase', 'Modelo', 'Propietario', 'Bloqueado 4 Contratos/Mes'];

    public $urls_acciones = '{"show":"cte_vehiculos/id_fila/show"}';

    public static function consultar_registros2($nro_registros, $search)
    {
    	$placa = Auth::user()->email;
    	$vehiculo = Vehiculo::where('placa',$placa)->get()->first();
    	$propietario_id = 0;
    	if (!is_null($vehiculo) )
    	{
    		$propietario_id = $vehiculo->propietario_id;
    	}    	

        return Vehiculo::leftJoin('cte_propietarios', 'cte_propietarios.id', '=', 'cte_vehiculos.propietario_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_propietarios.tercero_id')
            ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
            ->where('cte_vehiculos.propietario_id',$propietario_id)
            ->select(
                'cte_vehiculos.int AS campo1',
                'cte_vehiculos.numero_vin AS campo2',
                'cte_vehiculos.placa AS campo3',
                'cte_vehiculos.marca AS campo4',
                'cte_vehiculos.clase AS campo5',
                'cte_vehiculos.modelo AS campo6',
                DB::raw('CONCAT(core_tipos_docs_id.abreviatura," - ",core_terceros.numero_identificacion," - ",core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo7'),
                'cte_vehiculos.bloqueado_cuatro_contratos AS campo8',
                'cte_vehiculos.id AS campo9'
            )
            ->orderBy('cte_vehiculos.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Vehiculo::leftJoin('cte_propietarios', 'cte_propietarios.id', '=', 'cte_vehiculos.propietario_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_propietarios.tercero_id')
            ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
            ->select(
                'cte_vehiculos.int AS INTERNO',
                'cte_vehiculos.numero_vin AS VINCULACIÓN',
                'cte_vehiculos.placa AS PLACA',
                'cte_vehiculos.marca AS MARCA',
                'cte_vehiculos.clase AS CLASE',
                'cte_vehiculos.modelo AS MODELO',
                DB::raw('CONCAT(core_tipos_docs_id.abreviatura," - ",core_terceros.numero_identificacion," - ",core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS PROPIETARIO'),
                'cte_vehiculos.bloqueado_cuatro_contratos AS BLOQUEADO_4_CONTRATOS'
            )
            ->orderBy('cte_vehiculos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE VEHÍCULOS";
    }

}
