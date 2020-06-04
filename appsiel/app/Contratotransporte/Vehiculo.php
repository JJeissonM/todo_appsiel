<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Vehiculo extends Model
{
    protected $table = 'cte_vehiculos';
    protected $fillable = ['id', 'int', 'placa', 'numero_vin', 'numero_motor', 'modelo', 'marca', 'clase', 'color', 'cilindraje', 'capacidad', 'fecha_control_kilometraje', 'propietario_id', 'created_at', 'updated_at'];

    public $encabezado_tabla = ['Interno', 'Vinculación', 'Placa', 'Marca', 'Clase', 'Modelo', 'Propietario', 'Acción'];

    public $vistas = '{"index":"layouts.index3"}';

    public static function opciones_campo_select()
    {
        $opciones = Vehiculo::all();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->clase . ' ' . $opcion->marca . ' ' . $opcion->modelo . ' ' . $opcion->placa . ')';
        }

        return $vec;
    }


    public static function consultar_registros2()
    {
        return Vehiculo::leftJoin('cte_propietarios', 'cte_propietarios.id', '=', 'cte_vehiculos.propietario_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_propietarios.tercero_id')
            ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
            ->select(
                'cte_vehiculos.int AS campo1',
                'cte_vehiculos.numero_vin AS campo2',
                'cte_vehiculos.placa AS campo3',
                'cte_vehiculos.marca AS campo4',
                'cte_vehiculos.clase AS campo5',
                'cte_vehiculos.modelo AS campo6',
                DB::raw('CONCAT(core_tipos_docs_id.abreviatura," - ",core_terceros.numero_identificacion," - ",core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo7'),
                'cte_vehiculos.id AS campo8'
            )
            ->orderBy('cte_vehiculos.created_at', 'DESC')
            ->paginate(100);
    }

    public function propietario()
    {
        return $this->belongsTo(Propietario::class);
    }

    public function documentosvehiculos()
    {
        return $this->hasMany(Documentosvehiculo::class);
    }

    public function contratos()
    {
        return $this->hasMany(Contrato::class);
    }
}
