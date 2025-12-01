<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

// moelo_id = 337
class RegistroTurno extends Model
{
    protected $table = 'nom_turnos_registros';

    /**
     * estado => { Pendiente | Aprobado | Liquidado }
     */
    protected $fillable = ['contrato_id', 'tipo_turno_id', 'fecha', 'checkin_time_1', 'checkout_time_1', 'checkin_time_2', 'checkout_time_2', 'valor', 'anotacion', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Núm. identificación', 'Empleado', 'Tipo Turno', 'Fecha', 'Valor', 'Anotación', 'Estado'];

    public $urls_acciones = '{"create":"nom_turnos_registros/create","edit":"web/id_fila/edit","show":"web/id_fila","eliminar":"web_eliminar/id_fila"}';

    public static function consultar_registros($nro_registros, $search)
    {
        $collection = RegistroTurno::leftJoin('nom_contratos', 'nom_contratos.id', '=', 'nom_turnos_registros.contrato_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
            ->leftJoin('nom_turnos_tipos', 'nom_turnos_tipos.id', '=', 'nom_turnos_registros.tipo_turno_id')
            ->select(
                'core_terceros.numero_identificacion AS campo1',
                'core_terceros.descripcion AS campo2',
                'nom_turnos_tipos.descripcion AS campo3',
                'nom_turnos_registros.fecha AS campo4',
                'nom_turnos_registros.valor AS campo5',
                'nom_turnos_registros.anotacion AS campo6',
                'nom_turnos_registros.estado AS campo7',
                'nom_turnos_registros.id AS campo8'
            )
            ->orderBy('nom_turnos_registros.fecha', 'DESC')
            ->get();

        //hacemos el filtro de $search si $search tiene contenido
        $nuevaColeccion = [];
        if (count($collection) > 0) {
            if (strlen($search) > 0) {
                $nuevaColeccion = $collection->filter(function ($c) use ($search) {
                    if (self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4, $c->campo5, $c->campo6, $c->campo7, $c->campo8], $search)) {
                        return $c;
                    }
                });
            } else {
                $nuevaColeccion = $collection;
            }
        }

        $request = request(); //obtenemos el Request para obtener la url y la query builder

        if (empty($nuevaColeccion)) {
            return $array = new LengthAwarePaginator([], 1, 1, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
        }

        foreach( $collection AS $register_collect )
        {
            $register_collect->campo5 = '$' . number_format( $register_collect->campo5, 0, ',', '.' );
        }

        //obtenemos el numero de la página actual, por defecto 1
        $page = 1;
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
        }
        $total = count($nuevaColeccion); //Total para contar los registros mostrados
        $starting_point = ($page * $nro_registros) - $nro_registros; // punto de inicio para mostrar registros
        $array = $nuevaColeccion->slice($starting_point, $nro_registros); //indicamos desde donde y cuantos registros mostrar
        $array = new LengthAwarePaginator($array, $total, $nro_registros, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]); //finalmente se pagina y organiza la coleccion a devolver con todos los datos

        return $array;
    }

    /**
     * SQL Like operator in PHP.
     * Returns TRUE if match else FALSE.
     * @param array $valores_campos_seleccionados de campos donde se busca
     * @param string $searchTerm termino de busqueda
     * @return bool
     */
    public static function likePhp($valores_campos_seleccionados, $searchTerm)
    {
        $encontrado = false;
        $searchTerm = str_slug($searchTerm); // Para eliminar acentos
        foreach ($valores_campos_seleccionados as $valor_campo) {
            $str = str_slug($valor_campo);
            $pos = strpos($str, $searchTerm);
            if ($pos !== false) {
                $encontrado = true;
            }
        }
        return $encontrado;
    }

    public static function sqlString($search)
    {
        $string = RegistroTurno::leftJoin('nom_contratos', 'nom_contratos.id', '=', 'nom_turnos_registros.contrato_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
            ->leftJoin('nom_turnos_tipos', 'nom_turnos_tipos.id', '=', 'nom_turnos_registros.tipo_turno_id')
            ->select(
                'core_terceros.numero_identificacion AS NUM_IDENTIFICACIÓN',
                'core_terceros.descripcion AS EMPLEADO',
                'nom_turnos_tipos.descripcion AS TIPO_TURNO',
                'nom_turnos_registros.fecha AS FECHA',
                'nom_turnos_registros.valor AS VALOR',
                'nom_turnos_registros.anotacion AS ANOTACION',
                'nom_turnos_registros.estado AS ESTADO'
            )
            ->where("core_terceros.numero_identificacion", "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_turnos_tipos.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_turnos_registros.anotacion", "LIKE", "%$search%")
            ->orWhere("nom_turnos_registros.estado", "LIKE", "%$search%")
            ->orderBy('nom_turnos_registros.fecha', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE REGISTROS DE TURNOS";
    }

    public function validar_eliminacion($id)
    {
        $registro = RegistroTurno::find( $id );
        if ( $registro->estado == 'Liquidado' ) {
            return 'No es posible eliminar un registro de turno que ya ha sido liquidado.';
        }

        return 'ok';
    }

    public static function get_campos_adicionales_edit($lista_campos, $registro)
    {

        if($registro == null)
        {
            return $lista_campos;
        }

        if ( $registro->estado == 'Liquidado') {
            return [
                [
                    "id" => 999,
                    "descripcion" => "El turno ya está Liquidado.",
                    "tipo" => "personalizado",
                    "name" => "lbl_turno_liquidado",
                    "opciones" => "",
                    "value" => '<div class="form-group">                    
                                                    <label class="control-label col-sm-3" style="color: red;" > <b> No se puede Modificar un turno que ya ha sido Liquidado </b> </label>    
                                                </div>',
                    "atributos" => [],
                    "definicion" => "",
                    "requerido" => 0,
                    "editable" => 1,
                    "unico" => 0
                ]
            ];
        }

        return $lista_campos;
    }
}
