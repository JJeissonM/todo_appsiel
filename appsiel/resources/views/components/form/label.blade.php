<div class="form-group">
	<label class="control-label col-sm-3 col-md-3" for="{{$name}}">{{$lbl}}:</label>
	<div class="col-sm-9 col-md-9">
		{{ Form::text( $name.'_lbl', $value[0], array_merge( [ 'class' => 'col-md-9','id' => $name.'_lbl','style' => 'border: none;border-color: transparent;','disabled' => 'disabled', ], $attributes ) ) }}
	</div>	
	{{ Form::hidden( $name, $value[1] ) }}
</div>