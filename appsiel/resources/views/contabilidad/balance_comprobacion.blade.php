@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-10 col-md-offset-1">

			
			{{ Form::open(['url'=>'contab_ajax_balance_comprobacion','id'=>'form_consulta']) }}
				<div class="row">
					<div class="col-sm-2">
						{{ Form::label('fecha_desde','Fecha desde') }}
						{{ Form::date('fecha_desde',date('Y-m-d'),['class'=>'form-control','id'=>'fecha_desde']) }}
					</div>
					<div class="col-sm-2">
						{{ Form::label('fecha_hasta','Fecha hasta') }}
						{{ Form::date('fecha_hasta',date('Y-m-d'),['class'=>'form-control','id'=>'fecha_hasta']) }}
					</div>
					<div class="col-sm-3">
						<!-- 
						{{ Form::label('contab_grupo_cuenta_id','Grupo') }}
						{{ Form::select('contab_grupo_cuenta_id',$grupos,null,[ 'class' => 'form-control','id'=>'contab_grupo_cuenta_id']) }}
					-->
						{{ Form::label('detalla_terceros','Detalla terceros') }}
						{{ Form::select('detalla_terceros',['No'=>'No', 'Si'=>'Si'],null,[ 'class' => 'form-control','id'=>'detalla_terceros']) }}
					</div>
					<div class="col-sm-3">
						<!--
						{{ Form::label('contab_cuenta_id','Cuenta') }}
						{{ Form::select('contab_cuenta_id',$cuentas,null,['class'=>'form-control','id'=>'contab_cuenta_id']) }}
						-->
					</div>
					<div class="col-sm-2">
						{{ Form::label(' ','.') }}
						<a href="#" class="btn btn-primary bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i> Generar</a>
					</div>
				</div>
				
				<!--
				<div class="row">
					<div class="col-sm-12">
						&nbsp;
					</div>
				</div>

				
				<div class="row">
					<div class="col-sm-2">
						{{ Form::checkbox('detallar_grupo_cuentas', '1')}}
						{{ Form::label(' ',' Detallar grupo cuentas') }}
					</div>
					<div class="col-sm-2">
						{{ Form::checkbox('detallar_terceros', '1')}}
						{{ Form::label(' ',' Detallar terceros') }}
					</div>
					<div class="col-sm-2">
						{{ Form::checkbox('detallar_documentos', '1')}}
						{{ Form::label(' ',' Detallar documentos') }}
					</div>
				</div>
			-->
			{{ Form::close() }}
					<!--	<button id="btn_ir">ir</button>	-->
			
		</div>
	</div>

	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			
			{{ Form::bsBtnExcel('balance_de_comrpobacion') }}
			
			<div id="resultado_consulta">

			</div>	
		</div>
	</div>
	<br/><br/>	

@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			
			$('#fecha_inicial').focus();

			$('#fecha_inicial').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#fecha_final').focus();				
				}		
			});

			$('#fecha_final').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#mov_bodega_id').focus();				
				}		
			});

			$('#contab_grupo_cuenta_id').change(function(){
				if ($('#contab_grupo_cuenta_id').val()!='') {
					$('#contab_cuenta_id').focus();
				}
			});

			$('#contab_cuenta_id').change(function(){
				if ($('#contab_cuenta_id').val()!='') {
					$('#btn_generar').focus();
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
				if($('#fecha_inicial').val()=='' || $('#fecha_final').val()=='' || $('#mov_bodega_id').val()=='' || $('#mov_producto_id').val()==''){
					valida = false;
				}
				return valida;
			}
		});

		
	</script>
@endsection