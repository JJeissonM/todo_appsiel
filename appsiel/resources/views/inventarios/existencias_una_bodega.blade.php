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
			<table class="table table-bordered" id="myTable">
			    {{ Form::bsTableHeader(['Cód.','Producto','Cantidad','Costo Prom.','Costo Total']) }}
			    </thead>
			    <tbody>
			        <?php 
			        $total_cantidad=0;
			        $total_costo_total=0;
			        for($i=0;$i<count($productos);$i++){ 
			        		$productos[$i]['Cantidad'] = round($productos[$i]['Cantidad'],2);
			        		$costo_unitario = 0;
			        		if( $productos[$i]['Cantidad'] != 0)
			        		{
			        			$costo_unitario = $productos[$i]['Costo'] / $productos[$i]['Cantidad'];
			        		}else{
			        			$productos[$i]['Costo'] = 0;	
			        		}
			        	?>
			        	<!-- @  -->
			            <!-- @ endif -->
				            <tr>
				                <td>{{ $productos[$i]['id'] }}</td>
				                <td>{{ $productos[$i]['descripcion'] }}</td>
				                <td>{{ number_format($productos[$i]['Cantidad'], 2, ',', '.') }} {{ $productos[$i]['unidad_medida1'] }}</td>
				                <td>{{ '$'.number_format($costo_unitario, 2, ',', '.') }}</td>
				                <td>{{ '$'.number_format($productos[$i]['Costo'], 2, ',', '.') }}</td>
				            </tr>
			        <?php 
			            $total_cantidad+= $productos[$i]['Cantidad'];
			            $total_costo_total+= $productos[$i]['Costo'];
			        } ?>
			    </tbody>
			    <tfoot>
			        <tr>
			            <td colspan="2">&nbsp;</td>
			            <td> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
			            <td>&nbsp;</td>
			            <td> {{ '$'.number_format($total_costo_total, 2, ',', '.') }} </td>
			        </tr>
			    </tfoot>
			</table>			
			
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