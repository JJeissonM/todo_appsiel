@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-12">

			{{ Form::open(['url'=>'contab_ajax_generacion_eeff','id'=>'form_consulta']) }}
				<div class="row">
					<div class="col-sm-3">
						{{ Form::label('reporte_id','Reporte') }}
						{{ Form::select('reporte_id',$reportes,null,[ 'class' => 'form-control','id'=>'reporte_id']) }}
					</div>
					<div class="col-sm-6">
						<div class="row">
							<div class="col-sm-2">
								&nbsp;
							</div>
							<div class="col-sm-2">
								{{ Form::label('etiqueta1','ETIQUETA',[]) }}
							</div>
							<div class="col-sm-4">
								{{ Form::label('etiqueta1','DESDE',[]) }}
							</div>
							<div class="col-sm-4">
								{{ Form::label('etiqueta1','HASTA',[]) }}
							</div>
						</div>
						<div class="row">
							<div class="col-sm-2">
								{{ Form::label('etiqueta_1','Lapso 1:',[]) }}
							</div>
							<div class="col-sm-2">
								{{ Form::text('lapso1_lbl',date('Y'),['class'=>'form-control','id'=>'lapso1_lbl']) }}
							</div>
							<div class="col-sm-4">
								{{ Form::date('lapso1_ini',date('Y-01-01'),['class'=>'form-control','id'=>'lapso1_ini']) }}
							</div>
							<div class="col-sm-4">
								{{ Form::date('lapso1_fin',date('Y-12-31'),['class'=>'form-control','id'=>'lapso1_fin']) }}
							</div>
						</div>
						<div class="row">
							<div class="col-sm-2">
								{{ Form::label('etiqueta_2','Lapso 2:',[]) }}
							</div>
							<div class="col-sm-2">
								{{ Form::text('lapso2_lbl','',['class'=>'form-control','id'=>'lapso2_lbl']) }}
							</div>
							<div class="col-sm-4">
								{{ Form::date('lapso2_ini','',['class'=>'form-control','id'=>'lapso2_ini']) }}
							</div>
							<div class="col-sm-4">
								{{ Form::date('lapso2_fin','',['class'=>'form-control','id'=>'lapso2_fin']) }}
							</div>
						</div>
					</div>
					<div class="col-sm-3">
						{{ Form::label(' ','.') }}
						<a href="#" class="btn btn-primary bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i> Generar</a>
					</div>
				</div>
			{{ Form::close() }}
				<!--	<button id="btn_ir">ir</button>		-->
			
		</div>
	</div>

	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			
			{{ Form::bsBtnExcel('generacion_eeff') }}
			
			<div id="resultado_consulta">

			</div>	
		</div>
	</div>
	<br/><br/>	

@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			
			$('#reporte_id').focus();

			$('#reporte_id').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#fecha_inicial').focus();				
				}		
			});

			$('#fecha_inicial').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#fecha_final').focus();				
				}		
			});

			$('#btn_ir').click(function(event){
				$('#form_consulta').submit();
			});

			// Click para generar la consulta
			$('#btn_generar').click(function(event){
				if(!valida_campos()){
					alert('Debe diligenciar todos los campos.');
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
				});
			});

			function valida_campos(){
				var valida = true;
				if($('#lapso1_ini').val()=='' || $('#lapso1_fin').val()=='' || $('#reporte_id').val()==''|| $('#lapso1_lbl').val()=='')
				{
					valida = false;
				}

				if($('#lapso2_lbl').val() != ''){
					if($('#lapso2_ini').val()=='' || $('#lapso2_fin').val()=='' || $('#lapso2_lbl').val()=='' || $('#reporte_id').val()==''){
						valida = false;
					}
				}

				return valida;
			}
		});

		
	</script>
@endsection