<style>

	img {
		padding-left:30px;
	}

	table {
		width:100%;
		border-collapse: collapse;
	}

	table.banner{
		border: 0px solid;
		border-collapse: collapse;
		border-spacing:  0;
	}

	table.banner{
		font-size: 1.2em;
	}

    th, td {
        border-bottom: 1px solid black;
        border-right: 1px solid black;
    }

    th {
        background-color: #CACACA;
    }

    td.cuadrito {
        border-left: 1px solid black;
        border-right: 1px solid black;
    }

    h3 {
        text-align:center;
    }

    div.recuadro{
        border: 1px solid black;
    }

    .matriz_dofa td{
        width: 50%;
    }

    .matriz_dofa td div{
        height: 300px;
        width: 100%;
    }

    .matriz_dofa h5 b{
        vertical-align: middle;
    }	

    .gota1 .lista{
        background-color: yellow;
        /*border-radius: 0 20% 0 20%;*/
    }

    .gota1 h5{
        background-color: yellow; 
        height: 30px; 
        width: 30%; 
        margin-bottom: -10px; 
        border-radius: 10px 10px 0 0;
        text-align: center;
    }

    .gota2 .lista{
        background-color: cyan;
        /*border-radius: 20% 0 20% 0;*/
    }

    .gota2 h5{
        background-color: cyan; 
        height: 30px; 
        width: 30%; 
        margin-bottom: -10px;
        border-radius: 10px 10px 0 0;
        text-align: center;
    }

    .gota3 .lista{
        background-color: #61de61;
        /*border-radius: 20% 0 20% 0;*/
    }

    .gota3 h5{
        background-color: #61de61; 
        height: 30px; 
        width: 30%; 
        margin-top: -10px;
        border-radius: 0 0 10px 10px;
        text-align: center;
    }

    .gota4 .lista{
        background-color: #f97672;
        /*border-radius: 0 20% 0 20%;*/
    }

    .gota4 h5{
        background-color: #f97672; 
        height: 30px; 
        width: 30%; 
        margin-top: -10px;
        border-radius: 0 0 10px 10px;
        text-align: center;
    }

    .watermark-letter {
        position: absolute;
        top: 12%;
        left: 15%;
        text-align: center;
        opacity: .2;
        z-index: -1000;
        width: 70%;
    }

    .watermark-folio {
        position: absolute;
        top: 20%;
        left: 15%;
        text-align: center;
        opacity: .2;
        z-index: -1000;
        width: 70%;
    }

    .page-break {
        page-break-after: always;
    }
</style>

<div class="container" style="width: 100%; border: 1px solid #333333;">

    
	<?php 
        $curso_label = 'Sin matricula registrada';
        $anio_lectivo_label = '';
        if($estudiante->matriculas->last() != null)
        {
            $anio_lectivo_label = $estudiante->matriculas->last()->periodo_lectivo->descripcion;

            $curso_label = $estudiante->matriculas->last()->curso->descripcion;
        }
    ?>
	
    @include('calificaciones.boletines.formatos.banner_colegio_con_escudo', ['opacity'=>0.7, 'tam_letra'=>4])

    <div class="watermark-{{$tam_hoja}} escudo">
        <img src="{{ config('matriculas.url_imagen_marca_agua') }}" />
    </div>
	
	<h4 align="center">OBSERVADOR DEL ALUMNO</h4>
    <h5 align="center" style="margin-top:-15px;">{{$anio_lectivo_label}}</h5>
    
	@include('matriculas.estudiantes.datos_basicos')

    @include('calificaciones.boletines.pie_pagina')

	<div class="page-break"></div>

    <div class="watermark-{{$tam_hoja}} escudo">
        <img src="{{ config('matriculas.url_imagen_marca_agua') }}" />
    </div>

	@include('matriculas.estudiantes.observador.valorar_aspectos_show')
	
    
    @include('calificaciones.boletines.pie_pagina')

	<div class="page-break"></div>

    <div class="watermark-{{$tam_hoja}} escudo">
        <img src="{{ config('matriculas.url_imagen_marca_agua') }}" />
    </div>
	
	<br><br>
	@include('matriculas.estudiantes.observador.novedades_y_anotaciones')

	@if( (int)config('matriculas.manejar_control_disciplinario') )
		<div class="page-break"></div>
		
		@include('matriculas.estudiantes.observador.control_disciplinario_show')
	@endif

	@if( (int)config('matriculas.manejar_matriz_dofa') )
		<div class="page-break"></div>
		
		@include('matriculas.estudiantes.observador.analisis_foda_show')
	@endif
	
    @include('calificaciones.boletines.pie_pagina')

</div>