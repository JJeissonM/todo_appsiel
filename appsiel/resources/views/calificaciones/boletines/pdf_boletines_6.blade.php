@extends( 'calificaciones.boletines.formatos.layout' )

@section('contenido_formato')

	<?php
	    if ( $mostrar_areas == 'Si')
		{
			$lbl_asigatura = 'ÁREA / ASIGNATURA';
		}else{

			$lbl_asigatura = 'ASIGNATURA';
		}
	?>

	@foreach($datos as $registro)
        <div style="border-width: 10px; border-color: red; border-style: double; height: 96%; width: 100%; opacity: 0.1; position:absolute;">
		</div>

            <div class="watermark-{{$tam_hoja}} escudo">
                <img src="{{ $url_imagen_marca_agua }}" />
            </div>
            
            <br>

	        @include('calificaciones.boletines.formatos.banner_colegio_con_escudo')

            <?php 

                $lineas_cuerpo_boletin = $registro->cuerpo_boletin->lineas;

                $area_anterior = '';
                $cant_columnas = 2;

                $lbl_numero_periodo = '';
                switch ($periodo->numero) {
                    case '1':
                        $lbl_numero_periodo = 'PRIMER';
                        break;
                        
                    case '2':
                        $lbl_numero_periodo = 'SEGUNDO';
                        break;
                        
                    case '3':
                        $lbl_numero_periodo = 'TERCER';
                        break;
                        
                    case '4':
                        $lbl_numero_periodo = 'CUARTO';
                        break;
                    
                    case '5':
                        $lbl_numero_periodo = 'ÚLTIMO';
                        break;
                    
                    default:
                        # code...
                        break;
                }
            ?>

            <h4 style="text-align: center; padding: 15px;">INFORME {{$lbl_numero_periodo}} PERIODO AÑO LECTIVO {{ explode( "-", $periodo->fecha_desde )[0] }}</h4>
            
            <p style="padding-left: 20px;">
                <b>FULL NAME:</b>  	{{ $registro->estudiante->tercero->descripcion }}
                <br>
                <b>GRADE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </b>	{{ $curso->descripcion }}
            </p>
            
            <br>
                    
            <table class="contenido table-bordered" style="width: 94% !important; padding-left: 3% !important;">
                <thead>
                    <tr>
                        <th style="width:180px; padding: 5px;">{{ $lbl_asigatura }}</th>
                        <th style="padding: 5px;">LOGROS</th>
                        @if($curso->maneja_calificacion==1)
                            <th style="width:80px; padding: 5px;">VALORACIÓN</th>
                            <?php $cant_columnas++;  ?>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach( $lineas_cuerpo_boletin as $linea )

                        <?php 
                            if ($linea->asignacion_asignatura->asignatura->id == (int)config('calificaciones.asignatura_id_para_asistencias')) {
                                continue;
                            }
                        ?>

                        @include('calificaciones.boletines.fila_area')

                        <tr>

                            <td style="width:150px; padding: 5;">
                                {{ $linea->asignacion_asignatura->asignatura->descripcion }}
                            </td>

                            <td style="padding: 5; text-align: justify;">
                                @include('calificaciones.boletines.proposito')
                                
                                @include('calificaciones.boletines.lista_logros')

                                @include('calificaciones.boletines.formatos.etiqueta_nombre_docente')
                            </td>
                            
                            @if( $curso->maneja_calificacion == 1)
                                <td style="padding: 5; text-align: center;"> 
                                    @if( !is_null( $linea->calificacion ) )
                                        @if( $linea->calificacion->calificacion > 0)
                                            @include('calificaciones.boletines.lbl_descripcion_calificacion')
                                        @endif
                                    @endif
                                </td>
                            @endif
                        </tr>

                        <?php 
                            $area_anterior = $linea->asignacion_asignatura->asignatura->area->descripcion;
                        ?>

                    @endforeach {{--  End For Each Asignatura --}}

                    @include('calificaciones.boletines.formatos.fila_observaciones')

                    @include('calificaciones.boletines.formatos.fila_etiqueta_final')

                </tbody>

            </table>
            
            @include('calificaciones.boletines.mostrar_usuarios_estudiantes')
            
            @include('calificaciones.boletines.seccion_firmas')
            
            <div class="page-break"></div>
	@endforeach
@endsection