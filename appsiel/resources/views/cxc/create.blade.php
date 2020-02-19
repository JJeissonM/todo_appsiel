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
		    <h4>Nuevo registro</h4>
		    <hr>
			{{ Form::open( [ 'url' => 'web', 'id' => 'form_create'] ) }}

				<?php
				  if (count($form_create['campos'])>0) {
				  	$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
				  	echo '<div class="row" style="margin: 5px;">'.Form::bsButtonsForm2($url).'</div>';
				  }else{
				  	echo "<p>El modelo no tiene campos asociados.</p>";
				  }
				?>

				<!-- <div class="row" style="padding: 5px 5px 5px 15px;"> <label>Registro cartera: </label> <label class="radio-inline"> <input type="radio" name="registro_cartera" id="registro_cartera" value="unica" checked>Única </label> <label class="radio-inline"> <input type="radio" name="registro_cartera" id="registro_cartera" value="multiple">Múltiple </label> </div>

				-->

				{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

				{{ Form::hidden( 'url_id', Input::get( 'id' ) ) }}
				{{ Form::hidden( 'url_id_modelo', Input::get( 'id_modelo' ) ) }}
				{{ Form::hidden('url_id_transaccion', Input::get('id_transaccion')) }}

				{{ Form::hidden( 'core_tercero_id_aux', '', [ 'id' => 'core_tercero_id_aux' ] ) }}
				{{ Form::hidden( 'fecha_aux', '', [ 'id' => 'fecha_aux' ] ) }}
				{{ Form::hidden( 'tipo_recaudo_aux', '', [ 'id' => 'tipo_recaudo_aux' ] ) }}

			{{ Form::close() }}

			<hr>

			<?php
				/*$datos = [
							'titulo' => 'Ingresar Conceptos/Servicios',
							'columnas' => [
												[ 'name' => 'servicio_id', 'display' => 'none', 'etiqueta' => ''],
												[ 'name' => 'tercero_id', 'display' => 'none', 'etiqueta' => ''],
												[ 'name' => 'valor', 'display' => 'none', 'etiqueta' => ''],
												[ 'name' => 'lbl_servicio', 'display' => '', 'etiqueta' => 'Concepto/Servicio'],
												[ 'name' => 'lbl_tercero', 'display' => '', 'etiqueta' => 'Tercero'],
												[ 'name' => 'lbl_valor', 'display' => '', 'etiqueta' => 'Valor']
											]
						];*/
			?>

			<a href="#" data-toggle="tooltip" data-placement="right" title="Si selecciona un tercero, la contabilización de la cuenta contrapartida del Concepto/Servicio se hará a nombre de ese tercero."> <i class="fa fa-question-circle"></i> </a>

			{!! $tabla->dibujar() !!}
			
			<!-- @ include('layouts.elementos.tabla_ingreso_lineas_registros') -->

		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')

	<script src="{{asset('assets/js/cxc.js')}}"></script>
	
	<script type="text/javascript">
		$(document).ready(function(){

			$('[data-toggle="tooltip"]').tooltip();

			var today = new Date();
			var dd = today.getDate();
			var mm = today.getMonth()+1; //January is 0!
			var yyyy = today.getFullYear();

			if(dd<10) {
			    dd = '0'+dd
			} 

			if(mm<10) {
			    mm = '0'+mm
			} 

			today = yyyy + '-' + mm + '-' + dd;

			$('#fecha').val( today );
			$('#fecha').focus();
			$('#core_tercero_id_no').parent().hide();
			// Par que deje pasar el primer Continuar
			$('#core_tercero_id_no').removeAttr('required');
			$('#core_tercero_id').removeAttr('required');

		});
	</script>
@endsection