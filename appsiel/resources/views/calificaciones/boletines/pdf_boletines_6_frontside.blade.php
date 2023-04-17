
<?php
    if ( $mostrar_areas == 'Si')
    {
        $lbl_asigatura = 'ÁREA / ASIGNATURA';
    }else{

        $lbl_asigatura = 'ASIGNATURA';
    }

    $ancho_primera_columna = 130;
    $ancho_columna_calificacion = 70;
    $area_anterior = '';
    $cant_columnas = 2;
?>

    <div style="border-width: 10px; border-color: red; border-style: double; height: 96%; width: 100%; opacity: 0.6; position:absolute;">
    </div>

    <div class="watermark-{{$tam_hoja}} escudo">
        <img src="{{ $url_imagen_marca_agua }}" />
    </div>
        
    <br>

    @include('calificaciones.boletines.formatos.banner_colegio_con_escudo')

    <h4 style="text-align: center; padding: 10px;">INFORME {{$lbl_numero_periodo}} PERIODO AÑO LECTIVO {{ explode( "-", $periodo->fecha_desde )[0] }}</h4>
    
    <p style="padding-left: 20px;">
        <b>FULL NAME:</b>  	{{ $registro->estudiante->tercero->descripcion }}.
        <br>
        <b>GRADE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </b>	{{ $curso->descripcion }}.
    </p>
    
    <br>
            
    <table class="contenido table-bordered" style="margin-left: auto; margin-right: auto; width: 94%;">
        <thead>
            <tr>
                <th style="width:{{$ancho_primera_columna}}px; padding: 5px;">{{ $lbl_asigatura }}</th>
                <th style="padding: 5px;">LOGROS</th>
                @if($curso->maneja_calificacion==1)
                    <th style="width:{{$ancho_columna_calificacion}}px; padding: 5px;">VALORACIÓN</th>
                    <?php $cant_columnas++;  ?>
                @endif
            </tr>
        </thead>
        <tbody>
            <?php 
                $cant_caracteres = 0;
            ?>
            @foreach( $lineas_cuerpo_boletin as $linea )

                <?php 
                    if ($linea->asignacion_asignatura->asignatura->id == (int)config('calificaciones.asignatura_id_para_asistencias')) {
                        continue;
                    }
                ?>

                @include('calificaciones.boletines.fila_area')

                <tr>

                    <td style="width:{{$ancho_primera_columna}}px; padding: 5;">
                        {{ $linea->asignacion_asignatura->asignatura->descripcion }}
                    </td>

                    <td style="text-align: justify; padding: 5px 5px 5px 25px;">
                        @include('calificaciones.boletines.proposito')
                        
                        @include('calificaciones.boletines.lista_logros')

                        @include('calificaciones.boletines.formatos.etiqueta_nombre_docente')
                    </td>
                    
                    @if( $curso->maneja_calificacion == 1)
                        <td style="width:{{$ancho_columna_calificacion}}px; padding: 5; text-align: center;"> 
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

        </tbody>

    </table>
        
    @include('calificaciones.boletines.pie_pagina')

    <div class="page-break"></div>