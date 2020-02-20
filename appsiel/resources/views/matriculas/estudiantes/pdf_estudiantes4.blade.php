<style>
table {
	width: 100%;
	font-size: 13px;
}

th, td {
    border-bottom: 1px solid #ddd;
}

th {
	background-color: #CACACA;
}

td.celda {
	width: 50px;
	border-left: 1px solid #ddd;
	border-right: 1px solid #ddd;
}

h3 {
	text-align:center;
}

div.recuadro{
	
}

.page-break {
    page-break-after: always;
}
</style>
<div class="container">
	@for($k=0;$k < count($estudiantes) ;$k++)
		<!-- TITULOS -->
		<div align="center"> <b> Lista de usuarios de estudiantes </b> </div>
		<b>Grado: </b> {{ $estudiantes[$k]['grado'] }}
		<b>Curso: </b> {{ $estudiantes[$k]['curso'] }}
        <br><br>
				@foreach ($estudiantes[$k]['listado'] as $registro)
            		<table class="table">
            				<tr>
            					<td colspan="2"><b>Estudiante: </b> {{ $registro->nombre_completo }} </td>
            				</tr>
            				<tr>
            					<td class="celda1" width="400px"> 
            					    <b>Enlace plataforma: </b> {{ url('inicio') }}
            					    <br>
            					    <b>Usuario: </b>{{ $registro->email }}
            					    <br>
            					    <b>Contraseña: </b>colombia1
            					</td>
            					<td>
            					    NOTA: Debe cambiar la contraseña. Ingresando en la parte superior derecha. Hace clic en el <b>Nombre del estudiante</b>, luego en <b>Perfil</b> y luego en <b>Cambiar Contraseña</b>
            					</td>
            				</tr>
            		</table>
            	@endforeach
		<div class="page-break"></div>
	@endfor
</div>