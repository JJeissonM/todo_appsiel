<style>

	.page-break {
		page-break-after: always;
	}
</style>

<style>
    @page { margin: 60px 75px 100px ; }
    header { 
    	position: fixed; 
    	top: -60px; 
    	left: 0px; 
    	right: 0px; 
    	background-color: lightblue; 
    	height: 50px; 
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
	footer { 
    	position: fixed; 
    	bottom: -70px; 
    	left: 0px; 
    	right: 0px; 
    	height: 40px;
    	text-align: center;
    }
 </style>

<?php    
    $colegio = App\Core\Colegio::where('empresa_id',Auth::user()->empresa_id)->get()->first();

	$url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/escudos/'.$colegio->imagen;


	if ( !isset( $estudiante->nombre_completo ) )
	{
		dd('Ningún estudiante seleccionado.');
	}
?>    

@if($matriculado)
	<div style="font-size: 17px;">
		<div class="watermark-{{$tam_hoja}} escudo">
		    <img src="{{ $url }}"/>
		</div> 
		<footer style="border: none">
			<hr>
			<div style="text-align: center">Dirección: {{ $colegio->direccion }} Celular: {{ $colegio->telefonos }}</div>
			<div style="text-align: center">{{ $colegio->ciudad }}</div>
		</footer>

			<br><br>
			
		<div align="center" style="font-size: 1.1em;">
				<b>{{ $colegio->descripcion }}</b><br/>
				{{ $colegio->resolucion }}. <br> Expedida por Secretaria de Educación Municipal <br/>
				{{ $colegio->ciudad }}<br/><hr>
		</div>

		<br><br><br>

		<div align="center">
			LA SUSCRITA RECTORA DE {{ $colegio->descripcion }}
		</div>
		
		<br><br>

		<div align="center">
			HACE CONSTAR:
		</div>



		<br><br><br>

		<div style="text-align: justify;">
			Que el estudiante <b>{{ $estudiante->nombre_completo }}</b>, identificado con {{ $estudiante->abreviatura }} No. {{ number_format( $estudiante->numero_identificacion, 0, ',', '.' ) }}, se encuentra matriculado en el grado <b>{{ $curso->grado->descripcion }}</b> de Educación Básica {{ $curso->nivel->descripcion }} en nuestra institución educativa en el {{ $periodo_lectivo->descripcion }}.
		</div>		

		<br><br>

		<?php
			$mensaje_valor_matricula_pension = get_mensaje_valor_matricula_pension( $detalla_valores_matricula_pension, $libreta_pago);
		?>

		{!! $mensaje_valor_matricula_pension !!}

		<div style="text-align: justify;">
			Esta constancia se expide por solicitud del interesado, se firma y se sella en {{ $colegio->ciudad }} a los {{ $array_fecha[0] }} días del mes de {{ $array_fecha[1] }} de {{ $array_fecha[2] }}.
		</div>



		<br><br><br>

		Atentamente,

	</div>
<br><br><br>
	<?php
		$url_firma = '';
		if ( $firma_autorizada_1->imagen != '' )
		{
			$url_firma = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/firmas_autorizadas/'.$firma_autorizada_1->imagen;
			$img = getimagesize($url_firma);
		}
	?>
	@if( $url_firma != '')
	<div style="position: relative;">
		<img src="{{ $url_firma }}" width="250px" style="margin: 0; padding: 0"/>
	</div>
	@endif
	_____________________________<br><br>
	{{ $firma_autorizada_datos_1->tercero_nombre }}
	<br>
	{{ $firma_autorizada_datos_1->tercero_tipo_doc_identidad }} {{ number_format( $firma_autorizada_datos_1->tercero_numero_identificacion, 0, ',', '.' ) }}
	<br>
	{{ $firma_autorizada_1->titulo_tercero }} 


@else
	<h2>Estudiante no tiene matrículas para este Año Lectivo: {{ $periodo_lectivo->descripcion }}</h2>
@endif

<?php
	function get_mensaje_valor_matricula_pension($detalla_valores_matricula_pension, $libreta_pago)
	{

		if( is_null($libreta_pago) && $detalla_valores_matricula_pension != 'no' )
		{
			return '<span style="color: red;">El estudiante no tiene libreta de pagos creada.</span><br><br>';
		}		

		switch ($detalla_valores_matricula_pension)
		{
			case 'no':
				$mensaje_valor_matricula_pension = '';
				break;
				
			case 'ambos':
				$mensaje_valor_matricula_pension = '<div style="text-align: justify;">
								En el año lectivo el estudiante pagó una matrícula por valor de <b>$'.number_format( $libreta_pago->valor_matricula, 0, ',', '.' ).'</b> y paga una pensión mesual por valor de <b>$'.number_format( $libreta_pago->valor_pension_mensual, 0, ',', '.' ).'</b>.
								 <br><br>
							</div>';
				break;
				
			case 'solo_matricula':
				$mensaje_valor_matricula_pension = '<div style="text-align: justify;">
								En el año lectivo el estudiante pagó una matrícula por valor de <b>$'.number_format( $libreta_pago->valor_matricula, 0, ',', '.' ).'</b>.
								 <br><br>
							</div>';
				break;
				
			case 'solo_pension':
				$mensaje_valor_matricula_pension = '<div style="text-align: justify;">
								En el año lectivo el estudiante paga una pensión mesual por valor de <b>$'.number_format( $libreta_pago->valor_pension_mensual, 0, ',', '.' ).'</b>.
								 <br><br>
							</div>';
				break;
			
			default:
				$mensaje = '';
				break;
		}

		return $mensaje_valor_matricula_pension;
	}

?>