<?php
    use App\Http\Controllers\Sistema\VistaController;
?>

@extends('layouts.principal')

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')
<div class="container-fluid">
    <div class="marco_formulario">

        @include('matriculas.incluir.matriculas_anteriores')

        <br /><br />
        <form action="{{ url('matriculas') }}" method="POST" class="form-horizontal" id="form_create">
            {{ csrf_field() }}

            <input type="hidden" name="id_colegio" id="id_colegio" value="{{ $id_colegio }}">
            <input type="hidden" name="email" id="email" value="{{ $inscripcion->tercero->email }}">
            <input type="hidden" name="core_tercero_id" id="core_tercero_id" value="{{ $inscripcion->tercero->id }}">
            <input type="hidden" name="fecha_nacimiento" id="fecha_nacimiento" value="{{ $inscripcion->fecha_nacimiento }}">
            <input type="hidden" name="estudiante_existe" id="estudiante_existe" value="{{ $estudiante_existe }}">

            @include('matriculas.incluir.datos_inscripcion', [ 'tercero' => $inscripcion->tercero ])

            <div class="panel panel-primary">
                <div class="panel-heading">DATOS DE LA MATRÍCULA</div>
                <div class="panel-body">
                    {{ VistaController::campos_dos_colummnas($form_create['campos']) }}
                </div>

            </div>

            @include('matriculas.incluir.formularios.requisitos_matricula')

            @include('matriculas.incluir.formularios.controles_medicos', [ 'estudiante' => $inscripcion->tercero ])

            @if((int)config('matriculas.incluir_formulario_para_crear_libreta_pagos') == 1)
                @include('matriculas.incluir.formularios.libreta_pagos')                
            @else
                
            @endif

            <div align="center">

                {{ Form::hidden( 'lineas_registros', '', ['id'=>'lineas_registros'] ) }}
                {{ Form::hidden( 'responsable_agregado', '', ['id'=>'responsable_agregado'] ) }}

                {{ Form::hidden( 'url_id', Input::get( 'id' ) ) }}
                {{ Form::hidden( 'url_id_modelo', Input::get( 'id_modelo' ) ) }}

                {{ Form::bsButtonsForm($miga_pan[count($miga_pan)-2]['url'])}}
            </div>
        </form>

    </div>
</div>

@endsection

@section('scripts')

    <script type="text/javascript">
        
        var html;
        var hay_responsable = 0;

        function ejecutar_acciones_con_item_sugerencia( item_sugerencia, obj_text_input )
        {

            $('#tiporesponsable_idp').focus();

            // generar_html_fila
            html = "<tr>";

            html = html + '<td style="display: none;">' + item_sugerencia.attr( 'data-registro_id' ) + '</td>';
            html = html + '<td style="display: none;"> _id_tipo_responsable_ </td>';

            html = html + "<td>" + item_sugerencia.attr( 'data-numero_identificacion' ) + " - " + item_sugerencia.attr( 'data-descripcion' ) + "</td>";
            html = html + "<td>" + item_sugerencia.attr( 'data-direccion1' ) + "</td>";
            html = html + "<td>" + item_sugerencia.attr( 'data-telefono1' ) + "</td>";
            html = html + "<td>" + item_sugerencia.attr( 'data-email' ) + "</td>";
            html = html + '<td> _texto_responsable_ </td>';
            html = html + "<td><a class='btn btn-xs btn-danger delete'><i class='fa fa-trash-o'></i></a></td>";
            html = html + "</tr>";

        }

        $.fn.addRow = function() {
            
            if( $( "#tercero_responsable_id" ).val() == '' || $( "#tiporesponsable_idp" ).val() == '' )
            {
                alert('Debe selecionar un tercero y el tipo de responsable.');

                return false;
            }

            var html2 = html.replace('_id_tipo_responsable_', $( "#tiporesponsable_idp" ).val() );
            var html3 = html2.replace('_texto_responsable_', $( "#tiporesponsable_idp option:selected" ).text() );
            $('#ingreso_lineas_registros tbody:last').append( html3 );
            
            $( "#tercero_responsable_id" ).val('');

            if ( $( "#tiporesponsable_idp" ).val() == 3 )
            {
                $( "#responsable_agregado" ).val(1);
            }

            reset_tipo_responsable( $( "#tiporesponsable_idp" ).val(), $( "#tiporesponsable_idp option:selected" ).text(), 'quitar' );

            hay_responsable++;
        }

        function reset_tipo_responsable( tipo_id, texto_opcion, accion )
        {
            switch( accion )
            {
                case 'quitar':
                    $("#tiporesponsable_idp option[value='"+tipo_id+"']").remove();

                    break;
                case 'agregar':
                    $('#tiporesponsable_idp').append( $('<option>', { value: tipo_id, text: texto_opcion} ) );

                    if ( tipo_id == 3 )
                    {
                        $( "#responsable_agregado" ).val(0);
                    }
                    break;
                default:
                    break;
            }

            console.log( $( "#responsable_agregado" ).val() );
        }

        $(document).on('click', '.delete', function(event) {
            event.preventDefault();
            var fila = $(this).closest('tr');

            reset_tipo_responsable( fila.find('td').eq(1).text(), fila.find('td').eq(6).text(), 'agregar' );

            fila.remove();

            hay_responsable--;
        });



        $(document).ready(function() {

            $('#btn_excel').show();

            if( "{{ $estudiante_existe }}" == 1 )
            {
                $( "#responsable_agregado" ).val( 1 );
            }

            $('#sga_grado_id').focus();

            if (typeof $('#btn_imprimir') !== 'undefined') {
                $('#btn_imprimir').focus();
            }

            $('#btn_agregar_linea').on('click', function(event) {
                event.preventDefault();

                $.fn.addRow();

            });

            $('#sga_grado_id').change(function() {
                var grado = $('#sga_grado_id').val().split('-');

                var codigo = $('#codigo').val();

                $('#codigo').val(codigo.replace(codigo.substr(codigo.search("-")), '-' + grado[1]));
            });

            
            $('#bs_boton_guardar').on('click', function(event) {
                event.preventDefault();

                if ( $( "#responsable_agregado" ).val() == 0 )
                {
                    alert('Debe ingresar al responsable del estudiante.');
                    $('#tercero_responsable_id').focus();
                    return false;
                }

                if (!validar_requeridos()) {
                    return false;
                }

                // Desactivar el click del botón
                $(this).off(event);

                var table = $('#ingreso_lineas_registros').tableToJSON();
                $('#lineas_registros').val(JSON.stringify(table));

                $('#form_create').submit();
            });


        });
    </script>
@endsection