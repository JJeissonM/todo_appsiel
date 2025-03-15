@extends('layouts.principal')

@section('webstyle')
<style>
	.page {
		padding: 5px;
		-webkit-box-shadow: 0px 0px 20px -3px rgba(0, 0, 0, 0.9);
		-moz-box-shadow: 0px 0px 20px -3px rgba(0, 0, 0, 0.9);
		box-shadow: 0px 0px 20px -3px rgba(0, 0, 0, 0.9);
		font-size: 14px;
	}

	.border {
		border: 1px solid;
		padding: 5px;
	}
</style>
@endsection

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

<div class="container-fluid">
	<div class="marco_formulario">
		<div class="row" style="padding: 2px;">
			<div class="col-md-12">
				{{ Form::open(['route'=>'cte_contratos.store','method'=>'post','class'=>'form-horizontal', 'id' => 'form_create']) }}
					
					<h4 style="border-left: 5px solid #42A3DC !important; padding: 20px; background-color: #c9e2f1;">Crear Contrato</h4>
					
					<div class="col-md-12">
						<input type="hidden" name="variables_url" value="{{$variables_url}}" />
						<input type="hidden" name="source" value="{{$source}}" />
						<input type="hidden" name="plantilla_id" value="{{$v->id}}" />
						
						<input type="hidden" name="permitir_ingreso_contrato_en_mes_distinto_al_actual" value="{{ config('contratos_transporte.permitir_ingreso_contrato_en_mes_distinto_al_actual') }}" id="permitir_ingreso_contrato_en_mes_distinto_al_actual" />

						<input type="hidden" name="bloquear_ingreso_fecha_final_planilla_en_mes_siguiente" value="{{ (int)config('contratos_transporte.bloquear_ingreso_fecha_final_planilla_en_mes_siguiente') }}" id="bloquear_ingreso_fecha_final_planilla_en_mes_siguiente" />

						<div class="col-md-12" style="padding: 5px 30px;">
							<h4 style="border-left: 5px solid #42A3DC !important; padding: 20px; background-color: #c9e2f1;">Información del Contrato</h4>
						</div>

						<div class="col-md-6" style="padding: 30px;">
							<div class="form-group">
								<label>Representante Legal (CONTRATISTA)</label>
								<input type="text" name="rep_legal" class="form-control" required="required" value="{{$e->representante_legal()}}">
							</div>
							<div class="form-group">
								<label>Contratante</label>
								<select class="form-control select2" id="contratante" name="contratante_id" onchange="manual()" required="required">
									<option value="">-- Seleccione una opción --</option>
									<option value="MANUAL">INTRODUCCIÓN MANUAL</option>
									@if($contratantes!=null)
										@foreach($contratantes as $key=>$value)
											<option value="{{$key}}">{!!$value!!}</option>
										@endforeach
									@endif
								</select>
								<input type="text" name="contratanteText" id="contratanteText" class="form-control" style="display: none; margin-top: 20px; margin-bottom: 20px;" placeholder="Nombres y apellidos del contratante">
								<input type="text" name="contratanteIdentificacion" id="contratanteIdentificacion" class="form-control" style="display: none; margin-top: 20px; margin-bottom: 20px;" placeholder="Identificación del contratante">
								<input type="text" name="contratanteDireccion" id="contratanteDireccion" class="form-control" style="display: none; margin-top: 20px; margin-bottom: 20px;" placeholder="Dirección del contratante">
								<input type="text" name="contratanteTelefono" id="contratanteTelefono" class="form-control" style="display: none; margin-top: 20px; margin-bottom: 20px;" placeholder="Teléfono del contratante">
							</div>
							<div class="form-group">
								<label>En Representación de...</label>
								<textarea required="required" class="form-control" name="representacion_de" rows="3">{{config('contratos_transporte.texto_en_representacion_de')}}</textarea>
							</div>
							<div class="form-group">
								<label>Objeto del Contrato</label>
								<textarea class="form-control" required="required" name="objeto" rows="3">Prestacion del servicio transporte especial para un grupo especifico de usuarios de transporte de personal (transporte particular).</textarea>
							</div>
						</div>

						<!-- Segunda Columna -->
						<div class="col-md-6" style="padding: 30px;">
							<div class="form-group">
								<label>Fecha Inicio Contrato</label>
								<input onchange="validar_fecha_inicio_contrato()" type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required="required"/>
							</div>
							<div class="form-group">
								<label>Fecha Terminación Contrato</label>
								<input onchange="validar_fecha_fin_contrato()" class="form-control" type="date" name="fecha_fin" id="fecha_fin" required="required"/>
							</div>
							<div class="form-group">
								<label>Fecha Firma</label>
								<input type="number" class="form-control" placeholder="Día" required="required" name="dia_contrato" />
								<input type="text" class="form-control" placeholder="Mes" name="mes_contrato" required="required"/>
								<input type="number" class="form-control" placeholder="Año" required="required" name="anio_contrato" />
							</div>
							<div class="form-group">
								<label>Convenio Consorcio Unión Temporal Con</label>
								<input type="text" class="form-control" name="convenio">
							</div>
						</div>
					</div>
						

					<div class="col-md-12">
						
						<div class="col-md-12" style="padding: 5px 30px;">
							<h4 style="border-left: 5px solid #42A3DC !important; padding: 20px; background-color: #c9e2f1;">Información del FUEC</h4>
						</div>						
						
						<div class="col-md-12" style="padding: 5px 20px; display: none;">
							<p>Fechas del FUEC igual a las del contrato.</p>
							<label class="radio-inline">
								<input type="radio" name="fechas_iguales" value="1" >Sí
							</label>
							<label class="radio-inline">
								<input type="radio" name="fechas_iguales" value="0" checked>No
							</label>
						</div>						
						
						<div class="col-md-6" style="padding: 30px;">
							
							<div class="form-group" id="div_fecha_inicio_fuec">
								<label>Fecha de Inicio FUEC</label>
								<input onchange="validar_fecha_inicio_fuec()" type="date" class="form-control" name="fecha_inicio_fuec" id="fecha_inicio_fuec" required="required"/>
							</div>
							<div class="form-group" id="div_fecha_fin_fuec">
								<label>Fecha de Terminación FUEC</label>
								<input onchange="validar_fecha_fin_fuec()" class="form-control" type="date" name="fecha_fin_fuec" id="fecha_fin_fuec" required="required"/>
							</div>

							<div class="form-group">
								<label>Nro. de Personas a Movilizar</label>
								<input type="number" class="form-control" name="nro_personas" required="required">
							</div>
							<div class="form-group">
								<label>Origen</label>
								<select class="form-control select2" name="origen" id="origen" required="required">
									@foreach($ciudades as $key=>$value)
										<option value="{{$key}}">{!!$value!!}</option>
									@endforeach
								</select>
							</div>
							<div class="form-group">
								<label>Destino</label>
								<select class="form-control select2" name="destino" id="destino" required="required">
									@foreach($ciudades as $key=>$value)
										<option value="{{$key}}">{!!$value!!}</option>
									@endforeach
								</select>
							</div>
							<div class="form-group">
								<label>Tipo de Servicio</label>
								<select class="form-control" name="tipo_servicio" required="required">
									<option value="IDA-REGRESO">IDA Y REGRESO</option>
									<option value="IDA">SOLO IDA</option>
									<option value="REGRESO">SOLO REGRESO</option>
								</select>
							</div>
							<div class="form-group">
								<label>Disponibilidad</label>
								<select class="form-control" name="disponibilidad" required="required">
									<option value="SI">SI</option>
									<option value="NO">NO</option>
								</select>
							</div>
						</div>
						
						<div class="col-md-6" style="padding: 30px;">
							<div class="form-group">
								<label>Vehículo</label>
								<a href="#" data-toggle="tooltip" data-placement="right" title="Solo se muestran los vehículos que tengan todos sus documentos en regla."> <i class="fa fa-question-circle"></i> </a>
								<select class="form-control select2" name="vehiculo_id" id="vehiculo_id" required="required" onchange="conductores()">
									@if($vehiculos!=null)
										<option value="">-- Seleccione vehículo --</option>
										@foreach($vehiculos as $key=>$value)
											<option value="{{$key}}">{!!$value!!}</option>
										@endforeach
									@else
										<option value="">No hay vehículos con documentos en regla habilitados. Si continúa, el contrato no será guardado.</option>
									@endif
								</select>
							</div>
							<div class="form-group">
								<label>Conductor 1</label>
								<select name="conductor_id[]" id="conductor1" required="required" class="form-control select2">
									<option value="">-- Seleccione opción --</option>
								</select>
							</div>
							<div class="form-group">
								<label>Conductor 2</label>
								<select name="conductor_id[]" id="conductor2" class="form-control select2">
									<option value="">-- Seleccione opción --</option>
								</select>
							</div>
							<div class="form-group">
								<label>Conductor 3</label>
								<select name="conductor_id[]" id="conductor3" class="form-control select2">
									<option value="">-- Seleccione opción --</option>
								</select>
							</div>
						</div>
						
					</div>
					<div class="form-group">
						<div class="col-md-12" style="margin-top: 50px; text-align: center;">
							<button id="btn_guardar" class="btn btn-primary" title="Guardar Contrato y FUEC"><i class="fa fa-save"></i> Guardar Contrato y FUEC</a>
						</div>
					</div>
				{{ Form::close() }}
		</div>
	</div>
</div>
@endsection


@section('scripts')
<script type="text/javascript">
	$(document).ready(function() {
		$('.select2').select2();

		$( 'input[name="fechas_iguales"]:radio' ).on('change', function(e) {
			var value_option = $('input[name="fechas_iguales"]:checked').val();

			if ( value_option == 1 )
			{
				$("#div_fecha_inicio_fuec").fadeOut();
				$("#div_fecha_fin_fuec").fadeOut();
				
				$("#fecha_inicio_fuec").removeAttr('required');
				$("#fecha_fin_fuec").removeAttr('required');
			} else {
				$("#div_fecha_inicio_fuec").fadeIn();
				$("#div_fecha_fin_fuec").fadeIn();	
				
				$("#fecha_inicio_fuec").attr('required',true);
				$("#fecha_fin_fuec").attr('required',true);			
			}

			return false;
		});
	});

	$(document).on('click', '.delete', function(event) {
		event.preventDefault();
		$(this).closest('tr').remove();
	});

	function addRow(tabla) {
		var html = "<tr><td><input type='text' class='form-control' name='identificacion[]' required='required'/></td><td><input type='text' class='form-control' name='persona[]' required='required'/></td><td><a class='btn btn-xs btn-danger delete'><i class='fa fa-trash-o'></i></a></td></tr>";
		$('#' + tabla + ' tr:last').after(html);
	}

	function manual()
	{
		if ($("#contratante").val() == '')
		{
			remove_requeridos();
			ocultar_campos_manuales();
			return false;
		}

		if ($("#contratante").val() == 'MANUAL')
		{
			mostrar_campos_manuales();
			set_requeridos();
		} else {
			remove_requeridos();
			ocultar_campos_manuales();
		}
	}

	function mostrar_campos_manuales()
	{
		$("#contratanteText").fadeIn();
		$("#contratanteIdentificacion").fadeIn();
		$("#contratanteDireccion").fadeIn();
		$("#contratanteTelefono").fadeIn();
	}

	function ocultar_campos_manuales()
	{
		$("#contratanteText").fadeOut();
		$("#contratanteIdentificacion").fadeOut();
		$("#contratanteDireccion").fadeOut();
		$("#contratanteTelefono").fadeOut();
	}

	function set_requeridos()
	{
		$("#contratanteText").prop('required',true);
		$("#contratanteIdentificacion").prop('required',true);
		$("#contratanteDireccion").prop('required',true);
		$("#contratanteTelefono").prop('required',true);
	}

	function remove_requeridos()
	{
		$("#contratanteText").prop('required',false);
		$("#contratanteIdentificacion").prop('required',false);
		$("#contratanteDireccion").prop('required',false);
		$("#contratanteTelefono").prop('required',false);
	}

	/*
	*/
	function validar_fecha_inicio_contrato()
	{
		var fecha_fin = $("#fecha_fin").val();

		if ( fecha_fin == '' )
		{
			return true;
		}

		var fecha_inicio = $("#fecha_inicio").val();

		if ( fecha_inicio > fecha_fin )
		{
			Swal.fire({
				icon: 'error',
				title: 'Oh no!',
				text: 'La fecha de inicio no puede ser mayor a la fecha final.',
				footer: 'Para cambiar esta configuración, comuníquese con soporte.'
			});

			$("#fecha_inicio").val('');

			return false;
		}

		return true;
	}

	/*
	*/
	function validar_fecha_fin_contrato()
	{
		var f = $("#fecha_fin").val();
		var arr_fecha_fin_contrato = f.split("-");
		var hoy = new Date();
		var mes_siguiente = hoy.getMonth() + 1;
		if ( mes_siguiente != parseInt( arr_fecha_fin_contrato[1] ) && $("#permitir_ingreso_contrato_en_mes_distinto_al_actual").val() == 0) {
			Swal.fire({
				icon: 'error',
				title: 'Oh no!',
				text: 'La fecha final no puede ser de un mes diferente al actual, si continua sin corregir el contrato no será guardado y perderá los datos',
				footer: 'Para cambiar esta configuración, comuníquese con soporte.'
			});

			return false;
		}

		if ( !validar_fecha_inicio_contrato() ) {
			
			console.log('NO validar_fecha_inicio_contrato');
			return false;			
		}

		return true;
	}

	/*
	*/
	function validar_fecha_inicio_fuec()
	{
		var fecha_inicio_contrato = $("#fecha_inicio").val();
		var fecha_fin_contrato = $("#fecha_fin").val();

		if ( fecha_fin_contrato == '' || fecha_inicio_contrato == '' )
		{
			Swal.fire({
				icon: 'error',
				title: 'Oh no!',
				text: 'Primero debe seleccionar las fechas de inicio y final del contrato.'
			});

			$("#fecha_inicio_fuec").val('');
			$("#fecha_fin").focus();

			return true;
		}

		var fecha_inicio_fuec = $("#fecha_inicio_fuec").val();

		if ( fecha_inicio_fuec < fecha_inicio_contrato )
		{
			Swal.fire({
				icon: 'error',
				title: 'Oh no!',
				text: 'La fecha de inicio del FUEC no puede ser menor a la fecha de inicio del contrato.',
				footer: 'Para cambiar esta configuración, comuníquese con soporte.'
			});

			$("#fecha_inicio_fuec").val('');

			return false;
		}

		if ( fecha_inicio_fuec > fecha_fin_contrato )
		{
			Swal.fire({
				icon: 'error',
				title: 'Oh no!',
				text: 'La fecha de inicio del FUEC no puede ser mayor a la fecha final del contrato.',
				footer: 'Para cambiar esta configuración, comuníquese con soporte.'
			});

			$("#fecha_inicio_fuec").val('');

			return false;
		}

		var fecha_fin_fuec = $("#fecha_fin_fuec").val();

		if ( fecha_fin_fuec == '' )
		{
			return true;
		}

		if ( fecha_inicio_fuec > fecha_fin_fuec )
		{
			Swal.fire({
				icon: 'error',
				title: 'Oh no!',
				text: 'La fecha de inicio no puede ser mayor a la fecha final.',
				footer: 'Para cambiar esta configuración, comuníquese con soporte.'
			});

			$("#fecha_inicio_fuec").val('');

			return false;
		}
		
		return true;
	}

	/*
	*/
	function validar_fecha_fin_fuec()
	{
		var fecha_fin_fuec = $("#fecha_fin_fuec").val();
		var arr_fecha_fin_fuec = fecha_fin_fuec.split("-");
		var hoy = new Date();
		var mes_siguiente = hoy.getMonth() + 1;

		if ( mes_siguiente != parseInt( arr_fecha_fin_fuec[1] ) && $("#bloquear_ingreso_fecha_final_planilla_en_mes_siguiente").val() == 1) {

			console.log('validar_fecha_fin_fuec', mes_siguiente, arr_fecha_fin_fuec[1], parseInt( arr_fecha_fin_fuec[1] ), $("#bloquear_ingreso_fecha_final_planilla_en_mes_siguiente").val() );
			Swal.fire({
				icon: 'error',
				title: 'Oh no!',
				text: 'La Fecha Final máxima debe ser el último día del mes.',
				footer: 'Para cambiar esta configuración, comuníquese con soporte.'
			});

			$("#fecha_fin_fuec").val('');

			return false;
		}

		if (!validar_fecha_inicio_fuec()) {
			return false;			
		}

		return true;
	}

	function conductores() {
		var id = $("#vehiculo_id").val();
		limpiarselect();
		$.ajax({
			type: 'GET',
			url: "{{url('')}}/" + "cte_contratos/" + id + "/conductores",
			data: {},
		}).done(function(msg) {
			var m = JSON.parse(msg);
			if (m.error == 'NO') {
				$.each(m.data, function(index, item) {
					$("#conductor1").append("<option value='" + index + "'>" + item + "</option>");
					$("#conductor2").append("<option value='" + index + "'>" + item + "</option>");
					$("#conductor3").append("<option value='" + index + "'>" + item + "</option>");
				});
			} else {
				Swal.fire({
					icon: 'error',
					title: 'Alerta!',
					text: m.mensaje
				});
			}
		});
	}

	function limpiarselect() {
		$("#conductor1 option").each(function() {
			$(this).remove();
		});
		$("#conductor2 option").each(function() {
			$(this).remove();
		});
		$("#conductor3 option").each(function() {
			$(this).remove();
		});
		$("#conductor1").append("<option value=''>-- Seleccione opción --</option>");
		$("#conductor2").append("<option value=''>-- Seleccione opción --</option>");
		$("#conductor3").append("<option value=''>-- Seleccione opción --</option>");
	}

	
	$("#btn_guardar").on('click',function(event){
		event.preventDefault();

		console.log('submit1');
		if ( !validar_requeridos() ) {
			return false;
		}

		console.log('submit2');
		if ( !validar_fecha_fin_contrato() ) {
			return false;
		}

		console.log('submit3');
		var value_option = $('input[name="fechas_iguales"]:checked').val();
		if ( value_option == 0 )
		{
			if ( !validar_fecha_fin_fuec() ) {
				return false;
			}
		}		

		console.log('submit4');
		$('#form_create').submit();
	});

</script>
@endsection