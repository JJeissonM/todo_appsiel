@if( !is_null($doc_encabezado->datos_auxiliares_estudiante) )
<?php 
		function nombre_mes($numero_mes){
	    	switch($numero_mes){
	            case '01':
	                $mes="Enero";
	                break;
	            case '02':
	                $mes="Febrero";
	                break;
	            case '03':
	                $mes="Marzo";
	                break;
	            case '04':
	                $mes="Abril";
	                break;
	            case '05':
	                $mes="Mayo";
	                break;
	            case '06':
	                $mes="Junio";
	                break;
	            case '07':
	                $mes="Julio";
	                break;
	            case '08':
	                $mes="Agosto";
	                break;
	            case '09':
	                $mes="Septiembre";
	                break;
	            case '10':
	                $mes="Octubre";
	                break;
	            case '11':
	                $mes="Noviembre";
	                break;
	            case '12':
	                $mes="Diciembre";
	                break;
	            default:
	                $mes="";
	                break;
	        }
	        return $mes;
	    }
	?>
	<br>
	<div style="display: inline;">
		<b>Estudiante:</b> {{ $doc_encabezado->datos_auxiliares_estudiante->matricula->estudiante->tercero->descripcion }} &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp; <b>Curso: </b> {{ $doc_encabezado->datos_auxiliares_estudiante->matricula->curso->descripcion }}
		<br>
		<b>Concepto:</b> {{ $doc_encabezado->datos_auxiliares_estudiante->cartera_estudiante->concepto->descripcion }} &nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp; <b>Mes: </b> {{ nombre_mes( explode( '-', $doc_encabezado->datos_auxiliares_estudiante->cartera_estudiante->fecha_vencimiento)[1] ) }} / {{ explode( '-', $doc_encabezado->datos_auxiliares_estudiante->cartera_estudiante->fecha_vencimiento)[0] }}
	</div>
@endif