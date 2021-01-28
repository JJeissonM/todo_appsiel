@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			
			{{ Form::open(['url'=>'nom_ajax_listado_vacaciones_pendientes','id'=>'form_consulta']) }}
				<div class="row">
					<div class="col-sm-2">
						&nbsp;
					</div>
					<div class="col-sm-3">
						{{ Form::label('fecha_corte','Fecha de corte') }}
						<br/>
						{{ Form::date('fecha_corte',date('Y-m-d'),[ 'class' => 'form-control', 'id' => 'fecha_corte' ]) }}
					</div>
					<div class="col-sm-3">
						{{ Form::label('nom_contrato_id','Empleados') }}
						<br/>
						{{ Form::select('nom_contrato_id',$empleados,null, [ 'class' => 'combobox', 'id' => 'nom_contrato_id' ]) }}
					</div>
					<div class="col-sm-2">
						{{ Form::label('calcular_valor_con_base_en','Calcular valor con base en') }}
						<br/>
						{{ Form::select('calcular_valor_con_base_en',['salario_actual_empleado'=>'Salario actual del empleado','saldo_consolidado_fecha_corte' => 'Saldo consolidado a la fecha de corte'],null, [ 'class' => 'form-control', 'id' => 'calcular_valor_con_base_en' ]) }}
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
			
			{{ Form::bsBtnExcel('listado_vacaciones_pendientes') }}
			{{ Form::bsBtnPdf('listado_vacaciones_pendientes') }}

			{{ Form::Spin(48) }}

			<div id="resultado_consulta">

			</div>	
		</div>
	</div>
	<br/><br/>	

@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			// Click para generar la consulta
			$('#btn_generar').click(function(event){
				event.preventDefault();

				$('#resultado_consulta').html( '' );
				$('#div_spin').show();
				$('#div_cargando').show();


				$('#btn_excel').hide();
				$('#btn_pdf').hide();

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

					var url_pdf = $('#btn_pdf').attr('href');
					var n = url_pdf.search('a3p0');
					if ( n > 0) {
						var new_url = url_pdf.replace('a3p0','nomina_pdf_listado_vacaciones_pendientes?'+datos);
					}else{
						n = url_pdf.search('nomina_pdf_listado_vacaciones_pendientes');
						var url_aux = url_pdf.substr(0,n);
						var new_url = url_aux + 'nomina_pdf_listado_vacaciones_pendientes?' + datos;
					}
					
					$('#btn_pdf').attr('href', new_url);
				});
			});

		});

		
	</script>
@endsection