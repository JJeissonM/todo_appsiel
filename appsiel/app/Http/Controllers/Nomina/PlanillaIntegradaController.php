<?php

namespace App\Http\Controllers\Nomina;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use View;
use Auth;
use Input;
use Carbon\Carbon;

use Spatie\Permission\Models\Permission;

use App\Sistema\Aplicacion;

use App\Nomina\Procesos\ArchivoPlanoPlanillaIntegrada;

use App\Nomina\NomDocEncabezado;
use App\Nomina\NomDocRegistro;
use App\Nomina\NomConcepto;
use App\Nomina\AgrupacionConcepto;
use App\Nomina\NomContrato;
use App\Nomina\PlanillaGenerada;
use App\Nomina\NovedadTnl;

use App\Nomina\PilaNovedades;
use App\Nomina\PilaSalud;
use App\Nomina\PilaPension;
use App\Nomina\PilaRiesgoLaboral;
use App\Nomina\PilaParafiscales;
use App\Nomina\EmpleadoPlanilla;


/*
    Los campos tipo numérico “N”, se reportarán justificados a la derecha y rellenados con ceros a la izquierda. Los campos tipo alfa-numérico “A”, se reportarán justificados a la izquierda y se rellenarán con espacios a la derecha”.
*/
class PlanillaIntegradaController extends Controller
{
    protected $ibc_salud; // Se usa para Salud, Pensión y Riesgos laborales
    protected $valor_ibc_un_dia;
    protected $cantidad_dias_laborados;
    protected $ibc_parafiscales;
    protected $cantidad_dias_parafiscales;
    protected $fecha_inicial;
    protected $fecha_final;
    protected $empleado_planilla_id;
    protected $dias_incapacidad_accidente_trabajo;
    protected $novedad_de_ausentismo;
    protected $el_empleado_id;

    public function show($planilla_generada_id)
    {

        $view_pdf = '';//$this->vista_preliminar($id,'show');
        
        $planilla_generada = PlanillaGenerada::find($planilla_generada_id);

        $datos_planilla = $this->get_datos_planilla( $planilla_generada );

        $tabla_planilla = View::make('nomina.planilla_integrada.tabla_planilla', compact('datos_planilla') )->render();

        $miga_pan = [
                  ['url'=>'nomina?id='.Input::get('id'),'etiqueta'=>'Nómina'],
                  ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta' => 'Planillas generadas' ],
                  ['url'=>'NO','etiqueta' => $planilla_generada->descripcion ]
              ];

        return view( 'nomina.planilla_integrada.show',compact('miga_pan','view_pdf','planilla_generada','tabla_planilla') );
    }

    // Obtiene los datos almacenados en las tablas de liquidacion PILA
    public function get_datos_planilla( $planilla )
    {
        //$empleados = $planilla->empleados;
        $empleados_planilla = EmpleadoPlanilla::where('planilla_generada_id',$planilla->id)->get();

        $datos_filas = [];
        $secuencia = 1;
        foreach ($empleados_planilla as $key => $linea )
        {
            $empleado = NomContrato::find( (int)$linea->nom_contrato_id );
            $datos_columnas = [];

            /*
                        Datos básicos del empleado
            */
            $datos_columnas[] = '02';
            $datos_columnas[] = $this->formatear_campo($secuencia,'0','izquierda',8);

            $tercero = $empleado->tercero;
            $datos_columnas[] = $this->get_tipo_identificacion( $tercero->id_tipo_documento_id );
            $datos_columnas[] = $this->formatear_campo($tercero->numero_identificacion,' ','derecha',16);

            $datos_columnas[] = $this->get_tipo_cotizante( $empleado );
            $datos_columnas[] = '00'; // $subtipo_cotizante

            $datos_columnas[] = ' '; // Extranjero no obligado a cotizar a pensiones
            $datos_columnas[] = ' '; // Colombiano en el exterior

            $datos_columnas[] = substr($tercero->ciudad->id, 3,2); // Departamento
            $datos_columnas[] = substr($tercero->ciudad->id, 5);


            $datos_columnas[] = $this->formatear_campo( $this->formatear_acentos( $tercero->apellido1 ),' ','derecha',20);
            $datos_columnas[] = $this->formatear_campo( $this->formatear_acentos( $tercero->apellido2 ),' ','derecha',20);
            $datos_columnas[] = $this->formatear_campo( $this->formatear_acentos( $tercero->nombre1 ),' ','derecha',20);
            $datos_columnas[] = $this->formatear_campo( $this->formatear_acentos( $tercero->otros_nombres ),' ','derecha',30);


            // Primera columna para mostrar la TNL registrada del empleado
            if ( $linea->novedad_tnl_id == 0 )
            {
                $datos_columnas[] = '';
            }else{
                $datos_columnas[] = '<a href="' . url('web/' . $linea->novedad_tnl_id . '?id=17&id_modelo=261&id_transaccion=') . '" target="_blank">' . $linea->novedad_tnl->observaciones . '</a>';
            }

            /*
                        DATOS DE NOVEDADES
            */
            $datos_novedades = $this->get_campos_novedades($planilla->id, $empleado->id, $linea->id);
            foreach ($datos_novedades as $key => $value)
            {
                $datos_columnas[] = $value;
            }



            /*
                        DATOS DE SALUD
            */
            $datos_salud = $this->get_campos_salud($planilla->id, $empleado->id, $linea->id);
            foreach ($datos_salud as $key => $value)
            {
                $datos_columnas[] = $value;
            }



            /*
                        DATOS DE PENSION
            */
            $datos_pension = $this->get_campos_pension($planilla->id, $empleado->id, $linea->id);
            foreach ($datos_pension as $key => $value)
            {
                $datos_columnas[] = $value;
            }



            /*
                        DATOS DE RIESGOS LABORALES
            */
            $datos_riesgos_laborales = $this->get_campos_riesgos_laborales($planilla->id, $empleado->id, $linea->id);
            foreach ($datos_riesgos_laborales as $key => $value)
            {
                $datos_columnas[] = $value;
            }



            /*
                        DATOS DE PARAFISCALES
            */
            $datos_parafiscales = $this->get_campos_parafiscales($planilla->id, $empleado->id, $linea->id);
            foreach ($datos_parafiscales as $key => $value)
            {
                $datos_columnas[] = $value;
            }

            $datos_filas[] = $datos_columnas;
            $secuencia++;
        }

        return $datos_filas;

    }

    public function get_campos_novedades($planilla_id, $empleado_id,$empleado_planilla_id)
    {
        $nodevades_empleado = PilaNovedades::where('planilla_generada_id',$planilla_id)
                                                ->where('nom_contrato_id',$empleado_id)
                                                ->where('empleado_planilla_id',$empleado_planilla_id)
                                                ->get()
                                                ->first();

        $pila_novedades = new PilaNovedades;
        $campos = $pila_novedades->getFillable();
        foreach ($campos as $key => $value)
        {
            if ($key > 2)
            {
                if( is_null( $nodevades_empleado ) )
                {
                    $vector[] = '';
                }else{
                    $vector[] = $nodevades_empleado->$value;
                }             
            }                
        }

        array_pop($vector);
        array_pop($vector);
        array_pop($vector);
        array_pop($vector);     

        return $vector;
    }

    public function get_campos_salud($planilla_id, $empleado_id,$empleado_planilla_id)
    {
        $nodevades_empleado = PilaSalud::where('planilla_generada_id',$planilla_id)
                                                ->where('nom_contrato_id',$empleado_id)
                                                ->where('empleado_planilla_id',$empleado_planilla_id)
                                                ->get()
                                                ->first();

        $pila_novedades = new PilaSalud;
        $campos = $pila_novedades->getFillable();
        foreach ($campos as $key => $value)
        {
            if ($key > 2)
            {
                if( is_null( $nodevades_empleado ) )
                {
                    $vector[] = '';
                }else{
                    $vector[] = $nodevades_empleado->$value;
                }             
            }                
        }

        array_pop($vector);
        return $vector;
    }

    public function get_campos_pension($planilla_id, $empleado_id,$empleado_planilla_id)
    {
        $nodevades_empleado = PilaPension::where('planilla_generada_id',$planilla_id)
                                                ->where('nom_contrato_id',$empleado_id)
                                                ->where('empleado_planilla_id',$empleado_planilla_id)
                                                ->get()
                                                ->first();

        $pila_novedades = new PilaPension;
        $campos = $pila_novedades->getFillable();
        foreach ($campos as $key => $value)
        {
            if ($key > 2)
            {
                if( is_null( $nodevades_empleado ) )
                {
                    $vector[] = '';
                }else{
                    $vector[] = $nodevades_empleado->$value;
                }             
            }                
        }

        array_pop($vector);
        return $vector;
    }

    public function get_campos_riesgos_laborales($planilla_id, $empleado_id,$empleado_planilla_id)
    {
        $nodevades_empleado = PilaRiesgoLaboral::where('planilla_generada_id',$planilla_id)
                                                ->where('nom_contrato_id',$empleado_id)
                                                ->where('empleado_planilla_id',$empleado_planilla_id)
                                                ->get()
                                                ->first();

        $pila_novedades = new PilaRiesgoLaboral;
        $campos = $pila_novedades->getFillable();
        foreach ($campos as $key => $value)
        {
            if ($key > 2)
            {
                if( is_null( $nodevades_empleado ) )
                {
                    $vector[] = '';
                }else{
                    $vector[] = $nodevades_empleado->$value;
                }             
            }                
        }

        array_pop($vector);
        array_pop($vector);
        return $vector;
    }

    public function get_campos_parafiscales($planilla_id, $empleado_id,$empleado_planilla_id)
    {
        $nodevades_empleado = PilaParafiscales::where('planilla_generada_id',$planilla_id)
                                                ->where('nom_contrato_id',$empleado_id)
                                                ->where('empleado_planilla_id',$empleado_planilla_id)
                                                ->get()
                                                ->first();

        $pila_novedades = new PilaParafiscales;
        $campos = $pila_novedades->getFillable();
        foreach ($campos as $key => $value)
        {
            if ($key > 2)
            {
                if( is_null( $nodevades_empleado ) )
                {
                    $vector[] = '';
                }else{
                    $vector[] = $nodevades_empleado->$value;
                }             
            }                
        }

        array_pop($vector);
        return $vector;
    }

    /* 
        orientacion_relleno: 
            derecha= completar con caracter de relleno hacia la derecha
            izquierda= completar con caracter de relleno hacia la izquierda

    */
    public function formatear_campo( $valor_campo, $caracter_relleno, $orientacion_relleno, $longitud_campo )
    {
        $largo_campo = strlen( $valor_campo );
        $longitud_campo -= $largo_campo;
        switch ( $orientacion_relleno)
        {
            case 'izquierda':
                for ($i=0; $i < $longitud_campo; $i++)
                {
                    $valor_campo = $caracter_relleno . $valor_campo;
                }
                break;            
            
            case 'derecha':
                for ($i=0; $i < $longitud_campo; $i++)
                {
                    $valor_campo = $valor_campo . $caracter_relleno;
                }
                break;
            
            default:
                # code...
                break;
            
        }

        return $valor_campo;
    }


    public function get_tipo_identificacion( $id_tipo_documento_id )
    {
        switch ( $id_tipo_documento_id )
        {
            case '11': // Registro civil de nacimiento
                return 'RC';
                break;
            case '12': // Tarjeta de identidad
                return 'TI';
                break;
            case '13': // Cédula de ciudadanía
                return 'CC';
                break;
            case '31': // Cédula de ciudadanía
                return 'NI';
                break;
            case '22': // Cédula de extranjería
                return 'CE';
                break;
            case '41': // Pasaporte
                return 'PA';
                break;
            case '42': // Documento de identificación extranjero
                return 'CD';
                break;
            
            default:
                return 'CC';
                break;
        }
    }


    public function catalogos( $permiso_padre_id )
    {
        $app = Aplicacion::find( Input::get('id') );

        $permisos = Permission::where( 'core_app_id', $app->id )
                                ->where('parent',$permiso_padre_id)
                                ->orderBy('orden','ASC')
                                ->get()
                                ->toArray();

        $miga_pan = [
                        ['url' => $app->app.'?id='.$app->id, 'etiqueta' => $app->descripcion],
                        ['url' => 'NO', 'etiqueta' => 'Planilla integrada']
                    ];

        return view( 'layouts.catalogos', compact('permisos', 'miga_pan') );
    }


    /*
            LIQUIDACION PILA
    */
    public function liquidar_planilla( $planilla_id )
    {
        $this->eliminar_registros_tablas_auxiliares_planilla( $planilla_id );

        $planilla = PlanillaGenerada::find( $planilla_id );
        $this->fecha_inicial = $planilla->lapso()->fecha_inicial;
        $this->fecha_final = $planilla->lapso()->fecha_final;

        $empleados_planilla = EmpleadoPlanilla::where( 'planilla_generada_id', $planilla_id )->get();

        foreach ( $empleados_planilla as $linea )
        {   
            $empleado = NomContrato::find( (int)$linea->nom_contrato_id );

            $this->empleado_planilla_id = $linea->id;
            $this->calcular_ibc( $planilla, $empleado );
            $this->almacenar_datos_novedades( $planilla, $empleado, $linea );
        }

        return redirect( 'nom_pila_show/' . $planilla_id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' )->with('flash_message', 'Registros de Planilla actualizados correctamente.');
    }


    public function calcular_ibc( $planilla, $empleado )
    {
        $this->ibc_salud = $this->get_valor_acumulado_agrupacion_entre_meses( $empleado, (int)config('nomina.agrupacion_calculo_ibc_salud'), $this->fecha_inicial, $this->fecha_final ) + 10;// $10 para que alcance la siguiente decena más cercana

        $this->cantidad_dias_laborados = round( $this->calcular_dias_reales_laborados( $empleado, $this->fecha_inicial, $this->fecha_final, (int)config('nomina.agrupacion_calculo_ibc_salud') ), 0);

        $this->validar_ibc_mayor_al_minimino_legal();

        $this->ibc_parafiscales = $this->get_valor_acumulado_agrupacion_entre_meses( $empleado, (int)config('nomina.agrupacion_calculo_ibc_parafiscales'), $this->fecha_inicial, $this->fecha_final );

        $this->cantidad_dias_parafiscales = $this->calcular_dias_reales_laborados( $empleado, $this->fecha_inicial, $this->fecha_final, (int)config('nomina.agrupacion_calculo_ibc_parafiscales') );
    }

    public function validar_ibc_mayor_al_minimino_legal()
    {
        // No se puede tener un IBC por debajo del salario mínimo legal
        $this->valor_ibc_un_dia = 0;
        if ( $this->cantidad_dias_laborados != 0 )
        {
            $this->valor_ibc_un_dia = $this->ibc_salud / $this->cantidad_dias_laborados;
        }        
            
        $valor_ibc_un_dia_minimo_legal = (float)config('nomina.SMMLV') / (int)config('nomina.horas_laborales') * (int)config('nomina.horas_dia_laboral');
        if ( $this->valor_ibc_un_dia < $valor_ibc_un_dia_minimo_legal )
        {
            $this->ibc_salud = ( $valor_ibc_un_dia_minimo_legal * $this->cantidad_dias_laborados ) + 10;// $10 para que alcance la siguiente decena más cercana
            $this->valor_ibc_un_dia = $this->ibc_salud / $this->cantidad_dias_laborados;
        }
    }

    public function get_valor_acumulado_agrupacion_entre_meses( $empleado, $nom_agrupacion_id, $fecha_inicial, $fecha_final )
    {

        $conceptos_de_la_agrupacion = AgrupacionConcepto::find( $nom_agrupacion_id )->conceptos->pluck('id')->toArray();



        $total_devengos = NomDocRegistro::whereIn( 'nom_concepto_id', $conceptos_de_la_agrupacion )
                                            ->where( 'nom_contrato_id', $empleado->id )
                                            ->whereBetween( 'fecha', [$fecha_inicial,$fecha_final] )
                                            ->sum( 'valor_devengo' );

        $total_deducciones = NomDocRegistro::whereIn( 'nom_concepto_id', $conceptos_de_la_agrupacion )
                                            ->where( 'nom_contrato_id', $empleado->id )
                                            ->whereBetween( 'fecha', [$fecha_inicial,$fecha_final] )
                                            ->sum( 'valor_deduccion' );

        return abs( $total_devengos - $total_deducciones );
    }

    public function get_valor_acumulado_concepto_entre_fechas( $empleado, $nom_concepto_id, $fecha_inicial, $fecha_final )
    {
        $total_devengos = NomDocRegistro::where( 'nom_concepto_id', $nom_concepto_id )
                                            ->where( 'nom_contrato_id', $empleado->id )
                                            ->whereBetween( 'fecha', [$fecha_inicial,$fecha_final] )
                                            ->sum( 'valor_devengo' );

        $total_deducciones = NomDocRegistro::where( 'nom_concepto_id', $nom_concepto_id )
                                            ->where( 'nom_contrato_id', $empleado->id )
                                            ->whereBetween( 'fecha', [$fecha_inicial,$fecha_final] )
                                            ->sum( 'valor_deduccion' );

        return abs( $total_devengos - $total_deducciones );
    }

    public function calcular_dias_reales_laborados( $empleado, $fecha_inicial, $fecha_final, $nom_agrupacion_id )
    {
        $conceptos_de_la_agrupacion = AgrupacionConcepto::find( $nom_agrupacion_id )->conceptos;

        // El tiempo se calcula para los concepto que forman parte del básico
        $vec_conceptos = [];
        foreach ($conceptos_de_la_agrupacion as $concepto)
        {
            if ( $empleado->estado == 'Retirado' && $concepto->id == 66 ) // Vacaciones liquidacion de contrato
            {
                continue;
            }
            
            if ($concepto->forma_parte_basico)
            {
                $vec_conceptos[] = $concepto->id;
            }
        }
        
        $cantidad_horas_laboradas = NomDocRegistro::whereIn( 'nom_concepto_id', $vec_conceptos )
                                            ->where( 'nom_contrato_id', $empleado->id )
                                            ->whereBetween( 'fecha', [$fecha_inicial,$fecha_final] )
                                            ->sum( 'cantidad_horas' );

        return ( $cantidad_horas_laboradas / (int)config('nomina.horas_dia_laboral') );
    }

    public function conceptos_liquidados_mes( $empleado, $fecha_inicial, $fecha_final )
    {
        return NomDocRegistro::where( 'nom_contrato_id', $empleado->id )
                            ->whereBetween( 'fecha', [$fecha_inicial,$fecha_final] )
                            ->distinct( 'nom_concepto_id' )
                            ->get()
                            ->pluck('nom_concepto_id')
                            ->toArray();
    }

    public function almacenar_datos_novedades($planilla, $empleado, $linea_empleado_planilla)
    {

        $ing = ' ';
        $vsp = ' ';
        $fecha_de_ingreso = '          ';
        if ( $empleado->fecha_ingreso > $this->fecha_inicial )
        {
            $ing = 'X';
            $vsp = 'X';
            $fecha_de_ingreso = $empleado->fecha_ingreso;
        }

        $ret = ' ';
        $fecha_de_retiro = '          ';
        if ( $empleado->estado == 'Retirado' )
        {
            $ret = 'X';
            $fecha_de_retiro = $empleado->contrato_hasta;
        }

        $vst = ' ';
        if ( $empleado->sueldo != $this->ibc_salud )
        {
            $vst = 'X';
        }


        $sln = ' ';
        $fecha_inicial_suspension_temporal_del_contrato_sln = '          ';
        $fecha_final_suspension_temporal_del_contrato_sln = '          ';
        $ige = ' ';
        $fecha_inicial_incapacidad_enfermedad_general_ige = '          ';
        $fecha_final_incapacidad_enfermedad_general_ige = '          ';
        $lma = ' ';
        $fecha_inicial_licencia_por_maternidad_lma = '          ';
        $fecha_final_licencia_por_maternidad_lma = '          ';
        $vac = ' ';
        $fecha_inicial_vacaciones_licencias_remuneradas_vac = '          ';
        $fecha_final_vacaciones_licencias_remuneradas_vac = '          ';
        $irl = ' ';
        $fecha_inicial_incapacidad_riesgos_laborales_irl = '          ';
        $fecha_final_incapacidad_riesgos_laborales_irl = '          ';

        $this->dias_incapacidad_accidente_trabajo = 0;
        $this->novedad_de_ausentismo = false;

        if ( $linea_empleado_planilla->tipo_linea == 'adicional' )
        {

            /*
                61: PERMISO NO REMUNERADO
            */
            $conceptos_suspencion = [61,62,63];
            foreach ($conceptos_suspencion as $key => $value)
            {
                if ( $linea_empleado_planilla->nom_concepto_id == $value )
                {
                    $sln = 'X';
                    $vst = ' ';
                    $ret = ' ';
                    $fecha_de_retiro = '          ';
                    $this->novedad_de_ausentismo = true;

                    $novedad_tnl = $linea_empleado_planilla->novedad_tnl;

                    if ( !is_null($novedad_tnl) )
                    {
                        $fecha_inicial_suspension_temporal_del_contrato_sln = $novedad_tnl->fecha_inicial_tnl;
                        if ( $novedad_tnl->fecha_inicial_tnl < $this->fecha_inicial )
                        {
                            $fecha_inicial_suspension_temporal_del_contrato_sln = $this->fecha_inicial;
                        }

                        $fecha_final_suspension_temporal_del_contrato_sln = $novedad_tnl->fecha_final_tnl;
                        if ($novedad_tnl->fecha_final_tnl > $this->fecha_final)
                        {
                            $fecha_final_suspension_temporal_del_contrato_sln = $this->fecha_final;
                        }

                        $this->cambiar_ibc_salud( $novedad_tnl, $linea_empleado_planilla );
                        $es_linea_principal = false;
                    } 
                }
            }
            
            $conceptos_incapacidad_enfermedad_general = [58,71]; // Enferemedad general (58) e Incapacidad pagada por la empresa (71)
            foreach ($conceptos_incapacidad_enfermedad_general as $key => $value)
            {
                if ( $linea_empleado_planilla->nom_concepto_id == $value )
                {
                    $ige = 'X';
                    $vst = ' ';
                    $ret = ' ';
                    $fecha_de_retiro = '          ';
                    $this->novedad_de_ausentismo = true;

                    $novedad_tnl = $linea_empleado_planilla->novedad_tnl;

                    if ( !is_null($novedad_tnl) )
                    {
                        $fecha_inicial_incapacidad_enfermedad_general_ige = $novedad_tnl->fecha_inicial_tnl;
                        if ( $novedad_tnl->fecha_inicial_tnl < $this->fecha_inicial )
                        {
                            $fecha_inicial_incapacidad_enfermedad_general_ige = $this->fecha_inicial;
                        }

                        $fecha_final_incapacidad_enfermedad_general_ige = $novedad_tnl->fecha_final_tnl;
                        if ($novedad_tnl->fecha_final_tnl > $this->fecha_final)
                        {
                            $fecha_final_incapacidad_enfermedad_general_ige = $this->fecha_final;
                        }
                        $this->cambiar_ibc_salud( $novedad_tnl, $linea_empleado_planilla );
                        $es_linea_principal = false;
                    }                    
                }
            }
            
            $conceptos_licencia_maternidad = [59, 33]; // O Paternidad
            foreach ($conceptos_licencia_maternidad as $key => $value)
            {
                if ( $linea_empleado_planilla->nom_concepto_id == $value )
                {
                    $lma = 'X';
                    $vst = ' ';
                    $ret = ' ';
                    $fecha_de_retiro = '          ';
                    $this->novedad_de_ausentismo = true;

                    $novedad_tnl = $linea_empleado_planilla->novedad_tnl;

                    if ( !is_null($novedad_tnl) )
                    {
                        $fecha_inicial_licencia_por_maternidad_lma = $novedad_tnl->fecha_inicial_tnl;
                        if ( $novedad_tnl->fecha_inicial_tnl < $this->fecha_inicial )
                        {
                            $fecha_inicial_licencia_por_maternidad_lma = $this->fecha_inicial;
                        }

                        $fecha_final_licencia_por_maternidad_lma = $novedad_tnl->fecha_final_tnl;
                        if ($novedad_tnl->fecha_final_tnl > $this->fecha_final)
                        {
                            $fecha_final_licencia_por_maternidad_lma = $this->fecha_final;
                        }
                        $this->cambiar_ibc_salud( $novedad_tnl, $linea_empleado_planilla );
                        $es_linea_principal = false;
                    } 
                }
            }
            
            $conceptos_vacaciones = [66]; // Disfrutadas
            foreach ($conceptos_vacaciones as $key => $value)
            {
                if ( $linea_empleado_planilla->nom_concepto_id == $value )
                {
                    // Si la vaciones se liquidaron por la liquidacion del contrato
                    if ( $empleado->estado == 'Retirado' )
                    {
                        break;
                    }
                    
                    $vac = 'X';
                    $vst = ' ';
                    $ret = ' ';
                    $fecha_de_retiro = '          ';
                    $this->novedad_de_ausentismo = true;

                    $novedad_tnl = $linea_empleado_planilla->novedad_tnl;

                    if ( !is_null($novedad_tnl) )
                    {
                        $fecha_inicial_vacaciones_licencias_remuneradas_vac = $novedad_tnl->fecha_inicial_tnl;
                        if ( $novedad_tnl->fecha_inicial_tnl < $this->fecha_inicial )
                        {
                            $fecha_inicial_vacaciones_licencias_remuneradas_vac = $this->fecha_inicial;
                        }

                        $fecha_final_vacaciones_licencias_remuneradas_vac = $novedad_tnl->fecha_final_tnl;
                        if ($novedad_tnl->fecha_final_tnl > $this->fecha_final)
                        {
                            $fecha_final_vacaciones_licencias_remuneradas_vac = $this->fecha_final;
                        }
                        $this->cambiar_ibc_salud( $novedad_tnl, $linea_empleado_planilla );
                        $es_linea_principal = false;
                    } 
                }
            }
            
            $conceptos_accidente_laboral = [60];
            foreach ($conceptos_accidente_laboral as $key => $value)
            {
                if ( $linea_empleado_planilla->nom_concepto_id == $value )
                {
                    $irl = 'X';
                    $vst = ' ';
                    $ret = ' ';
                    $fecha_de_retiro = '          ';
                    $this->novedad_de_ausentismo = true;

                    $novedad_tnl = $linea_empleado_planilla->novedad_tnl;

                    if ( !is_null($novedad_tnl) )
                    {
                        $fecha_inicial_incapacidad_riesgos_laborales_irl = $novedad_tnl->fecha_inicial_tnl;
                        if ( $novedad_tnl->fecha_inicial_tnl < $this->fecha_inicial )
                        {
                            $fecha_inicial_incapacidad_riesgos_laborales_irl = $this->fecha_inicial;
                        }

                        $fecha_final_incapacidad_riesgos_laborales_irl = $novedad_tnl->fecha_final_tnl;
                        if ($novedad_tnl->fecha_final_tnl > $this->fecha_final)
                        {
                            $fecha_final_incapacidad_riesgos_laborales_irl = $this->fecha_final;
                        }
                        
                        $this->dias_incapacidad_accidente_trabajo = $this->diferencia_en_dias_entre_fechas( $fecha_inicial_incapacidad_riesgos_laborales_irl, $fecha_final_incapacidad_riesgos_laborales_irl );

                        $this->cambiar_ibc_salud( $novedad_tnl, $linea_empleado_planilla );
                    } 
                }
            }

        }else // $es_linea_principal 
        {
            $this->cambiar_ibc_salud_linea_principal( $planilla, $empleado );

            $dias_ya_laborados_novedades = PilaNovedades::where([
                                                                ['planilla_generada_id','=',$planilla->id],
                                                                ['nom_contrato_id','=',$empleado->id]
                                                            ])
                                                        ->sum('aux_cantidad_dias_laborados');
            if ( ( $dias_ya_laborados_novedades + $this->cantidad_dias_laborados ) > 30 )
            {
                $this->cantidad_dias_laborados--;
                $this->cantidad_dias_parafiscales--;
            }
        }

        $tipo_de_salario = 'F'; // Los valores permitidos son, Salario fijo (F), Salario variable (V), Salario integral (X)
        if ( $empleado->salario_integral )
        {
            $tipo_de_salario = 'X';
        }

        if ( $empleado->es_pasante_sena )
        {
            $tipo_de_salario = ' ';
            $vst = ' ';
        }

        $datos = [ 'planilla_generada_id' => $planilla->id ] +
                    [ 'nom_contrato_id' => $empleado->id ] +
                    [ 'fecha_final_mes' => $planilla->fecha_final_mes ] +
                    [ 'ing' => $ing ] +
                    [ 'ret' => $ret ] +
                    [ 'tde' => ' ' ] +
                    [ 'tae' => ' ' ] +
                    [ 'tdp' => ' ' ] +
                    [ 'tap' => ' ' ] +
                    [ 'vsp' => $vsp ] +
                    [ 'cor' => ' ' ] +
                    [ 'vst' => $vst ] +
                    [ 'sln' => $sln ] +
                    [ 'ige' => $ige ] +
                    [ 'lma' => $lma ] +
                    [ 'vac' => $vac ] +
                    [ 'avp' => ' ' ] +
                    [ 'vct' => ' ' ] +
                    [ 'irl' => $irl ] +
                    [ 'salario_basico' => $this->formatear_campo($empleado->sueldo,'0','izquierda',9) ] +
                    [ 'tipo_de_salario' => $tipo_de_salario ] +
                    [ 'cantidad_dias_laborados' => $this->formatear_campo($this->cantidad_dias_laborados,'0','izquierda',2) ] +
                    [ 'fecha_de_ingreso' => $fecha_de_ingreso ] +
                    [ 'fecha_de_retiro' => $fecha_de_retiro ] +
                    [ 'fecha_inicial_variacion_permanente_de_salario_vsp' => '          ' ] +
                    [ 'fecha_inicial_suspension_temporal_del_contrato_sln' => $fecha_inicial_suspension_temporal_del_contrato_sln ] +
                    [ 'fecha_final_suspension_temporal_del_contrato_sln' => $fecha_final_suspension_temporal_del_contrato_sln ] +
                    [ 'fecha_inicial_incapacidad_enfermedad_general_ige' => $fecha_inicial_incapacidad_enfermedad_general_ige ] +
                    [ 'fecha_final_incapacidad_enfermedad_general_ige' => $fecha_final_incapacidad_enfermedad_general_ige ] +
                    [ 'fecha_inicial_licencia_por_maternidad_lma' => $fecha_inicial_licencia_por_maternidad_lma ] +
                    [ 'fecha_final_licencia_por_maternidad_lma' => $fecha_final_licencia_por_maternidad_lma ] +
                    [ 'fecha_inicial_vacaciones_licencias_remuneradas_vac' => $fecha_inicial_vacaciones_licencias_remuneradas_vac ] +
                    [ 'fecha_final_vacaciones_licencias_remuneradas_vac' => $fecha_final_vacaciones_licencias_remuneradas_vac ] +
                    [ 'fecha_inicial_variacion_centro_de_trabajo_vct' => '          ' ] +
                    [ 'fecha_final_variacion_centro_de_trabajo_vct' => '          ' ] +
                    [ 'fecha_inicial_incapacidad_riesgos_laborales_irl' => $fecha_inicial_incapacidad_riesgos_laborales_irl ] +
                    [ 'fecha_final_incapacidad_riesgos_laborales_irl' => $fecha_final_incapacidad_riesgos_laborales_irl ] +
                    [ 'aux_ibc_salud' => $this->ibc_salud ] +
                    [ 'aux_cantidad_dias_laborados' => $this->cantidad_dias_laborados ] +
                    [ 'empleado_planilla_id' => $this->empleado_planilla_id ] +
                    [ 'estado' => 'Activo' ];/**/

        PilaNovedades::create($datos);

        $this->almacenar_datos_salud( $planilla, $empleado );
        $this->almacenar_datos_pension( $planilla, $empleado );
        $this->almacenar_datos_riesgos_laborales( $planilla, $empleado );
        $this->almacenar_datos_parafiscales( $planilla, $empleado );

    }

    public function diferencia_en_dias_entre_fechas( string $fecha_inicial, string $fecha_final )
    {
        $fecha_ini = Carbon::createFromFormat('Y-m-d', $fecha_inicial);
        $fecha_fin = Carbon::createFromFormat('Y-m-d', $fecha_final );

        return abs( $fecha_ini->diffInDays($fecha_fin) );
    }

    // PARA LÍNEAS ADICIONALES
    public function cambiar_ibc_salud( $novedad_tnl, $linea_empleado_planilla )
    {
        // Se excluyen Vacaciones días NO HABILES (cpto 84) (se tratan como salario - linea_principal)
        $registros_asociados_novedad = NomDocRegistro::where([
                                                                ['novedad_tnl_id','=',$novedad_tnl->id],
                                                                ['nom_concepto_id','<>',84]
                                                            ])
                                                    ->get();

        $this->cantidad_dias_laborados = round( $registros_asociados_novedad->sum('cantidad_horas') / (int)config('nomina.horas_dia_laboral') , 0 );

        // sumar devengos/deducciones asociados a la novedad
        $this->ibc_salud = $registros_asociados_novedad->sum('valor_devengo') - $registros_asociados_novedad->sum('valor_deduccion') + 10;// $10 para que alcance la siguiente decena más cercana
        
        $this->validar_ibc_mayor_al_minimino_legal();

        $this->ibc_parafiscales = $this->ibc_salud;
        $this->cantidad_dias_parafiscales = $this->cantidad_dias_laborados;
        
        // Debe haber algun pago de Parafiscales. El operador de la PILLA dice: 
        // El tipo de cotizante 01 por ausentismo no está obligado aportar a todos los sistemas pero debe por lo menos realizar aportes a uno.
        if ( $this->ibc_salud <= 0 )
        {
            // No se puede asignar un valor por defecto a $this->ibc_salud porque hace calculos para otras cotizaciones
            $this->ibc_parafiscales = (float)config('nomina.SMMLV');
        }
    }

    
    public function cambiar_ibc_salud_linea_principal( $planilla, $empleado )
    {
        $datos_lineas_adicionales = PilaNovedades::where('planilla_generada_id',$planilla->id)
                                                    ->where('nom_contrato_id',$empleado->id)
                                                    ->where('sln',' ')// Validación para conceptos de suspención pues sus dias no se tienen en cuenta en el tiempo de días laborados de SALUD; entonces no se deben restar de la cantidad de dias laborados 
                                                    ->get();
        
        
        $this->ibc_salud -= $datos_lineas_adicionales->sum('aux_ibc_salud');
        $this->cantidad_dias_laborados -= $datos_lineas_adicionales->sum('aux_cantidad_dias_laborados');

        $this->ibc_parafiscales -= $datos_lineas_adicionales->sum('aux_ibc_salud');
        $this->cantidad_dias_parafiscales -= $datos_lineas_adicionales->sum('aux_cantidad_dias_laborados');            
    }



    public function almacenar_datos_salud($planilla, $empleado)
    {
        $porcentaje_salud = 4 / 100;
        if ( $empleado->es_pasante_sena )
        {
            $porcentaje_salud = 12.5 / 100;
        }
        $valor_cotizacion_salud = number_format( $this->ibc_salud * $porcentaje_salud, 0,'','');
        $tarifa_salud = $this->formatear_campo( $porcentaje_salud,'0','derecha',7);
        $cotizacion_salud = $this->formatear_campo( $this->redondear_a_unidad_seguida_ceros( $valor_cotizacion_salud, 100, 'superior'),'0','izquierda',9);

        $datos = [ 'planilla_generada_id' => $planilla->id ] +
                    [ 'nom_contrato_id' => $empleado->id ] +
                    [ 'fecha_final_mes' => $planilla->fecha_final_mes ] +
                    [ 'codigo_entidad_salud' => $this->formatear_campo($empleado->entidad_salud->codigo_nacional,' ','derecha',6) ] +
                    [ 'dias_cotizados_salud' => $this->formatear_campo($this->cantidad_dias_laborados,'0','izquierda',2) ] +
                    [ 'ibc_salud' => $this->formatear_campo( number_format( $this->ibc_salud,0,'',''),'0','izquierda',9) ] +
                    [ 'tarifa_salud' => $tarifa_salud ] +
                    [ 'cotizacion_salud' => $cotizacion_salud ] +
                    [ 'valor_upc_adicional_salud' => '000000000' ] +
                    [ 'total_cotizacion_salud' => $cotizacion_salud ] +
                    [ 'empleado_planilla_id' => $this->empleado_planilla_id ];/**/

        PilaSalud::create($datos);
    }

    public function almacenar_datos_pension($planilla, $empleado)
    {
        $this->el_empleado_id = $empleado->id;
        $cantidad_dias_laborados = $this->cantidad_dias_laborados;
        $ibc_salud = $this->ibc_salud;

        $codigo_entidad_pension = $this->formatear_campo( $empleado->entidad_pension->codigo_nacional,' ','derecha',6);
        $porcentaje_pension = 16 / 100;
        if ( $empleado->es_pasante_sena )
        {
            $porcentaje_pension = '0.0';
            $codigo_entidad_pension = '      ';
            $cantidad_dias_laborados = 0;
            $ibc_salud = 0;
        }
        $valor_cotizacion_pension = number_format( $this->ibc_salud * $porcentaje_pension, 0,'','');
        $tarifa_pension = $this->formatear_campo( $porcentaje_pension,'0','derecha',7);
        $cotizacion_pension = $this->formatear_campo( $this->redondear_a_unidad_seguida_ceros( (float)$valor_cotizacion_pension, 100, 'superior'),'0','izquierda',9);

        $valor_cotizacion_fsp = 0;
        $subcuenta_solidaridad_fsp = '000000000';
        $subcuenta_subsistencia_fsp = '000000000';
        $conceptos_liquidados_mes = $this->conceptos_liquidados_mes( $empleado, $this->fecha_inicial, $this->fecha_final );
        $concepto_fsp = 75; // Fondo de Solidaridad Pensional
        
        if ( in_array($concepto_fsp, $conceptos_liquidados_mes) )
        {
            $valor_cotizacion_fsp = number_format( $this->get_valor_acumulado_concepto_entre_fechas( $empleado, $concepto_fsp, $this->fecha_inicial, $this->fecha_final ), 0,'','');
            $mitad = number_format( $valor_cotizacion_fsp / 2, 0,'','');
            $subcuenta_solidaridad_fsp = $this->formatear_campo( $this->redondear_a_unidad_seguida_ceros( $mitad, 100, 'superior'),'0','izquierda',9);
            $subcuenta_subsistencia_fsp = $this->formatear_campo( $this->redondear_a_unidad_seguida_ceros( $mitad, 100, 'superior'),'0','izquierda',9);
        }

        $valor_total_cotizacion_pension = $valor_cotizacion_pension + $valor_cotizacion_fsp;
        $total_cotizacion_pension = $this->formatear_campo( number_format( $this->redondear_a_unidad_seguida_ceros( $valor_total_cotizacion_pension, 100, 'superior'), 0,'',''),'0','izquierda',9);
        
        $datos = [ 'planilla_generada_id' => $planilla->id ] +
                    [ 'nom_contrato_id' => $empleado->id ] +
                    [ 'fecha_final_mes' => $planilla->fecha_final_mes ] +
                    [ 'codigo_entidad_pension' => $codigo_entidad_pension ] +
                    [ 'dias_cotizados_pension' => $this->formatear_campo( $cantidad_dias_laborados,'0','izquierda',2) ] +
                    [ 'ibc_pension' => $this->formatear_campo( number_format( $ibc_salud,0,'',''),'0','izquierda',9) ] +
                    [ 'tarifa_pension' => $tarifa_pension ] +
                    [ 'cotizacion_pension' => $cotizacion_pension ] +
                    [ 'afp_voluntario_rais_empleado' => '000000000' ] +
                    [ 'afp_voluntatio_rais_empresa' => '000000000' ] +
                    [ 'subcuenta_solidaridad_fsp' => $subcuenta_solidaridad_fsp ] +
                    [ 'subcuenta_subsistencia_fsp' => $subcuenta_subsistencia_fsp ] +
                    [ 'total_cotizacion_pension' => $total_cotizacion_pension ] +
                    [ 'valor_cotizacion_pension' => '000000000' ] +
                    [ 'empleado_planilla_id' => $this->empleado_planilla_id ];/**/

        PilaPension::create($datos);
    }

    public function almacenar_datos_riesgos_laborales($planilla, $empleado)
    {

        $porcentaje_riesgo_laboral = '0.0';
        $clase_de_riesgo = '000000000';
        if( !is_null($empleado->clase_riesgo_laboral) )
        {
            $porcentaje_riesgo_laboral = $empleado->clase_riesgo_laboral->porcentaje_liquidacion / 100;
            $clase_de_riesgo = $this->formatear_campo( $empleado->clase_riesgo_laboral->id,'0','izquierda',9);
        }

        // Cuando se presenta novedad de ausentismo la tarifa de ARL debe de ser cero.
        if ( $this->novedad_de_ausentismo )
        {
            $porcentaje_riesgo_laboral = '0.0';
        }

        $valor_cotizacion_riesgo_laboral = number_format( $this->ibc_salud * $porcentaje_riesgo_laboral, 0,'','');
        $tarifa_riesgo_laboral = $this->formatear_campo( $porcentaje_riesgo_laboral,'0','derecha',9);
        $cotizacion_riesgo_laboral = $this->formatear_campo( $this->redondear_a_unidad_seguida_ceros( $valor_cotizacion_riesgo_laboral, 100, 'superior'),'0','izquierda',9);

        $datos = [ 'planilla_generada_id' => $planilla->id ] +
                    [ 'nom_contrato_id' => $empleado->id ] +
                    [ 'fecha_final_mes' => $planilla->fecha_final_mes ] +
                    [ 'codigo_arl' => $this->formatear_campo($empleado->entidad_arl->codigo_nacional,' ','derecha',6) ] +
                    [ 'dias_cotizados_riesgos_laborales' => $this->formatear_campo($this->cantidad_dias_laborados,'0','izquierda',2) ] +
                    [ 'ibc_riesgos_laborales' => $this->formatear_campo( number_format( $this->ibc_salud,0,'',''),'0','izquierda',9) ] +
                    [ 'tarifa_riesgos_laborales' => $tarifa_riesgo_laboral ] +
                    [ 'total_cotizacion_riesgos_laborales' => $cotizacion_riesgo_laboral ] +
                    [ 'clase_de_riesgo' => $clase_de_riesgo ] +
                    [ 'empleado_planilla_id' => $this->empleado_planilla_id ] +
                    [ 'dias_incapacidad_accidente_trabajo' => $this->formatear_campo( $this->dias_incapacidad_accidente_trabajo,'0', 'izquierda', 2 ) ];/**/

        PilaRiesgoLaboral::create($datos);
    }

    public function almacenar_datos_parafiscales($planilla, $empleado)
    {
        $codigo_entidad_ccf = $this->formatear_campo($empleado->entidad_caja_compensacion->codigo_nacional,' ','derecha',6);
        $cotizante_exonerado_de_aportes_parafiscales = 'S';
        if ( $empleado->es_pasante_sena )
        {
            $cotizante_exonerado_de_aportes_parafiscales = 'N';
            $codigo_entidad_ccf = '      ';
        }

        $porcentaje_caja_compensacion = $planilla->datos_empresa->porcentaje_caja_compensacion / 100;
        $valor_cotizacion_ccf = number_format( $this->ibc_parafiscales * $porcentaje_caja_compensacion, 0,'','');
        $tarifa_ccf = $this->formatear_campo( $porcentaje_caja_compensacion,'0','derecha',7);
        $cotizacion_ccf = $this->formatear_campo( $this->redondear_a_unidad_seguida_ceros( $valor_cotizacion_ccf, 100, 'superior'),'0','izquierda',9);
        if ( $empleado->es_pasante_sena || $this->novedad_de_ausentismo && ($this->ibc_salud > 0) )
        {
            $tarifa_ccf = '0.00000';
            $valor_cotizacion_ccf = 0;
            $cotizacion_ccf = '000000000';
        }

        $porcentaje_sena = $planilla->datos_empresa->porcentaje_sena / 100;
        $valor_cotizacion_sena = number_format( $this->ibc_parafiscales * $porcentaje_sena, 0,'','');
        $tarifa_sena = $this->formatear_campo( $porcentaje_sena,'0','derecha',7);
        $cotizacion_sena = $this->formatear_campo( $this->redondear_a_unidad_seguida_ceros( $valor_cotizacion_sena, 100, 'superior'),'0','izquierda',9);
        if ( $empleado->es_pasante_sena || ( $this->ibc_parafiscales < 10 * (float)config('nomina.SMMLV') ) || $this->novedad_de_ausentismo )
        {
            $tarifa_sena = '0.00000';
            $valor_cotizacion_sena = 0;
            $cotizacion_sena = '000000000';
        }

        $porcentaje_icbf = $planilla->datos_empresa->porcentaje_icbf / 100;
        $valor_cotizacion_icbf = number_format( $this->ibc_parafiscales * $porcentaje_icbf, 0,'','');
        $tarifa_icbf = $this->formatear_campo( $porcentaje_icbf,'0','derecha',7);
        $cotizacion_icbf = $this->formatear_campo( $this->redondear_a_unidad_seguida_ceros( $valor_cotizacion_icbf, 100, 'superior'),'0','izquierda',9);
        if ( $empleado->es_pasante_icbf || ( $this->ibc_parafiscales < 10 * (float)config('nomina.SMMLV') ) || $this->novedad_de_ausentismo )
        {
            $tarifa_icbf = '0.00000';
            $valor_cotizacion_icbf = 0;
            $cotizacion_icbf = '000000000';
        }

        $valor_total_cotizacion = number_format( $valor_cotizacion_ccf + $valor_cotizacion_sena + $valor_cotizacion_icbf, 0,'','');
        $total_cotizacion = $this->formatear_campo( $this->redondear_a_unidad_seguida_ceros( $valor_total_cotizacion, 100, 'superior'),'0','izquierda',9);

        $datos = [ 'planilla_generada_id' => $planilla->id ] +
                    [ 'nom_contrato_id' => $empleado->id ] +
                    [ 'fecha_final_mes' => $planilla->fecha_final_mes ] +
                    [ 'cotizante_exonerado_de_aportes_parafiscales' => $cotizante_exonerado_de_aportes_parafiscales ] +
                    [ 'codigo_entidad_ccf' => $codigo_entidad_ccf ] +
                    [ 'dias_cotizados' => $this->formatear_campo($this->cantidad_dias_parafiscales,'0','izquierda',2) ] +
                    [ 'ibc_parafiscales' => $this->formatear_campo( number_format( $this->ibc_parafiscales,0,'',''),'0','izquierda',9) ] +
                    [ 'tarifa_ccf' => $tarifa_ccf ] +
                    [ 'cotizacion_ccf' => $cotizacion_ccf ] +
                    [ 'tarifa_sena' => $tarifa_sena ] +
                    [ 'cotizacion_sena' => $cotizacion_sena ] +
                    [ 'tarifa_icbf' => $tarifa_icbf ] +
                    [ 'cotizacion_icbf' => $cotizacion_icbf ] +
                    [ 'total_cotizacion' => $total_cotizacion ] +
                    [ 'empleado_planilla_id' => $this->empleado_planilla_id ];/**/

        PilaParafiscales::create($datos);
    }

    public function descargar_archivo_plano( $planilla_id )
    {
        $planilla = PlanillaGenerada::find($planilla_id);

        $namefile = str_slug( $planilla->descripcion ) . '.txt';

        $content = $this->get_datos_encabezado_para_plano( $planilla ) . $this->get_datos_planilla_para_plano( $planilla ) . $this->get_datos_archivo_plano_registro_tipo_6( $planilla );

        //save file
        $file = fopen($namefile, "w") or die("No se pudo generar el archivo. Problemas con el Internet. Por favor, intente nuevamente!");
        fwrite($file, $content);
        fclose($file);

        //header download
        header("Content-Disposition: attachment; filename=\"" . $namefile . "\"");
        header("Content-Type: application/force-download");
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header("Content-Type: text/plain");

        echo $content;
    }

    public function get_datos_encabezado_para_plano( $planilla )
    {

        $fila = '01'; // Tipo de registro
        $fila .= '0';
        $fila .= '0001'; // Secuencia

        $fila .= $this->formatear_campo( $planilla->datos_empresa->empresa->razon_social, ' ', 'derecha', 200);
        $fila .= $this->get_tipo_identificacion( $planilla->datos_empresa->empresa->id_tipo_documento_id );
        $fila .= $this->formatear_campo( $planilla->datos_empresa->empresa->numero_identificacion, ' ', 'derecha', 16);
        $fila .= $planilla->datos_empresa->empresa->digito_verificacion;
        $fila .= 'E';
        $fila .= '          '; // Número de la planilla asociada a esta planilla
        $fila .= '          '; // Fecha de pago de la planilla asociada a esta planilla

        $fila .= $planilla->datos_empresa->forma_presentacion;
        $fila .= '          '; // Código de la sucursal del aportante
        $fila .= '                                        '; // Nombre de la Sucursal
        $fila .= $this->formatear_campo( $planilla->datos_empresa->entidad_arl->codigo_nacional, ' ', 'derecha', 6);

        $anio = substr($planilla->fecha_final_mes, 0, 4);
        $mes_salud = (int)substr($planilla->fecha_final_mes, 5, 2);
        if ( $mes_salud == 12 )
        {
            $mes_salud = $anio + 1 . '-01';
        }else{
            $mes_salud = $anio . '-' . $this->formatear_campo( $mes_salud + 1, '0', 'izquierda', 2);
        }

        $fila .= substr($planilla->fecha_final_mes, 0, 7); // Período de pago para los sistemas diferentes al de salud (Pensión - mes vencido)
        $fila .= $mes_salud; // Período de pago para el sistema de salud. (Mes del reporte)

        $fila .= '0000000000'; // Número de radicación o de la Planilla Integrada de Liquidación de Aportes.

        $fila .= '          '; // Fecha de pago

        $cantidad_cotizantes_1 = EmpleadoPlanilla::where('planilla_generada_id', $planilla->id)->get();
        $cantidad_cotizantes = $cantidad_cotizantes_1->unique('nom_contrato_id')->count();
        $fila .= $this->formatear_campo( $cantidad_cotizantes, '0', 'izquierda', 5);  // Número total de cotizantes reportados en esta planilla.

        $suma_ibc_parafiscales = PilaParafiscales::where('planilla_generada_id',$planilla->id)->sum('ibc_parafiscales');
        $fila .= $this->formatear_campo( $suma_ibc_parafiscales, '0', 'izquierda', 12);  // Valor total de la nómina. Ccorresponde a la sumatoria de los IBC para el pago de los aportes de parafiscales de la totalidad de los empleados.

        $fila .= $planilla->datos_empresa->tipo_aportante;

        $fila .= "00\n"; // Código del operador de información.

        return $fila;
    }

    // REGISTRO TIPO 6. TOTAL APORTES DEL PERIODO A CAJAS DE COMPENSACIÓN FAMILIAR
    public function get_datos_archivo_plano_registro_tipo_6( $planilla )
    {

        $fila = '06'; // Tipo de registro
        $fila .= '00001'; // Secuencia

        $fila .= $this->formatear_campo( $planilla->datos_empresa->entidad_arl->codigo_nacional, ' ', 'derecha', 6);

        $fila .= $this->formatear_campo( $planilla->datos_empresa->entidad_arl->tercero->numero_identificacion, '0', 'izquierda', 16);

        $fila .= $planilla->datos_empresa->entidad_arl->tercero->digito_verificacion;

        //$total_cotizacion_riesgos_laborales = PilaRiesgoLaboral::where('planilla_generada_id',$planilla->id)->sum('total_cotizacion_riesgos_laborales');
        //$fila .= $this->formatear_campo( $total_cotizacion_riesgos_laborales, '0', 'izquierda', 13);

        $fila .= '0000000000000               000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000';

        return $fila;
    }


    // Obtiene los datos almacenados en las tablas de liquidacion PILA
    public function get_datos_planilla_para_plano( $planilla )
    {
        //$empleados = $planilla->empleados;
        $empleados_planilla = EmpleadoPlanilla::where('planilla_generada_id',$planilla->id)->get();

        $datos_filas = '';
        $secuencia = 1;
        foreach ($empleados_planilla as $key => $linea )
        {
            $empleado = NomContrato::find( (int)$linea->nom_contrato_id );
            $datos_columnas = '';

            /*
                        Datos básicos del empleado
            */
            $datos_columnas .= '02';
            $datos_columnas .= $this->formatear_campo($secuencia,'0','izquierda',5);

            $tercero = $empleado->tercero;
            $datos_columnas .= $this->get_tipo_identificacion( $tercero->id_tipo_documento_id );
            $datos_columnas .= $this->formatear_campo($tercero->numero_identificacion,' ','derecha',16);

            
            $datos_columnas .= $this->get_tipo_cotizante( $empleado );
            $datos_columnas .= '00'; // $subtipo_cotizante

            $datos_columnas .= ' '; // Extranjero no obligado a cotizar a pensiones
            $datos_columnas .= ' '; // Colombiano en el exterior

            $datos_columnas .= substr($tercero->ciudad->id, 3,2); // Departamento
            $datos_columnas .= substr($tercero->ciudad->id, 5);


            $datos_columnas .= $this->formatear_campo( $this->formatear_acentos( $tercero->apellido1 ),' ','derecha',20);
            $datos_columnas .= $this->formatear_campo( $this->formatear_acentos( $tercero->apellido2 ),' ','derecha',30);
            $datos_columnas .= $this->formatear_campo( $this->formatear_acentos( $tercero->nombre1 ),' ','derecha',20);
            $datos_columnas .= $this->formatear_campo( $this->formatear_acentos( $tercero->otros_nombres ),' ','derecha',30);

            $datos_novedades = PilaNovedades::where( 'planilla_generada_id', $planilla->id)
                                            ->where( 'nom_contrato_id', $empleado->id)
                                            ->where( 'empleado_planilla_id', $linea->id)
                                            ->get()
                                            ->first();

            if( is_null($datos_novedades) )
            {
                dd( 'No se han generado Novedades para el empleado ' . $empleado->tercero->descripcion . ' en la planilla ' . $planilla->descripcion );
            }

            $datos_salud = PilaSalud::where('planilla_generada_id',$planilla->id)
                                    ->where('nom_contrato_id',$empleado->id)
                                    ->where('empleado_planilla_id',$linea->id)
                                    ->get()
                                    ->first();
            if( is_null($datos_salud) )
            {
                dd( 'No se han generado datos de Salud para el empleado ' . $empleado->tercero->descripcion . ' en la planilla ' . $planilla->descripcion );
            }

            $datos_pension = PilaPension::where('planilla_generada_id',$planilla->id)
                                    ->where('nom_contrato_id',$empleado->id)
                                    ->where('empleado_planilla_id',$linea->id)
                                    ->get()
                                    ->first();
            if( is_null($datos_pension) )
            {
                dd( 'No se han generado datos de Pension para el empleado ' . $empleado->tercero->descripcion . ' en la planilla ' . $planilla->descripcion );
            }

            $datos_riesgos_laborales = PilaRiesgoLaboral::where('planilla_generada_id',$planilla->id)
                                    ->where('nom_contrato_id',$empleado->id)
                                    ->where('empleado_planilla_id',$linea->id)
                                    ->get()
                                    ->first();
            if( is_null($datos_riesgos_laborales) )
            {
                dd( 'No se han generado datos de Riesgos laborales para el empleado ' . $empleado->tercero->descripcion . ' en la planilla ' . $planilla->descripcion );
            }

            $datos_parafiscales = PilaParafiscales::where('planilla_generada_id',$planilla->id)
                                    ->where('nom_contrato_id',$empleado->id)
                                    ->where('empleado_planilla_id',$linea->id)
                                    ->get()
                                    ->first();
            if( is_null($datos_parafiscales) )
            {
                dd( 'No se han generado datos de Parafiscales para el empleado ' . $empleado->tercero->descripcion . ' en la planilla ' . $planilla->descripcion );
            }

            $datos_columnas .= $datos_novedades->ing;
            $datos_columnas .= $datos_novedades->ret;
            $datos_columnas .= $datos_novedades->tde;
            $datos_columnas .= $datos_novedades->tae;
            $datos_columnas .= $datos_novedades->tdp;
            $datos_columnas .= $datos_novedades->tap;
            $datos_columnas .= $datos_novedades->vsp;
            $datos_columnas .= $datos_novedades->cor;
            $datos_columnas .= $datos_novedades->vst;
            $datos_columnas .= $datos_novedades->sln;
            $datos_columnas .= $datos_novedades->ige;
            $datos_columnas .= $datos_novedades->lma;
            $datos_columnas .= $datos_novedades->vac;
            $datos_columnas .= $datos_novedades->avp;
            $datos_columnas .= $datos_novedades->vct;

            $datos_columnas .= $datos_riesgos_laborales->dias_incapacidad_accidente_trabajo;

            $datos_columnas .= $datos_pension->codigo_entidad_pension;
            $datos_columnas .= '      '; // Código de la administradora de fondos de pensiones a la cual se traslada el afiliado
            $datos_columnas .= $datos_salud->codigo_entidad_salud;
            $datos_columnas .= '      '; // Código EPS o EOC a la cual se traslada el afiliado
            $datos_columnas .= $datos_parafiscales->codigo_entidad_ccf;
            $datos_columnas .= $datos_pension->dias_cotizados_pension;
            $datos_columnas .= $datos_salud->dias_cotizados_salud;
            $datos_columnas .= $datos_riesgos_laborales->dias_cotizados_riesgos_laborales;
            $datos_columnas .= $datos_parafiscales->dias_cotizados;

            $datos_columnas .= $datos_novedades->salario_basico;
            $datos_columnas .= $datos_novedades->tipo_de_salario;
            $datos_columnas .= $datos_pension->ibc_pension;
            $datos_columnas .= $datos_salud->ibc_salud;
            $datos_columnas .= $datos_riesgos_laborales->ibc_riesgos_laborales;
            $datos_columnas .= $datos_parafiscales->ibc_parafiscales;

            $datos_columnas .= $datos_pension->tarifa_pension;
            $datos_columnas .= $datos_pension->cotizacion_pension;
            $datos_columnas .= $datos_pension->afp_voluntario_rais_empleado;
            $datos_columnas .= $datos_pension->afp_voluntatio_rais_empresa;
            $datos_columnas .= $datos_pension->total_cotizacion_pension;        // Posicion inicial: 272
            $datos_columnas .= $datos_pension->subcuenta_solidaridad_fsp;
            $datos_columnas .= $datos_pension->subcuenta_subsistencia_fsp;
            $datos_columnas .= '000000000'; // Valor no retenido por aportes voluntarios.

            $datos_columnas .= $datos_salud->tarifa_salud;
            $datos_columnas .= $datos_salud->cotizacion_salud;
            $datos_columnas .= $datos_salud->valor_upc_adicional_salud;
            $datos_columnas .= '               '; // No. autorización de la incapacidad por enfermedad general
            $datos_columnas .= '000000000'; // Valor de la incapacidad por enfermedad general
            $datos_columnas .= '               '; // No. de autorización de la licencia de maternidad o paternidad
            $datos_columnas .= '000000000'; // Valor de la licencia de maternidad
            $datos_columnas .= $datos_riesgos_laborales->tarifa_riesgos_laborales;
            $datos_columnas .= $datos_riesgos_laborales->clase_de_riesgo;
            $datos_columnas .= $datos_riesgos_laborales->total_cotizacion_riesgos_laborales;
            $datos_columnas .= $datos_parafiscales->tarifa_ccf;
            $datos_columnas .= $datos_parafiscales->cotizacion_ccf;
            $datos_columnas .= $datos_parafiscales->tarifa_sena;
            $datos_columnas .= $datos_parafiscales->cotizacion_sena;
            $datos_columnas .= $datos_parafiscales->tarifa_icbf;
            $datos_columnas .= $datos_parafiscales->cotizacion_icbf;
            $datos_columnas .= '0.00000'; // Tarifa aportes ESAP
            $datos_columnas .= '000000000'; // Valor aporte ESAP
            $datos_columnas .= '0.00000'; // Tarifa aportes MEN
            $datos_columnas .= '000000000'; // Valor aporte MEN

            $datos_columnas .= '  '; // Tipo de documento del cotizante principal
            $datos_columnas .= '                '; // Número de identificación del cotizante principal
            $datos_columnas .= $datos_parafiscales->cotizante_exonerado_de_aportes_parafiscales;
            $datos_columnas .= $datos_riesgos_laborales->codigo_arl;
            
            $clase_riesgo_laboral_id = 0;
            if( !is_null( $linea->empleado->clase_riesgo_laboral ) )
            {
                $clase_riesgo_laboral_id = $linea->empleado->clase_riesgo_laboral->id;
            }
            $datos_columnas .= $clase_riesgo_laboral_id;
            $datos_columnas .= ' '; // Indicador tarifa especial pensiones                

            $datos_columnas .= $datos_novedades->fecha_de_ingreso;
            $datos_columnas .= $datos_novedades->fecha_de_retiro;
            $datos_columnas .= $datos_novedades->fecha_inicial_variacion_permanente_de_salario_vsp;
            $datos_columnas .= $datos_novedades->fecha_inicial_suspension_temporal_del_contrato_sln;
            $datos_columnas .= $datos_novedades->fecha_final_suspension_temporal_del_contrato_sln;
            $datos_columnas .= $datos_novedades->fecha_inicial_incapacidad_enfermedad_general_ige;
            $datos_columnas .= $datos_novedades->fecha_final_incapacidad_enfermedad_general_ige;
            $datos_columnas .= $datos_novedades->fecha_inicial_licencia_por_maternidad_lma;
            $datos_columnas .= $datos_novedades->fecha_final_licencia_por_maternidad_lma;
            $datos_columnas .= $datos_novedades->fecha_inicial_vacaciones_licencias_remuneradas_vac;
            $datos_columnas .= $datos_novedades->fecha_final_vacaciones_licencias_remuneradas_vac;
            $datos_columnas .= $datos_novedades->fecha_inicial_variacion_centro_de_trabajo_vct;
            $datos_columnas .= $datos_novedades->fecha_final_variacion_centro_de_trabajo_vct;
            $datos_columnas .= $datos_novedades->fecha_inicial_incapacidad_riesgos_laborales_irl;
            $datos_columnas .= $datos_novedades->fecha_final_incapacidad_riesgos_laborales_irl;

            $datos_columnas .= '000000000';
            $datos_columnas .= $this->formatear_campo($datos_novedades->aux_cantidad_dias_laborados * (int)config('nomina.horas_dia_laboral'),'0','izquierda',3);
            $datos_columnas .= '          '; // Fecha radicación en el exterior

            $datos_filas .= $datos_columnas . "\n";
            $secuencia++;
        }

        return $datos_filas;
    }

    public function formatear_acentos( $texto )
    {
        return strtoupper( str_slug( $texto ) );
    }

    public function get_tipo_cotizante( $empleado )
    {
        /*$tipo_cotizante = '01';
        if ( $empleado->es_pasante_sena)
        {
            $tipo_cotizante = '19';
        }*/

        return $empleado->tipo_cotizante;
    }

    /*
            PARA REDONDEAR A LA UNIDAD, DECENA, CENTENA... MÁS CERCANA
    */
    public function redondear_a_unidad_seguida_ceros( $numero, $valor_unidad_seguida_ceros, $tipo_redondeo)
    {
        if ( $numero == 0 )
        {
            return 0;
        }
        
        $valor_redondeado = $numero;

        if ( $valor_unidad_seguida_ceros != 0 )
        {
            $decimal = $numero / $valor_unidad_seguida_ceros;
            $aux = (string) $decimal;
            // Si, no existe el punto en el string $aux, $numero no necesita ser redondeado
            if ( (int)strpos( $aux, "." ) == 0 )
            {
                return $numero;
            }

            // Extraer la parte decimal
            $residuo = substr( $aux, strpos( $aux, "." ) );

            $valor_residuo_tipo_unidad = $residuo * $valor_unidad_seguida_ceros;

            switch ( $tipo_redondeo )
            {
                case 'superior':
                    $diferecia = $valor_unidad_seguida_ceros - $valor_residuo_tipo_unidad;
                    $valor_redondeado = $numero + $diferecia;
                    break;
                
                case 'inferior':
                    $valor_redondeado = $numero - $valor_residuo_tipo_unidad;
                    break;
                
                default:
                    $valor_redondeado = $numero;
                    break;
            }
                    
        }

        return $valor_redondeado;
    }

    public function eliminar_planilla( $planilla_id )
    {
        $this->eliminar_registros_tablas_auxiliares_planilla( $planilla_id );

        EmpleadoPlanilla::where('planilla_generada_id',$planilla_id)->delete();
        
        PlanillaGenerada::find($planilla_id)->delete();

        return redirect('web?id=17&id_modelo=271')->with('flash_message','Planilla eliminada correctamente.');
    }

    public function eliminar_registros_tablas_auxiliares_planilla( $planilla_id )
    {
        PilaNovedades::where('planilla_generada_id',$planilla_id)->delete();
        PilaSalud::where('planilla_generada_id',$planilla_id)->delete();
        PilaPension::where('planilla_generada_id',$planilla_id)->delete();
        PilaRiesgoLaboral::where('planilla_generada_id',$planilla_id)->delete();
        PilaParafiscales::where('planilla_generada_id',$planilla_id)->delete();
    }

}
