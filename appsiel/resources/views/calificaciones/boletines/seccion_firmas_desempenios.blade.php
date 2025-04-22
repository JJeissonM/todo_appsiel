
	<br/>

	<?php
	
		$nombre_archivo = 'firma_rector';
		$tercero_rector_descripcion = '';
		$titulo_tercero_rector = $colegio->piefirma1;

		if ($firmas[0] != 'No cargada') {
			$url_firma = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/'.$nombre_archivo.'.png';
		}else{

			$url_firma = '';
			$firma_autorizada_1 = null;
			
			if ( $colegio->representante_legal() != null) {
				$tercero_rector_descripcion = $colegio->representante_legal()->descripcion;
					$firma_autorizada_1 = $colegio->representante_legal()->firma_autorizada;
			}

			if ( $firma_autorizada_1 != null ) {
				if ( $firma_autorizada_1->imagen != '' )
				{
					$url_firma = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/firmas_autorizadas/'.$firma_autorizada_1->imagen;
				}

				$tercero_rector_descripcion = $firma_autorizada_1->tercero->descripcion;
				$titulo_tercero_rector = $firma_autorizada_1->titulo_tercero;
			}
		}

		if( $url_firma != '')
		{
			$texto_firma_1 = '<img src="' . $url_firma . '" style="margin-bottom: -20px; max-height:70px; width:auto;"/>';
		}else{
			$texto_firma_1 = '_____________________________';
		}
		
		$url_firma = '';
		$nombre_archivo = 'firma_profesor';
		$firma_autorizada_2 = null;
		$tercero_profesor_descripcion = '';
		$titulo_tercero_profesor = $colegio->piefirma2;

		if ($firmas[1] != 'No cargada') {
			$url_firma = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/'.$nombre_archivo.'.png';
		}else{

			$url_firma = '';

			if ( $curso->director_grupo->first() != null) {
				$tercero_profesor = $curso->director_grupo->first()->tercero;

				if ($tercero_profesor != null) {
					$tercero_profesor_descripcion = $tercero_profesor->descripcion;
					$firma_autorizada_2 = $tercero_profesor->firma_autorizada;
				}
			}

			if ( $firma_autorizada_2 != null ) {
				if ( $firma_autorizada_2->imagen != '' )
				{
					$url_firma = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/firmas_autorizadas/'.$firma_autorizada_2->imagen;
				}

				$tercero_profesor_descripcion = $firma_autorizada_2->tercero->descripcion;
				$titulo_tercero_profesor = $firma_autorizada_2->titulo_tercero;
			}
		}

		if( $url_firma != '')
		{
			$texto_firma_2 = '<img src="' . $url_firma . '" style="margin-bottom: -20px; max-height:70px; width:auto;"/>';
		}else{
			$texto_firma_2 = '_____________________________';
		}
	?>

    <div style="width: 100%; padding-top: 10px; padding-bottom: 10px; text-align: justify;">
        <div style="width: 39%; padding-left: 20px; padding-top: 10px; padding-bottom: 10px; text-align: justify; float: left;">
            <?php

                $escala = App\Calificaciones\EscalaValoracion::where('periodo_lectivo_id',$periodo->periodo_lectivo_id)->orderBy('calificacion_maxima','DESC')->get();
            ?>
            <b>NIVELES DE DESEMPEÑO</b>
            @foreach ( $escala as $linea )
                <br/>
                <b> {{$linea->nombre_escala}}: </b> {{$linea->descripcion }}
            @endforeach
        </div>
        <div style="width: 60%; float: left;">
            <br><br>
            <table border="0">
                <tr>
                    <td width="50px"> &nbsp; </td>
                    <td align="center">
                        {!! $texto_firma_1 !!}
                    </td>
                    <td align="center"> &nbsp;	</td>
                    <td align="center">
                        {!! $texto_firma_2 !!}
                    </td>
                    <td width="50px">&nbsp;</td>
                </tr>
                <tr style="font-size: {{$tam_letra}}mm;">
                    <td width="50px"> &nbsp; </td>
                    <td align="center">	{{ $titulo_tercero_rector }} </td>
                    <td align="center"> &nbsp;	</td>
                    <td align="center">	{{ $titulo_tercero_profesor }} </td>
                    <td width="50px">&nbsp;</td>
                </tr>
            </table>
        </div>
    </div>