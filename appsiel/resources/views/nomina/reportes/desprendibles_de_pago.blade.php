@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

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
						&nbsp;
					</div>
					<div class="col-sm-3">
						{{ Form::label('nom_doc_encabezado_id','Documento de Nómina') }}
						<br/>
						{{ Form::select('nom_doc_encabezado_id',$documentos,null, [ 'class' => 'combobox', 'id' => 'nom_doc_encabezado_id' ]) }}
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
			
			{{ Form::bsBtnExcel('reporte_cartera_por_curso') }}
			{{ Form::bsBtnPdf('reporte_cartera_por_curso') }}

			<div id="resultado_consulta">

			</div>	
		</div>
	</div>
	<br/><br/>	

@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			$('#btn_ir').click(function(event){
				$('#form_consulta').submit();
			});

			// Click para generar la consulta
			$('#btn_generar').click(function(event){
				event.preventDefault();
				
				if(!valida_campos()){
					alert('Debe seleccionar un docuemnto de nómina.');
					return false;
				}

				$('#resultado_consulta').html( '' );
				$('#div_cargando').show();

				// Preparar datos de los controles para enviar formulario
				var form_consulta = $('#form_consulta');
				var url = form_consulta.attr('action');
				var datos = form_consulta.serialize();
				// Enviar formulario de ingreso de productos vía POST
				$.post(url,datos,function(respuesta){
					$('#div_cargando').hide();
					$('#resultado_consulta').html(respuesta);
					$('#btn_excel').show(500);
					$('#btn_pdf').show(500);

					var url_pdf = $('#btn_pdf').attr('href');
					var n = url_pdf.search('a3p0');
					if ( n > 0) {
						var new_url = url_pdf.replace('a3p0','nomina_pdf_reporte_desprendibles_de_pago?'+datos);
					}else{
						n = url_pdf.search('nomina_pdf_reporte_desprendibles_de_pago');
						var url_aux = url_pdf.substr(0,n);
						var new_url = url_aux + 'nomina_pdf_reporte_desprendibles_de_pago?' + datos;
					}
					
					
					$('#btn_pdf').attr('href', new_url);
				});
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