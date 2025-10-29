<?php

namespace App\Nomina;

use App\Nomina\CambioSalario;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class NomContratoPorTurno extends NomContrato
{
    protected $table = 'nom_contratos';

    /**
     * clase_contrato = { normal | labor_contratada | por_turnos }
     */
    protected $fillable = ['core_tercero_id', 'clase_contrato', 'cargo_id', 'clase_riesgo_laboral_id', 'horas_laborales', 'sueldo', 'salario_integral', 'fecha_ingreso', 'contrato_hasta', 'entidad_salud_id', 'entidad_pension_id', 'entidad_arl_id', 'estado', 'liquida_subsidio_transporte', 'planilla_pila_id', 'es_pasante_sena', 'entidad_cesantias_id', 'entidad_caja_compensacion_id', 'grupo_empleado_id','genera_planilla_integrada','tipo_cotizante'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Núm. identificación', 'Empleado', 'Grupo Empleado', 'Cargo', 'Fecha ingreso', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"web/id_fila"}';

    public static function consultar_registros($nro_registros, $search)
    {
        $collection =  NomContrato::leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
            ->leftJoin('nom_cargos', 'nom_cargos.id', '=', 'nom_contratos.cargo_id')
            ->leftJoin('nom_grupos_empleados', 'nom_grupos_empleados.id', '=', 'nom_contratos.grupo_empleado_id')
            ->select(
                'core_terceros.numero_identificacion AS campo1',
                'core_terceros.descripcion AS campo2',
                'nom_grupos_empleados.descripcion AS campo3',
                'nom_cargos.descripcion AS campo4',
                'nom_contratos.fecha_ingreso AS campo5',
                'nom_contratos.estado AS campo6',
                'nom_contratos.id AS campo7'
            )
            ->where("nom_contratos.clase_contrato", "=", "por_turnos")
            ->orderBy('nom_contratos.created_at', 'DESC')
            ->get();

        //hacemos el filtro de $search si $search tiene contenido
        $nuevaColeccion = [];
        if (count($collection) > 0) {
            if (strlen($search) > 0) {
                $nuevaColeccion = $collection->filter(function ($c) use ($search) {
                    if (self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4, $c->campo5, $c->campo6, $c->campo7], $search)) {
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
        $string = NomContrato::leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
            ->leftJoin('nom_cargos', 'nom_cargos.id', '=', 'nom_contratos.cargo_id')
            ->leftJoin('nom_grupos_empleados', 'nom_grupos_empleados.id', '=', 'nom_contratos.grupo_empleado_id')
            ->select(
                'core_terceros.numero_identificacion AS NUM_IDENTIFICACIÓN',
                'core_terceros.descripcion AS EMPLEADO',
                'nom_grupos_empleados.descripcion AS GRUPO_EMPLEADO',
                'nom_cargos.descripcion AS CARGO',
                'nom_contratos.id AS ID',
                'nom_contratos.estado AS ESTADO'
            )
            ->where("core_terceros.numero_identificacion", "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_grupos_empleados.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_cargos.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_contratos.estado", "LIKE", "%$search%")
            ->orderBy('nom_contratos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CONTRATOS POR TURNOS";
    }

    public function store_adicional($datos, $registro)
    {
        $registro->clase_contrato = 'por_turnos';

        if ($registro->contrato_hasta == '')
        {
            $registro->contrato_hasta = date('2099-12-31');
        }
        
        $registro->save();
    }

    public function get_campos_adicionales_edit($lista_campos, $registro)
    {

        if ($registro->estado == 'Retirado') {
            return [[
                "id" => 999,
                "descripcion" => "",
                "tipo" => "personalizado",
                "name" => "name_1",
                "opciones" => "",
                "value" => '<div class="container-fluid">                    
                                                    <div class="alert alert-danger">
                                                      <strong>¡Advertencia!</strong>
                                                      <br>
                                                      El empleado <b>' . $registro->tercero->descripcion . '</b> está Retirado. Los datos del contrato no pueden ser modifcado.
                                                    </div>
                                                </div>',
                "atributos" => [],
                "definicion" => "",
                "requerido" => 0,
                "editable" => 1,
                "unico" => 0
            ]];
        }

        return $lista_campos;
    }

    public static function update_adicional($datos, $registro_id)
    {
        //
    }
}
