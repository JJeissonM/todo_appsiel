<div class="row">
	<div class="col-md-6">
		<div class="row campo" style="padding:5px;">
			{{ Form::bsSelect('tam_hoja','','Tamaño hoja',['letter'=>'Carta','folio'=>'Oficio'],[]) }}
		</div>

		<?php 
			$tam_letra = [ 
							'2.5'=>'10',
							'2.75'=>'10.5',
							'3'=>'11',
							'3.25'=>'11.5',
							'3.5'=>'12',
							'3.75'=>'12.5',
							'4'=>'13',
							'4.25'=>'13.5',
							'4.5'=>'14',
							'4.75'=>'14.5',
							'5'=>'15',
							'5.25'=>'15.5',
							'5.5'=>'16'
						];
		?>

		<div class="row campo" style="padding:5px;">
			{{ Form::bsSelect( 'tam_letra',  4, 'Tamaño Letra', $tam_letra, []) }}
		</div>
	</div>

	<div class="col-md-6">
		<div class="row campo" style="padding:5px;">
			{{ Form::bsSelect('convetir_logros_mayusculas',1,'Convertir logros a mayúsculas',['No'=>'No','Si'=>'Si'],[]) }}
		</div>

		<div class="row campo" style="padding:5px;">
			<p style="width: 100%; background: #ddd; text-align: center;"> <b>Márgenes (px)</b> </p>
			<div class="row campo">
				<div class="col-md-3">
					<p> <b>Izquierdo</b> </p>
					<input type="number" min="5" max="300" value="5" class="slider" name="margen_izquierdo">
				</div>
				<div class="col-md-3">
					<p> <b>Superior</b> </p>
					<input type="number" min="5" max="300" value="5" class="slider" name="margen_superior">
				</div>
				<div class="col-md-3">
					<p> <b>Derecho</b> </p>
					<input type="number" min="5" max="300" value="5" class="slider" name="margen_derecho">
				</div>
				<div class="col-md-3">
					<p> <b>Inferior</b> </p>
					<input type="number" min="5" max="300" value="5" class="slider" name="margen_inferior">
				</div>
			</div>
		</div>
	</div>
</div>