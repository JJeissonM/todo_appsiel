{{ Form::label( $name.'_lbl', $lbl, [ 'class' => 'col-md-3' ] ) }}
{{ Form::text( $name.'_lbl', $value[0], array_merge( [ 'class' => 'col-md-9','id' => $name.'_lbl','style' => 'border: none;border-color: transparent;','disabled' => 'disabled', ], $attributes ) ) }}
{{ Form::hidden( $name, $value[1] ) }}