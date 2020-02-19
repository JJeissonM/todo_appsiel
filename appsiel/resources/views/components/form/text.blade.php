<div class="form-group">
	<label class="control-label col-sm-3" for="{{$name}}">{{$lbl}}:</label>
	<div class="col-sm-9">
		{{ Form::text($name, $value, array_merge(['class' => 'form-control','id'=>$name,'placeholder'=>$lbl,'autocomplete'=>'off'], $attributes)) }}
	</div>
</div>