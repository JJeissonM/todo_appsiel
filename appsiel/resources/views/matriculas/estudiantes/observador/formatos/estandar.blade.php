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
    border-bottom: 1px solid gray;
}

th {
	background-color: #CACACA;
}

td.cuadrito {
	border-left: 1px solid gray;
	border-right: 1px solid gray;
}

h3 {
	text-align:center;
}

div.recuadro{
	border: 1px solid gray;
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

.page-break {
    page-break-after: always;
}
</style>

<div class="container" style="width: 100%; border: 1px solid #333333;" id="container_formato">
	
    @if($vista != 'show')
		<table class="banner" width="100%" >
			<tr>
				<td width="250px">
					<img src="{{ asset(config('configuracion.url_instancia_cliente').'/storage/app/escudos/'.$colegio->imagen.'?'.rand(1,1000)) }}" width="160px" height="160px" />
				</td>

				<td align="center">
					<b>{{ $colegio->descripcion }}</b><br/>
					<b>{{ $colegio->slogan }}</b><br/>
					Resolución No. {{ $colegio->resolucion }}<br/>
					{{ $colegio->direccion }}<br/>
					Teléfonos: {{ $colegio->telefonos }}<br/>
				</td>
			</tr>
		</table>
	@endif
	
	<h4 align="center">OBSERVADOR DEL ALUMNO</h4>
    <h5 align="center" style="margin-top:-15px;">{{$anio_lectivo_label}}</h5>
    
	@include('matriculas.estudiantes.observador.datos_basicos_estudiante')
	@include('matriculas.estudiantes.observador.datos_adicionales_estudiante')
	@include('matriculas.estudiantes.observador.datos_basicos_padres')

    <div class="observacion">
        <b>Observación:</b>
        <br>
        @if ( $matricula_a_mostrar != null )
            {{ $matricula_a_mostrar->get_observacion_general() }}
        @endif
    </div>

	<div class="page-break"></div>

	@include('matriculas.estudiantes.observador.valorar_aspectos_show')
	
	<div class="page-break"></div>
	
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
	
</div>