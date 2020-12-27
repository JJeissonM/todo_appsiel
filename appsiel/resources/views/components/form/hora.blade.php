{{ Form::label($name, $lbl, ['class' => 'col-md-3']) }}
{{ Form::time($name, $value, array_merge(['class' => 'form-control col-md-9'], $attributes)) }}