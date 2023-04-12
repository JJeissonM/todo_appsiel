<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">


	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.3/jquery.min.js"
		integrity="sha384-I6F5OKECLVtK/BL+8iSLDEHowSAfUo76ZL9+kGAgTRdiByINKJaqTPH/QVNS1VDb" crossorigin="anonymous">
	</script>

	<!-- DataTable -->
	<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
	<script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.flash.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
	<script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.html5.min.js"></script>
	<script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.print.min.js"></script>

<script type="text/javascript">
  function mandar_codigo(valor){
	  window.opener.getChildVar(valor);
	  window.close();
  };
</script>

<?php
	$nom_asignatura = App\Calificaciones\Asignatura::where('id','=',$id_asignatura)->value('descripcion');
	$curso = App\Matriculas\Curso::find($curso_id);
?>
@if (count($logros) > 0)
	<div class="panel panel-default">
		<div class="panel-heading" align="center">
			<h3>
				Logros adicionales para la asignatura <b> {{$nom_asignatura}} </b>
				<br>
				Curso <b> {{$curso->descripcion}} </b>
			</h3>
			<h4 style="color:red;">Haga clic en el código del logro para asignarlo al estudiante correspondiente.</h4>
			<h5 style="color:purple;">Nota: Estos son logros particulares usados para estudiantes específicos.</h5>
		</div>
		<div class="panel-body">
			<table class="table table-striped estudiante-table" border="1px" style="font-size:11;" id="myTable">
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

<script type="text/javascript">
	$(document).ready(function() {
		$('#myTable').DataTable({
								dom: 'Bfrtip',
								"paging": false,
								buttons: [],
								order: [
									[0, 'desc']
								],
								"language": {
									            "search": "Buscar",
									            "zeroRecords": "Ningún registro encontrado.",
									            "info": "Mostrando página _PAGE_ de _PAGES_",
									            "infoEmpty": "Tabla vacía.",
									            "infoFiltered": "(filtrado de _MAX_ registros totales)"
									        }
							});
	});
</script>