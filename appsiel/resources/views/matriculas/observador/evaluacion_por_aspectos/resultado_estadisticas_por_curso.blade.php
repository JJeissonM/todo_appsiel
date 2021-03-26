<?php
    $url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/escudos/'.$colegio->imagen;
?>
<div class="container-fluid">
	<div class="marco_formulario">
		<div class="row">
			<div class="col-sm-12">
				<div class="table-responsive">
						<table class="table table-bordered table-striped" id="tbDatos">
							<thead>
								<tr align="center">
									<th>
                                        <img src="{{ $url }}" width="{{ config('configuracion.ancho_logo_formatos') }}" height="{{ config('configuracion.alto_logo_formatos') }}" />
                                    </th>
									<th colspan="4">
                                        <h4 style="text-align: center;">
                                            FORMATO DE CONGRATULATIONS
                                        </h4>
                                    </th>
								</tr>
								<tr>
                                    <th> Student Name <br> (Nombres y Apellidos Completos) </th>
                                    <th> Date and time <br> (dd/mm/yyyy) </th>
                                    <th> Class <br> (Curso) </th>
                                    <th> Subject <br> (Asignatura) </th>
                                    <th> Reason for praise <br> (Aspectos a resaltar del estudiante) </th>
								</tr>
							</thead>
							<tbody>

								@foreach( $valores_consolidados_estudiantes AS $fila )
                                    <?php  
                                        if( is_null( $fila->ultimo_resultado_valoracion() ) )
                                        {
                                            dd( $fila );
                                        }
                                     ?>
									<tr>

                                        <td>
                                            {{ $fila->estudiante->tercero->descripcion }}
                                        </td>
                                        <td>
                                            {{ $fila->ultimo_resultado_valoracion()->fecha_valoracion }}
                                        </td>
                                        <td>
                                            {{ $fila->curso->codigo }}
                                        </td>
                                        <td>
                                            {{ $fila->asignatura->descripcion }}
                                        </td>
                                        <td width="40%">
                                            {{ $fila->observacion->observacion }}
                                        </td>
										
									</tr>
								@endforeach
								
							</tbody>
						</table>
				</div>				
			</div>
		</div>

	</div>
</div>

<?php

	function get_frecuencia( $valoraciones_est, $numero_fila )
	{
		$array_convenciones = ['','Alto','Medio','Bajo'];

        $array_valoracion = [];
        $title = '';
        $hay_alto = 0;
        $hay_medio = 0;
        $hay_bajo = 0;
        foreach ( $valoraciones_est as $key => $convencion_valoracion_id )
        {
            //

            if ( $numero_fila == 1 )
	        {
	        	//dd( $valoraciones_est, $convencion_valoracion_id );
	        }

            switch ( $convencion_valoracion_id )
            {
                case '1':
                    $hay_alto++;
                break;
              
                case '2':
                    $hay_medio++;
                break;
              
                case '3':
                    $hay_bajo++;
                break;
              
                default:
                    break;
            }
        }

        if ( $numero_fila == 1 )
        {
        	//dd([$hay_alto, $hay_medio, $hay_bajo]);
        }
        	
        $color_fondo = 'yellow';
        $color_texto = 'black';
        $lbl_valoracion = '--';
        $value_valoracion = 0;

        if ( $hay_alto == 2 && $hay_medio == 1 )
        {
            $color_fondo = 'purple';
            $color_texto = 'white';
            $lbl_valoracion = $array_convenciones[ 1 ];
            $value_valoracion = 1;
        }

        if ( ($hay_alto == 1 || $hay_alto == 2 || $hay_alto == 3 ) && $hay_medio == 0 && $hay_bajo == 0 )
        {
            $color_fondo = 'purple';
            $color_texto = 'white';
            $lbl_valoracion = $array_convenciones[ 1 ];
            $value_valoracion = 1;
        }

        if ( $hay_alto == 1 && $hay_medio == 2 )
        {
            $color_fondo = 'yellow';
            $color_texto = 'black';
            $lbl_valoracion = $array_convenciones[ 2 ];
            $value_valoracion = 2;
        }

        if ( ($hay_medio == 1 || $hay_medio == 2 || $hay_medio == 3 ) && $hay_alto == 0 && $hay_bajo == 0 )
        {
            $color_fondo = 'yellow';
            $color_texto = 'black';
            $lbl_valoracion = $array_convenciones[ 2 ];
            $value_valoracion = 2;
        }

        if ( $hay_alto == 1 && $hay_medio == 1 && $hay_bajo == 1 )
        {
            $color_fondo = 'yellow';
            $color_texto = 'black';
            $lbl_valoracion = $array_convenciones[ 2 ];
            $value_valoracion = 2;
        }

        if ( $hay_alto == 2 && $hay_bajo == 1 )
        {
            $color_fondo = 'yellow';
            $color_texto = 'black';
            $lbl_valoracion = $array_convenciones[ 2 ];
            $value_valoracion = 2;
        }

        if ( $hay_medio == 2 && $hay_bajo == 1 )
        {
            $color_fondo = 'red';
            $color_texto = 'white';
            $lbl_valoracion = $array_convenciones[ 3 ];
            $value_valoracion = 3;
        }

        if ( $hay_bajo == 2 && $hay_alto == 1 )
        {
            $color_fondo = 'red';
            $color_texto = 'white';
            $lbl_valoracion = $array_convenciones[ 3 ];
            $value_valoracion = 3;
        }

        if ( $hay_bajo == 2 && $hay_medio == 1 )
        {
            $color_fondo = 'red';
            $color_texto = 'white';
            $lbl_valoracion = $array_convenciones[ 3 ];
            $value_valoracion = 3;
        }

        if ( ($hay_bajo == 1 || $hay_bajo == 2 || $hay_bajo == 3 ) && $hay_alto == 0 && $hay_medio == 0 )
        {
            $color_fondo = 'red';
            $color_texto = 'white';
            $lbl_valoracion = $array_convenciones[ 3 ];
            $value_valoracion = 3;
        }

        $valoracion = '<span style="background: ' . $color_fondo . '; color:' . $color_texto . ';" title="' . $title . '">' . $lbl_valoracion . '</span>';

        return (object)[ 'lbl_valoracion' => $valoracion, 'value_valoracion' => $value_valoracion ];
	}
?>