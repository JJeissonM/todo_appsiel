<?php
	if( array_key_exists('class',$attributes) ){
		$attributes['class'] = 'form-control'.' '.$attributes['class'];
	}else{
		$attributes['class'] = 'form-control';
	}
	
	if ( !is_array( $opciones ) )
	{
		$opciones = [];
	}
?>

<div class="form-group">
	<label class="control-label col-sm-3" for="{{$name}}"><?php echo $lbl?>:</label>
	<div class="col-sm-9">
		{{ Form::select($name, $opciones, $value, array_merge( [ 'id' => $name ], $attributes )) }}
	</div>
</div>

