{{ Form::label($name, $lbl, ['class' => 'col-md-3']) }}
<?php
	$lista_valores = explode(",",$value);
	//dd($opciones);
	$opciones = json_decode($opciones,true);
?>
<div class="col-md-9">
	@foreach ($opciones as $valor_opcion => $lbl_opcion)
		<label class="radio-inline">
			<?php $checked = false; ?>
			@foreach($lista_valores as $llave => $valor_seleccionado)
				@if( $valor_seleccionado == $valor_opcion )
					<?php $checked = true; break; ?>
				@else
					<?php $checked = false; ?>
				@endif
			@endforeach
			{{ Form::radio($name."[]", $valor_opcion, $checked, $attributes ) }} {{ $lbl_opcion }}
	    </label>
	@endforeach
</div>