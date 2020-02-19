@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	<div class="row">
		<div class="col-sm-offset-3 col-sm-6">
			<div class="panel panel-success">
				<div class="panel-heading" align="center">
					<h4>Generar listados de estudiantes</h4>
				</div>
				
				<div class="panel-body">
					{{Form::open(array('route'=>array('matriculas.estudiantes.update','listado'),'method'=>'PUT'))}}
					
						<div class="row" style="padding:5px;">
                            {{ Form::bsSelect('tipo_listado', null, 'Tipo de listado', ['1'=>'Listado de estudiantes', '2'=>'Ficha de datos básicos', '3'=>'Listado de datos básicos', '4'=>'Listado de usuarios'], []) }}
                        </div>

						<div class="row" style="padding:5px;">
                            {{ Form::bsSelect('periodo_lectivo_id', null, 'Año lectivo', $periodos_lectivos, ['required' => 'required']) }}
                        </div>

						<div class="row" style="padding:5px;">
                            {{ Form::bsSelect('sga_grado_id', null, 'Grado', $grados, []) }}
                        </div>

						<div class="row" style="padding:5px;">
                            {{ Form::bsSelect('curso_id', null, 'Curso', ['Todos'=>'Todos'], []) }}
                        </div>

                        {{--<option value="4">Cumpleaños</option>--}}

						<div class="row" style="padding:5px;">
                            {{ Form::bsSelect('tam_hoja', null, 'Tamaño hoja', ['Letter'=>'Carta','Legal'=>'Oficio'], []) }}
                        </div>

						<div class="row" style="padding:5px;">
                            {{ Form::bsSelect('orientacion', null, 'Orientación hoja', ['portrait'=>'Vertical','landscape'=>'Horizontal'], []) }}
                        </div>

						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('tam_letra',3.5,'Tamaño Letra',['2.5'=>'09','3'=>'10','3.5'=>'11','4'=>'12','4.5'=>'13','5'=>'14','5.5'=>'15'],[]) }}
						</div>
						
						<div class="form-group">
							<div class="col-sm-offset-3 col-sm-6">
								<br/>
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-btn fa-list"></i>Generar listado
								</button>
							</div>
						</div>
					{{Form::close()}}
				</div>
			</div>
		</div>
	</div>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){	
			$('#sga_grado_id').change( function(){
				var grado_id = $('#sga_grado_id').val();
				$('#div_cargando').fadeIn();
				$('#curso_id').html( '<option>Espere...</option>' );

				var url = '../../get_cursos_del_grado/' + grado_id;

				$.get( url, function( datos ) {
			        $('#div_cargando').hide();
			        $('#curso_id').html( datos );
				});
			});

			$('#tipo_listado').change( function(){
				if ($(this).val() == '3' ) {
					$('#orientacion').val('landscape');
				}else{
					$('#orientacion').val('portrait');
				}
			});
		});
	</script>
@endsection