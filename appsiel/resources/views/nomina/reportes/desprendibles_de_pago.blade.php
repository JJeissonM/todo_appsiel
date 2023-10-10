@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('estilos_1')
	<style type="text/css">
		#grupo_empleado_id {
			border: 1px solid #c5c5c5;
			border-radius: 3px;
			margin-left: 15px;
			padding: 5px 10px;
			background: #f6f6f6;
		}
	</style>
@endsection

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			
			{{ Form::open(['url'=>'nomina/ajax_reporte_desprendibles_de_pago','id'=>'form_consulta']) }}
				<div class="row">
					<div class="col-sm-2">
						&nbsp;
					</div>
					<div class="col-sm-2">
						{{ Form::label('nom_doc_encabezado_id','Documento de Nómina') }}
						<br/>
						{{ Form::select('nom_doc_encabezado_id',$documentos,null, [ 'class' => 'combobox', 'id' => 'nom_doc_encabezado_id' ]) }}
					</div>
					<div class="col-sm-3">
						{{ Form::label('grupo_empleado_id','Grupo de Empleados') }}
						<br/>
						{{ Form::select('grupo_empleado_id',$grupos_empleados,null, [ 'id' => 'grupo_empleado_id' ]) }}

					</div>
					<div class="col-sm-3">
						{{ Form::label('core_tercero_id','Empleados') }}
						<br/>
						{{ Form::select('core_tercero_id',$empleados,null, [ 'class' => 'combobox', 'id' => 'core_tercero_id' ]) }}
					</div>
					<div class="col-sm-2">
						{{ Form::label(' ','.') }}
						<a href="#" class="btn btn-primary bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i> Generar</a>
					</div>
				</div>				
			{{ Form::close() }}
			<!--	<button id="btn_ir">ir</button>	-->
			
		</div>
	</div>

	<hr>

	<div class="container-fluid">
		<div class="marco_formulario">
			
			{{ Form::bsBtnExcel('desprendibles_de_pago_nomina') }}
			{{ Form::bsBtnPdf('desprendibles_de_pago_nomina') }}

			<div style="display: none;">
				{{ Form::open(['url'=>'nom_enviar_por_email_desprendibles_de_pago','id'=>'form_enviar_email']) }}
					<input type="hidden" name="nom_doc_encabezado_id2" id="nom_doc_encabezado_id2">
					<input type="hidden" name="core_tercero_id2" id="core_tercero_id2">
					<input type="hidden" name="grupo_empleado_id2" id="grupo_empleado_id2">
				{{ Form::close() }}
			</div>

			<a class="btn-gmail" id="btn_email" style="display: none;" title="Enviar por correo"> <i class="fa fa-envelope"></i> </a>
			<br><br>
			<div class="well">
				<p id="mensaje_email" style="color: red; font-weight: bold;"></p>
				<p id="resultados_envio_emails" style="display: none;">
					<label style="color: green; font-weight: bold; padding: 10px;">Enviados: <span id="envio_email_exitoso">0</span></label>
					<label style="color: red; font-weight: bold; padding: 10px;">No enviados: <span id="envio_email_errado">0</span></label>
					<p id="lista_no_enviados" style="display: none;">
						Lista No enviados:
						<span id="nombres_lista_no_enviados"></span>
					</p>
				</p>
			</div>

			{{ Form::Spin(48) }}

			<div id="resultado_consulta">

			</div>	
		</div>
	</div>
	<br/><br/>	

@endsection

@section('scripts')
	<script type="text/javascript">

		var arr_empleados;
		$(document).ready(function(){

			$('#btn_ir').click(function(event){
				$('#form_consulta').submit();
			});


			$('#grupo_empleado_id').change( function(event){
				if ( $(this).val() != '' )
				{
					$('#core_tercero_id').parent().fadeOut(500);
				}else{
					$('#core_tercero_id').parent().fadeIn(500);
				}
			});

			// Click para generar la consulta
			$('#btn_generar').click(function(event){
				event.preventDefault();
				
				if(!valida_campos()){
					alert('Debe seleccionar un docuemnto de nómina.');
					return false;
				}

				$('#resultado_consulta').html( '' );
				$('#div_spin').show();
				$('#div_cargando').show();


				$('#mensaje_email').html('');
				$('#resultados_envio_emails').hide();
				$('#mensaje_email').html('');
				$('#lista_no_enviados').hide();

				$('#envio_email_exitoso').text(0);
				$('#envio_email_errado').text(0);

				$('#btn_email').children('.fa.fa-spinner.fa-spin').attr('class','fa fa-envelope');
				$('#btn_email').removeAttr('disabled');
				$('#btn_excel').hide();
				$('#btn_pdf').hide();
				$('#btn_email').hide();

				// Preparar datos de los controles para enviar formulario
				var form_consulta = $('#form_consulta');
				var url = form_consulta.attr('action');
				var datos = form_consulta.serialize();
				// Enviar formulario de ingreso de productos vía POST
				$.post(url,datos,function(respuesta){
					$('#div_cargando').hide();
					$('#div_spin').hide();
					$('#resultado_consulta').html(respuesta);
					$('#btn_excel').show(500);
					$('#btn_pdf').show(500);
					$('#btn_email').show(500);

					var url_pdf = $('#btn_pdf').attr('href');
					var n = url_pdf.search('a3p0');
					if ( n > 0) {
						var new_url = url_pdf.replace('a3p0','nomina_pdf_reporte_desprendibles_de_pago?'+datos);
					}else{
						n = url_pdf.search('nomina_pdf_reporte_desprendibles_de_pago');
						var url_aux = url_pdf.substr(0,n);
						var new_url = url_aux + 'nomina_pdf_reporte_desprendibles_de_pago?' + datos;
					}

					arr_empleados = $("[name='empleado_id']").map(function() {
														return $(this).val();
													}).get();

					$('#btn_pdf').attr('href', new_url);
				});
			});

			$('#btn_email').click(function(event){

				if ( !confirm('¿Quiere enviar por correo electrónico todos los desprendibles generados?') )
				{
					return false;
				}

				$(this).children('.fa-envelope').attr('class','fa fa-spinner fa-spin');

				$(this).attr('disabled','disabled');
				//$('#mensaje_email').html('');

				restantes = arr_empleados.length;

				$('#mensaje_email').html( '<h1 style="text-align:center;"> <small>Por favor espere</small>  <br> Enviando emails... <span id="contador_facturas" style="color:#9c27b0">' + restantes + '</span> empleados restantes.</h1>' );

				$('#resultados_envio_emails').show(500);
				$('#nombres_lista_no_enviados').html('');

				// fires off the first call 
				getShelfRecursive();

				/*
				$('#nom_doc_encabezado_id2').val( $('#nom_doc_encabezado_id').val() );
				$('#core_tercero_id2').val( $('#core_tercero_id').val() );
				$('#grupo_empleado_id2').val( $('#grupo_empleado_id').val() );

				// Preparar datos de los controles para enviar formulario
				var form_consulta = $('#form_enviar_email');
				var url = form_consulta.attr('action');
				var datos = form_consulta.serialize();
				// Enviar formulario de ingreso de productos vía POST
				$.post(url,datos,function(respuesta){
					$('#btn_email').children('.fa.fa-spinner.fa-spin').attr('class','fa fa-envelope');
					$('#btn_email').removeAttr('disabled');
					$('#mensaje_email').html(respuesta);
				});
				*/

			});

			function getShelfRecursive() { 
			
				// terminate if array exhausted 
				if (arr_empleados.length === 0) 
				{
					$('#btn_email').children('.fa.fa-spinner.fa-spin').attr('class','fa fa-envelope');
					$('#btn_email').removeAttr('disabled');
					return; 
				}

				// pop top value 
				var empleado_id = arr_empleados[0]; 
				arr_empleados.shift(); 
				
				// ajax request

				$.ajax({
					type: "GET",
					url: "{{url('nom_enviar_por_email_un_desprendible_de_pago')}}" + "/" + $('#nom_doc_encabezado_id').val() + "/" + empleado_id,
					async: true,
					success : function(respuesta) {
						// call completed - so start next request 
						restantes--;
						document.getElementById('contador_facturas').innerHTML = restantes;

						var arr_respuesta = respuesta.split("-");
						if ( arr_respuesta[0] == 'true' )
						{
							var envio_email_exitoso = parseFloat($('#envio_email_exitoso').text()) + 1;
							$('#envio_email_exitoso').text(envio_email_exitoso);
						}else{
							var envio_email_errado = parseFloat($('#envio_email_errado').text()) + 1;
							$('#envio_email_errado').text(envio_email_errado);

							$('#lista_no_enviados').show(500);
							var nombres_lista_no_enviados = $('#nombres_lista_no_enviados').text();
							$('#nombres_lista_no_enviados').text( nombres_lista_no_enviados + ', ' + arr_respuesta[1] );
						}
						getShelfRecursive();
					}
				});
			}

			$('#btn_excel,#btn_pdf').click(function(event){
				$('#mensaje_email').html('');
			});

			function valida_campos(){
				var valida = true;
				if( $('#nom_doc_encabezado_id').val() == '' )
				{
					valida = false;
				}
				return valida;
			}
		});

		
	</script>
@endsection