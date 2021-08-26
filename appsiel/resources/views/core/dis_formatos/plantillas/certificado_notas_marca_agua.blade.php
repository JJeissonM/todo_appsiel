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
	<style>	
	#body *{
		font-family: 'Open Sans', sans-serif;
	}
	img {
		padding-left:30px;
	}
	
	.page-break {
		page-break-after: always;
	}
    @page { margin: 40px 25px 100px; }
    header { 
    	position: fixed; 
    	top: -60px; 
    	left: 0px; 
    	right: 0px; 
    	height: 50px; 
    }

    footer { 
    	position: fixed; 
    	left: 0px; 
    	right: 0px; 
    	text-align: center;
    }

    p { page-break-after: always; }
    p:last-child { page-break-after: never; }

    .watermark-letter {
	    position: fixed;
	    top: 7%;
	    text-align: center;
	    opacity: .2;
	    z-index: -1000;
	  }

    .watermark-folio {
	    position: fixed;
	    top: 15%;
	    text-align: center;
	    opacity: .2;
	    z-index: -1000;
	  }

	.escudo img{
		display:block;
		margin:auto;
		width: 95%;
	}

	</style>
</head>
<body id="body" style="font-size: 17px;">

<?php    
    $colegio = App\Core\Colegio::where('empresa_id',Auth::user()->empresa_id)->get()->first();
	$cont = 0;
	$url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/escudos/'.$colegio->imagen;
?>

@foreach($estudiantes as $estudiante)
    
		<div class="watermark-{{$tam_hoja}} escudo">
		    <img src="{{ $url }}"/>
		</div> 
		<footer style="border: none">
			<hr>
			<div style="text-align: center">Dirección: {{ $colegio->direccion }} Celular: {{ $colegio->telefonos }}</div>
			<div style="text-align: center">{{ $colegio->ciudad }}</div>
		</footer>

<table width="100%">
	<tr>
		<!--<td>
			<img src="{{ $url }}" width="120px"/>
		</td>-->
		<td colspan="6" style="text-align: center; font-size: 1.1em;">
			<div style="width: 100%; padding-left: 70px; padding-right: 70px; margin-left: -20px; padding-top: 10px">
				<b>{{ $colegio->descripcion }}</b><br/>
				<br/>
				{{ $colegio->resolucion }}. <br> Expedida por Secretaria de Educación Municipal <br/>
				{{ $colegio->ciudad }}<br/><hr>
			</div>
		</td>
		<!--<td>
			<div style="width: 100px"></div>
		</td>-->
	</tr>
	<tr>
		<td colspan="6">
			<br>
			<div align="center">
				EL PRESENTE RECTOR(A) DE: {{ $colegio->descripcion }}
			</div>		
			<div align="center">
				CERTIFICA QUE:
			</div>
			<br>
			<div style="text-align: justify;">
			<b>{{ $estudiante->nombre_completo }}</b>,
			Cursó en esta institución educativa el grado <b>{{ $curso->grado->descripcion }}</b>, según pensum oficial. Habiendo obtenido en el {{ $periodo_lectivo->descripcion }} las calificaciones que a continuación se registran:
			</div>		

			<br>
				
		</td>
	</tr>
	{!! View::make( 'core.dis_formatos.plantillas.tabla_asignaturas_calificacion_2', compact( 'asignaturas','colegio','estudiante','curso', 'periodo_id' ) )->render() !!}	
	<tr>
		<td colspan="6">
			<div style="text-align: justify;">
				Observaciones: APROBÓ( &nbsp;&nbsp; )  &nbsp;&nbsp;&nbsp;&nbsp;    REPROBÓ( &nbsp;&nbsp; )    &nbsp;&nbsp;&nbsp;&nbsp;    APLAZÓ( &nbsp;&nbsp; )
				<br>
				{{ $observacion_adicional }}
			</div>
			<br><br>
			<div style="text-align: justify;">
				Para mayor constancia, se firma la presente en la ciudad de {{ $colegio->ciudad }} a los {{ $array_fecha[0] }} días del mes de {{ $array_fecha[1] }} de {{ $array_fecha[2] }}.
			</div>
			<br>			
		</td>
	</tr>
	<tr>
		<td colspan="6">
		<div style="width: 100%;">
			
			<div style="float: left; width: 50%; text-align: center;">
				<?php
					$url_firma = '';
					if ( $firma_autorizada_1->imagen != '' )
					{
						$url_firma = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/firmas_autorizadas/'.$firma_autorizada_1->imagen;
					}
				?>
				@if( $url_firma != '')
					<img src="{{ $url_firma }}" width="250px" height="70px" style="left: 30px;position: absolute; z-index: 1;"/>
				@endif
				<br><br><br>
				<br>
				<p style="border-top: 1px solid black; margin: 0 50px;">{{ $firma_autorizada_1->titulo_tercero }}</p>
			</div>

			<div style="float: left; width: 50%; text-align: center;">
				<?php
					$url_firma = '';
					if ( $firma_autorizada_2->imagen != '' )
					{
						$url_firma = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/firmas_autorizadas/'.$firma_autorizada_2->imagen;
					}
				?>
				@if( $url_firma != '')
					<img src="{{ $url_firma }}" width="250px" height="70px" style="left: 30px;position: absolute; z-index: 1;"/>
				@endif
				<br><br><br>
				<br>
				<p style="border-top: 1px solid black; margin: 0 50px;">{{ $firma_autorizada_2->titulo_tercero }}</p>
			</div>
			
		</div>
		
		<div style="clear: both"></div>	
		</td>
	</tr>
</table>
<?php	
	if($cont > 0){
		echo '<div class="page-break"></div>';
	}
	$cont++;
?>
@endforeach
</body>
</html>
