<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

<script type="text/javascript">
  function mandar_codigo(valor){
	  window.opener.getChildVar(valor);
	  window.close();
  };
</script>

<?php
$nom_asignatura = App\Calificaciones\Asignatura::where('id','=',$id_asignatura)->value('descripcion');
?>
@if (count($logros) > 0)
	<div class="panel panel-default">
		<div class="panel-heading" align="center">
			<h3>Consulta de logros asignatura <b> {{$nom_asignatura}} </b> </h3>
			<h4 style="color:red;">Haga clic en el código del logro para asignarlo a la asignatura correspondiente</h4>
		</div>
		<div class="panel-body">
			<table class="table table-striped estudiante-table" border="1px" style="font-size:11;">
				<thead>
					<th>C&oacute;digo</th>
					<th>Descripci&oacute;n</th>
				</thead>
				<tbody>
					@foreach ($logros as $campo)
						<tr>
							<td class="table-text">
								<a href="#" onclick="mandar_codigo({{ $campo->codigo }});" class="btn btn-info btn-sm">
									{{ $campo->codigo }}
								</a>
							</td>
							<td class="table-text"><div>{{ $campo->descripcion }}</div></td>
						</tr>
					@endforeach
				</tbody>
			</table>
			
		</div>
	</div>
@else
	<h4 align="center">Ning&uacute;n logro encontrado.</h4>
@endif

<div align="center">
	<a class="btn btn-danger" href="#" onclick="window.close();"><i class="fa fa-btn fa-close"></i>Cerrar</a>
</div>