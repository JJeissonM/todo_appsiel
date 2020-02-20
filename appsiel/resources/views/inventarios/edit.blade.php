@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Modificando el registro</h4>
		    <hr>
			{{ Form::model($registro, ['url' => ['inventarios/'.$registro->id], 'method' => 'PUT']) }}
				<?php
				  if (count($form_create['campos'])>0) {
				  	$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
				  	echo '<div class="row" style="margin: 5px;">'.Form::bsButtonsForm2($url).'</div>';
				  }else{
				  	echo "<p>El modelo no tiene campos asociados.</p>";
				  }
				?>

				{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

				{{ Form::hidden('url_id',Input::get('id'))}}
				{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}
				{{ Form::hidden('inv_bodega_id_aux',null,['id'=>'inv_bodega_id_aux'])}}
				
			{{ Form::close() }}

			<br/>
		    <h4>Ingreso de productos</h4>
		    <table class="table table-striped" id="ingreso_productos">
		        <thead>
		            <tr>
		                <th data-override="inv_producto_id">Cod.</th>
		                <th width="280px">Producto</th>
		                <th width="200px" data-override="motivo">Motivo</th>
		                <th data-override="costo_unitario"> Costo Unit. </th>
		                <th data-override="cantidad">Cantidad</th>
		                <th data-override="costo_total">Costo Total</th>
		                <th width="10px">&nbsp;</th>
		            </tr>
		        </thead>
		        <tbody>
		            <tr>
		                <td></td>
		                <td class="nom_prod"></td>
		                <td></td>
		                <td></td>
		                <td></td>
		                <td></td>
		                <td></td>
		            </tr>
		        </tbody>
		        <tfoot>
		            <tr>
		                <td colspan="4">&nbsp;</td>
		                <td> <div id="total_cantidad"> 0 </div> </td>
		                <td> <div id="total_costo_total"> $0</div> </td>
		                <td> &nbsp;</td>
		            </tr>
		            <tr>
		                <td colspan="5">
		                	<!-- Trigger the modal with a button -->
							<button type="button" class="btn btn-info btn-xs" id="btn_nuevo" style="display:none;"><i class="fa fa-btn fa-plus"></i></button>
						</td>
		                <td colspan="2">
		                	@if($id_transaccion==4)
		                		<button type="button" class="btn btn-warning btn-xs" id="btn_calcular_costos_finales"><i class="fa fa-btn fa-calculator"></i> Calcular costos</button>
		                	@else
		                		&nbsp;
		                	@endif
						</td>
		            </tr>
		        </tfoot>
		    </table>		    

			<!-- Modal -->
			@include('inventarios.incluir.ingreso_productos_2')
			
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			$('#fecha').focus();
		});
	</script>
@endsection