@extends('layouts.principal')

<?php 
	use App\Http\Controllers\Contabilidad\ContabReportesController;

?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	@include('layouts.mensajes')

	<!-- <div class="container-fluid">
		<div class="marco_formulario">
			@ foreach( $grupos_cuentas as $grupo )
				{ { $grupo->nivel }} | { { $grupo->abuelo_descripcion }} | { { $grupo->padre_descripcion }} | { { $grupo->hijo_descripcion }}
				<br>
			@ endforeach
		</div>
	</div>
-->
	<div class="table-responsive" id="table_content">
		<table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th> Código </th>
                    <th> Cuenta </th>
                    <th> Grupo </th>
                </tr>
            </thead>
            <tbody>
            	@foreach ($cuentas as $fila)
            		<tr>
                        <td> {{ $fila->codigo}} </td>
                        <td> {{ $fila->descripcion }} </td>
                        <td> {!! ContabReportesController::get_select_grupo_cuentas( $fila->contab_cuenta_grupo_id, $fila->id) !!} <span id="span_'{{ $fila->id }} " style="color:red;"></span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
	</div>


	<br/><br/>

@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function()
		{
			$('.combobox2').change(function()
			{

				var cuenta_id = $(this).attr('id');

				document.getElementById( 'span_'+cuenta_id ).innerHTML = "";

				$('#div_cargando').show();
						
				var url = 'reasignar_grupos_cuentas_save/' + cuenta_id + '/' + $(this).val();

				$.get( url, function( respuesta ) {
			        $('#div_cargando').hide();

			        document.getElementById( 'span_'+cuenta_id ).innerHTML = respuesta;

			    });
			});
		});

		
	</script>
@endsection