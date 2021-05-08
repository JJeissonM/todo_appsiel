@extends('layouts.pdf')

@section('estilos_1')
<style type="text/css">

    table {
        border: solid 1px;
        border-collapse: separate !important;
        border-spacing: 0;
        /*margin: 30px;*/
        width: 100%;
        font-size: 16px;
        line-height: 1.5;
        /*height: 48%;*/
    }

    table td{
        border: solid 1px;
    }
</style>
@endsection 

@section('content')
    <?php

        $matricula = DB::table('sga_matriculas')
                    ->where('id_estudiante','=',$cartera->id_estudiante)
                    ->where('estado','=','Activo')
                    ->get();
    
        $nom_curso=DB::table('sga_cursos')->where('id',$matricula[0]->curso_id)->value('descripcion');

        $estudiante = App\Matriculas\Estudiante::get_datos_basicos($cartera->id_estudiante);

        $empresa = App\Core\Empresa::find($colegio->empresa_id);

        foreach ($recaudos as $recaudo) {

            $valor_recaudo = number_format($recaudo->valor_recaudo, 0, ',', '.');
            $numero_en_letras= NumerosEnLetras::convertir($recaudo->valor_recaudo,'pesos',false);
    ?>

            <table>
                <tr align="center">
                    <td>
                        {{ $colegio->descripcion }} <br/>
                        {{ config("configuracion.tipo_identificador") }} @if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $empresa->numero_identificacion, 0, ',', '.') }}	@else {{ $empresa->numero_identificacion}} <br/>
                        {{ $colegio->direccion }} Tel. {{ $colegio->telefonos }} {{ $colegio->ciudad }}
                    </td>
                    <td>
                        RECIBO DE CAJA <br/>
                        No. {{ $recaudo->id }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        @php $fecha=explode("-",$recaudo->fecha_recaudo) @endphp
                        <b>Fecha: </b> &nbsp; {{ $fecha[2] }} de {{ nombre_mes($fecha[1]) }} de {{ $fecha[0] }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>Estudiante:</b> {{ $estudiante->nombre_completo }}
                    </td>
                    <td>
                        <b>Matrícula: </b> {{ $matricula[0]->codigo }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <b>Dirección: </b> {{ $estudiante->direccion1 }}
                    </td>
                </tr>
                <tr>
                    <td>
                        La suma de ${{$valor_recaudo}} ({{$numero_en_letras}})
                    </td>
                    <td rowspan="3" style="vertical-align: top;">
                        Firma de quien recibe:
                    </td>
                </tr>
                <tr>
                    @php $fecha=explode("-",$cartera->fecha_vencimiento) @endphp
                    <td>
                        <b>Concepto: </b> Pago de {{$recaudo->concepto}}
                    </td>
                </tr>
                <tr>
                    <td>
                        @php $tipo_recaudo = App\Tesoreria\TesoMedioRecaudo::find($recaudo->teso_medio_recaudo_id) @endphp
                        <b>Tipo recaudo: </b> {{ $tipo_recaudo->descripcion }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <br/><br/>
                        ___________________ <br/>
                        Elaboró
                    </td>
                    <td>
                        <br/><br/>
                        ___________________ <br/>
                        Revisó
                    </td>
                </tr>
            </table>
            @if(count($recaudos)>1)
                <div class="page-break"></div>
            @endif
        <?php
        } // end foreach
        ?>

<?php
    function nombre_mes($num_mes){
        switch($num_mes){
            case '01':
                $mes="Enero";
                break;
            case '02':
                $mes="Febrero";
                break;
            case '03':
                $mes="Marzo";
                break;
            case '04':
                $mes="Abril";
                break;
            case '05':
                $mes="Mayo";
                break;
            case '06':
                $mes="Junio";
                break;
            case '07':
                $mes="Julio";
                break;
            case '08':
                $mes="Agosto";
                break;
            case '09':
                $mes="Septiembre";
                break;
            case '10':
                $mes="Octubre";
                break;
            case '11':
                $mes="Noviembre";
                break;
            case '12':
                $mes="Diciembre";
                break;
            default:
                $mes="----------";
                break;
        }
        return $mes;
    }
?>