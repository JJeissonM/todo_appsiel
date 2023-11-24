<div class="row">
	<div class="col-md-6">
		<div class="row campo" style="padding:5px;">
			{{ Form::bsSelect('mostrar_areas', $parametros['mostrar_areas'], 'Mostrar áreas',['No'=>'No','Si'=>'Si'],[]) }}
		</div>

		<div class="row campo" style="padding:5px;">
			{{ Form::bsSelect('mostrar_calificacion_media_areas', $parametros['mostrar_calificacion_media_areas'], 'Mostrar calificación media del área',['No','Si'],[]) }}
		</div>

		<div class="row campo" style="padding:5px;">
			{{ Form::bsSelect('mostrar_nombre_docentes', $parametros['mostrar_nombre_docentes'], 'Mostrar nombre de docentes',['No'=>'No','Si'=>'Si'],[]) }}
		</div>

		<div class="row campo" style="padding:5px;">
			{{ Form::bsSelect('mostrar_etiqueta_final', $parametros['mostrar_etiqueta_final'], 'Mostrar etiqueta al final',['No'=>'No','aprobo_reprobo'=>'Aprobó() Reprobó() Aplazó()'],[]) }}
		</div>

		<div class="row campo" style="padding:5px;">
			{{ Form::bsSelect('mostrar_logros', $parametros['mostrar_logros'], 'Mostrar logros',['1'=>'Si','0'=>'No'],[]) }}
		</div>

		<?php 
			echo campo_firma('Firma para Rector(a)', 'firma_rector');
		?>
	</div>

	<div class="col-md-6">
		<div class="row campo" style="padding:5px;">
			{{ Form::bsSelect('mostrar_escala_valoracion', $parametros['mostrar_escala_valoracion'], 'Mostrar Escala de valoración',['No'=>'No','Si'=>'Si'],[]) }}
		</div>

		<div class="row campo" style="padding:5px;">
			{{ Form::bsSelect('mostrar_fallas', $parametros['mostrar_fallas'], 'Mostrar fallas del estudiante (inasistencia)',['No','Si'],[]) }}
		</div>

		<div class="row campo" style="padding:5px;">
			{{ Form::bsSelect('mostrar_usuarios_estudiantes', $parametros['mostrar_usuarios_estudiantes'], 'Mostrar usuario de estudiantes',['No'=>'No','Si'=>'Si'],[]) }}
		</div>

		<div class="row campo" style="padding:5px;">
			{{ Form::bsSelect('mostrar_nota_nivelacion', $parametros['mostrar_nota_nivelacion'], 'Mostrar nota nivelación',[ '' => 'No', 'solo_nota_nivelacion_con_etiqueta'=>'Solo nota nivelación (con etiqueta)', 'solo_nota_nivelacion_sin_etiqueta'=>'Solo nota nivelación (sin etiqueta)','ambas_notas'=>'Ambas notas'],[]) }}
		</div>

		<div class="row campo" style="padding:5px;">
			{{ Form::bsSelect('mostrar_intensidad_horaria', $parametros['mostrar_intensidad_horaria'], 'Mostrar intensidad horaria',['1'=>'Si','0'=>'No'],[]) }}
		</div>

		<?php 
			echo campo_firma('Firma para Director(a) de grupo', 'firma_profesor');
		?>
	</div>
</div>