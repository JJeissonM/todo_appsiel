<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Documentosvehiculo extends Model
{
    protected $table = 'cte_documentosvehiculos';
    protected $fillable = ['id', 'vehiculo_id', 'tarjeta_operacion', 'documento', 'recurso', 'nro_documento', 'vigencia_inicio', 'vigencia_fin', 'created_at', 'updated_at'];

    public $encabezado_tabla = ['Nro. Documento', 'Documento', 'Inicio Vigencia', 'Vence', 'Vehículo', 'Documento Propietario', 'Propietario', 'Acción'];

    public $vistas = '{"index":"layouts.index3"}';

    public static function consultar_registros2()
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
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo7'),
                'cte_documentosvehiculos.id AS campo8'
            )
            ->orderBy('cte_documentosvehiculos.created_at', 'DESC')
            ->paginate(100);
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }
}
