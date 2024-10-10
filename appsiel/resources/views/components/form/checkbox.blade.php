{{ Form::label($name, $lbl, ['class' => 'col-md-3']) }}
<?php
	$lista_valores = explode(",",$value);
	//dd($lista_valores, $opciones);
	//$opciones = json_decode($opciones,true);
?>
<div class="col-md-9">
	@foreach ($opciones as $valor_opcion => $lbl_opcion)
		<label class="checkbox-inline">
			<?php $checked = false; ?>
			@foreach($lista_valores as $llave => $valor_seleccionado)
				@if( $valor_seleccionado == $valor_opcion )
					<?php $checked = true; break; ?>
				@else
					<?php $checked = false; ?>
				@endif
			@endforeach
			{{ Form::checkbox($name."[]", $valor_opcion, $checked) }}{{$lbl_opcion}}
	    </label>
	@endforeach
</div>