<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

//use App\Nomina\EmpleadoPlanilla;

use DB;

class PlanillaGenerada extends Model
{
    protected $table = 'nom_pila_planillas_generadas';
    protected $fillable = ['pila_datos_empresa_id', 'descripcion', 'fecha_final_mes', 'estado'];
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Código', 'Descripción', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"nom_pila_show/id_fila"}';

    public function empleados()
    {
        return $this->belongsToMany(NomContrato::class, 'nom_pila_empleados_planilla', 'planilla_generada_id', 'nom_contrato_id');
    }

    public function datos_empresa()
    {
        return $this->belongsTo(PilaDatosEmpresa::class, 'pila_datos_empresa_id');
    }

    public function lapso()
    {
        $array_fecha = explode('-', $this->fecha_final_mes);

        $dia_inicio = '01';

        $dia_fin = '30';
        // Mes de febrero
        if ($array_fecha[1] == '02') {
            $dia_fin = '28';
        }

        return (object)[
            'fecha_inicial' => $array_fecha[0] . '-' . $array_fecha[1] . '-' . $dia_inicio,
            'fecha_final' => $array_fecha[0] . '-' . $array_fecha[1] . '-' . $dia_fin
        ];
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return PlanillaGenerada::select(
            'nom_pila_planillas_generadas.pila_datos_empresa_id AS campo1',
            'nom_pila_planillas_generadas.descripcion AS campo2',
            'nom_pila_planillas_generadas.fecha_final_mes AS campo3',
            'nom_pila_planillas_generadas.id AS campo4'
        )
            ->where("nom_pila_planillas_generadas.pila_datos_empresa_id", "LIKE", "%$search%")
            ->orWhere("nom_pila_planillas_generadas.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_pila_planillas_generadas.fecha_final_mes", "LIKE", "%$search%")

            ->orderBy('nom_pila_planillas_generadas.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = PlanillaGenerada::select(
            'nom_pila_planillas_generadas.pila_datos_empresa_id AS CÓDIGO',
            'nom_pila_planillas_generadas.descripcion AS DESCRIPCIÓN',
            'nom_pila_planillas_generadas.fecha_final_mes AS ESTADO'
        )
            ->where("nom_pila_planillas_generadas.pila_datos_empresa_id", "LIKE", "%$search%")
            ->orWhere("nom_pila_planillas_generadas.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_pila_planillas_generadas.fecha_final_mes", "LIKE", "%$search%")

            ->orderBy('nom_pila_planillas_generadas.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PLANILLAS GENERADAS";
    }

    public static function opciones_campo_select()
    {
        $opciones = PlanillaGenerada::where('nom_pila_planillas_generadas.estado', 'Activo')
            ->select('nom_pila_planillas_generadas.id', 'nom_pila_planillas_generadas.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }


    /*
                RESOLUCIÓN 2388 DE 2016
        2.1.2.2 Uso de múltiples registros tipo 2 para un mismo cotizante en una planilla <Numeral modificado por el artículo 1 de la Resolución 5858 de 2016. El nuevo texto es el siguiente:> Un registro tipo 2. Liquidación detallada de aportes” puede ser incluido más de una vez para un mismo cotizante en una misma planilla siempre y cuando se den las siguientes situaciones:

        a) Con más de una novedad de ingreso o retiro en un mismo período:

        Por cada novedad de ingreso o retiro se debe diligenciar un registro en el cual se incluya la fecha correspondiente a la novedad.

        Cuando exista en el mismo periodo una novedad de ingreso y retiro esta se debe dejar en el mismo registro.

        b) Con novedades que afectan el valor del Ingreso Base de Cotización (IBC)

        Cuando se reporten novedades en las cuales se presente un IBC diferente se deberá reportar en un registro cada novedad.

        Cuando se presente concurrencia entre una de las novedades “LMA – Licencia de Maternidad o Paternidad”, “IGE – Incapacidad temporal por enfermedad general”, “SLN Suspensión temporal del contrato de trabajo o licencia no remunerada o comisión de servicios”, “IRL – Días de incapacidad por accidente de trabajo o enfermedad laboral” o “VAC – LR – Vacaciones, Licencia Remunerada” y una novedad “VSP Variación permanente de salario” o “VST – Variación transitoria del salario”, por los 30 días del periodo, el aportante reportará la variación en las líneas que sean necesarias. Es responsabilidad del aportante hacer el cálculo del ingreso base de cotización que afecta las novedades que se presentan durante el periodo.


    */

    public function store_adicional($datos, $registro)
    {
        $fecha_lapso = explode("-", $datos['fecha_final_mes']);

        $fecha_inicial = $fecha_lapso[0] . '-' . $fecha_lapso[1] . '-01';

        $empleados = NomDocRegistro::whereBetween('fecha', [$fecha_inicial, $datos['fecha_final_mes']])
            ->select('nom_contrato_id')
            ->distinct('nom_contrato_id')
            ->get();

        // Se agregan todos los contratos al documento
        $i = 1;
        foreach ($empleados as $empleado) {
            $contrato = NomContrato::find($empleado->nom_contrato_id);

            /*
				cambiar la validacion siguiente por un campo que el empleado que diga: genera planilla SI: NO 
        	*/
            if ($contrato->grupo_empleado_id != 5) // Grupo prepensionados
            {
                $conceptos_liquidados_mes = $this->conceptos_liquidados_mes($contrato, $fecha_inicial, $datos['fecha_final_mes']);

                // Se agrega una línea de empleado por cada novedad de TNL

                $conceptos_suspencion = [61, 62, 63];
                foreach ($conceptos_suspencion as $key => $value) {
                    if (in_array($value, $conceptos_liquidados_mes)) {
                        EmpleadoPlanilla::create([
                            'orden' => $i,
                            'planilla_generada_id' => $registro->id,
                            'nom_contrato_id' => $contrato->id,
                            'tipo_linea' => 'adicional'
                        ]);
                        $i++;
                    }
                }

                $conceptos_incapacidad_enfermedad_general = [58];
                foreach ($conceptos_incapacidad_enfermedad_general as $key => $value) {
                    if (in_array($value, $conceptos_liquidados_mes)) {
                        EmpleadoPlanilla::create([
                            'orden' => $i,
                            'planilla_generada_id' => $registro->id,
                            'nom_contrato_id' => $contrato->id,
                            'tipo_linea' => 'adicional'
                        ]);
                        $i++;
                    }
                }

                $conceptos_licencia_maternidad = [59, 33]; // O Paternidad
                foreach ($conceptos_licencia_maternidad as $key => $value) {
                    if (in_array($value, $conceptos_liquidados_mes)) {
                        EmpleadoPlanilla::create([
                            'orden' => $i,
                            'planilla_generada_id' => $registro->id,
                            'nom_contrato_id' => $contrato->id,
                            'tipo_linea' => 'adicional'
                        ]);
                        $i++;
                    }
                }

                $conceptos_vacaciones = [66]; // Disfrutadas
                foreach ($conceptos_vacaciones as $key => $value) {
                    if (in_array($value, $conceptos_liquidados_mes)) {
                        EmpleadoPlanilla::create([
                            'orden' => $i,
                            'planilla_generada_id' => $registro->id,
                            'nom_contrato_id' => $contrato->id,
                            'tipo_linea' => 'adicional'
                        ]);
                        $i++;
                    }
                }

                $conceptos_accidente_laboral = [60];
                foreach ($conceptos_accidente_laboral as $key => $value) {
                    if (in_array($value, $conceptos_liquidados_mes)) {
                        EmpleadoPlanilla::create([
                            'orden' => $i,
                            'planilla_generada_id' => $registro->id,
                            'nom_contrato_id' => $contrato->id,
                            'tipo_linea' => 'adicional'
                        ]);
                        $i++;
                    }
                }

                // SE AGREGA EL REGISTRO PRINCIPAL
                EmpleadoPlanilla::create([
                    'orden' => $i,
                    'planilla_generada_id' => $registro->id,
                    'nom_contrato_id' => $contrato->id,
                    'tipo_linea' => 'principal'
                ]);
                $i++;
            }
        }
    }

    public function conceptos_liquidados_mes($empleado, $fecha_inicial, $fecha_final)
    {
        return NomDocRegistro::where('nom_contrato_id', $empleado->id)
            ->whereBetween('fecha', [$fecha_inicial, $fecha_final])
            ->distinct('nom_concepto_id')
            ->get()
            ->pluck('nom_concepto_id')
            ->toArray();
    }
}
