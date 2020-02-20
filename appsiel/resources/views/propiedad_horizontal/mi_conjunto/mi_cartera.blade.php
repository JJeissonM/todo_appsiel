@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	<div class="container col-sm-10 col-sm-offset-1">

		<table class="table table-bordered">
		    <tr>
		        <td colspan="4" style="border: solid 1px black;">
		            <b>Nombre propietario: </b>{{ $tercero->descripcion }}
		        </td>
		        <td colspan="3" style="border: solid 1px black;">
		            <b>Teléfono propietario: </b>{{ $tercero->telefono1 }} {{ $tercero->telefono2 }}
		        </td>
		    </tr>
		    <tr>
		        <td colspan="7" style="border: solid 1px black;">
		            <b>Nombre Residente: </b>{{ $propiedad->nombre_arrendatario }}
		        </td>
		    </tr>
		    <tr>
		        <td colspan="3" style="border: solid 1px black;">
		            <b>Teléfono: </b>{{ $propiedad->telefono_arrendatario }}
		        </td>
		        <td colspan="4" style="border: solid 1px black;">
		            <b>Email: </b>{{ $propiedad->email_arrendatario }}
		        </td>
		    </tr>
		    <tr>
		        <td colspan="3" style="border: solid 1px black;">
		            <b>{{ $propiedad->tipo_propiedad }}: </b> {{ $propiedad->nomenclatura }}
		        </td>
		        <td colspan="2" style="border: solid 1px black;">
		            <b>Cód. inmueble: </b> {{ $propiedad->codigo }}
		        </td>
		        <td colspan="2" style="border: solid 1px black;">
		            <b>Coeficiente copropiedad: </b> {{ $propiedad->coeficiente_copropiedad }}
		        </td>
		    </tr>
		    <tr>
		        <td colspan="3" style="border: solid 1px black;">
		            <b>Fecha de entrega: </b> {{ $propiedad->fecha_entrega }}
		        </td>
		        <td colspan="4" style="border: solid 1px black;">
		            <b>Núm. matrícula inmobiliaria: </b> {{ $propiedad->numero_matricula_inmobiliaria }}
		        </td>
		    </tr>
		</table>

		{!! $cartera !!}
	</div>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			$(document).on('click', '.btn_ver_documento', function() {
				var fila = $(this).closest("tr");
				var id_mov_cxc = fila[0].id;
				console.log(id_mov_cxc);
			});
		});
	</script>
@endsection