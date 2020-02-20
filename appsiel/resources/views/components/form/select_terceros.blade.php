{{ Form::label($name, $lbl,[ 'class'=>'col-md-3']) }}
	<select name="{{ $name }}" id="{{ $name }}" class="col-md-9">
		<option value="9999"></option>
		@foreach ($opciones as $opcion)
			<option value="<?php echo $opcion->id;?>" <?php if($value==$opcion->id)echo "selected";?>>
				<?php echo $opcion->nombre1." ".$opcion->otros_nombres." ".$opcion->apellido1." ".$opcion->apellido2;?>
			</option>
		@endforeach
	</select>