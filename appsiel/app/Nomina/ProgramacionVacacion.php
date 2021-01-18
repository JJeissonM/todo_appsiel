<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use DB;

use App\Nomina\ParametroLiquidacionPrestacionesSociales;
use App\Nomina\LibroVacacion;

class ProgramacionVacacion extends Model
{
    protected $table = 'nom_novedades_tnl';

    protected $fillable = ['nom_concepto_id', 'nom_contrato_id', 'fecha_inicial_tnl', 'fecha_final_tnl', 'cantidad_dias_tnl', 'cantidad_horas_tnl', 'tipo_novedad_tnl', 'codigo_diagnostico_incapacidad', 'numero_incapacidad', 'fecha_expedicion_incapacidad', 'origen_incapacidad', 'clase_incapacidad', 'fecha_incapacidad', 'valor_a_pagar_eps', 'valor_a_pagar_arl', 'valor_a_pagar_afp', 'valor_a_pagar_empresa', 'observaciones', 'estado', 'cantidad_dias_amortizados', 'cantidad_dias_pendientes_amortizar', 'es_prorroga', 'novedad_tnl_anterior_id'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Empleado', 'Inicio',  'Fin', 'Cant. días', 'Observaciones', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","eliminar":"web_eliminar/id_fila"}';
    //public $urls_acciones = '{"show":"no"}';

    public $archivo_js = 'assets/js/nomina/programacion_vacaciones.js';

    public function concepto()
    {
        return $this->belongsTo(NomConcepto::class, 'nom_concepto_id');
    }

    public function contrato()
    {
        return $this->belongsTo(NomContrato::class, 'nom_contrato_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return ProgramacionVacacion::leftJoin('nom_conceptos', 'nom_conceptos.id', '=', 'nom_novedades_tnl.nom_concepto_id')
            ->leftJoin('nom_contratos', 'nom_contratos.id', '=', 'nom_novedades_tnl.nom_contrato_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
            ->where([['nom_novedades_tnl.tipo_novedad_tnl', '=', 'vacaciones']])
            ->select(
                'core_terceros.descripcion AS campo1',
                'nom_novedades_tnl.fecha_inicial_tnl AS campo2',
                'nom_novedades_tnl.fecha_final_tnl AS campo3',
                'nom_novedades_tnl.cantidad_dias_tnl AS campo4',
                'nom_novedades_tnl.observaciones AS campo5',
                'nom_novedades_tnl.estado AS campo6',
                'nom_novedades_tnl.id AS campo7'
            )
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_cargos.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_contratos.sueldo", "LIKE", "%$search%")
            ->orWhere("nom_contratos.fecha_ingreso", "LIKE", "%$search%")
            ->orWhere("nom_contratos.contrato_hasta", "LIKE", "%$search%")
            ->orWhere("nom_contratos.estado", "LIKE", "%$search%")
            ->orderBy('nom_contratos.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = ProgramacionVacacion::leftJoin('nom_conceptos', 'nom_conceptos.id', '=', 'nom_novedades_tnl.nom_concepto_id')
            ->leftJoin('nom_contratos', 'nom_contratos.id', '=', 'nom_novedades_tnl.nom_contrato_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
            ->where([['nom_novedades_tnl.tipo_novedad_tnl', '=', 'vacaciones']])
            ->select(
                'core_terceros.descripcion AS EMPLEADO',
                'nom_novedades_tnl.fecha_inicial_tnl AS INICIO',
                'nom_novedades_tnl.fecha_final_tnl AS FIN',
                'nom_novedades_tnl.cantidad_dias_tnl AS CANT_DÍAS',
                'nom_novedades_tnl.observaciones AS OBSERVACIONES',
                'nom_novedades_tnl.estado AS ESTADO'
            )
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
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
        return "LISTADO DE PROGRAMACIÓN DE VACACIONES";
    }

    public static function opciones_campo_select()
    {
        $opciones = ProgramacionVacacion::where('nom_novedades_tnl.estado', 'Activo')
            ->select('nom_novedades_tnl.id', 'nom_novedades_tnl.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public function store_adicional($datos, $registro)
    {

        $dias_disfrutados = (int)$datos['cantidad_dias_tomados'] - (int)$datos['dias_compensados'];
        LibroVacacion::create(
            ['nom_contrato_id' => $registro->nom_contrato_id] +
                ['novedad_tnl_id' => $registro->id] +
                ['periodo_disfrute_vacacion_desde' => $datos['fecha_inicial_tnl']] +
                ['periodo_disfrute_vacacion_hasta' => $datos['fecha_final_tnl']] +
                ['dias_pagados' => $datos['cantidad_dias_tomados']] +
                ['dias_compensados' => $datos['dias_compensados']] +
                ['dias_no_habiles' => $datos['dias_no_habiles']] +
                ['dias_disfrutados' => $dias_disfrutados]
        );

        $empleado = NomContrato::find($registro->nom_contrato_id);

        $parametros_prestacion = ParametroLiquidacionPrestacionesSociales::where('concepto_prestacion', 'vacaciones')
            ->where('grupo_empleado_id', $empleado->grupo_empleado_id)
            ->get()->first();
        $concepto = NomConcepto::find($parametros_prestacion->nom_concepto_id);

        $registro->tipo_novedad_tnl = 'vacaciones';
        $registro->cantidad_dias_pendientes_amortizar = $datos['cantidad_dias_tnl'];
        $registro->nom_concepto_id = $concepto->id;
        $registro->estado = 'Activo';
        $registro->save();
    }

    public function get_campos_adicionales_edit($lista_campos, $registro)
    {
        $libro_vacaciones = LibroVacacion::where('novedad_tnl_id', $registro->id)->get()->first();

        // Personalizar campos
        $cantida_campos = count($lista_campos);
        for ($i = 0; $i <  $cantida_campos; $i++) {
            switch ($lista_campos[$i]['name']) {
                case 'cantidad_dias_tomados':
                    $lista_campos[$i]['value'] = $libro_vacaciones->dias_pagados;
                    break;
                case 'dias_compensados':
                    $lista_campos[$i]['value'] = $libro_vacaciones->dias_compensados;
                    break;
                case 'dias_no_habiles':
                    $lista_campos[$i]['value'] = $libro_vacaciones->dias_no_habiles;
                    break;
                default:
                    # code...
                    break;
            }
        }

        return $lista_campos;
    }

    public function update_adicional($datos, $id)
    {
        $dias_disfrutados = (int)$datos['cantidad_dias_tomados'] - (int)$datos['dias_compensados'];
        $libro_vacaciones = LibroVacacion::where('novedad_tnl_id', $id)->get()->first();
        $libro_vacaciones->periodo_disfrute_vacacion_desde = $datos['fecha_inicial_tnl'];
        $libro_vacaciones->periodo_disfrute_vacacion_hasta = $datos['fecha_final_tnl'];
        $libro_vacaciones->dias_pagados = $datos['cantidad_dias_tomados'];
        $libro_vacaciones->dias_compensados = $datos['dias_compensados'];
        $libro_vacaciones->dias_no_habiles = $datos['dias_no_habiles'];
        $libro_vacaciones->dias_disfrutados = $dias_disfrutados;
        $libro_vacaciones->save();
    }

    public function validar_eliminacion($id)
    {
        if (ProgramacionVacacion::find($id)->cantidad_dias_amortizados != 0) {
            return 'Ya tiene días amortizados.';
        }

        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"nom_doc_registros",
                                    "llave_foranea":"novedad_tnl_id",
                                    "mensaje":"Ya tiene movimientos amortizados en registros de documentos de nómina."
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
