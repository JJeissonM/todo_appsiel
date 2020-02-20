{{ Form::label($name, $lbl, ['class' => 'col-md-3']) }}
{{ Form::select($name, $opciones, $value, array_merge(['class' => 'col-md-8','id'=>$name,'style'=>'border: none;border-color: transparent;border-bottom: 1px solid gray;'], $attributes))}}
<div class="col-md-1">
	<a class="btn btn-primary btn-sm" href="{{ url('#') }}"><i class="fa fa-btn fa-plus"></i></a> 	
</div>