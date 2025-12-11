<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use App\Nomina\TipoTurno;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class NomCargo extends Model
{
    //protected $table = 'nom_cargos';
    protected $fillable = ['descripcion', 'estado', 'cargo_padre_id', 'rango_salarial_id'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = NomCargo::select(
            'nom_cargos.descripcion AS campo1',
            'nom_cargos.estado AS campo2',
            'nom_cargos.id AS campo3'
        )
            ->where("nom_cargos.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_cargos.estado", "LIKE", "%$search%")
            ->orderBy('nom_cargos.created_at', 'DESC')
            ->paginate($nro_registros);
        return $registros;
    }

    public static function sqlString($search)
    {
        $string = NomCargo::select(
            'nom_cargos.descripcion AS DESCRIPCIÇ"N',
            'nom_cargos.estado AS ESTADO'
        )
            ->where("nom_cargos.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_cargos.estado", "LIKE", "%$search%")
            ->orderBy('nom_cargos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportaciÇün en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CARGOS";
    }

    public static function opciones_campo_select()
    {
        $opciones = NomCargo::where('estado', 'Activo')->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }


    public function tipos_turno()
    {
        return $this->belongsToMany(TipoTurno::class, 'nom_cargo_tipo_turno', 'cargo_id', 'tipo_turno_id');
    }

    /*
        FUNCIONES relativas al modelo relacionado a este modelo, en este caso TipoTurno
    */
    public static function get_tabla($registro_modelo_padre, $registros_asignados)
    {
        $encabezado_tabla = ['ID', 'ID turno', 'Tipo de turno', 'Valor', 'Hora entrada 1', 'Hora salida 1', 'Hora entrada 2', 'Hora salida 2', 'Estado', 'Acción'];

        $registros = [];
        $i = 0;
        foreach ($registros_asignados as $fila) {
            $registros[$i] = collect([
                $fila['id'],            // Se usa para mostrar
                $fila['id'],            // Se usa en data-registro_modelo_hijo_id para modificar/eliminar
                $fila['descripcion'],
                $fila['valor'],
                $fila['checkin_time_1'],
                $fila['checkout_time_1'],
                $fila['checkin_time_2'],
                $fila['checkout_time_2'],
                $fila['estado'],
            ]);
            $i++;
        }

        return View::make('core.modelos.tabla_modelo_relacionado', compact('encabezado_tabla', 'registros', 'registro_modelo_padre'))->render();
    }

    public static function get_opciones_modelo_relacionado($cargo_id)
    {
        $vec[''] = '';
        $opciones = DB::table('nom_turnos_tipos')->get();
        foreach ($opciones as $opcion) {
            $esta = DB::table('nom_cargo_tipo_turno')->where('cargo_id', $cargo_id)->where('tipo_turno_id', $opcion->id)->get();
            if (empty($esta)) {
                $vec[$opcion->id] = $opcion->id . ' ' . $opcion->descripcion;
            }
        }
        return $vec;
    }

    public static function get_datos_asignacion()
    {
        $nombre_tabla = 'nom_cargo_tipo_turno';
        $nombre_columna1 = 'id';
        $registro_modelo_padre_id = 'cargo_id';
        $registro_modelo_hijo_id = 'tipo_turno_id';

        return compact('nombre_tabla', 'nombre_columna1', 'registro_modelo_padre_id', 'registro_modelo_hijo_id');
    }


    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"nom_contratos",
                                    "llave_foranea":"cargo_id",
                                    "mensaje":"Está asociado al Contrato de un empleado."
                                },
                            "1":{
                                    "tabla":"nom_cargos",
                                    "llave_foranea":"cargo_padre_id",
                                    "mensaje":"Está asociado como Cargo padre de otro Cargo."
                                }
                        }';
        $tablas = json_decode($tablas_relacionadas);
        foreach ($tablas as $una_tabla) {
            $registro = DB::table($una_tabla->tabla)->where($una_tabla->llave_foranea, $id)->get();

            if (!empty($registro)) {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }
}
