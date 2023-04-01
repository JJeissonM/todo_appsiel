<h4 align="center">Reporte de fallas</h4>
<h5 align="center">Curso {{$curso->descripcion}} </h5>
<div class="row">
        <div class="col-sm-4">
            <b>Fecha inicial: </b> {{ $fecha_inicial }}
        </div>
        <div class="col-sm-4">
           <b> Fecha final: </b> {{$fecha_final }}
        </div>
        <div class="col-sm-4">
            &nbsp;
        </div>
</div>
<div class="table-responsive">
<table class="table table-bordered table-striped">
	<thead>
	    <tr>
	        <th>Estudiante</th>
	        <th>Cantidad de fallas</th>
	    </tr>
	</thead>
	<tbody>
	    <?php $i=0; ?>
	    @foreach ($registros as $fila)	    
	    	<tr>
	            <td> {{ App\Matriculas\Estudiante::get_nombre_completo($fila->id_estudiante) }} </td>
	            <td> {{ $fila->cantidad }} </td>
	        </tr>
	        <?php $i += $fila->cantidad; ?>
	    @endforeach
	    	<tr>
	            <td> Total fallas</td>
	            <td> {{ $i }} </td>
	        </tr>
	</tbody>
</table>