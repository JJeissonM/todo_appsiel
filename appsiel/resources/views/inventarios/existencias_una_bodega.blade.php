@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	&nbsp;&nbsp;&nbsp;{{ Form::bsBtnPrint('#') }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			<br/>
			<div class="row">
				<div class="col-md-6">
					<h2 align="center">Existencia bodega {{ $bodega->descripcion }}</h2>
				</div>
				<div class="col-md-6">
					<div style="vertical-align: center;">
						<br/>
						<input type="hidden" name="bodega_id" value="{{ $bodega->id }}" id="bodega_id">
						{{ Form::bsFecha('fecha_corte', $fecha_corte, 'Fecha corte', null, ['id'=>'fecha_corte']) }}
					</div>
				</div>
			</div>

			{{ Form::bsBtnExcel('Existencias de inventarios') }}
			<div class="table-responsive">
				<table class="table table-bordered" id="myTable">
				    {{ Form::bsTableHeader(['Cód.','Producto','Cantidad','Costo Prom.','Costo Total']) }}
				    <tbody>
				        <?php 
							$total_cantidad=0;
							$total_costo_total=0;
						?>

						@foreach ( $movimientos as $movimiento)
							<?php 
								$item = $movimiento->producto;
								
								$costo_unitario = 0;
								
								if( $movimiento->Cantidad != 0)
								{
									$costo_unitario = $movimiento->Costo / $movimiento->Cantidad;
								}else{
									$movimiento->Costo = 0;	
								}

								if ( (int)config('inventarios.maneja_costo_promedio_por_bodegas') == 0)
								{
									$movimiento->Costo = $item->get_costo_promedio( 0 ) * $movimiento->Cantidad;
									if( $movimiento->Cantidad > 0 && $fecha_corte != date('Y-m-d'))
									{
										$costo_unitario = $movimiento->Costo / $movimiento->Cantidad;
									}
								}
							?>
							<tr>
								<td class="text-center">{{$item->id}}</td>
								<td>{{ $item->get_value_to_show( true ) }}</td>
								<td class="text-center">
									{{ number_format($movimiento->Cantidad, 2, ',', '.') }}
								</td>
								<td class="text-right">
									{{ '$'.number_format($costo_unitario, 2, ',', '.') }}
								</td>
								<td class="text-right">
									{{ '$'.number_format($movimiento->Costo, 2, ',', '.') }}
								</td>
							</tr>
							<?php 
								$total_cantidad+= $movimiento->Cantidad;
								$total_costo_total+= $movimiento->Costo;
							?>
						@endforeach
				    </tbody>
				    <tfoot>
				        <tr>
				            <td colspan="2">&nbsp;</td>
				            <td class="text-center"> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
				            <td>&nbsp;</td>
				            <td class="text-right"> {{ '$'.number_format($total_costo_total, 2, ',', '.') }} </td>
				        </tr>
				    </tfoot>
				</table>
			</div>
				
		</div>
	</div>
	<br/><br/>	

@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			$('#btn_excel').show();

			$('#fecha_corte').change(function(event){
				var id = getParameterByName('id');
				var fecha_corte = $('#fecha_corte').val();
				var bodega_id = $('#bodega_id').val();

				window.location.assign('../inv_consultar_existencias/'+bodega_id+'?id='+id+'&fecha_corte='+fecha_corte);

				//$('#consultar_existencias').attr('href','../inventarios/'+bodega_id+'?id='+id+'&fecha_corte='+fecha_corte);
			});

			function getParameterByName(name) {
			    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
			    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
			    results = regex.exec(location.search);
			    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
			}
		});

		
	</script>
@endsection