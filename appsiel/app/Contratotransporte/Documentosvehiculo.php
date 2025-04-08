<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Documentosvehiculo extends Model
{
    protected $table = 'cte_documentosvehiculos';
    protected $fillable = ['id', 'vehiculo_id', 'tarjeta_operacion', 'documento', 'recurso', 'nro_documento', 'vigencia_inicio', 'vigencia_fin', 'created_at', 'updated_at'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Nro. Documento', 'Documento', 'Inicio Vigencia', 'Vence', 'Vehículo', 'Documento Propietario', 'Propietario'];

    public $vistas = '{"index":"layouts.index3"}';

    public static function consultar_registros2($nro_registros, $search)
    {
        return Documentosvehiculo::leftJoin('cte_vehiculos', 'cte_vehiculos.id', '=', 'cte_documentosvehiculos.vehiculo_id')
            ->leftJoin('cte_propietarios', 'cte_propietarios.id', '=', 'cte_vehiculos.propietario_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_propietarios.tercero_id')
            ->select(
                'cte_documentosvehiculos.nro_documento AS campo1',
                'cte_documentosvehiculos.documento AS campo2',
                'cte_documentosvehiculos.vigencia_inicio AS campo3',
                'cte_documentosvehiculos.vigencia_fin AS campo4',
                DB::raw('CONCAT("INTERNO: ",cte_vehiculos.int," - PLACA: ",cte_vehiculos.placa," - MODELO: ",cte_vehiculos.modelo," - MARCA: ",cte_vehiculos.marca," - CLASE: ",cte_vehiculos.clase) AS campo5'),
                'core_terceros.numero_identificacion AS campo6',
                'core_terceros.descripcion AS campo7',
                'cte_documentosvehiculos.id AS campo8'
            )->where("cte_documentosvehiculos.nro_documento", "LIKE", "%$search%")
            ->orWhere("cte_documentosvehiculos.documento", "LIKE", "%$search%")
            ->orWhere("cte_documentosvehiculos.vigencia_inicio", "LIKE", "%$search%")
            ->orWhere("cte_documentosvehiculos.vigencia_fin", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT("INTERNO: ",cte_vehiculos.int," - PLACA: ",cte_vehiculos.placa," - MODELO: ",cte_vehiculos.modelo," - MARCA: ",cte_vehiculos.marca," - CLASE: ",cte_vehiculos.clase)'), "LIKE", "%$search%")
            ->orWhere("core_terceros.numero_identificacion", "LIKE", "%$search%")
            ->orWhere('core_terceros.descripcion', "LIKE", "%$search%")
            ->orderBy('cte_documentosvehiculos.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Documentosvehiculo::leftJoin('cte_vehiculos', 'cte_vehiculos.id', '=', 'cte_documentosvehiculos.vehiculo_id')
            ->leftJoin('cte_propietarios', 'cte_propietarios.id', '=', 'cte_vehiculos.propietario_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_propietarios.tercero_id')
            ->select(
                'cte_documentosvehiculos.nro_documento AS NRO_DOCUMENTO',
                'cte_documentosvehiculos.documento AS DOCUMENTO',
                'cte_documentosvehiculos.vigencia_inicio AS INICIO_VIGENCIA',
                'cte_documentosvehiculos.vigencia_fin AS FIN_VIGENCIA',
                DB::raw('CONCAT("INTERNO: ",cte_vehiculos.int," - PLACA: ",cte_vehiculos.placa," - MODELO: ",cte_vehiculos.modelo," - MARCA: ",cte_vehiculos.marca," - CLASE: ",cte_vehiculos.clase) AS VEHÍCULO'),
                'core_terceros.numero_identificacion AS IDENTIFICACIÓN',
                'core_terceros.descripcion AS PROPIETARIO'
            )->where("cte_documentosvehiculos.nro_documento", "LIKE", "%$search%")
            ->orWhere("cte_documentosvehiculos.documento", "LIKE", "%$search%")
            ->orWhere("cte_documentosvehiculos.vigencia_inicio", "LIKE", "%$search%")
            ->orWhere("cte_documentosvehiculos.vigencia_fin", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT("INTERNO: ",cte_vehiculos.int," - PLACA: ",cte_vehiculos.placa," - MODELO: ",cte_vehiculos.modelo," - MARCA: ",cte_vehiculos.marca," - CLASE: ",cte_vehiculos.clase)'), "LIKE", "%$search%")
            ->orWhere("core_terceros.numero_identificacion", "LIKE", "%$search%")
            ->orWhere('core_terceros.descripcion', "LIKE", "%$search%")
            ->orderBy('cte_documentosvehiculos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE DOCUMENTOS DE VEHÍCULOS";
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }
}
