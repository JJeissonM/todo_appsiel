<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

<div class="container-fluid">
	<div class="marco_formulario">
	    <div class="container">
		    <h4>Consulta</h4>
		    <hr>

		    <div class="row">
		    	<div class="col-md-12">
		    		<div class="row" style="padding:5px;"> <b> ID: </b> {{$registro->id}} </div>
					
					<?php

						VistaController::campos_dos_colummnas($form_create['campos'], 'show');
					?>
		    	</div>
		    </div>
		</div>
		<br/><br/>

		@if($tabla!='')
			<br/><br/>
			<ul class="nav nav-tabs">
			  <li class="active"><a href="#">{{ $titulo_tab }}</a></li>
			</ul>
			
			<br/><br/>
			
			{!! $tabla !!}

			<br/><br/>

			<?php
				switch ( $opciones[''] ) 
				{
					case 'PQR':
			?>
						@include('propiedad_horizontal.pqr_form_show')
			<?php
						break;

					case 'Cursos':
			?>
						@include('matriculas.cursos_form_show')
			<?php
						break;

					case 'AsignarVariableExamen':
			?>
						@include('consultorio_medico.asignar_variable_examen_form')
			<?php
						break;
					
					default:
			?>
						{{ Form::open(array('url'=>'web/guardar_asignacion')) }}
							<div class="row">
								<div class="col-md-8 col-md-offset-2" style="vertical-align: center; border: 1px solid gray;">
									<h3>Asignar nuevo</h3>
									<div class="row">
										<div class="col-md-6">
											{{ Form::bsSelect('registro_modelo_hijo_id',null,$titulo_tab,$opciones,['class'=>'combobox']) }}
										</div>
										<div class="col-md-6">
											{{ Form::bsText('nombre_columna1',null,'Orden',[]) }}
										</div>
										{{ Form::hidden('registro_modelo_padre_id',$registro_modelo_padre_id) }}

										{{ Form::hidden('url_id',Input::get('id'))}}
										{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}
									</div>
									<div align="center">
										<br/>
										{{ Form::submit('Guardar', array('class' => 'btn btn-primary btn-sm')) }}
									</div>
									<br/><br/>
								</div>
							</div>
						{{ Form::close() }}
			<?php
						break;
				}
			?>

			<br/><br/>

		@endif

	</div>
</div>
<br/><br/>