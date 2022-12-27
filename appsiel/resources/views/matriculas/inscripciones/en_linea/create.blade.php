@extends('layouts.principal')

<?php
    use App\Http\Controllers\Sistema\VistaController;
?>
            
@section('content')
    <div
        <?php
            $empresa = App\Core\Empresa::get()->first();
        ?>
        style="width: 100%; padding-left: 70px; padding-right: 70px; margin-left: -20px; padding-top: 10px">
        @include('core.dis_formatos.plantillas.banner_logo_datos_empresa',compact('empresa'))
    </div>

	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Nuevo registro <small>(Los campos marcados con asterisco <i class="fa fa-asterisk"></i> son obligatorios)</small></h4>
		    <hr>

            {{ Form::open(['url'=>$form_create['url'],'id'=>'form_create','files' => true]) }}

                <div class="row">
                    <div class="col-md-12">
                
                        <ul class="nav nav-tabs">
                            <li class="active">
                                <a data-toggle="tab" href="#info_estudiante"><span class="label label-info">1</span>&nbsp;{{ config('matriculas.etiqueta_datos_aspirante') }}</a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#info_padres"><span class="label label-info">2</span>&nbsp;DATOS DE PADRES Y ACUDIENTE</a>
                            </li>
                        </ul>
                        
                        <div class="tab-content">                            
                            <div id="info_estudiante" class="tab-pane fade in active">
                                <br>
                                {{ VistaController::campos_dos_colummnas($form_create['campos']) }}
                            </div>
                            
                            <div id="info_padres" class="tab-pane fade in">
                                <br>
                                @include('matriculas.incluir.paneles_padres')
                                <br>
                            </div>
                        </div>
                    </div>
                </div>

                {{ Form::hidden('url_id', 1) }}

                @if( Input::get('id_modelo') != null )
                    {{ Form::hidden('url_id_modelo', Input::get('id_modelo')) }}
                @else
                    {{ Form::hidden('url_id_modelo', 323) }}
                @endif

                <div style="width: 100%; text-align: center;">
                    <a href="#" class="btn btn-primary btn-lg" id="bs_boton_guardar">Guardar</a>
                </div>

            {{ Form::close() }}

		</div>
	</div>
    
    <div style="background: aliceblue; margin: -25px 0px 15px 0px;">
        {!! generado_por_appsiel() !!}
    </div>
    
@endsection

@section('scripts')

	<script type="text/javascript">

		$(document).ready(function(){

            $('#btnPaula').hide();

            $('#fecha').val( get_fecha_hoy() );
            
			$('input[type=text]').removeAttr('autocomplete');var url;

            var formulario_lleno = false;

            var url_raiz = "{{ url('/') }}";

            var documento_inicial = parseInt( $("#numero_identificacion2").val() );

            $("#id_tipo_documento_id").val('');
            $("#numero_identificacion2").focus();

            $(document).on('blur, keyup','#numero_identificacion2',function(){
                // Creando
                url = '../core/validar_numero_identificacion2/';
                validar_tercero_create( url );
            });

            function validar_tercero_create( url )
            {
                var documento = $("#numero_identificacion2").val();
                $('#tercero_existe').remove();
                $('#bs_boton_guardar').show();

                $.get( url + documento, function( respuesta ) 
                {
                    console.log(respuesta);

                    if ( respuesta == 'tercero_no_existe' ) 
                    {
                        return false;
                    }

                    $('#bs_boton_guardar').hide();
                    if ( respuesta == 'ya_inscrito' ) 
                    {
                        $("#numero_identificacion2").parent().append('<div style="color:red;" id="tercero_existe">Esta persona ya se encuentra inscrita. Haga clic aquí para consultar su ficha de inscripción: <a class="btn btn-xs btn-info" href="'+url_raiz+'/inscripciones_en_linea/identity_number:' + documento + '">Consultar</a></div>');
                        return false;
                    }

                    $("#numero_identificacion2").parent().append('<div style="color:red;" id="tercero_existe">No puede continuar. Ya existe una persona con este número de identificación en nuestras bases de datos. Por favor, contacte a la institución.</div>');
                    return false;
                });
            }

            $('#acudiente_seleccionado').on('change',function(){
                if ($(this).val() == 'padre') {
                    $('#div_acudiente').fadeOut();
                    set_required_campos_papa();
                    
                    unset_required_campos_mama();
                    unset_required_campos_acudiente();
                }
                
                if ($(this).val() == 'madre') {
                    $('#div_acudiente').fadeOut();
                    set_required_campos_mama();

                    unset_required_campos_papa();
                    unset_required_campos_acudiente();
                }
                
                if ($(this).val() == 'otro') {
                    $('#div_acudiente').fadeIn();
                    set_required_campos_acudiente();

                    unset_required_campos_papa();
                    unset_required_campos_mama();
                }

                if ($(this).val() == '') {
                    $('#div_acudiente').fadeOut();
                    unset_required_campos_papa();
                    unset_required_campos_mama();
                    unset_required_campos_acudiente();
                }
            });

            function set_required_campos_papa()
            {
                $('#cedula_papa').attr('required','required');
                $('#cedula_papa').parent().prev().html('<i class="fa fa-asterisk"></i>Cédula:');
                $('#papa').attr('required','required');
                $('#papa').parent().prev().html('<i class="fa fa-asterisk"></i>Nombre Padre:');
                $('#direccion_papa').attr('required','required');
                $('#direccion_papa').parent().prev().html('<i class="fa fa-asterisk"></i>Dirección:');
                $('#telefono_papa').attr('required','required');
                $('#telefono_papa').parent().prev().html('<i class="fa fa-asterisk"></i>Teléfono:');
                $('#email_papa').attr('required','required');
                $('#email_papa').parent().prev().html('<i class="fa fa-asterisk"></i>Email:');
                $('#ocupacion_papa').attr('required','required');
                $('#ocupacion_papa').parent().prev().html('<i class="fa fa-asterisk"></i>Ocupación:');
            }

            function unset_required_campos_papa()
            {
                $('#cedula_papa').removeAttr('required');
                $('#cedula_papa').parent().prev().html('Cédula:');
                $('#papa').removeAttr('required');
                $('#papa').parent().prev().html('Nombre Padre:');
                $('#direccion_papa').removeAttr('required');
                $('#direccion_papa').parent().prev().html('Dirección:');
                $('#telefono_papa').removeAttr('required');
                $('#telefono_papa').parent().prev().html('Teléfono:');
                $('#email_papa').removeAttr('required');
                $('#email_papa').parent().prev().html('Email:');
                $('#ocupacion_papa').removeAttr('required');
                $('#ocupacion_papa').parent().prev().html('Ocupación:');
            }

            function set_required_campos_mama()
            {
                $('#cedula_mama').attr('required','required');
                $('#cedula_mama').parent().prev().html('<i class="fa fa-asterisk"></i>Cédula:');
                $('#mama').attr('required','required');
                $('#mama').parent().prev().html('<i class="fa fa-asterisk"></i>Nombre Madre:');
                $('#direccion_mama').attr('required','required');
                $('#direccion_mama').parent().prev().html('<i class="fa fa-asterisk"></i>Dirección:');
                $('#telefono_mama').attr('required','required');
                $('#telefono_mama').parent().prev().html('<i class="fa fa-asterisk"></i>Teléfono:');
                $('#email_mama').attr('required','required');
                $('#email_mama').parent().prev().html('<i class="fa fa-asterisk"></i>Email:');
                $('#ocupacion_mama').attr('required','required');
                $('#ocupacion_mama').parent().prev().html('<i class="fa fa-asterisk"></i>Ocupación:');
            }

            function unset_required_campos_mama()
            {
                $('#cedula_mama').removeAttr('required');
                $('#cedula_mama').parent().prev().html('Cédula:');
                $('#mama').removeAttr('required');
                $('#mama').parent().prev().html('Nombre Madre:');
                $('#direccion_mama').removeAttr('required');
                $('#direccion_mama').parent().prev().html('Dirección:');
                $('#telefono_mama').removeAttr('required');
                $('#telefono_mama').parent().prev().html('Teléfono:');
                $('#email_mama').removeAttr('required');
                $('#email_mama').parent().prev().html('Email:');
                $('#ocupacion_mama').removeAttr('required');
                $('#ocupacion_mama').parent().prev().html('Ocupación:');
            }

            function set_required_campos_acudiente()
            {
                $('#cedula_acudiente').attr('required','required');
                $('#cedula_acudiente').parent().prev().html('<i class="fa fa-asterisk"></i>Cédula:');
                $('#acudiente').attr('required','required');
                $('#acudiente').parent().prev().html('<i class="fa fa-asterisk"></i>Nombre Acudiente:');
                $('#direccion_acudiente').attr('required','required');
                $('#direccion_acudiente').parent().prev().html('<i class="fa fa-asterisk"></i>Dirección:');
                $('#telefono_acudiente').attr('required','required');
                $('#telefono_acudiente').parent().prev().html('<i class="fa fa-asterisk"></i>Teléfono:');
                $('#email_acudiente').attr('required','required');
                $('#email_acudiente').parent().prev().html('<i class="fa fa-asterisk"></i>Email:');
                $('#ocupacion_acudiente').attr('required','required');
                $('#ocupacion_acudiente').parent().prev().html('<i class="fa fa-asterisk"></i>Ocupación:');
            }

            function unset_required_campos_acudiente()
            {
                $('#cedula_acudiente').removeAttr('required');
                $('#cedula_acudiente').parent().prev().html('Cédula:');
                $('#acudiente').removeAttr('required');
                $('#acudiente').parent().prev().html('Nombre Acudiente:');
                $('#direccion_acudiente').removeAttr('required');
                $('#direccion_acudiente').parent().prev().html('Dirección:');
                $('#telefono_acudiente').removeAttr('required');
                $('#telefono_acudiente').parent().prev().html('Teléfono:');
                $('#email_acudiente').removeAttr('required');
                $('#email_acudiente').parent().prev().html('Email:');
                $('#ocupacion_acudiente').removeAttr('required');
                $('#ocupacion_acudiente').parent().prev().html('Ocupación:');
            }

			$('#bs_boton_guardar').on('click',function(event){
				event.preventDefault();

				if ( !validar_requeridos() )
				{
					return false;
				}

				// Desactivar el click del botón
				$( this ).off( event );

				$('#form_create').submit();
			});
		});
	</script>
	@if( isset($archivo_js) )
		<script src="{{ asset( $archivo_js ) }}"></script>
	@endif

	@yield('script_adicional')
@endsection