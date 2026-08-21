@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')


	<div class="row">
		<div class="col-md-8 col-md-offset-2" style="background-color: white;border: 1px solid #d9d7d7;box-shadow: 5px 5px gray;">
		    <h4 style="color: gray;">Ingreso de registros de nómina</h4>
		    <hr>
			{{ Form::open( array( 'url'=>'nomina/crear_registros2?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'), 'id' => 'form_filtros_registros' ) ) }}

				<div class="row" style="padding:5px;">
					{{ Form::bsSelect('nom_doc_encabezado_id', old('nom_doc_encabezado_id'), 'Seleccionar Nómina', $documentos, ['required'=>'required']) }}
				</div>

				<div class="row" style="padding:5px;">
					{{ Form::bsSelect('grupo_empleado_id', '', 'Grupo de empleados', ['' => 'Todos los grupos'], ['disabled' => 'disabled']) }}
				</div>

				<div class="row" style="padding:5px;">
					{{ Form::bsSelect('cargo_id', '', 'Cargo', ['' => 'Todos los cargos'], ['disabled' => 'disabled']) }}
				</div>

				<div class="row" style="padding:5px;">
					{{ Form::bsSelect('nom_contrato_id', '', 'Empleado del documento', ['' => 'Todos los empleados'], ['disabled' => 'disabled']) }}
					<div class="col-sm-9 col-sm-offset-3">
						<span id="estado_filtros" class="help-block">Seleccione primero el documento de nómina.</span>
					</div>
				</div>

				<div class="row" style="padding:5px;">
					{{ Form::bsSelect('nom_concepto_id', old('nom_concepto_id'), 'Seleccionar concepto', $conceptos, ['required'=>'required']) }}
				</div>
								
				<div class="form-group">
					<div class="col-sm-offset-3 col-sm-6">
						<br/>
						<button type="submit" class="btn btn-primary" id="btn_continuar">
							<i class="fa fa-btn fa-arrow-right"></i>Continuar
						</button>
						<br/><br/>
					</div>
				</div>
				
				<br/><br/>
			{{ Form::close() }}

		</div>
	</div>
	<br/><br/>	
@endsection


@section('scripts')
	<script>
		$(document).ready(function(){
			var empleadosDocumento = [];
			var seleccionInicial = {
				grupo: {{ (int) old('grupo_empleado_id') }},
				cargo: {{ (int) old('cargo_id') }},
				empleado: {{ (int) old('nom_contrato_id') }}
			};

			function llenarSelect($select, opciones, textoTodos) {
				$select.empty().append($('<option>', { value: '', text: textoTodos }));
				$.each(opciones, function(indice, opcion) {
					$select.append($('<option>', { value: opcion.id, text: opcion.texto }));
				});
				$select.prop('disabled', false);
			}

			function filtrarEmpleados(valorInicial) {
				var grupoId = parseInt($('#grupo_empleado_id').val(), 10) || 0;
				var cargoId = parseInt($('#cargo_id').val(), 10) || 0;
				var opciones = [];

				$.each(empleadosDocumento, function(indice, empleado) {
					if (grupoId && parseInt(empleado.grupo_empleado_id, 10) !== grupoId) {
						return;
					}
					if (cargoId && parseInt(empleado.cargo_id, 10) !== cargoId) {
						return;
					}
					opciones.push({ id: empleado.id, texto: empleado.texto });
				});

				llenarSelect($('#nom_contrato_id'), opciones, 'Todos los empleados (' + opciones.length + ')');
				if (valorInicial) {
					$('#nom_contrato_id').val(valorInicial);
				}
				$('#estado_filtros').text(opciones.length ?
					'Seleccione un empleado o deje la opción “Todos” para registrar el concepto al grupo filtrado.' :
					'No hay empleados que cumplan los filtros seleccionados.');
			}

			function limpiarFiltros() {
				empleadosDocumento = [];
				llenarSelect($('#grupo_empleado_id'), [], 'Todos los grupos');
				llenarSelect($('#cargo_id'), [], 'Todos los cargos');
				llenarSelect($('#nom_contrato_id'), [], 'Todos los empleados (0)');
				$('#grupo_empleado_id, #cargo_id, #nom_contrato_id').prop('disabled', true);
			}

			function cargarFiltrosDocumento(documentoId) {
				limpiarFiltros();
				if (!documentoId) {
					$('#estado_filtros').text('Seleccione primero el documento de nómina.');
					return;
				}

				$('#btn_continuar').prop('disabled', true);
				$('#estado_filtros').removeClass('text-danger').html('<i class="fa fa-spinner fa-spin"></i> Cargando empleados del documento...');

				$.get('{{ url('nomina/documentos') }}/' + documentoId + '/filtros-registros')
					.done(function(respuesta) {
						empleadosDocumento = respuesta.empleados || [];
						llenarSelect($('#grupo_empleado_id'), respuesta.grupos || [], 'Todos los grupos');
						llenarSelect($('#cargo_id'), respuesta.cargos || [], 'Todos los cargos');
						if (seleccionInicial.grupo) {
							$('#grupo_empleado_id').val(seleccionInicial.grupo);
						}
						if (seleccionInicial.cargo) {
							$('#cargo_id').val(seleccionInicial.cargo);
						}
						filtrarEmpleados(seleccionInicial.empleado);
						seleccionInicial = { grupo: 0, cargo: 0, empleado: 0 };
						$('#btn_continuar').prop('disabled', false);
					})
					.fail(function(respuesta) {
						var mensaje = respuesta.responseJSON && respuesta.responseJSON.mensaje ? respuesta.responseJSON.mensaje : 'No fue posible cargar los empleados del documento.';
						$('#estado_filtros').addClass('text-danger').text(mensaje);
					});
			}

			$("#nom_doc_encabezado_id").focus();

			$("#nom_doc_encabezado_id").on('change',function(){
				cargarFiltrosDocumento($(this).val());
			});

			$('#grupo_empleado_id, #cargo_id').on('change', function() {
				filtrarEmpleados(0);
			});
			
			$("#nom_concepto_id").on('change',function(){
				$("#btn_continuar").focus();
			});

			if ($('#nom_doc_encabezado_id').val()) {
				cargarFiltrosDocumento($('#nom_doc_encabezado_id').val());
			}


		});
	</script>
@endsection
