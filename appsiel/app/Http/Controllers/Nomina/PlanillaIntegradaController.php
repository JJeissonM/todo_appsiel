<?php

namespace App\Http\Controllers\Nomina;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use View;
use Auth;
use Input;
use Spatie\Permission\Models\Permission;

use App\Sistema\Aplicacion;

use App\Nomina\Procesos\ArchivoPlanoPlanillaIntegrada;

use App\Nomina\NomDocEncabezado;
use App\Nomina\NomDocRegistro;
use App\Nomina\NomConcepto;
use App\Nomina\AgrupacionConcepto;
use App\Nomina\NomContrato;
use App\Nomina\PlanillaGenerada;

use App\Nomina\PilaNovedades;
use App\Nomina\PilaSalud;
use App\Nomina\PilaPension;
use App\Nomina\PilaRiesgoLaboral;
use App\Nomina\PilaParafiscales;

class PlanillaIntegradaController extends Controller
{
    protected $ibc_salud; // Se usa para Salud, Pensión y Riesgos laborales
    protected $ibc_parafiscales;
    protected $cantidad_dias_laborados;
    protected $cantidad_dias_parafiscales;
    protected $fecha_inicial;
    protected $fecha_final;

    public function show($planilla_generada_id)
    {

        $view_pdf = '';//$this->vista_preliminar($id,'show');
        
        $planilla_generada = PlanillaGenerada::find($planilla_generada_id);

        $datos_planilla = $this->get_datos_planilla( $planilla_generada );

        $tabla_planilla = View::make('nomina.planilla_integrada.tabla_planilla', compact('datos_planilla') )->render();

        $miga_pan = [
                  ['url'=>'nomina?id='.Input::get('id'),'etiqueta'=>'Nómina'],
                  ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta' => $planilla_generada->descripcion ],
                  ['url'=>'NO','etiqueta' => 'Consulta' ]
              ];

        // Para el modelo relacionado: Empleados
        /*$modelo_crud = new ModeloController;
        $respuesta = $modelo_crud->get_tabla_relacionada($modelo, $encabezado_doc);

        $tabla = $respuesta['tabla'];
        $opciones = $respuesta['opciones'];
        $registro_modelo_padre_id = $respuesta['registro_modelo_padre_id'];
        $titulo_tab = $respuesta['titulo_tab'];

        return view( 'nomina.show',compact('reg_anterior','reg_siguiente','miga_pan','view_pdf','id','encabezado_doc','tabla','opciones','registro_modelo_padre_id','titulo_tab') ); 
        */

        return view( 'nomina.planilla_integrada.show',compact('miga_pan','view_pdf','planilla_generada','tabla_planilla') );
    }

    // Obtiene los datos almacenados en las tablas de liquidacion PILA
    public function get_datos_planilla( $planilla )
    {
        $empleados = $planilla->empleados;

        $datos_filas = [];
        $secuencia = 1;
        foreach ($empleados as $key => $empleado)
        {
            $datos_columnas = [];

            /*
                        Datos básicos del empleado
            */
            $datos_columnas[] = '02';
            $datos_columnas[] = $this->formatear_campo($secuencia,'0','izquierda',8);

            $tercero = $empleado->tercero;
            $datos_columnas[] = $this->get_tipo_identificacion( $tercero->id_tipo_documento_id );
            $datos_columnas[] = $this->formatear_campo($tercero->numero_identificacion,' ','derecha',16);

            $tipo_cotizante = '01';
            if ( $empleado->es_pasante_sena)
            {
                $tipo_cotizante = '19';
            }
            $datos_columnas[] = $tipo_cotizante;
            $datos_columnas[] = '00'; // $subtipo_cotizante

            $datos_columnas[] = ' '; // Extranjero no obligado a cotizar a pensiones
            $datos_columnas[] = ' '; // Colombiano en el exterior

            $datos_columnas[] = substr($tercero->ciudad->id, 3,2); // Departamento
            $datos_columnas[] = substr($tercero->ciudad->id, 5);


            $datos_columnas[] = $this->formatear_campo($tercero->apellido1,' ','derecha',20);
            $datos_columnas[] = $this->formatear_campo($tercero->apellido2,' ','derecha',20);
            $datos_columnas[] = $this->formatear_campo($tercero->nombre1,' ','derecha',20);
            $datos_columnas[] = $this->formatear_campo($tercero->otros_nombres,' ','derecha',30);

            /*
                        DATOS DE NOVEDADES
            */
            $datos_novedades = $this->get_campos_novedades($planilla->id, $empleado->id);
            foreach ($datos_novedades as $key => $value)
            {
                $datos_columnas[] = $value;
            }



            /*
                        DATOS DE SALUD
            */
            $datos_salud = $this->get_campos_salud($planilla->id, $empleado->id);
            foreach ($datos_salud as $key => $value)
            {
                $datos_columnas[] = $value;
            }



            /*
                        DATOS DE PENSION
            */
            $datos_pension = $this->get_campos_pension($planilla->id, $empleado->id);
            foreach ($datos_pension as $key => $value)
            {
                $datos_columnas[] = $value;
            }



            /*
                        DATOS DE RIESGOS LABORALES
            */
            $datos_riesgos_laborales = $this->get_campos_riesgos_laborales($planilla->id, $empleado->id);
            foreach ($datos_riesgos_laborales as $key => $value)
            {
                $datos_columnas[] = $value;
            }



            /*
                        DATOS DE PARAFISCALES
            */
            $datos_parafiscales = $this->get_campos_parafiscales($planilla->id, $empleado->id);
            foreach ($datos_parafiscales as $key => $value)
            {
                $datos_columnas[] = $value;
            }

            $datos_filas[] = $datos_columnas;
            $secuencia++;
        }

        return $datos_filas;

    }

    public function get_campos_novedades($planilla_id, $empleado_id)
    {
        $nodevades_empleado = PilaNovedades::where('planilla_generada_id',$planilla_id)
                                                ->where('nom_contrato_id',$empleado_id)
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

        return $vector;
    }

    public function get_campos_salud($planilla_id, $empleado_id)
    {
        $nodevades_empleado = PilaSalud::where('planilla_generada_id',$planilla_id)
                                                ->where('nom_contrato_id',$empleado_id)
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

        return $vector;
    }

    public function get_campos_pension($planilla_id, $empleado_id)
    {
        $nodevades_empleado = PilaPension::where('planilla_generada_id',$planilla_id)
                                                ->where('nom_contrato_id',$empleado_id)
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

        return $vector;
    }

    public function get_campos_riesgos_laborales($planilla_id, $empleado_id)
    {
        $nodevades_empleado = PilaRiesgoLaboral::where('planilla_generada_id',$planilla_id)
                                                ->where('nom_contrato_id',$empleado_id)
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

        return $vector;
    }

    public function get_campos_parafiscales($planilla_id, $empleado_id)
    {
        $nodevades_empleado = PilaParafiscales::where('planilla_generada_id',$planilla_id)
                                                ->where('nom_contrato_id',$empleado_id)
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

        return $vector;
    }

    /* 
        orientacion_relleno: 
            derecha= completar con caracter de relleno hacia la derecha
            izquierda= completar con caracter de relleno hacia la izquierda

    */
    public function formatear_campo( $valor_campo, $caracter_relleno, $orientacion_relleno, $longitud_campo )
    {
        $largo_campo = strlen($valor_campo);
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

                                //dd($permisos);

        $miga_pan = [
                        ['url' => $app->app.'?id='.$app->id, 'etiqueta' => $app->descripcion],
                        ['url' => 'NO', 'etiqueta' => 'Planilla integrada']
                    ];

        return view( 'layouts.catalogos', compact('permisos', 'miga_pan') );
    }

    public function liquidar_planilla( $planilla_id )
    {
        $planilla = PlanillaGenerada::find( $planilla_id );
        $this->fecha_inicial = $planilla->lapso()->fecha_inicial;
        $this->fecha_final = $planilla->lapso()->fecha_final;

        $empleados = $planilla->empleados;

        foreach ($empleados as $empleado)
        {
            $this->calcular_ibc( $planilla, $empleado );
            $this->almacenar_datos_novedades( $planilla, $empleado );
            $this->almacenar_datos_salud( $planilla, $empleado );
            $this->almacenar_datos_pension( $planilla, $empleado );
            $this->almacenar_datos_riesgos_laborales( $planilla, $empleado );
            $this->almacenar_datos_parafiscales( $planilla, $empleado );
        }

        return redirect( 'nom_pila_show/' . $planilla_id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' )->with('flash_message', 'Registros de Planilla actualizados correctamente.');
    }

    public function calcular_ibc( $planilla, $empleado )
    {
        $this->ibc_salud = $this->get_valor_acumulado_agrupacion_entre_meses( $empleado, (int)config('nomina.agrupacion_calculo_ibc_salud'), $this->fecha_inicial, $this->fecha_final );

        $this->cantidad_dias_laborados = $this->calcular_dias_reales_laborados( $empleado, $this->fecha_inicial, $this->fecha_final, (int)config('nomina.agrupacion_calculo_ibc_salud') );

        $this->ibc_parafiscales = $this->get_valor_acumulado_agrupacion_entre_meses( $empleado, (int)config('nomina.agrupacion_calculo_ibc_parafiscales'), $this->fecha_inicial, $this->fecha_final );

        $this->cantidad_dias_parafiscales = $this->calcular_dias_reales_laborados( $empleado, $this->fecha_inicial, $this->fecha_final, (int)config('nomina.agrupacion_calculo_ibc_parafiscales') );
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

        return ( $total_devengos - $total_deducciones );
    }

    public function calcular_dias_reales_laborados( $empleado, $fecha_inicial, $fecha_final, $nom_agrupacion_id )
    {
        $conceptos_de_la_agrupacion = AgrupacionConcepto::find( $nom_agrupacion_id )->conceptos;

        // El tiempo se calcula para los concepto que forman parte del básico
        $vec_conceptos = [];
        foreach ($conceptos_de_la_agrupacion as $concepto)
        {
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

    public function almacenar_datos_novedades($planilla, $empleado)
    {
        $registro_anterior = PilaNovedades::where('planilla_generada_id',$planilla->id)
                                                ->where('nom_contrato_id',$empleado->id)
                                                ->get()
                                                ->first();

        if ( !is_null($registro_anterior) )
        {
            $registro_anterior->delete();
        }

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

        $conceptos_liquidados_mes = $this->conceptos_liquidados_mes( $empleado, $this->fecha_inicial, $this->fecha_final );
        
        $sln = ' ';
        $conceptos_suspencion = [61,62,63];
        foreach ($conceptos_suspencion as $key => $value)
        {
            if ( in_array($value, $conceptos_liquidados_mes) )
            {
                $sln = 'X';
            }
        }
        
        $ige = ' ';
        $conceptos_incapacidad_enfermedad_general = [58];
        foreach ($conceptos_incapacidad_enfermedad_general as $key => $value)
        {
            if ( in_array($value, $conceptos_liquidados_mes) )
            {
                $ige = 'X';
            }
        }
        
        $lma = ' ';
        $conceptos_licencia_maternidad = [59];
        foreach ($conceptos_licencia_maternidad as $key => $value)
        {
            if ( in_array($value, $conceptos_liquidados_mes) )
            {
                $lma = 'X';
            }
        }
        
        $vac = ' ';
        $conceptos_vacaciones = [66];
        foreach ($conceptos_vacaciones as $key => $value)
        {
            if ( in_array($value, $conceptos_liquidados_mes) )
            {
                $vac = 'X';
            }
        }
        
        $irl = ' ';
        $conceptos_accidente_laboral = [60];
        foreach ($conceptos_accidente_laboral as $key => $value)
        {
            if ( in_array($value, $conceptos_liquidados_mes) )
            {
                $irl = 'X';
            }
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
                    [ 'tipo_de_salario' => ' ' ] +
                    [ 'cantidad_dias_laborados' => $this->formatear_campo($this->cantidad_dias_laborados,'0','izquierda',2) ] +
                    [ 'fecha_de_ingreso' => $fecha_de_ingreso ] +
                    [ 'fecha_de_retiro' => $fecha_de_retiro ] +
                    [ 'fecha_inicial_variacion_permanente_de_salario_vsp' => '          ' ] +
                    [ 'fecha_inicial_suspension_temporal_del_contrato_sln' => '          ' ] +
                    [ 'fecha_final_suspension_temporal_del_contrato_sln' => '          ' ] +
                    [ 'fecha_inicial_incapacidad_enfermedad_general_ige' => '          ' ] +
                    [ 'fecha_final_incapacidad_enfermedad_general_ige' => '          ' ] +
                    [ 'fecha_inicial_licencia_por_maternidad_lma' => '          ' ] +
                    [ 'fecha_final_licencia_por_maternidad_lma' => '          ' ] +
                    [ 'fecha_inicial_vacaciones_licencias_remuneradas_vac' => '          ' ] +
                    [ 'fecha_final_vacaciones_licencias_remuneradas_vac' => '          ' ] +
                    [ 'fecha_inicial_variacion_centro_de_trabajo_vct' => '          ' ] +
                    [ 'fecha_final_variacion_centro_de_trabajo_vct' => '          ' ] +
                    [ 'fecha_inicial_incapacidad_riesgos_laborales_irl' => '          ' ] +
                    [ 'fecha_final_incapacidad_riesgos_laborales_irl' => '          ' ] +
                    [ 'estado' => 'Activo' ];/**/

        PilaNovedades::create($datos);
    }

    public function almacenar_datos_salud($planilla, $empleado)
    {
        $registro_anterior = PilaSalud::where('planilla_generada_id',$planilla->id)
                                                ->where('nom_contrato_id',$empleado->id)
                                                ->get()
                                                ->first();

        if ( !is_null($registro_anterior) )
        {
            $registro_anterior->delete();
        }

        // $this->formatear_campo( number_format( $this->ibc_parafiscales,0,'',''),'0','izquierda',9)

        $porcentaje_salud = 4 / 100;
        if ( $empleado->es_pasante_sena )
        {
            $porcentaje_salud = 12.5 / 100;
        }
        $valor_cotizacion_salud = number_format( $this->ibc_salud * $porcentaje_salud, 0,'','');
        $tarifa_salud = $this->formatear_campo( $porcentaje_salud,'0','derecha',7);
        $cotizacion_salud = $this->formatear_campo( $valor_cotizacion_salud,'0','izquierda',9);

        $datos = [ 'planilla_generada_id' => $planilla->id ] +
                    [ 'nom_contrato_id' => $empleado->id ] +
                    [ 'fecha_final_mes' => $planilla->fecha_final_mes ] +
                    [ 'codigo_entidad_salud' => $this->formatear_campo($empleado->entidad_salud->codigo_nacional,' ','derecha',6) ] +
                    [ 'dias_cotizados_salud' => $this->formatear_campo($this->cantidad_dias_laborados,'0','izquierda',2) ] +
                    [ 'ibc_salud' => $this->formatear_campo( number_format( $this->ibc_salud,0,'',''),'0','izquierda',9) ] +
                    [ 'tarifa_salud' => $tarifa_salud ] +
                    [ 'cotizacion_salud' => $cotizacion_salud ] +
                    [ 'valor_upc_adicional_salud' => '000000000' ] +
                    [ 'total_cotizacion_salud' => $cotizacion_salud ];/**/

        PilaSalud::create($datos);
    }

    public function almacenar_datos_pension($planilla, $empleado)
    {
        $registro_anterior = PilaPension::where('planilla_generada_id',$planilla->id)
                                                ->where('nom_contrato_id',$empleado->id)
                                                ->get()
                                                ->first();

        if ( !is_null($registro_anterior) )
        {
            $registro_anterior->delete();
        }

        $porcentaje_pension = 16 / 100;
        if ( $empleado->es_pasante_sena )
        {
            $porcentaje_pension = 0;
        }
        $valor_cotizacion_pension = number_format( $this->ibc_salud * $porcentaje_pension, 0,'','');
        $tarifa_pension = $this->formatear_campo( $porcentaje_pension,'0','derecha',7);
        $cotizacion_pension = $this->formatear_campo( $valor_cotizacion_pension,'0','izquierda',9);

        $datos = [ 'planilla_generada_id' => $planilla->id ] +
                    [ 'nom_contrato_id' => $empleado->id ] +
                    [ 'fecha_final_mes' => $planilla->fecha_final_mes ] +
                    [ 'codigo_entidad_pension' => $this->formatear_campo( $empleado->entidad_pension->codigo_nacional,' ','derecha',6) ] +
                    [ 'dias_cotizados_pension' => $this->formatear_campo( $this->cantidad_dias_laborados,'0','izquierda',2) ] +
                    [ 'ibc_pension' => $this->formatear_campo( number_format( $this->ibc_salud,0,'',''),'0','izquierda',9) ] +
                    [ 'tarifa_pension' => $tarifa_pension ] +
                    [ 'cotizacion_pension' => $cotizacion_pension ] +
                    [ 'afp_voluntario_rais_empleado' => '000000000' ] +
                    [ 'afp_voluntatio_rais_empresa' => '000000000' ] +
                    [ 'subcuenta_solidaridad_fsp' => '000000000' ] +
                    [ 'subcuenta_subsistencia_fsp' => '000000000' ] +
                    [ 'total_cotizacion_pension' => '000000000' ] +
                    [ 'valor_cotizacion_pension' => $cotizacion_pension ];/**/

        PilaPension::create($datos);
    }

    public function almacenar_datos_riesgos_laborales($planilla, $empleado)
    {
        $registro_anterior = PilaRiesgoLaboral::where('planilla_generada_id',$planilla->id)
                                                ->where('nom_contrato_id',$empleado->id)
                                                ->get()
                                                ->first();

        if ( !is_null($registro_anterior) )
        {
            $registro_anterior->delete();
        }


        $porcentaje_riesgo_laboral = 0;
        $clase_de_riesgo = 0;
        if( !is_null($empleado->clase_riesgo_laboral) )
        {
            $porcentaje_riesgo_laboral = $empleado->clase_riesgo_laboral->porcentaje_liquidacion / 100;
            $clase_de_riesgo = $empleado->clase_riesgo_laboral->id;
        }        
        $valor_cotizacion_riesgo_laboral = number_format( $this->ibc_salud * $porcentaje_riesgo_laboral, 0,'','');
        $tarifa_riesgo_laboral = $this->formatear_campo( $porcentaje_riesgo_laboral,'0','derecha',7);
        $cotizacion_riesgo_laboral = $this->formatear_campo( $valor_cotizacion_riesgo_laboral,'0','izquierda',9);

        $datos = [ 'planilla_generada_id' => $planilla->id ] +
                    [ 'nom_contrato_id' => $empleado->id ] +
                    [ 'fecha_final_mes' => $planilla->fecha_final_mes ] +
                    [ 'codigo_arl' => $this->formatear_campo($empleado->entidad_arl->codigo_nacional,' ','derecha',6) ] +
                    [ 'dias_cotizados_riesgos_laborales' => $this->formatear_campo($this->cantidad_dias_laborados,'0','izquierda',2) ] +
                    [ 'ibc_riesgos_laborales' => $this->formatear_campo( number_format( $this->ibc_salud,0,'',''),'0','izquierda',9) ] +
                    [ 'tarifa_riesgos_laborales' => $tarifa_riesgo_laboral ] +
                    [ 'total_cotizacion_riesgos_laborales' => $cotizacion_riesgo_laboral ] +
                    [ 'clase_de_riesgo' => $clase_de_riesgo ];/**/

        PilaRiesgoLaboral::create($datos);
    }

    public function almacenar_datos_parafiscales($planilla, $empleado)
    {
        $registro_anterior = PilaParafiscales::where('planilla_generada_id',$planilla->id)
                                                ->where('nom_contrato_id',$empleado->id)
                                                ->get()
                                                ->first();

        if ( !is_null($registro_anterior) )
        {
            $registro_anterior->delete();
        }

        $cotizante_exonerado_de_aportes_parafiscales = 'N';
        if ( $empleado->es_pasante_sena )
        {
            $cotizante_exonerado_de_aportes_parafiscales = 'S';
        }



        $porcentaje_caja_compensacion = $planilla->datos_empresa->porcentaje_caja_compensacion / 100;
        $valor_cotizacion_ccf = number_format( $this->ibc_parafiscales * $porcentaje_caja_compensacion, 0,'','');
        $tarifa_ccf = $this->formatear_campo( $porcentaje_caja_compensacion,'0','derecha',7);
        $cotizacion_ccf = $this->formatear_campo( $valor_cotizacion_ccf,'0','izquierda',9);
        if ( $empleado->es_pasante_sena )
        {
            $tarifa_ccf = '0.00000';
            $valor_cotizacion_ccf = 0;
            $cotizacion_ccf = '000000000';
        }

        $porcentaje_sena = $planilla->datos_empresa->porcentaje_sena / 100;
        $valor_cotizacion_sena = number_format( $this->ibc_parafiscales * $porcentaje_sena, 0,'','');
        $tarifa_sena = $this->formatear_campo( $porcentaje_sena,'0','derecha',7);
        $cotizacion_sena = $this->formatear_campo( $valor_cotizacion_sena,'0','izquierda',9);
        if ( $empleado->es_pasante_sena || ( $this->ibc_parafiscales < 10 * (float)config('nomina.SMMLV') ) )
        {
            $tarifa_sena = '0.00000';
            $valor_cotizacion_sena = 0;
            $cotizacion_sena = '000000000';
        }

        $porcentaje_icbf = $planilla->datos_empresa->porcentaje_icbf / 100;
        $valor_cotizacion_icbf = number_format( $this->ibc_parafiscales * $porcentaje_icbf, 0,'','');
        $tarifa_icbf = $this->formatear_campo( $porcentaje_icbf,'0','derecha',7);
        $cotizacion_icbf = $this->formatear_campo( $valor_cotizacion_icbf,'0','izquierda',9);
        if ( $empleado->es_pasante_icbf || ( $this->ibc_parafiscales < 10 * (float)config('nomina.SMMLV') ) )
        {
            $tarifa_icbf = '0.00000';
            $valor_cotizacion_icbf = 0;
            $cotizacion_icbf = '000000000';
        }

        $valor_total_cotizacion = number_format( $valor_cotizacion_ccf + $valor_cotizacion_sena + $valor_cotizacion_icbf, 0,'','');
        //dd([(float)config('nomina.SMMLV'),$valor_cotizacion_ccf,$valor_cotizacion_sena,$valor_cotizacion_icbf,$valor_total_cotizacion]);
        $total_cotizacion = $this->formatear_campo( $valor_total_cotizacion,'0','izquierda',9);

        $datos = [ 'planilla_generada_id' => $planilla->id ] +
                    [ 'nom_contrato_id' => $empleado->id ] +
                    [ 'fecha_final_mes' => $planilla->fecha_final_mes ] +
                    [ 'cotizante_exonerado_de_aportes_parafiscales' => $cotizante_exonerado_de_aportes_parafiscales ] +
                    [ 'codigo_entidad_ccf' => $this->formatear_campo($empleado->entidad_caja_compensacion->codigo_nacional,' ','derecha',6) ] +
                    [ 'dias_cotizados' => $this->formatear_campo($this->cantidad_dias_parafiscales,'0','izquierda',2) ] +
                    [ 'ibc_parafiscales' => $this->formatear_campo( number_format( $this->ibc_parafiscales,0,'',''),'0','izquierda',9) ] +
                    [ 'tarifa_ccf' => $tarifa_ccf ] +
                    [ 'cotizacion_ccf' => $cotizacion_ccf ] +
                    [ 'tarifa_sena' => $tarifa_sena ] +
                    [ 'cotizacion_sena' => $cotizacion_sena ] +
                    [ 'tarifa_icbf' => $tarifa_icbf ] +
                    [ 'cotizacion_icbf' => $cotizacion_icbf ] +
                    [ 'total_cotizacion' => $total_cotizacion ];/**/

        PilaParafiscales::create($datos);
    }

}
