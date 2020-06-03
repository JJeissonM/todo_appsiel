<div class="form-group">
	<label class="control-label col-sm-3" for="{{$name}}">{{$lbl}}:</label>
	<div class="col-sm-9">
		{{ Form::text($name.'_aux', $value[0], array_merge( ['id'=>$name,'placeholder'=>$lbl,'autocomplete'=>'off'], $attributes ) ) }}
		<input type="hidden" name="{{$name}}" value="{{$value[1]}}">
	</div>
</div>