@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	<div class="row">
		<div class="col-sm-offset-2 col-sm-8">
			<div class="panel panel-success">
				<div class="panel-heading" align="center">
					<h4>Creación de una nueva matricula</h4>
				</div>
				
				<div class="panel-body">
					<form action="{{ url('matriculas') }}" method="POST" class="form-horizontal">
						{{ csrf_field() }}

						<input type="hidden" name="id_colegio" id="id_colegio" value="{{ Auth::user()->id_colegio }}">

						<!-- estudiante nombres 
						-->
						<div class="form-group">
							<label for="tipo_doc" class="col-sm-5 control-label">Ingrese documento de identidad del candidato</label>
							<div class="col-sm-5">
								<input type="text" name="doc_identidad" id="doc_identidad" class="form-control" autocomplete="off">
								<div id="mensaje"></div>
							</div>
							<br/><br/>
						</div> 

						<!--
						<div class="form-group">
							<label for="tipo_doc" class="col-sm-5 control-label">Ingrese documento de identidad del estudiante</label>
							<div class="col-sm-5">
								{{ Form::select('estudiante_id', $estudiantes, null, [ 'class' => 'combobox', 'id' => 'estudiante_id']) }}
								<div id="mensaje"></div>
							</div>
							<br/><br/>
						</div> 
						-->
						
				        <!-- Add matricula Button -->
						<div class="form-group">
							<div class="col-sm-offset-4 col-sm-6">
								<a href="#" class="btn btn-success" id="btn_crear_est">
									<i class="fa fa-btn fa-forward"></i> Crear Estudiante
								</a>
							</div>

						</div>
					</form>
					
					<table class="table table-striped estudiante-table" id="tbl_estudiante">
						<tr>
							<td>Nombre</td>
							<td>Fecha nacimiento</td>
							<td>Direcci&oacute;n</td>
							<td>Barrio</td>
							<td>Tel&eacute;fono</td>
							<td>&nbsp;</td>
						</tr>
							<tr>
								<td class="table-text"><div id="lbl_nombre"></div></td>
								<td class="table-text"><div id="lbl_fecha_nacimiento"></div></td>
								<td class="table-text"><div id="lbl_direccion"></div></td>
								<td class="table-text"><div id="lbl_barrio"></div></td>
								<td class="table-text"><div id="lbl_telefono"></div></td>
							</tr>
							<tr>
								<td class="table-text" colspan="5">
									<div id="lbl_curso"></div>
									<a class="btn btn-primary btn-xs btn-detail" href="#" id="btn_matricular"><i class="fa fa-btn fa-plus"></i> Matricular</a>
								</td>
							</tr>
					</table>

					{{Form::open(array('route'=>array('matriculas.update','validar_est'),'method'=>'PUT','id'=>'form-buscar'))}}
						{!! Form::hidden('doc_est','asd', array('id' => 'doc_estudiante')) !!}
					{!! Form::close() !!}
				</div>
			</div>
		</div>
	</div>	
@endsection

@section('scripts')
	<script>
		$(document).ready(function(){
			$('#btn_crear_est').hide();
			$('#tbl_estudiante').hide();
			$('#doc_identidad').focus();
			
			$('#doc_identidad').keyup(function(){
				$('#div_cargando').show();
				var documento = $("#doc_identidad").val();
				//alert(documento);
				var form = $('#form-buscar');
				var url = form.attr('action');
				$("#doc_estudiante").val(documento);
				data = form.serialize();
				$.post(url,data,function(result){
					$('#div_cargando').hide();					
					var vec = result.split("a3p0");
					//var vec2 = vec[9].split("-");
					if(vec[0]!='9999999999'){
						// Si el estudiante existe
						$('#btn_crear_est').hide();
						$('#tbl_estudiante').show();
						$('#mensaje').html("<i class='fa fa-check-square-o'></i> Documento ya está inscrito. Puede continuar.");
						$('#lbl_nombre').html(vec[1]);
						$('#lbl_fecha_nacimiento').html(vec[7]);
						$('#lbl_direccion').html(vec[4]);
						$('#lbl_barrio').html(vec[5]);
						$('#lbl_telefono').html(vec[6]);
						if(vec[17]=="Sin matricula"){
							$('#lbl_curso').html("El candidato NO tiene matriculas activas. Presione el botón <b>Matricular</b> para crear una nueva.");
						}else{
							$('#lbl_curso').html("<i class='fa fa-btn fa-warning'></i>El candidato <b>YA</b> se encuentra matriculado en el curso <b>"+vec[17]+"</b></br>Al presionar el botón <b>Matricular</b> se inactivará la matricula anterior y se creará una nueva.");
						}
						
						
						//$('#btn_editar_est').attr('href','../estudiantes/modificar/'+vec[0]);
						//$('#btn_matricular').show(1000);
						$('#btn_matricular').attr('href','../matriculas/crear_nuevo/'+vec[0]+'?id=1&return=matriculas?id=1');
						
                    }else{
                    	var link = ' Haga clic <a href="../web/create?id=1&id_modelo=29"> aquí</a> para realizar la inscripción.'
						$('#mensaje').html("Candidato NO está inscrito." + link);
						//$('#btn_crear_est').attr('href','../matriculas/estudiantes/nuevo/'+documento+'/Si?id=1');
						
						//$('#btn_matricular').hide();
						$('#tbl_estudiante').hide();
						//$('#btn_crear_est').show();
					}
				});
			});
		});
	</script>
@endsection