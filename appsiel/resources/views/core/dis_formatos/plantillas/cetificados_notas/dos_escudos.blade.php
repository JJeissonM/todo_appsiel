<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Document</title>
	<style>
		@font-face {
			font-family: Arial, sans-serif;
		}

		img {
			padding-left: 30px;
		}

		.page-break {
			page-break-after: always;
		}

		@page {
			margin: 40px 25px 100px 25px;
		}

		.header {
			position: fixed;
			top: -60px;
			left: 0px;
			right: 0px;
			height: 50px;
		}

		footer {
			/*position: fixed;*/
			bottom: -70px;
			left: 0px;
			right: 0px;
			height: 40px;
			text-align: center;
		}

		.watermark-letter {
			position: fixed;
			top: 12%;
			left: 0%;
			text-align: center;
			opacity: .2;
			z-index: -1000;
		}

		.watermark-folio {
			position: fixed;
			top: 20%;
			left: 0%;
			text-align: center;
			opacity: .2;
			z-index: -1000;
		}

		.escudo img {
			display: block;
			margin: 0 20% 0;
			width: 80%;
		}

		.table {
			width: 100%;
		}


		.table>tbody>tr>td,
		.table>tbody>tr>th,
		.table>tfoot>tr>td,
		.table>tfoot>tr>th,
		.table>thead>tr>td,
		.table>thead>tr>th {
			line-height: 1.42857143;
			vertical-align: top;
			border-top: 1px solid gray;
		}


		.table-bordered {
			border: 1px solid gray;
		}

		.table-bordered>tbody>tr>td,
		.table-bordered>tbody>tr>th,
		.table-bordered>tfoot>tr>td,
		.table-bordered>tfoot>tr>th,
		.table-bordered>thead>tr>td,
		.table-bordered>thead>tr>th {
			border: 1px solid gray;
		}
	</style>
</head>

<body id="body" style="font-size: 17px; position: relative;">

	<?php    
    $colegio = App\Core\Colegio::where('empresa_id',Auth::user()->empresa_id)->get()->first();
	$cont = 0;
	$url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/escudos/'.$colegio->imagen;
?>

	@foreach($estudiantes as $estudiante)
		<div class="page-break">
		
		<div class="watermark-{{$tam_hoja}} escudo">
			<img src="{{ $url }}" />
		</div>

		@if(config('matriculas.banner_reportes') == 'renderizado')
			<div style="position: absolute; width: 100%">
				<img src="{{ $url }}" width="80px" style="float: left" />
				<img src="{{ asset('assets/img/escudo_colombia.png') }}" width="80px" style="float: right" />
			</div>
		@endif

		@if(config('matriculas.banner_reportes') == 'imagen')
			<div style="width: 100%;">
				<img src="{{ config('matriculas.url_imagen_banner') }}" height="150px"/>
			</div>
		@endif

		<table width="100%">
			@if(config('matriculas.banner_reportes') == 'renderizado')
				<tr>
					<td colspan="6" style="text-align: center; font-size: 1em;">
						<div
							style="width: 100%; padding-left: 70px; padding-right: 70px; margin-left: -20px; padding-top: 10px">
							@include('core.dis_formatos.plantillas.cetificados_notas.texto_encabezado')
						</div>
					</td>
				</tr>
			@endif
			
			<!-- @ inject('calificacion_service', 'App\Calificaciones\Services\CalificacionesService') -->
	
			<?php 
				$resultado_academico = 'APROBÓ';//$calificacion_service->get_resultado_academico($asignaturas, $periodo_lectivo->id, $periodo_id, $curso->id, $estudiante->id);
			?>

			<tr>
				<td colspan="6">
					@include('core.dis_formatos.plantillas.cetificados_notas.texto_introduccion')
				</td>
			</tr>
			<tr>
				<td colspan="6">
					@include('core.dis_formatos.plantillas.tabla_asignaturas_calificacion_2')
				</td>
			</tr>
			<tr>
				<td colspan="6">
					@include('core.dis_formatos.plantillas.cetificados_notas.texto_final')
				</td>
			</tr>
			<tr>
				<td colspan="6">
					@include('core.dis_formatos.plantillas.cetificados_notas.seccion_firmas_autorizadas')
				</td>
			</tr>
		</table>
		<footer style="border:none">
			<div style="border-top: 2px solid rgb(0, 0, 0); height: 10px; margin: 0 4rem"></div>
			<div style="text-align: center">
				Dirección: {{ $colegio->direccion }} Celular: {{ $colegio->telefonos }}
				@if( $colegio->empresa->email != '' )
					Email: {{$colegio->empresa->email}}
				@endif
			</div>
			<div style="text-align: center">{{ $colegio->ciudad }}</div>
		</footer>
		@if($cont > 0)
		<div></div>
		@endif
		<?php
		$cont++;
	?>
	</div>
	@endforeach

</body>

</html>