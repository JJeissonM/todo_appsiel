
<?php
    if ( $mostrar_areas == 'Si')
    {
        $lbl_asigatura = 'ÁREA / ASIGNATURA';
    }else{

        $lbl_asigatura = 'ASIGNATURA';
    }

    //$ancho_columna_asignatura = 130;ancho_columna_asignatura
    $ancho_columna_calificacion = 70;
    $area_anterior = '';
    $cant_columnas = 1;
    $estilo_advertencia = 'style="background-color:#F08282; color:white;"';
?>

    <div class="watermark-{{$tam_hoja}} escudo">
        <img src="{{ $url_imagen_marca_agua }}" />
    </div>
        
    <br>

    @include('calificaciones.boletines.formatos.banner_colegio_con_escudo')

    <h4 style="text-align: center; padding: 10px;">INFORME {{$lbl_numero_periodo}} PERIODO AÑO LECTIVO {{ explode( "-", $periodo->fecha_desde )[0] }}</h4>
    
    @include('calificaciones.boletines.formatos.tabla_datos_estudiante_grado')
    
    <br>
            
    <table class="contenido table-bordered" style="margin-left: auto; margin-right: auto; width: 94%;">
        @include('calificaciones.boletines.pdf_boletines_9_desempenios_encabezados_tabla')
        <tbody>
            <?php 
                $cant_caracteres = 0;
            ?>
            @foreach( $lineas_cuerpo_boletin as $linea )
                <?php 
                    if ($linea->asignatura_id == (int)config('calificaciones.asignatura_id_para_asistencias')) {
                        continue;
                    }
                    
                    $is_the_first = true;
                    
                    $asignatura = $linea->asignacion_asignatura->asignatura;

                    $logros_asignatura = $logros->where( 'asignatura_id', $asignatura->id )->all();

                    $cant_logros = count( $logros_asignatura );

                ?>

                @foreach ( $logros_asignatura as $logro )
                    <tr>
                        @if($is_the_first)
                            <td rowspan="{{ $cant_logros }}" style="width:{{$ancho_columna_asignatura}}px; font-size:12px; vertical-align: middle;" width="250px" >
                                <b> {{ $asignatura->descripcion }}</b>
                            </td>
                            <?php
                                $is_the_first = false;
                            ?>						
                        @endif
                        <td style="text-align: left;"> 
                            {{ $logro->descripcion }} 
                        </td>

                        <?php
                            $valor_desempenio = $todas_las_calificaciones->where('matricula_id', $registro->matricula->id )->where('logro_id', $logro->id)->first();
                        ?>

                        @if( $valor_desempenio == null )                
                            <td {!! $estilo_advertencia !!}>--</td>
                        @else
                            <td> {{ $valor_desempenio->escala_valoracion->nombre_escala }} </td>
                        @endif

                    </tr>
                @endforeach

                @if($cant_logros == 0)
                    <tr>
                        <td style="font-size:12px; vertical-align: middle;" width="250px" >
                            <b> {{ $asignatura->descripcion }}</b>
                        </td>
                        <td colspan="2" {!! $estilo_advertencia !!}>
                            No hay logros registrados en este periodo.
                        </td>
                    </tr>
                @endif
            @endforeach {{--  End For Each Asignatura --}}

            @include('calificaciones.boletines.formatos.fila_observaciones')

            @include('calificaciones.boletines.formatos.fila_etiqueta_final')

        </tbody>

    </table>
        
    @include('calificaciones.boletines.mostrar_usuarios_estudiantes')
    
    @include('calificaciones.boletines.seccion_firmas')
    
    @include('calificaciones.boletines.pie_pagina')
    
    @if($with_page_breaks)
        <div class="page-break"></div>	
    @endif