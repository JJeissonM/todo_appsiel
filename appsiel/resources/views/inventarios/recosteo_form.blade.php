@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	@include('layouts.mensajes')

	<div class="row">
		<div class="col-md-10 col-md-offset-1">			
			<div class="row">
				<div class="col-sm-3">
					{{ Form::label('fecha_desde','*Fecha desde') }}
					<br/>
					{{ Form::date('fecha_desde', null,[ 'class' => 'form-control', 'id' => 'fecha_desde' ]) }}
				</div>
				<div class="col-sm-3">
					{{ Form::label('fecha_hasta','*Fecha hasta') }}
					<br/>
					{{ Form::date('fecha_hasta', null,[ 'class' => 'form-control', 'id' => 'fecha_hasta' ]) }}
				</div>
				<div class="col-sm-4">
					{{ Form::label('inv_producto_id','Producto') }}
					<br/>
					{{ Form::select('inv_producto_id',$productos,null, [ 'class' => 'combobox', 'id' => 'inv_producto_id' ]) }}
				</div>
				<div class="col-sm-2">
					&nbsp;
				</div>
			</div>
			<br>		
			<div class="row">
				<div class="col-sm-3">
					{{ Form::label('recontabilizar_contabilizar_movimientos','*Recontabilizar movimientos') }}
					<br/>
					{{ Form::select('recontabilizar_contabilizar_movimientos',['1'=>'Si (recomendado)','0'=>'No'],null, [ 'class' => 'form-control', 'id' => 'recontabilizar_contabilizar_movimientos' ]) }}
				</div>
				<div class="col-sm-4">
					{{ Form::label('modo_recosteo','*Modo de recosteo') }}
					<br/>
					{{ Form::select('modo_recosteo',['desde_costo_promedio'=>'Tomar del Costo promedio actual','recalcular_costo_promedio'=>'Recalcular Costo promedio'],null, [ 'class' => 'form-control', 'id' => 'modo_recosteo' ]) }}
				</div>
				<div class="col-sm-3">
					{{ Form::label('tener_en_cuenta_movimientos_anteriores','*Tener en cuenta movimientos anteriores') }}
					<br/>
					{{ Form::select('tener_en_cuenta_movimientos_anteriores',['1'=>'Si (recomendado)','0'=>'No'],null, [ 'class' => 'form-control', 'id' => 'tener_en_cuenta_movimientos_anteriores' ]) }}
				</div>
				<div class="col-sm-2">
					{{ Form::label(' ','.') }}
					<a href="#" class="btn btn-primary bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i>  Continuar </a>
				</div>
			</div>			
		</div>
	</div>

	<hr>

	<div class="container-fluid">
		<div class="marco_formulario">
			<div id="resultado_consulta" style="display: none;">
				<br><br>
				<div class="well">
					<h3 style="text-align: center; width: 100%;">RECOSTEO</h3>
					Este proceso recorre todos los documentos de inventarios entre las fechas dadas y actualiza el costo unitario y costo total en las líneas de registros de cada documentos con base en el COSTO PROMEDIO ACTUAL de cada producto en la bodega de la transacción.
					<br>
					Además, se actualiza el movimiento de inventarios con los nuevos costos y los registros contables del movimiento de inventario.
					
					<br><br>
        			NOTA #1: Esto proceso genera diferencias entre las facturas de compras y los documentos de inventarios implicados en las compras.

					<br><br>
        			NOTA #2: Esto proceso NO hace recosteo sobre los documentos de fabricación o ensables.
        			
        			<br><br>
        			<div class="alert alert-warning">
					  <strong>Sea cuidadoso al ejecutar este proceso!</strong> Si no está seguro consulte con el administrador del sistema.
					</div>

					<p class="bg-info">Para ejecutar el recosteo en las fechas indicadas, haga click en el siguiente botón:</p>
					<div style="text-align: center;"> <a type="button" class="btn btn-success btn-lg" href="{{ url( '/a3p0' ) }}" id="btn_recostear">Recostear</a> </div>
					{{ Form::Spin('32') }}
				</div>
			</div>
		</div>
	</div>
	<br/><br/>	

@endsection

@section('scripts')
	<script type="text/javascript">

		
		$(document).ready(function(){

			$('#fecha_desde').focus();

			$('#fecha_desde').change(function(){
				$('#resultado_consulta').hide();
			});

			$('#fecha_hasta').change(function(){
				$('#resultado_consulta').hide();
			});


			$('#btn_generar').click(function(event){
				event.preventDefault();
				if( !valida_campos() )
				{
					alert('Debe ingresar las fechas desde y hasta.');
					return false;
				}

				var url_recostear = $('#btn_recostear').attr('href');
				var n = url_recostear.search('a3p0');
				if ( n > 0) {
					var new_url = url_recostear.replace( 'a3p0', 'inv_recosteo?id='+getParameterByName('id')+'&fecha_desde=' + $('#fecha_desde').val() + '&fecha_hasta=' + $('#fecha_hasta').val() + '&inv_producto_id=' + $('#inv_producto_id').val() + '&modo_recosteo=' + $('#modo_recosteo').val() + '&tener_en_cuenta_movimientos_anteriores=' + $('#tener_en_cuenta_movimientos_anteriores').val() );
				}else{
					n = url_recostear.search('inv_recosteo');
					var url_aux = url_recostear.substr(0,n);
					var new_url = url_aux + 'inv_recosteo?id='+getParameterByName('id')+'&fecha_desde=' + $('#fecha_desde').val() + '&fecha_hasta=' + $('#fecha_hasta').val() + '&inv_producto_id=' + $('#inv_producto_id').val() + '&modo_recosteo=' + $('#modo_recosteo').val() + '&tener_en_cuenta_movimientos_anteriores=' + $('#tener_en_cuenta_movimientos_anteriores').val();
				}
				
				
				$('#btn_recostear').attr('href', new_url);


				$('#resultado_consulta').show();

			});

			$('#btn_recostear').click(function(event){
				$(this).hide();
				$('#div_spin').show();
			});

			function valida_campos(){
				var valida = true;
				if( $('#fecha_desde').val() == '' || $('#fecha_hasta').val() == '' )
				{
					valida = false;
				}
				return valida;
			}
		});

		
	</script>
@endsection