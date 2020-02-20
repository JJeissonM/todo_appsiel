@extends('layouts.principal')

@section('estilos_1')
	<style type="text/css">
		#div_cargando{
			display: none;/**/
			color: #FFFFFF;
			background: #3394FF;
			position: fixed; /*El div será ubicado con relación a la pantalla*/
			/*left:0px; A la derecha deje un espacio de 0px*/
			/*right:0px; A la izquierda deje un espacio de 0px*/
			bottom:0px; /*Abajo deje un espacio de 0px*/
			/*height:50px; alto del div*/
			z-index:0;
		}

		#ingreso_registros select {
			width: 150px;
		}

	</style>
@endsection

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Nuevo registro</h4>
		    <hr>
			{{ Form::model($registro, ['url' => [ $form_create['url'] ], 'method' => 'PUT','files' => true, 'id' => 'form_create']) }}
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
				{{ Form::hidden('url_id_transaccion',Input::get('id_transaccion'))}}

			{{ Form::close() }}

			<br/>

		    <table class="table table-striped" id="ingreso_registros">
		        <thead>
		            <tr>
		                <th width="250px">Cuenta</th>
		                <th width="250px">Tercero</th>
		                <th>Detalle</th>
		                <th data-override="debito">Débito</th>
		                <th data-override="credito">Crédito</th>
		                <th width="10px">&nbsp;</th>
		            </tr>
		        </thead>
		        <tbody>
		        	{!! $lineas_documento !!}
		        </tbody>
		        <tfoot>
		            <tr>
		                <td>
		                	<button id="btn_nuevo" style="background-color: transparent; color: #3394FF; border: none;"><i class="fa fa-btn fa-plus"></i> Agregar registro</button>
		                </td>
		                <td></td>
		                <td></td>
		                <td></td>
		                <td></td>
		                <td></td>
		            </tr>
		            <tr>
		                <td colspan="3">&nbsp;</td>
		                <td> <div id="total_debito" > $0 </div> </td>
		                <td> <div id="total_credito"> $0 </div> </td>
		                <td> <div id="sumas_iguales"> - </div> </td>
		            </tr>
		        </tfoot>
		    </table>
		</div>
	</div>
	<br/><br/>

	<div id="div_cargando">Cargando...</div>
@endsection

@section('scripts')
	
	<script src="{{asset('assets/js/contabilidad.js')}}"></script>


	<script type="text/javascript">
		
		var LineaNum = {{ $linea_num }};

		calcular_totales();
		
		$(document).ready(function(){
			$('#fecha').focus();		
		});

		function calcular_totales()
		{
			var sum = 0.0;

			// Sumar columna de los débitos
			sum = 0.0;
			$('.debito').each(function()
			{
			    var cadena = $(this).text();
			    sum += parseFloat( cadena.substring(1) );
			});
			$('#total_debito').text("$"+sum.toFixed(2));
			sumas_iguales = sum;

			// Sumar columna de los créditos
			sum = 0.0;
			$('.credito').each(function()
			{
			    var cadena = $(this).text();
			    sum += parseFloat( cadena.substring(1) );
			});
			$('#total_credito').text("$"+sum.toFixed(2));


			sumas_iguales = sumas_iguales - sum;
			$('#sumas_iguales').text( sumas_iguales.toFixed(0) );
		}



	</script>
@endsection