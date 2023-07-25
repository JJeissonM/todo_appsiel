<?php 
	$registros_analisis = App\Matriculas\FodaEstudiante::where('id_estudiante',$estudiante->id)->get();
?>

<h3 align="center"> ANÁLISIS DOFA </h3>

@include('terceros.analisis_dofa.matriz')

<br/><br/>