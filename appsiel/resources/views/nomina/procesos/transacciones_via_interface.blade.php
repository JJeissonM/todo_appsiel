@extends('core.procesos.layout')

@section('titulo', 'Cargar libro de Excel para liquidación de conceptos')

@section('detalles')
	<p>
		Este proceso permite validar y cargar conceptos manuales de nómina desde la primera hoja de un libro de Excel.
		Antes de almacenar se mostrará una vista previa con el resultado de las validaciones por fila.
	</p>
@endsection

@section('formulario')
	<div class="row" id="div_formulario">
		{{ Form::open(['url'=>'nom_procesar_libro_excel','id'=>'formulario_inicial','files' => true]) }}
			<div class="col-sm-12">
				<div class="panel panel-info">
					<div class="panel-heading">
						<strong><i class="fa fa-info-circle"></i> Estructura del libro de Excel</strong>
					</div>
					<div class="panel-body">
						<p>La fila 1 debe contener estos encabezados. El orden de las columnas puede variar:</p>
						<div class="table-responsive">
							<table class="table table-bordered table-condensed">
								<thead>
									<tr>
										<th>Columna</th>
										<th>Obligatoria</th>
										<th>Contenido y validación</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td><code>numero_identificacion</code></td>
										<td>Sí</td>
										<td>Identificación del empleado, existente en la empresa del documento. Use formato Texto si contiene ceros a la izquierda.</td>
									</tr>
									<tr>
										<td><code>concepto_id</code></td>
										<td>Sí</td>
										<td>ID entero de un concepto activo cuyo modo de liquidación sea Manual.</td>
									</tr>
									<tr>
										<td><code>cantidad_horas</code></td>
										<td>Condicional</td>
										<td>Número mayor que cero cuando el valor deba calcularse a partir del salario por hora.</td>
									</tr>
									<tr>
										<td><code>valor</code></td>
										<td>Condicional</td>
										<td>Valor numérico a liquidar cuando no se informan horas. No use símbolos de moneda ni separadores de miles.</td>
									</tr>
								</tbody>
							</table>
						</div>

						<strong>Validaciones importantes:</strong>
						<ul style="margin-bottom: 0;">
							<li>Se admiten libros <code>.xlsx</code> y <code>.xls</code> de máximo 5 MB; solo se procesa la primera hoja.</li>
							<li>Los datos deben comenzar en la fila 2 y no se permiten columnas adicionales.</li>
							<li>El empleado debe tener un único contrato activo y vigente durante el período del documento.</li>
							<li>No se admiten combinaciones empleado/concepto repetidas en el libro ni conceptos ya liquidados en el documento.</li>
							<li>Debe informar <code>cantidad_horas</code> o <code>valor</code> con un número mayor que cero. Si informa ambos, prevalece la cantidad de horas.</li>
						</ul>
					</div>
				</div>
			</div>

			<div class="row" style="padding:5px;">					
				<label class="control-label col-sm-4" > <b> *Documento de liquidación: </b> </label>

				<div class="col-sm-8">
					{{ Form::select( 'nom_doc_encabezado_id', App\Nomina\NomDocEncabezado::opciones_campo_select(),null, [ 'class' => 'form-control', 'id' => 'nom_doc_encabezado_id', 'required' => 'required' ]) }}
				</div>					 
			</div>

			<div class="row" style="padding:5px;">					
				<label class="control-label col-sm-4" > <b> *Libro de Excel: </b> </label>

				<div class="col-sm-8">
					{{ Form::file('libro_excel', [ 'class' => 'form-control', 'id' => 'libro_excel', 'accept' => '.xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel', 'required' => 'required' ]) }}
				</div>					 
			</div>

			<div class="col-md-4">
				<button class="btn btn-success" id="btn_cargar"> <i class="fa fa-file-excel-o"></i> Validar libro </button>
			</div>
		{{ Form::close() }}
	</div>

	<div class="row" id="div_resultado">
			
	</div>

@endsection

@section('javascripts')
	<script type="text/javascript">

		$(document).ready(function(){

			$("#btn_cargar").on('click',function(event){
		    	event.preventDefault();

		    	if ( !validar_requeridos() )
		    	{
		    		return false;
		    	}

		 		$("#div_spin").show();
		 		$("#div_cargando").show();
				
				var form = $('#formulario_inicial');
				var url = form.attr('action');
				var datos = new FormData(document.getElementById("formulario_inicial"));

				$('#div_resultado').html('');

				$.ajax({
				    url: url,
				    type: "post",
				    dataType: "html",
				    data: datos,
				    cache: false,
				    contentType: false,
				    processData: false
				})
			    .done(function(respuesta){
					$("#div_resultado").html(respuesta).fadeIn(1000);
			    })
				.fail(function(){
					$("#div_resultado").html('<div class="alert alert-danger">No fue posible cargar el libro de Excel. Intente nuevamente.</div>').show();
				})
				.always(function(){
					$('#div_cargando').hide();
					$("#div_spin").hide();
				});
		    });

			$(document).on('click', '.btn_eliminar', function(event) {
				event.preventDefault();
				var fila = $(this).closest("tr");
				if ( confirm('¿Esta seguro de eliminar esta fila de los registros a almacenar?') )
				{
					if (parseInt(fila.find('.con_errores').text(), 10) === 0) {
						var cantidad = Math.max(0, parseInt($('#div_cantidad_registros').text(), 10) - 1);
						$('#div_cantidad_registros').text(cantidad);
						if (cantidad === 0) {
							$('#btn_almacenar_registros').hide();
						}
					}
					fila.remove();
				}
			});

			$(document).on('click', '#btn_almacenar_registros', function(event) {
				event.preventDefault();

				var table = $( '#ingreso_registros' ).tableToJSON();
				$('#lineas_registros').val(JSON.stringify(table));
				$(this).prop('disabled', true);
				$('#div_spin, #div_cargando').show();

				$('#form_almacenar_registros').submit();
				
				/*
				$("#div_resultado").fadeOut( 1000 );

				$("#div_spin").show();
		 		$("#div_cargando").show();
				
				var form = $('#form_almacenar_registros');
				var url = form.attr('action');
				var datos = new FormData(document.getElementById("form_almacenar_registros"));

				$("#div_resultado").html( '' );

				$.ajax({
				    url: url,
				    type: "post",
				    dataType: "html",
				    data: datos,
				    cache: false,
				    contentType: false,
				    processData: false
				})
			    .done(function( respuesta ){
			        $('#div_cargando').hide();
        			$("#div_spin").hide();

        			$("#div_resultado").html( respuesta );
        			$("#div_resultado").fadeIn( 1000 );
			    });
			    */
			});

		});
	</script>
@endsection
