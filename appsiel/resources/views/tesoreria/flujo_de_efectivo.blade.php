@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			
			{{ Form::open(['url'=>'tesoreria/ajax_flujo_de_efectivo','id'=>'form_consulta']) }}
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
						<!-- -->{{ Form::label('tipo_movimiento','Tipo de movimiento') }}
						<br/>
						{{ Form::select('tipo_movimiento',[ '' => 'Todos','entrada' => 'Entrada', 'salida' => 'Salida'],'',[ 'class' => 'form-control' , 'id' => 'tipo_movimiento']) }}
					
					</div>
					<div class="col-sm-3">
						<!-- -->{{ Form::label('core_tercero_id','Tercero') }}
						<br/>
						{{ Form::select('core_tercero_id',$terceros,null,['class'=>'combobox','id'=>'core_terecero_id']) }}
					
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

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			
			{{ Form::bsBtnExcel('flujo_de_efectivo') }}

			<div id="resultado_consulta">

			</div>	
		</div>
	</div>
	<br/><br/>	

@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			
			$('#fecha_desde').focus();

			$('#fecha_desde').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#fecha_hasta').focus();				
				}		
			});

			$('#fecha_hasta').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#tipo_movimiento').focus();				
				}		
			});

			$('#btn_ir').click(function(event){
				$('#form_consulta').submit();
			});

			// Click para generar la consulta
			$('#btn_generar').click(function(event){
				if(!valida_campos()){
					alert('Debe ingresar las fechas.');
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
				if( $('#fecha_desde').val() == '' || $('#fecha_hasta').val() == '' ){
					valida = false;
				}
				return valida;
			}
		});

		
	</script>
@endsection