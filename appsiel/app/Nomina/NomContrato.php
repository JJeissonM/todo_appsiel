<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use App\Core\Tercero;
use App\Nomina\MovimientoIbcEmpleado;
use App\Nomina\NomDocRegistro;
use App\Nomina\CambioSalario;
use App\Nomina\ParametrosRetefuenteEmpleado;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class NomContrato extends Model
{
    //protected $table = 'nom_contratos';
    /**
     * clase_contrato = { normal | labor_contratada | por_turnos }
     */
    protected $fillable = ['core_tercero_id', 'clase_contrato', 'cargo_id', 'clase_riesgo_laboral_id', 'horas_laborales', 'sueldo', 'salario_integral', 'fecha_ingreso', 'contrato_hasta', 'entidad_salud_id', 'entidad_pension_id', 'entidad_arl_id', 'estado', 'liquida_subsidio_transporte', 'planilla_pila_id', 'es_pasante_sena', 'entidad_cesantias_id', 'entidad_caja_compensacion_id', 'grupo_empleado_id','genera_planilla_integrada','tipo_cotizante','excluir_documentos_nomina_electronica','turno_default_id', 'fingerprint_reader_id'];

    protected $casts = [
        'excluir_documentos_nomina_electronica' => 'boolean'
    ];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Núm. identificación', 'Empleado', 'Grupo Empleado', 'Cargo', 'Sueldo', 'Fecha ingreso', 'Contrato hasta', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"web/id_fila","otros_enlaces":"[{\"url\":\"nomina/duplicar_contrato_retirado/id_fila?id=17&id_modelo=83\",\"title\":\"Duplicar contrato retirado\",\"color_bootstrap\":\"\",\"faicon\":\"copy\",\"size\":\"\",\"tag_html\":\"a\"}]"}';
    public $archivo_js = 'js/nomina/nom_contratos.js';

    public function tercero()
    {
        return $this->belongsTo(Tercero::class, 'core_tercero_id');
    }

    public function cargo()
    {
        return $this->belongsTo(NomCargo::class, 'cargo_id');
    }

    public function grupo_empleado()
    {
        return $this->belongsTo(GrupoEmpleado::class, 'grupo_empleado_id');
    }

    public function entidad_salud()
    {
        return $this->belongsTo(NomEntidad::class, 'entidad_salud_id');
    }

    public function entidad_pension()
    {
        return $this->belongsTo(NomEntidad::class, 'entidad_pension_id');
    }

    public function entidad_arl()
    {
        return $this->belongsTo(NomEntidad::class, 'entidad_arl_id');
    }

    public function entidad_cesantias()
    {
        return $this->belongsTo(NomEntidad::class, 'entidad_cesantias_id');
    }

    public function entidad_caja_compensacion()
    {
        return $this->belongsTo(NomEntidad::class, 'entidad_caja_compensacion_id');
    }

    public function clase_riesgo_laboral()
    {
        return $this->belongsTo(ClaseRiesgoLaboral::class, 'clase_riesgo_laboral_id');
    }

    public function planilla_pila()
    {
        return $this->belongsTo(NomEntidad::class, 'planilla_pila_id');
    }

    public function turno_default()
    {
        return $this->belongsTo(TipoTurno::class, 'turno_default_id');
    }

    public function registros_documentos_nomina()
    {
        return $this->hasMany(NomDocRegistro::class, 'core_tercero_id', 'core_tercero_id');
    }

    public function prestaciones_consolidadas()
    {
        return $this->hasMany(ConsolidadoPrestacionesSociales::class, 'nom_contrato_id');
    }

    public function salario_x_hora()
    {
        return $this->sueldo / (float)config('nomina.horas_laborales');
    }

    public function salario_x_dia()
    {
        return ($this->sueldo / (float)config('nomina.horas_laborales')) * (float)config('nomina.horas_dia_laboral');
    }

    public function fecha_liquidacion_contrato()
    {
        $todos_los_registros = $this->registros_documentos_nomina;

        foreach ($todos_los_registros as $registro)
        {
            if ( $registro->encabezado_documento->tipo_liquidacion == 'terminacion_contrato' )
            {
                return $registro->encabezado_documento->fecha;
            }
        }

        return null;
    }

    public function salario_anterior()
    {
        $ultimo_cambio = CambioSalario::where( 'nom_contrato_id', $this->id )->orderBy('fecha_modificacion')->get()->last();
        
        if ( is_null( $ultimo_cambio ) )
        {
            return null;
        }

        if ( $ultimo_cambio->salario_anterior == 0 )
        {
            return null;
        }

        return $ultimo_cambio->salario_anterior;
    }

    public function parametros_retefuente()
    {
        return ParametrosRetefuenteEmpleado::where('nom_contrato_id',$this->id)->orderBy('fecha_final_promedios')->get()->last();
    }

    public function valor_ibc()
    {
        $valor_ibc = MovimientoIbcEmpleado::where('nom_contrato_id', $this->id)->get()->last();

        if (is_null($valor_ibc)) {
            return $this->sueldo;
        }

        return $valor_ibc;
    }

    public function dias_laborados_adicionales_docentes()
    {
        // Verificar si tiene movimiento en 10 meses del año
        $cantidad_meses_laborados = NomDocRegistro::where('nom_contrato_id',$this->id)->groupBy('fecha')->get()->count();
        if( $cantidad_meses_laborados >= 10 )
        {
            return (12 - $cantidad_meses_laborados ) * 30;
        }

        return 0;
    }

    public function get_registros_documentos_nomina_entre_fechas($fecha_inicial, $fecha_final)
    {
        $todos_los_registros = $this->registros_documentos_nomina;
        $coleccion = collect();
        foreach ($todos_los_registros as $registro)
        {
            if ($registro->concepto != null) {
                // Las Cesantías consignadas no se le pagan al empleado.
                if( $registro->concepto->modo_liquidacion_id == 15 )
                {
                    continue;
                }
            }            

            if ($registro->fecha >= $fecha_inicial && $registro->fecha <= $fecha_final)
            {
                $coleccion[] = $registro;
            }
        }

        return $coleccion;
    }

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
                'nom_contratos.sueldo AS campo5',
                'nom_contratos.fecha_ingreso AS campo6',
                'nom_contratos.contrato_hasta AS campo7',
                'nom_contratos.estado AS campo8',
                'nom_contratos.id AS campo9'
            )
            ->where("nom_contratos.clase_contrato", "<>", "por_turnos")
            ->orderBy('nom_contratos.created_at', 'DESC')
            ->get();

        //hacemos el filtro de $search si $search tiene contenido
        $nuevaColeccion = [];
        if (count($collection) > 0) {
            if (strlen($search) > 0) {
                $nuevaColeccion = $collection->filter(function ($c) use ($search) {
                    if (self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4, $c->campo5, $c->campo6, $c->campo7, $c->campo8, $c->campo9], $search)) {
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
            ->leftJoin('nom_entidades as entidad_salud', 'entidad_salud.id', '=', 'nom_contratos.entidad_salud_id')
            ->leftJoin('nom_entidades as entidad_pension', 'entidad_pension.id', '=', 'nom_contratos.entidad_pension_id')
            ->leftJoin('nom_entidades as entidad_cesantias', 'entidad_cesantias.id', '=', 'nom_contratos.entidad_cesantias_id')
            ->select(
                'core_terceros.numero_identificacion AS NUM_IDENTIFICACIÓN',
                'core_terceros.descripcion AS EMPLEADO',
                'nom_grupos_empleados.descripcion AS GRUPO_EMPLEADO',
                'nom_cargos.descripcion AS CARGO',
                'nom_contratos.sueldo AS SUELDO',
                'nom_contratos.fecha_ingreso AS FECHA_INGRESO',
                'nom_contratos.contrato_hasta AS CONTRATO_HASTA',
                'entidad_salud.descripcion AS EPS',
                'entidad_pension.descripcion AS AFP',
                'entidad_cesantias.descripcion AS FONDO_CESANTIAS',
                'nom_contratos.id AS ID',
                'nom_contratos.estado AS ESTADO'
            )
            ->where("core_terceros.numero_identificacion", "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_grupos_empleados.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_cargos.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_contratos.sueldo", "LIKE", "%$search%")
            ->orWhere("nom_contratos.fecha_ingreso", "LIKE", "%$search%")
            ->orWhere("nom_contratos.contrato_hasta", "LIKE", "%$search%")
            ->orWhere("nom_contratos.estado", "LIKE", "%$search%")
            ->orderBy('nom_contratos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CONTRATOS";
    }

    public static function get_empleados($estado)
    {
        return NomContrato::leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
            ->leftJoin('nom_cargos', 'nom_cargos.id', '=', 'nom_contratos.cargo_id')
            ->where('nom_contratos.estado', 'LIKE', $estado . '%')
            ->select(
                'core_terceros.descripcion AS empleado',
                'core_terceros.id AS core_tercero_id',
                'nom_cargos.descripcion AS cargo',
                'nom_contratos.sueldo AS salario',
                'core_terceros.numero_identificacion AS cedula'
            )
            ->get();
    }

    // CON ID core_tercero_id
    public static function opciones_campo_select()
    {
        $opciones = NomContrato::leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
                                ->where('nom_contratos.estado', 'Activo')
                                ->select('core_terceros.id', 'core_terceros.descripcion', 'core_terceros.numero_identificacion')
                                ->orderby('core_terceros.descripcion')
                                ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion . ' (' . $opcion->numero_identificacion . ')';
        }

        return $vec;
    }

    // Con ID del contrato (empleado)
    public static function opciones_campo_select_2()
    {
        $opciones = NomContrato::leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
                                ->select('nom_contratos.id', 'core_terceros.descripcion', 'core_terceros.numero_identificacion')
                                ->orderby('core_terceros.descripcion')
                                ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion . ' (' . $opcion->numero_identificacion . ')';
        }

        return $vec;
    }


    public function store_adicional($datos, $registro)
    {
        CambioSalario::create(
            ['nom_contrato_id' => $registro->id] +
                ['salario_anterior' => 0] +
                ['nuevo_salario' => $registro->sueldo] +
                ['fecha_modificacion' => date('Y-m-d')] +
                ['tipo_modificacion' => 'creacion_contrato'] +
                ['observacion' => ''] +
                ['creado_por' => Auth::user()->email] +
                ['modificado_por' => '']
        );

        if ($registro->contrato_hasta == '')
        {
            $registro->contrato_hasta = date('2099-12-30');
            $registro->save();
        }
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

        $lista_campos[] = $this->get_campo_excluir_documentos($registro->excluir_documentos_nomina_electronica);

        return $lista_campos;
    }

    public function get_campos_adicionales_create($lista_campos)
    {
        $lista_campos[] = $this->get_campo_excluir_documentos(false);

        return $lista_campos;
    }

    protected function get_campo_excluir_documentos($valor)
    {
        return [
            "id" => 998,
            "descripcion" => "Excluir del documento soporte de nómina electrónica",
            "tipo" => "bsSelect",
            "name" => "excluir_documentos_nomina_electronica",
            "opciones" => [
                '0' => 'No',
                '1' => 'Sí'
            ],
            "value" => $valor ? '1' : '0',
            "atributos" => [ 'class' => 'form-control' ],
            "definicion" => "",
            "requerido" => 0,
            "editable" => 1,
            "unico" => 0
        ];
    }

    public static function update_adicional($datos, $registro_id)
    {
        $contrato = NomContrato::find( $registro_id );

        // ESTO NO ESTA FUNCIONANDO, ASI COMO ESTA EL CONDICIONAL NUNCA VAN A SER IGUALES
        if ( $contrato->sueldo != $datos['sueldo'] )
        {
            CambioSalario::create(
                                    [ 'nom_contrato_id' => $registro_id] +
                                        [ 'salario_anterior' => $datos['sueldo'] ] +
                                        [ 'nuevo_salario' => $contrato->sueldo] +
                                        [ 'fecha_modificacion' => date('Y-m-d')] +
                                        [ 'tipo_modificacion' => 'creacion_contrato'] +
                                        [ 'observacion' => ''] +
                                        [ 'creado_por' => Auth::user()->email] +
                                        [ 'modificado_por' => '']
                                );
        }
    }
}
