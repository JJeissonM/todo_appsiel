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
		&nbsp;
		<div class="row" style="padding: 2px;">
			<div class="col-md-12">
				<!-- <div class="panel panel-primary"> -->
					<h4 style="border-left: 5px solid #42A3DC !important; padding: 20px; background-color: #c9e2f1;">Crear FUEC Adicional</h4>
					<!-- <div class="panel-body"> -->
						<div class="col-md-12"><!-- -->
							{{ Form::open(['route'=>'cte_contratos_fuec_adicional.store','method'=>'post','class'=>'form-horizontal', 'id' => 'form_create']) }}
							<input type="hidden" name="variables_url" value="{{$variables_url}}" />
							<input type="hidden" name="source" value="{{$source}}" />
							<input type="hidden" name="plantilla_id" value="{{$v->id}}" />
							<input type="hidden" name="contrato_id" value="{{$contrato->id}}" />
							<input type="hidden" name="route" value="{{Input::get('route')}}" />
                            
							<input type="hidden" name="contrato_fecha_inicio" id="contrato_fecha_inicio" value="{{$contrato->fecha_inicio}}" />
							<input type="hidden" name="contrato_fecha_fin" id="contrato_fecha_fin" value="{{$contrato->fecha_fin}}" />

							<input type="hidden" name="permitir_ingreso_contrato_en_mes_distinto_al_actual" value="{{$permitir_ingreso_contrato_en_mes_distinto_al_actual}}" id="permitir_ingreso_contrato_en_mes_distinto_al_actual" />

							<div class="col-md-12" style="padding: 30px;">
								<h4 style="border-left: 5px solid #42A3DC !important; padding: 20px; background-color: #c9e2f1;">Información del Contrato</h4>
							</div>

                            <table class="table table-bordered" style="width: 100%;">
                                <tbody>
                                    <tr>
                                        <td align="right"><b>Nro. Contrato:</b></td>
                                        <td> {{ $contrato->numero_contrato }} </td>
                                    </tr>
                                    <tr>
                                        <td align="right"><b>Representante Legal (CONTRATISTA):</b></td>
                                        <td> {{ $contrato->planillacs->first()->razon_social }} </td>
                                    </tr>
                                    <tr>
                                        <td align="right"><b>CONTRATANTE:</b></td>
                                        <td>
                                            @if($contrato->contratante_id==null || $contrato->contratante_id=='null') 
                                                {{$contrato->contratanteText}} 
                                            @else 
                                                {{$contrato->contratante->tercero->descripcion}} 
                                            @endif
                                        </td>
                                        <td><b>{{ config("configuracion.tipo_identificador") }} /CC </b></td>
                                        <td>
                                            @if($contrato->contratante_id==null || $contrato->contratante_id=='null') 
                                                {{$contrato->contratanteIdentificacion}}
                                            @else 
                                                {{$contrato->contratante->tercero->numero_identificacion}} @if($contrato->contratante->tercero->tipo!='Persona natural') {{"-".$contrato->contratante->tercero->digito_verificacion}} @endif 
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="right"><b>Objeto Contrato:</b></td>
                                        <td colspan="3"> {{ $contrato->objeto }} </td>
                                    </tr>
                                    <tr>
                                        <td align="right"><b>Fecha de Inicio:</b></td>
                                        <td> {{ $contrato->fecha_inicio }} </td>
                                        <td align="right"><b>Fecha de Terminación:</b></td>
                                        <td> {{ $contrato->fecha_fin }} </td>
                                    </tr>
                                </tbody>
                            </table>
                            
							<div class="col-md-12" style="padding: 30px;">
								<h4 style="border-left: 5px solid #42A3DC !important; padding: 20px; background-color: #c9e2f1;">Información de la Planilla FUEC</h4>

                                
							<div class="col-md-6" style="padding: 30px;">
								<div class="form-group">
									<label>Fecha de Inicio</label>
									<input onchange="validar_fecha_inicio()" type="date" class="form-control" name="fecha_inicio" id="fecha_inicio" required />
								</div>
								<div class="form-group">
									<label>Fecha de Terminación</label>
									<input onchange="validar_fecha_fin()" class="form-control" type="date" name="fecha_fin" id="fecha_fin" required />
								</div>
								<div class="form-group">
									<label>Nro. de Personas a Movilizar</label>
									<input type="number" class="form-control" name="nro_personas" required>
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
									<label>Descripción del Recorrido</label>
									<textarea class="form-control" name="descripcion_recorrido" rows="2"></textarea>
								</div>
								<div class="form-group">
									<label>Tipo de Servicio</label>
									<select class="form-control" name="tipo_servicio" required>
										<option value="IDA-REGRESO">IDA Y REGRESO</option>
										<option value="IDA">SOLO IDA</option>
										<option value="REGRESO">SOLO REGRESO</option>
									</select>
								</div>
								<div class="form-group">
									<label>Disponibilidad</label>
									<select class="form-control" name="disponibilidad" required>
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
									<select name="conductor1_id" id="conductor1" required="required" class="form-control select2">
										<option value="">-- Seleccione opción --</option>
									</select>
								</div>
								<div class="form-group">
									<label>Conductor 2</label>
									<select name="conductor2_id" id="conductor2"  class="form-control select2">
										<option value="">-- Seleccione opción --</option>
									</select>
								</div>
								<div class="form-group">
									<label>Conductor 3</label>
									<select name="conductor3_id" id="conductor3" class="form-control select2">
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
							</form>
						</div>
					<!-- </div> --> <!-- Panel Body -->
				<!-- </div> -->
			</div>
		</div>
	</div>
</div>
@endsection


@section('scripts')
<script type="text/javascript">
	$(document).ready(function() {
		$('.select2').select2();
	});

	$(document).on('click', '.delete', function(event) {
		event.preventDefault();
		$(this).closest('tr').remove();
	});

	function addRow(tabla) {
		var html = "<tr><td><input type='text' class='form-control' name='identificacion[]' required /></td><td><input type='text' class='form-control' name='persona[]' required /></td><td><a class='btn btn-xs btn-danger delete'><i class='fa fa-trash-o'></i></a></td></tr>";
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

	function validar_fecha_fin() {

		var fecha_fin = $("#fecha_fin").val();
		var v = fecha_fin.split("-");
		var hoy = new Date();
		var mes = hoy.getMonth() + 1;
		if (mes != parseInt(v[1]) && $("#permitir_ingreso_contrato_en_mes_distinto_al_actual").val() == 0) {
			Swal.fire({
				icon: 'error',
				title: 'Oh no!',
				text: 'La fecha final no puede ser de un mes diferente al actual.',
				footer: 'Para cambiar esta configuración, comuníquese con soporte.'
			});

			$("#fecha_fin").focus();

			return false;
		}

		var date_contrato_fecha_fin = convert_date( $('#contrato_fecha_fin').val() );
		var date_fecha_fin = convert_date( fecha_fin );
		
		if ( date_contrato_fecha_fin < date_fecha_fin) {
			Swal.fire({
				icon: 'error',
				title: 'Oh no!',
				text: 'La fecha final no puede ser MAYOR a la Fecha Final del Contrato.'
			});
			
			$("#fecha_fin").focus();

			return false;
		}		

		var date_contrato_fecha_inicio = convert_date( $('#contrato_fecha_inicio').val() );
		
		if ( date_contrato_fecha_inicio > date_fecha_fin) {
			Swal.fire({
				icon: 'error',
				title: 'Oh no!',
				text: 'La fecha final no puede ser MENOR a la Fecha Inicial del Contrato.'
			});
			
			$("#fecha_fin").focus();

			return false;
		}

		return true;
	}

	

	function validar_fecha_inicio() {

		var fecha_inicio = $("#fecha_inicio").val();

		var date_contrato_fecha_inicio = convert_date( $('#contrato_fecha_inicio').val() );
		var date_fecha_inicio = convert_date( fecha_inicio );

		if ( date_contrato_fecha_inicio > date_fecha_inicio) {
			Swal.fire({
				icon: 'error',
				title: 'Oh no!',
				text: 'La Fecha de Inicio no puede ser MENOR a la Fecha Inicial del Contrato.'
			});
			
			$("#fecha_inicio").focus();

			return false;
		}

		var date_contrato_fecha_fin = convert_date( $('#contrato_fecha_fin').val() );

		if ( date_contrato_fecha_fin < date_fecha_inicio) {
			Swal.fire({
				icon: 'error',
				title: 'Oh no!',
				text: 'La Fecha de Inicio no puede ser MAYOR a la Fecha Final del Contrato.'
			});
			
			$("#fecha_inicio").focus();

			return false;
		}

		var date_fecha_fin = convert_date( $('#fecha_fin').val() );

		if ( date_fecha_fin < date_fecha_inicio) {
			Swal.fire({
				icon: 'error',
				title: 'Oh no!',
				text: 'La Fecha de Inicio no puede ser MAYOR a la Fecha Final.'
			});
			
			$("#fecha_inicio").focus();

			return false;
		}

		return true;
	}

	function convert_date( string_date )
	{
		var arr_string_date = string_date.split("-");
		
		var date_string_date = new Date( arr_string_date[0], arr_string_date[1] - 1, arr_string_date[2]);

		return date_string_date.setHours(0,0,0,0);
	}

	/* 
		contrato_fecha_inicio
	*/

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

		if ( !validar_requeridos() )
		{
			return false;	
		}

		if ( validar_fecha_inicio() && validar_fecha_fin()) {
			$('#form_create').submit();	
		}

		return false;
	});

</script>
@endsection