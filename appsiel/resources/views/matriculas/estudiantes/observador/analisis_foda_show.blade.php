<?php 
	$registros_analisis = App\Matriculas\FodaEstudiante::where('id_estudiante',$estudiante->id)->get();
?>
<h2>Análisis DOFA</h2>
<hr>

@include('terceros.analisis_dofa.matriz')

<br/><br/>