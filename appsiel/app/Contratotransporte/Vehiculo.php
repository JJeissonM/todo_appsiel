<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Core\PasswordReset;
use App\User;

class Vehiculo extends Model
{
    protected $table = 'cte_vehiculos';
    protected $fillable = ['id', 'int', 'bloqueado_cuatro_contratos', 'placa', 'numero_vin', 'numero_motor', 'modelo', 'marca', 'clase', 'color', 'cilindraje', 'capacidad', 'fecha_control_kilometraje', 'propietario_id', 'created_at', 'updated_at'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Interno', 'Vinculación', 'Placa', 'Marca', 'Clase', 'Modelo', 'Propietario', 'Bloqueado 4 Contratos/Mes'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"cte_vehiculos/id_fila/show"}';

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


    public static function consultar_registros2($nro_registros, $search)
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
                'cte_vehiculos.bloqueado_cuatro_contratos AS campo8',
                'cte_vehiculos.id AS campo9'
            )->where("cte_vehiculos.int", "LIKE", "%$search%")
            ->orWhere("cte_vehiculos.numero_vin", "LIKE", "%$search%")
            ->orWhere("cte_vehiculos.placa", "LIKE", "%$search%")
            ->orWhere("cte_vehiculos.clase", "LIKE", "%$search%")
            ->orWhere("cte_vehiculos.marca", "LIKE", "%$search%")
            ->orWhere("cte_vehiculos.modelo", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_id.abreviatura," - ",core_terceros.numero_identificacion," - ",core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
            ->orWhere("cte_vehiculos.bloqueado_cuatro_contratos", "LIKE", "%$search%")
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
            )->where("cte_vehiculos.int", "LIKE", "%$search%")
            ->orWhere("cte_vehiculos.numero_vin", "LIKE", "%$search%")
            ->orWhere("cte_vehiculos.placa", "LIKE", "%$search%")
            ->orWhere("cte_vehiculos.clase", "LIKE", "%$search%")
            ->orWhere("cte_vehiculos.marca", "LIKE", "%$search%")
            ->orWhere("cte_vehiculos.modelo", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_id.abreviatura," - ",core_terceros.numero_identificacion," - ",core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
            ->orWhere("cte_vehiculos.bloqueado_cuatro_contratos", "LIKE", "%$search%")
            ->orderBy('cte_vehiculos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE VEHÍCULOS";
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

    public function vehiculoconductors()
    {
        return $this->hasMany(Vehiculoconductor::class);
    }

    public function store_adicional($datos, $registro)
    {
        $descripcion = 'Vehículo ' . $registro->marca . ' ' . $registro->modelo . ', placa: ' . $registro->placa;

        $placa = str_replace(" ", "", $registro->placa);

        $password = str_random(7);

        $usuario = User::crear_y_asignar_role($descripcion, $placa, 22, $password); // 22 = Vehículo (FUEC)

        // Se almacena la contraseña temporalmente; cuando el usuario la cambie, se eliminará
        PasswordReset::insert([
            'email' => $placa,
            'token' => $password
        ]);
    }

    public static function get_campos_adicionales_edit($lista_campos, $registro)
    {
        //$placa = str_replace(" ", "", $registro->placa);
        $placa = $registro->placa;

        $user = User::where('email', $placa)->get()->first();

        if (!is_null($user)) {
            array_push($lista_campos, [
                "id" => 9999,
                "descripcion" => 'user_id',
                "tipo" => "hidden",
                "name" => "user_id",
                "opciones" => "",
                "value" => $user->id,
                "atributos" => [],
                "definicion" => "",
                "requerido" => 0,
                "editable" => 1,
                "unico" => 0
            ]);
        }

        return $lista_campos;
    }


    public function update_adicional($datos, $id)
    {
        // Se actualiza al usuario asociado
        $user = User::find($datos['user_id']);

        if (!is_null($user)) {
            $descripcion = 'Vehículo ' . $datos['marca'] . ' ' . $datos['modelo'] . ', placa: ' . $datos['placa'];

            //$placa = str_replace(" ", "", $datos['placa']);
            $placa = $datos['placa'];

            PasswordReset::where('email', $user->email)->update(['email' => $placa]);

            $user->update(['name' => $descripcion, 'email' => $placa]);
        }
    }
}
