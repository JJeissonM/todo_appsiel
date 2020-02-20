@extends('layouts.principal')

@section('content')

	{{ Form::bsMigaPan($miga_pan) }}

	@if(isset($url_crear))
		&nbsp;&nbsp;&nbsp;{{ Form::bsBtnCreate( $url_crear ) }}
	@endif
	<hr>

	@include('layouts.mensajes')

	<div class="table-responsive" id="table_content">
		<table class="table table-bordered table-striped" id="tabla_pacientes">
			{{ Form::bsTableHeader($encabezado_tabla) }}
		</table>
	</div>
@endsection

@section('scripts')
	<script>
		var SITEURL = '{{URL::to('')}}';

		$(document).ready( function () {

			$('#tabla_pacientes').DataTable( {
				dom: 'Bfrtip',
		        buttons: [
		            'excel', 'pdf'
		        ],
				"processing": true,
		        "serverSide": true,
		        "ajax": {
		            "url": SITEURL + "/ajax_datatable",
		            "data": function ( d ) {
		                d.id_modelo = getParameterByName('id_modelo');
		                d.id = getParameterByName('id');
		                // d.custom = $('#myInput').val();
		                // etc
		            }
		        },
		        columns: [
							{data: 'nombre_completo', name: 'nombre_completo' },
							{ data: 'numero_identificacion', name: 'numero_identificacion' },
							{ data: 'codigo_historia_clinica', name: 'codigo_historia_clinica' },
							{ data: 'fecha_nacimiento', name: 'fecha_nacimiento' },
							{data: 'genero', name: 'genero', orderable: false},
							{ data: 'grupo_sanguineo', name: 'grupo_sanguineo' },
							{data: 'action', name: 'action', orderable: false, searchable: false}
		               ],
		        order: [[0, 'asc']]
		    } );


		    function getParameterByName(name) {
			    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
			    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
			    results = regex.exec(location.search);
			    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
			}

		});

	</script>
@endsection