<div class="form-group" style="padding-left: 5px;">
	<label class="control-label" for="{{$name}}" style="padding-left: 5px;"> {{ $lbl }}: </label>
	<div class="col-sm-12">
		{{ Form::textarea($name, $value, array_merge(['class' => 'form-control', 'rows' => '4','placeholder'=>$lbl], $attributes)) }}
	</div>
</div>