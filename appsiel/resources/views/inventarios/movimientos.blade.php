@extends('layouts.reportes')

@section('sidebar')
	
	{{ Form::open(['url'=>'ajax_movimiento','id'=>'form_consulta']) }}
		{{ Form::label('fecha_inicial','Fecha inicial') }}
		{{ Form::date('fecha_inicial',date('Y-m-d'),['class'=>'form-control','id'=>'fecha_inicial']) }}
		
		{{ Form::label('fecha_final','Fecha final') }}
		{{ Form::date('fecha_final',date('Y-m-d'),['class'=>'form-control','id'=>'fecha_final']) }}
		
		{{ Form::label('mov_bodega_id','Bodega') }}
		{{ Form::select('mov_bodega_id',$bodegas,null,['class'=>'form-control','id'=>'mov_bodega_id']) }}
		
		{{ Form::label('mov_producto_id','Producto') }}
		{{ Form::select('mov_producto_id',$productos,null,['class'=>'combobox','id'=>'mov_producto_id']) }}
		
		{{ Form::label(' ','.') }}
		<a href="#" class="btn btn-primary bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i> Generar</a>

	{{ Form::close() }}
	<!-- <button id="btn_ir">ir</button> -->

@endsection


@section('contenido')
		<div class="col-md-12 marco_formulario">
			<br/>

            {{ Form::Spin(48) }}

            {{ Form::bsBtnExcel('existencias_inventario') }}
			{{ Form::bsBtnPdf('existencias_inventario') }}
			<div id="resultado_consulta">

			</div>	
		</div>
@endsection

@section('scripts_reporte')
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

			$('#mov_bodega_id').change(function(){
				if ($('#mov_bodega_id').val()!='') {
					$('#mov_producto_id').focus();
				}
			});

			$('#mov_producto_id').change(function(){
				if ($('#mov_producto_id').val()!='') {
					$('#btn_generar').focus();
				}
			});

			$('#btn_ir').click(function(event){
				$('#form_consulta').submit();
			});

			// Click para generar la consulta
			$('#btn_generar').click(function(event){
				if(!valida_campos()){
					alert('Debe diligencias todos los campos.');
					return false;
				}

				$('#resultado_consulta').html('');
				$('#div_spin').show();

				// Preparar datos de los controles para enviar formulario
				var form_consulta = $('#form_consulta');
				var url = form_consulta.attr('action');
				var datos = form_consulta.serialize();
				// Enviar formulario de ingreso de productos vía POST
				$.post(url,datos,function(respuesta){
					$('#div_spin').hide();
					$('#btn_excel').show();
					$('#resultado_consulta').html(respuesta);
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