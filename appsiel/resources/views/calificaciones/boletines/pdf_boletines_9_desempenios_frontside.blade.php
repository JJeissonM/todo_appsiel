
<?php
    if ( $mostrar_areas == 'Si')
    {
        $lbl_asigatura = 'ÁREA / ASIGNATURA';
    }else{

        $lbl_asigatura = 'ASIGNATURA';
    }

    $ancho_primera_columna = 100;
    $ancho_columna_calificacion = 70;
    $area_anterior = '';
    $cant_columnas = 2;
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
            
            @include('calificaciones.boletines.pdf_boletines_9_desempenios_contenido_tbody_asignaturas')

        </tbody>

    </table>
        
    @include('calificaciones.boletines.pie_pagina')

    <div class="page-break"></div>
