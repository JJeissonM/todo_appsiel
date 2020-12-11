<div class="row">
	<div class="col-md-6">
		<div class="row" style="padding:5px;">
			{{ Form::bsSelect('mostrar_areas',1,'Mostrar áreas',['No'=>'No','Si'=>'Si'],[]) }}
		</div>

		<div class="row" style="padding:5px;">
			{{ Form::bsSelect('mostrar_nombre_docentes',1,'Mostrar nombre de docentes',['No'=>'No','Si'=>'Si'],[]) }}
		</div>

		<div class="row" style="padding:5px;">
			{{ Form::bsSelect('mostrar_etiqueta_final',1,'Mostrar etiqueta al final',['No'=>'No','aprobo_reprobo'=>'Aprobó() Reprobó() Aplazó()'],[]) }}
		</div>

		<?php 
			echo campo_firma('Firma para Rector(a)', 'firma_rector');
		?>
	</div>

	<div class="col-md-6">
		<div class="row" style="padding:5px;">
			{{ Form::bsSelect('mostrar_escala_valoracion',1,'Mostrar Escala de valoración',['No'=>'No','Si'=>'Si'],[]) }}
		</div>

		<div class="row" style="padding:5px;">
			{{ Form::bsSelect('mostrar_usuarios_estudiantes',1,'Mostrar usuario de estudiantes',['No'=>'No','Si'=>'Si'],[]) }}
		</div>

		<div class="row" style="padding:5px;">
			<br><br><br>
		</div>

		<?php 
			echo campo_firma('Firma para Director(a) de grupo', 'firma_profesor');
		?>
	</div>
</div>