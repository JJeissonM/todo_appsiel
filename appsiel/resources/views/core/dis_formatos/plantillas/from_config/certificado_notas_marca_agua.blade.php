<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Document</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">

    <?php 
        $parametros = config('gestion_documental');
		$top = (100 - $parametros['ma_porcentaje_ancho_escudo']) / 2;
    ?>
	<style>
		#body * {
			font-family: 'Open Sans', sans-serif;
		}

		img {
			padding-left: 30px;
		}

		.page-break {
			page-break-after: always;
		}

		@page {
			margin: 40px 25px 0px;
		}

		header {
			position: fixed;
			top: -60px;
			left: 0px;
			right: 0px;
			height: 50px;
		}

		footer {
			
		}

		.watermark-letter {
			position: fixed;
			top: {{$top * 2.1}}%;
			left: {{$top + 1}}%;
			text-align: center;
			opacity: {{$parametros['ma_opacidad_escudo']/100}};
			z-index: -1000;
			width: {{$parametros['ma_porcentaje_ancho_escudo']}}%;
		}

		.watermark-folio {
			position: fixed;
			top: 15%;
			text-align: center;
			opacity: {{$parametros['ma_opacidad_escudo']/100}};
			z-index: -1000;
		}

		.escudo img {
			display: block;
			margin: auto;
			width: {{$parametros['ma_porcentaje_ancho_escudo']}}%;
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

<body id="body" style="font-size: 15px;">

	<?php    
	    $colegio = App\Core\Colegio::where('empresa_id',Auth::user()->empresa_id)->get()->first();
		$cont = 0;
		$url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/escudos/'.$colegio->imagen;

        // _____________________________________________
        $ma_encabezado = $parametros['ma_encabezado'];
        $ma_encabezado = str_replace('__field__colegio_name', $colegio->descripcion, $ma_encabezado);
        $ma_encabezado = str_replace('__field__colegio_resolucion', $colegio->resolucion, $ma_encabezado);        
        $ma_encabezado = str_replace('__field__colegio_ciudad', $colegio->ciudad, $ma_encabezado);
        // _____________________________________________

		$consecutivo = $parametros['consecutivo_inicial'];
	?>

	@foreach($estudiantes as $estudiante)

    <?php
        $ma_contenido_inicial = $parametros['ma_contenido_inicial']; // Se llama Otra vez para resetear los campos
        $ma_contenido_inicial = str_replace('__field__grado_nivel_academico', $curso->nivel->descripcion, $ma_contenido_inicial);
        $ma_contenido_inicial = str_replace('__field__periodo_lectivo_descripcion', $periodo_lectivo->descripcion, $ma_contenido_inicial);
        $ma_contenido_inicial = str_replace('__field__estudiante_nombre_completo', ($estudiante->nombre_completo), $ma_contenido_inicial);
        $ma_contenido_inicial = str_replace('__field__estudiante_numero_identificacion', number_format( $estudiante->numero_identificacion, 0, ',', '.' ), $ma_contenido_inicial);
        $ma_contenido_inicial = str_replace('__field__estudiante_grado', $curso->grado->descripcion, $ma_contenido_inicial);
		
		
        $ma_encabezado_2 = $parametros['ma_encabezado_2'];
		$label_consecutivo = str_pad($consecutivo, 4 - strlen($consecutivo), '0', STR_PAD_LEFT);
        $ma_encabezado_2 = str_replace('__field__consecutivo_cecrtificado', '24-' . $label_consecutivo, $ma_encabezado_2);
		$consecutivo++;
        $ma_encabezado_2 = str_replace('__field__mes', $array_fecha[1], $ma_encabezado_2);
        $ma_encabezado_2 = str_replace('__field__dia', $array_fecha[0], $ma_encabezado_2);
        $ma_encabezado_2 = str_replace('__field__anio', $array_fecha[2], $ma_encabezado_2);
    ?>
	<div class="page-break">
		<div class="watermark-{{$tam_hoja}} escudo">
			<img src="{{ $url }}" />
		</div>

		<table width="100%">
            <tr>
                <td colspan="6" style="text-align: center; font-size: 1em;">
					@if($parametros['columnas_encabezado'] == 3)
						<table style="width:99%; height: 60px;">
							<tr>
								<td style="width:15%; text-align: center;">
									<img alt="foto.jpg" src="{{ $parametros['url_imagen_izquierda_encabezado']}}" style="width: 90px; max-height: 60px;" />
								</td>
								<td style="width:70%; text-align: center;">
									{!! $ma_encabezado !!}
								</td>
								<td style="width:14%; text-align: center;">
									<img alt="foto.jpg" src="{{ $parametros['url_imagen_derecha_encabezado']}}" style="width: 90px; max-height: 60px;" />
								</td>
							</tr>
						</table>							
					@else
						{!! $ma_encabezado !!}
					@endif
					
                    {!! $ma_encabezado_2 !!}

                </td>
            </tr>
			<tr>
				<td colspan="6" style="text-align: justify; font-size: 0.9em;">
                    {!! $parametros['ma_preambulo'] !!}
				</td>
			</tr>
			<tr>
				<td colspan="6" style="text-align: center; font-size: 0.9em;">
                    {!! $parametros['ma_introduccion'] !!}
				</td>
			</tr>
			<tr>
				<td colspan="6" style="text-align: justify; font-size: 0.9em;">
                    {!! $ma_contenido_inicial !!}
				</td>
			</tr>
			<tr>
				<td colspan="6">
					@include('core.dis_formatos.plantillas.' . $parametros['tabla_desing'])					
				</td>
			</tr>
			<tr>
				<td colspan="6">
					@include('core.dis_formatos.plantillas.cetificados_notas_texto_final')
				</td>
			</tr>
			<tr>
				<td colspan="6">
					@include('core.dis_formatos.plantillas.cetificados_notas_seccion_firmas_autorizadas')
				</td>
			</tr>
		</table>

		<br><br><br><br><br><br><br><br>
		<div style="border: none; font-size: 0.8em;">
			<?php
        		$ma_contenido_pie_pagina = $parametros['ma_contenido_pie_pagina'];
				$ma_contenido_pie_pagina = str_replace('__field__colegio_direccion', $colegio->direccion, $ma_contenido_pie_pagina);
				$ma_contenido_pie_pagina = str_replace('__field__colegio_telefonos', $colegio->telefonos, $ma_contenido_pie_pagina);
				$ma_contenido_pie_pagina = str_replace('__field__empresa_email', $colegio->empresa->email, $ma_contenido_pie_pagina);
				$ma_contenido_pie_pagina = str_replace('__field__empresa_ciudad', $colegio->empresa->ciudad->descripcion, $ma_contenido_pie_pagina);
				$ma_contenido_pie_pagina = str_replace('__field__empresa_departamento', $colegio->empresa->ciudad->departamento->descripcion, $ma_contenido_pie_pagina);
				$ma_contenido_pie_pagina = str_replace('__field__empresa_pagina_web', $colegio->empresa->pagina_web, $ma_contenido_pie_pagina);
			?>

			<hr>
			<div style="text-align: center; width: 100%;">
				{!! $ma_contenido_pie_pagina !!}
			</div>
		</div>
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